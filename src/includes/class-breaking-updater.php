<?php

/**
 * WordPoints breaking updater class.
 *
 * @package WordPoints
 * @since 2.0.0
 */

/**
 * Does special checks before a breaking update of WordPoints.
 *
 * @since 2.0.0
 */
class WordPoints_Breaking_Updater extends WordPoints_Un_Installer_Base {

	/**
	 * @since 2.0.0
	 */
	protected $type = 'plugin';

	/**
	 * @since 2.0.0
	 */
	protected $updates = array(
		'breaking'  => array( 'single' => true, 'site' => true, 'network' => true ),
	);

	/**
	 * A list of modules which have already been checked.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	protected $checked_modules = array();

	/**
	 * @since 2.0.0
	 */
	protected function before_update() {

		/**
		 * WordPress's filesystem API.
		 *
		 * @since 2.0.0
		 */
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		if ( ! WP_Filesystem( false, ABSPATH ) ) {
			return;
		}

		$this->maintenance_mode();
	}

	/**
	 * @since 2.0.0
	 */
	protected function after_update() {

		$this->maintenance_mode( false );

		if ( $this->network_wide ) {

			$deactivated_modules = array_diff(
				$this->checked_modules
				, array_filter( $this->checked_modules )
			);

			update_site_option(
				'wordpoints_breaking_deactivated_modules'
				, array_keys( $deactivated_modules )
			);
		}
	}

	/**
	 * @since 2.0.0
	 */
	protected function is_network_installed() {
		return $this->network_wide;
	}

	/**
	 * Toggles maintenance mode.
	 *
	 * @since 2.0.0
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
	 * Check network-active modules when updating to a breaking version.
	 *
	 * @since 2.0.0
	 */
	protected function update_network_to_breaking() {

		if ( ! $this->network_wide ) {
			return;
		}

		$network_active_modules = array_keys(
			wordpoints_get_array_option( 'wordpoints_sitewide_active_modules', 'site' )
		);

		$this->check_modules( $network_active_modules );
	}

	/**
	 * Check the active modules on this site when updating to a breaking version.
	 *
	 * @since 2.0.0
	 */
	protected function update_site_to_breaking() {
		$this->update_single_to_breaking();
	}

	/**
	 * Check the active modules on this site when updating to a breaking version.
	 *
	 * @since 2.0.0
	 */
	protected function update_single_to_breaking() {

		$this->check_modules(
			wordpoints_get_array_option( 'wordpoints_active_modules' )
		);
	}

	/**
	 * Check if modules are compatible with the latest version of WordPoints.
	 *
	 * @since 2.0.0
	 *
	 * @param array $modules A list of basename-paths of modules to check.
	 */
	protected function check_modules( $modules ) {

		$modules = $this->validate_modules( $modules );

		if ( empty( $modules ) ) {
			return;
		}

		// First try checking all of them at once.
		if ( count( $modules ) > 1 ) {
			if ( $this->check_module( implode( ',', $modules ) ) ) {
				return;
			}
		}

		// If there was one or more broken, we'll have to check each to find them.
		$incompatible_modules = array();

		foreach ( $modules as $module ) {
			if ( ! $this->check_module( $module ) ) {
				$incompatible_modules[] = $module;
				$this->checked_modules[ $module ] = false;
			}
		}

		if ( empty( $incompatible_modules ) ) {
			return;
		}

		$this->deactivate_modules( $incompatible_modules );
	}

	/**
	 * Validate modules and remove any that have already been checked.
	 *
	 * @since 2.0.0
	 *
	 * @param string[] $modules A list of modules to validate.
	 *
	 * @return string[] The valid modules.
	 */
	protected function validate_modules( $modules ) {

		$incompatible = array();

		foreach ( $modules as $index => $module ) {

			if ( isset( $this->checked_modules[ $module ] ) ) {

				unset( $modules[ $index ] );

				if ( ! $this->checked_modules[ $module ] ) {
					$incompatible[] = $module;
				}
			}

			$valid = wordpoints_validate_module( $module );

			if ( is_wp_error( $valid ) ) {
				unset( $modules[ $index ] );
			}

			$this->checked_modules[ $module ] = true;
		}

		if ( ! empty( $incompatible ) ) {
			$this->deactivate_modules( $incompatible );
		}

		return $modules;
	}

	/**
	 * Checks if the modules admin screen will work when this module is active.
	 *
	 * @since 2.0.0
	 *
	 * @param string $module The basename-path of the module to check.
	 *
	 * @return bool|WP_Error Whether the page loaded correctly, or a WP_Error if
	 *                       unable to make the request.
	 */
	protected function check_module( $module ) {

		$rand_str = str_shuffle( wordpoints_hash( microtime() ) );
		$nonce = wordpoints_hash( $rand_str . 'wordpoints_check_modules-' . $module );

		if ( 'network' === $this->context ) {
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
				, self_admin_url( 'admin-ajax.php?action=wordpoints_breaking_module_check&check_module=' . $module )
			)
			, array( 'timeout' => 20 )
		);

		if ( 'network' === $this->context ) {
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
	 * Deactivate modules.
	 *
	 * @since 2.0.0
	 *
	 * @param array $modules A list of basename-paths of modules to deactivate.
	 */
	protected function deactivate_modules( $modules ) {

		if ( 'network' === $this->context ) {

			$active_modules = wordpoints_get_array_option( 'wordpoints_sitewide_active_modules', 'site' );
			$active_modules = array_diff_key( $active_modules, array_flip( $modules ) );
			update_site_option( 'wordpoints_sitewide_active_modules', $active_modules );

			update_site_option( 'wordpoints_incompatible_modules', $modules );

		} else {

			$active_modules = wordpoints_get_array_option( 'wordpoints_active_modules' );
			$active_modules = array_diff( $active_modules, $modules );
			update_option( 'wordpoints_active_modules', $active_modules );

			update_option( 'wordpoints_incompatible_modules', $modules );
		}
	}
}

// EOF
