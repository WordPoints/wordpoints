<?php

/**
 * Breaking core updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Updater for core when a backward-compatibility breaking update occurs.
 *
 * Checks that all extensions are compatible with the new version.
 *
 * @since 2.4.0
 */
class WordPoints_Updater_Core_Breaking extends WordPoints_Routine {

	/**
	 * A list of extensions which have already been checked.
	 *
	 * @since 2.4.0
	 *
	 * @var array
	 */
	protected $checked_extensions = array();

	/**
	 * Whether we are doing the check for the network or per-site extensions.
	 *
	 * @since 2.4.0
	 *
	 * @var bool
	 */
	protected $doing_network = false;

	/**
	 * @since 2.4.0
	 */
	public function __construct() {
		$this->network_wide = is_wordpoints_network_active();
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {

		$this->before();

		parent::run();

		$this->after();
	}

	/**
	 * @since 2.4.0
	 */
	protected function before() {

		/**
		 * WordPress's filesystem API.
		 *
		 * @since 2.4.0
		 */
		require_once ABSPATH . 'wp-admin/includes/file.php';

		if ( ! WP_Filesystem( false, ABSPATH ) ) {
			return;
		}

		$this->maintenance_mode();
	}

	/**
	 * @since 2.4.0
	 */
	protected function after() {

		$this->maintenance_mode( false );

		if ( $this->network_wide ) {

			$deactivated_extensions = array_diff(
				$this->checked_extensions
				, array_filter( $this->checked_extensions )
			);

			update_site_option(
				'wordpoints_breaking_deactivated_modules'
				, array_keys( $deactivated_extensions )
			);
		}
	}

	/**
	 * Toggles maintenance mode.
	 *
	 * @since 2.4.0
	 *
	 * @param bool $enable Whether to turn maintenance mode on or off.
	 */
	protected function maintenance_mode( $enable = true ) {

		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */
		global $wp_filesystem;

		if ( ! ( $wp_filesystem instanceof WP_Filesystem_Base ) ) {
			return;
		}

		$file = $wp_filesystem->abspath() . '.maintenance';

		if ( $enable ) {

			$maintenance_string = '<?php $upgrading = ' . time() . '; include( "' . WORDPOINTS_DIR . '/includes/maintenance.php" ); ?>';
			$wp_filesystem->delete( $file );
			$wp_filesystem->put_contents( $file, $maintenance_string, FS_CHMOD_FILE );

		} elseif ( $wp_filesystem->exists( $file ) ) {
			$wp_filesystem->delete( $file );
		}
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_network() {

		if ( ! $this->network_wide ) {
			return;
		}

		$this->doing_network = true;

		$network_active_extensions = array_keys(
			wordpoints_get_array_option( 'wordpoints_sitewide_active_modules', 'site' )
		);

		$this->check_extensions( $network_active_extensions );

		$this->doing_network = false;
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_site() {

		$this->check_extensions(
			wordpoints_get_array_option( 'wordpoints_active_modules' )
		);
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_single() {

		$this->check_extensions(
			wordpoints_get_array_option( 'wordpoints_active_modules' )
		);
	}


	/**
	 * Check if extensions are compatible with the latest version of WordPoints.
	 *
	 * @since 2.4.0
	 *
	 * @param array $extensions A list of basename-paths of extensions to check.
	 */
	protected function check_extensions( $extensions ) {

		$extensions = $this->validate_extensions( $extensions );

		if ( empty( $extensions ) ) {
			return;
		}

		// First try checking all of them at once.
		if ( count( $extensions ) > 1 ) {
			if ( $this->check_extension( implode( ',', $extensions ) ) ) {
				return;
			}
		}

		// If there was one or more broken, we'll have to check each to find them.
		$incompatible_extensions = array();

		foreach ( $extensions as $extension ) {
			if ( ! $this->check_extension( $extension ) ) {
				$incompatible_extensions[]              = $extension;
				$this->checked_extensions[ $extension ] = false;
			}
		}

		if ( empty( $incompatible_extensions ) ) {
			return;
		}

		$this->deactivate_extensions( $incompatible_extensions );
	}

	/**
	 * Validate extensions and remove any that have already been checked.
	 *
	 * @since 2.4.0
	 *
	 * @param string[] $extensions A list of extensions to validate.
	 *
	 * @return string[] The valid extensions.
	 */
	protected function validate_extensions( $extensions ) {

		$incompatible = array();

		foreach ( $extensions as $index => $extension ) {

			if ( isset( $this->checked_extensions[ $extension ] ) ) {

				unset( $extensions[ $index ] );

				if ( ! $this->checked_extensions[ $extension ] ) {
					$incompatible[] = $extension;
				}
			}

			$valid = wordpoints_validate_module( $extension );

			if ( is_wp_error( $valid ) ) {
				unset( $extensions[ $index ] );
			}

			$this->checked_extensions[ $extension ] = true;
		}

		if ( ! empty( $incompatible ) ) {
			$this->deactivate_extensions( $incompatible );
		}

		return $extensions;
	}

	/**
	 * Checks if the extensions admin screen will work when this extension is active.
	 *
	 * @since 2.4.0
	 *
	 * @param string $extension The basename-path of the extension to check.
	 *
	 * @return bool|WP_Error Whether the page loaded correctly, or a WP_Error if
	 *                       unable to make the request.
	 */
	protected function check_extension( $extension ) {

		$rand_str = wp_generate_password( 256, true, true );
		$nonce    = wordpoints_hash( $rand_str . 'wordpoints_check_modules-' . $extension );

		if ( $this->doing_network ) {
			update_site_option( 'wordpoints_module_check_rand_str', $rand_str );
			update_site_option( 'wordpoints_module_check_nonce', $nonce );
		} else {
			update_option( 'wordpoints_module_check_rand_str', $rand_str );
			update_option( 'wordpoints_module_check_nonce', $nonce );
		}

		$response = wp_safe_remote_post(
			add_query_arg(
				'wordpoints_module_check'
				, $nonce
				, self_admin_url( 'admin-ajax.php?action=wordpoints_breaking_module_check&check_module=' . $extension )
			)
			, array( 'timeout' => 20 )
		);

		if ( $this->doing_network ) {
			delete_site_option( 'wordpoints_module_check_rand_str' );
			delete_site_option( 'wordpoints_module_check_nonce' );
		} else {
			delete_option( 'wordpoints_module_check_rand_str' );
			delete_option( 'wordpoints_module_check_nonce' );
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );

		return (bool) strpos( $body, $rand_str );
	}

	/**
	 * Deactivate extensions.
	 *
	 * @since 2.4.0
	 *
	 * @param array $extensions A list of basename-paths of extensions to deactivate.
	 */
	protected function deactivate_extensions( $extensions ) {

		if ( $this->doing_network ) {

			$active_extensions = wordpoints_get_array_option( 'wordpoints_sitewide_active_modules', 'site' );
			$active_extensions = array_diff_key( $active_extensions, array_flip( $extensions ) );
			update_site_option( 'wordpoints_sitewide_active_modules', $active_extensions );

			update_site_option( 'wordpoints_incompatible_modules', $extensions );

		} else {

			$active_extensions = wordpoints_get_array_option( 'wordpoints_active_modules' );
			$active_extensions = array_diff( $active_extensions, $extensions );
			update_option( 'wordpoints_active_modules', $active_extensions );

			update_option( 'wordpoints_incompatible_modules', $extensions );
		}
	}
}

// EOF
