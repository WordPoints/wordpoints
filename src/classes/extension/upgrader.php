<?php

/**
 * Extension upgrader class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Upgrades extensions.
 *
 * This class is based on the WordPress Plugin_Upgrader class, and is designed to
 * upgrade/install extensions from a local zip, remote zip URL, or uploaded zip file.
 *
 * @see WP_Upgrader The WP Upgrader class.
 *
 * @since 2.4.0
 */
class WordPoints_Extension_Upgrader extends WordPoints_Module_Installer {

	/**
	 * Whether we are performing a bulk upgrade.
	 *
	 * @since 2.4.0
	 *
	 * @var bool
	 */
	protected $bulk = false;

	/**
	 * Whether the upgrade routine bailed out early.
	 *
	 * @since 2.4.0
	 *
	 * @var bool
	 */
	protected $bailed_early = false;

	/**
	 * The extension server API being used for the extension currently being updated.
	 *
	 * @since 2.5.0
	 *
	 * @var WordPoints_Extension_Server_APII
	 */
	protected $wordpoints_extension_api;

	/**
	 * The extension server API data for the extension currently being updated.
	 *
	 * @since 2.5.0
	 *
	 * @var WordPoints_Extension_Server_API_Extension_DataI
	 */
	protected $wordpoints_extension_data;

	/**
	 * Sets up the strings for an extension upgrade.
	 *
	 * @since 2.4.0
	 */
	protected function upgrade_strings() {

		$upgrade_strings = array(
			'up_to_date'                => esc_html__( 'The extension is at the latest version.', 'wordpoints' ),
			'no_package'                => esc_html__( 'Update package not available.', 'wordpoints' ),
			'no_server'                 => esc_html__( 'That extension cannot be updated, because there is no server specified to receive updates through.', 'wordpoints' ),
			'api_not_found'             => esc_html__( 'That extension cannot be updated, because there is no API installed that can communicate with that server.', 'wordpoints' ),
			'api_updates_not_supported' => esc_html__( 'That extension cannot be updated, because the API used to communicate with that server does not support updates.', 'wordpoints' ),
			// translators: Update package URL.
			'downloading_package'       => sprintf( esc_html__( 'Downloading update from %s&#8230;', 'wordpoints' ), '<span class="code">%s</span>' ),
			'verifying_package'         => esc_html__( 'Verifying the update&#8230;', 'wordpoints' ),
			// translators: Support forums link.
			'verifying_package_failed'  => wp_kses_data( sprintf( __( 'Verifying the update failed. The update was not installed, because the package may have been modified by an attacker. Please <a href="%s">report this failure</a>.', 'wordpoints' ), 'https://wordpress.org/support/plugin/wordpoints' ) ),
			'unpack_package'            => esc_html__( 'Unpacking the update&#8230;', 'wordpoints' ),
			'remove_old'                => esc_html__( 'Removing the old version of the extension&#8230;', 'wordpoints' ),
			'remove_old_failed'         => esc_html__( 'Could not remove the old extension.', 'wordpoints' ),
			'process_failed'            => esc_html__( 'Extension update failed.', 'wordpoints' ),
			'process_success'           => esc_html__( 'Extension updated successfully.', 'wordpoints' ),
			'not_installed'             => esc_html__( 'That extension cannot be updated, because it is not installed.', 'wordpoints' ),
		);

		$this->strings = array_merge( $this->strings, $upgrade_strings );
	}

	/**
	 * Upgrades an extension.
	 *
	 * @since 2.4.0
	 *
	 * @param string $extension_file Basename path to the extension file.
	 * @param array  $args           {
	 *        Optional arguments.
	 *
	 *        @type bool $clear_update_cache Whether the to clear the update cache.
	 *                                       The default is true.
	 * }
	 *
	 * @return bool|WP_Error True on success, false or a WP_Error on failure.
	 */
	public function upgrade( $extension_file, $args = array() ) {

		$args   = $this->before_upgrades( $args );
		$result = $this->do_upgrade( $extension_file );
		$this->after_upgrades( $extension_file, $args );

		if ( ! $result || is_wp_error( $result ) ) {
			return $result;
		}

		return true;
	}

	/**
	 * Performs a bulk upgrade.
	 *
	 * @since 2.4.0
	 *
	 * @param string[] $extensions Array of basename paths to the extensions.
	 * @param array    $args       {
	 *
	 *        @type bool $clear_update_cache Whether the to clear the update cache.
	 *                                       Default is true.
	 * }
	 *
	 * @return array|false The result of each update, indexed by extension, or false if
	 *                     unable to perform the upgrades.
	 */
	public function bulk_upgrade( $extensions, $args = array() ) {

		$this->bulk = true;

		$args = $this->before_upgrades( $args );

		$this->skin->header();

		// Connect to the Filesystem first.
		if ( ! $this->fs_connect( array( WP_CONTENT_DIR, wordpoints_extensions_dir() ) ) ) {

			$this->skin->footer();
			return false;
		}

		$this->skin->bulk_header();

		$this->maybe_start_maintenance_mode( $extensions );

		$results = array();

		$this->update_count   = count( $extensions );
		$this->update_current = 0;

		foreach ( $extensions as $extension ) {

			$this->update_current++;

			$results[ $extension ] = $this->do_upgrade( $extension );

			// Prevent credentials auth screen from displaying multiple times.
			if ( false === $results[ $extension ] && ! $this->bailed_early ) {
				break;
			}
		}

		$this->maintenance_mode( false );

		$this->skin->bulk_footer();
		$this->skin->footer();

		$this->after_upgrades( $extensions, $args );

		return $results;

	} // End public function bulk_upgrade().

	/**
	 * Sets up before running the upgrades.
	 *
	 * @since 2.4.0
	 *
	 * @param array $args The arguments passed to the upgrader.
	 *
	 * @return array The parsed upgrader arguments.
	 */
	protected function before_upgrades( $args ) {

		$args = wp_parse_args( $args, array( 'clear_update_cache' => true ) );

		$this->init();
		$this->upgrade_strings();

		add_filter( 'upgrader_clear_destination', array( $this, 'delete_old_extension' ), 10, 4 );
		add_filter( 'upgrader_source_selection', array( $this, 'check_package' ) );
		add_filter( 'upgrader_source_selection', array( $this, 'correct_extension_dir_name' ), 10, 4 );
		add_filter( 'upgrader_pre_download', array( $this, 'verify_package_signature' ), 10, 3 );
		add_filter( 'upgrader_pre_install', array( $this, 'deactivate_extension_before_upgrade' ), 10, 2 );

		return $args;
	}

	/**
	 * Upgrades an extension.
	 *
	 * This is the real meat of the upgrade functions.
	 *
	 * @since 2.4.0
	 *
	 * @param string $extension_file Basename path to the extension file.
	 *
	 * @return bool|array|WP_Error Returns true or an array on success, false or a
	 *                             WP_Error on failure.
	 */
	protected function do_upgrade( $extension_file ) {

		$this->bailed_early = false;

		$extensions = wordpoints_get_modules();

		if ( ! isset( $extensions[ $extension_file ] ) ) {
			$this->bail_early( 'not_installed' );
			return false;
		}

		$extension_data = $extensions[ $extension_file ];

		if ( $this->skin instanceof WordPoints_Extension_Upgrader_Skin_Bulk ) {
			$this->skin->set_extension( $extension_file );
		}

		if ( ! wordpoints_get_extension_updates()->has_update( $extension_file ) ) {
			$this->bail_early( 'up_to_date', 'feedback' );
			return true;
		}

		$server = wordpoints_get_server_for_extension( $extension_data );

		if ( ! $server ) {
			$this->bail_early( 'no_server' );
			return false;
		}

		$api = $server->get_api();

		if ( false === $api ) {
			$this->bail_early( 'api_not_found' );
			return false;
		}

		if ( ! $api instanceof WordPoints_Extension_Server_API_Updates_InstallableI ) {
			$this->bail_early( 'api_updates_not_supported' );
			return false;
		}

		$extension_data = new WordPoints_Extension_Server_API_Extension_Data(
			$extension_data['ID']
			, $server
		);

		$this->wordpoints_extension_api  = $api;
		$this->wordpoints_extension_data = $extension_data;

		return $this->run(
			array(
				'package'           => $api->get_extension_package_url( $extension_data ),
				'destination'       => wordpoints_extensions_dir(),
				'clear_destination' => true,
				'clear_working'     => true,
				'is_multi'          => $this->bulk,
				'hook_extra'        => array(
					'wordpoints_extension' => $extension_file,
				),
			)
		);

	} // End protected function do_upgrade().

	/**
	 * Cleans up after the upgrades.
	 *
	 * @since 2.4.0
	 *
	 * @param string|string[] $extensions The extension(s) being upgraded.
	 * @param array           $args       The arguments passed to the upgrader.
	 */
	protected function after_upgrades( $extensions, $args ) {

		remove_filter( 'upgrader_source_selection', array( $this, 'check_package' ) );
		remove_filter( 'upgrader_source_selection', array( $this, 'correct_extension_dir_name' ) );
		remove_filter( 'upgrader_clear_destination', array( $this, 'delete_old_extension' ) );
		remove_filter( 'upgrader_pre_download', array( $this, 'verify_package_signature' ) );
		remove_filter( 'upgrader_pre_install', array( $this, 'deactivate_extension_before_upgrade' ) );

		if ( ! $this->bulk ) {
			if ( ! $this->skin->result || is_wp_error( $this->skin->result ) ) {
				return;
			}
		}

		// Force refresh of extension update cache.
		wordpoints_clean_extensions_cache( $args['clear_update_cache'] );

		$details = array(
			'action' => 'update',
			'type'   => 'wordpoints_extension',
			'bulk'   => $this->bulk,
		);

		/**
		 * This action is documented in /wp-admin/includes/class-wp-upgrader.php.
		 */
		do_action( 'upgrader_process_complete', $this, $details, $extensions ); // WPCS: prefix OK.
	}

	/**
	 * Bail early before finishing a process normally.
	 *
	 * @since 2.4.0
	 *
	 * @param string $message Slug for the message to show the user.
	 * @param string $type    The type of message, 'error' (default), or 'feedback'.
	 */
	protected function bail_early( $message, $type = 'error' ) {

		$this->bailed_early = true;

		$this->skin->before();
		$this->skin->set_result( false );

		if ( 'feedback' === $type ) {
			$this->skin->feedback( $message );
		} else {
			$this->skin->error( $message );
		}

		$this->skin->after();
	}

	/**
	 * Conditionally starts maintenance mode, only if necessary.
	 *
	 * Used when performing bulk updates.
	 *
	 * Only start maintenance mode if:
	 * - running Multisite and there are one or more extensions specified, OR
	 * - an extension with an update available is currently active.
	 *
	 * @since 2.4.0
	 *
	 * @param string[] $extensions The extensions being upgraded in bulk.
	 */
	public function maybe_start_maintenance_mode( $extensions ) {

		if ( is_multisite() && ! empty( $extensions ) ) {

			$this->maintenance_mode( true );

		} else {

			$updates = wordpoints_get_extension_updates();

			foreach ( $extensions as $extension ) {

				if (
					is_wordpoints_module_active( $extension )
					&& $updates->has_update( $extension )
				) {
					$this->maintenance_mode( true );
					break;
				}
			}
		}
	}

	/**
	 * Makes sure an extension is inactive before it is upgraded.
	 *
	 * @since 2.4.0
	 *
	 * @WordPress\filter upgrader_pre_install Added by self::upgrade().
	 *
	 * @param bool|WP_Error $return True if we should do the upgrade, a WP_Error otherwise.
	 * @param array         $data   Data about the upgrade: what extension is being upgraded.
	 *
	 * @return bool|WP_Error A WP_Error on failure, otherwise nothing.
	 */
	public function deactivate_extension_before_upgrade( $return, $data ) {

		if ( is_wp_error( $return ) ) {
			return $return;
		}

		if ( empty( $data['wordpoints_extension'] ) ) {
			return new WP_Error( 'bad_request', $this->strings['bad_request'] );
		}

		if ( is_wordpoints_module_active( $data['wordpoints_extension'] ) ) {

			// Deactivate the extension silently (the actions won't be fired).
			wordpoints_deactivate_modules( array( $data['wordpoints_extension'] ), true );
		}

		return $return;
	}

	/**
	 * Ensures that an extension folder will have the correct name.
	 *
	 * @since 2.4.0
	 *
	 * @WordPress\filter upgrader_source_selection Added by self::upgrade().
	 *
	 * @param string      $source        The path to the extension source.
	 * @param array       $remote_source The remote source of the extension.
	 * @param WP_Upgrader $upgrader      The upgrader instance.
	 * @param array       $data          Data about the upgrade.
	 *
	 * @return string The extension folder.
	 */
	public function correct_extension_dir_name( $source, $remote_source, $upgrader, $data ) {

		global $wp_filesystem;

		if ( is_wp_error( $source ) ) {
			return $source;
		}

		if ( $upgrader !== $this || ! isset( $data['wordpoints_extension'] ) ) {
			return $source;
		}

		$source_name    = basename( $source );
		$extension_name = dirname( $data['wordpoints_extension'] );

		if ( '.' === $extension_name || $source_name === $extension_name ) {
			return $source;
		}

		$correct_source = dirname( $source ) . '/' . $extension_name;

		$moved = $wp_filesystem->move( $source, $correct_source );

		if ( ! $moved ) {
			return new WP_Error( 'wordpoints_incorrect_source_name', $this->strings['incorrect_source_name'] );
		}

		return $correct_source;
	}

	/**
	 * Deletes the old extension before installing the new one.
	 *
	 * @since 2.4.0
	 *
	 * @WordPress\filter upgrader_clear_destination Added by self::upgrade() and
	 *                                              self::bulk_upgrade().
	 *
	 * @param true|WP_Error $removed            Whether the destination folder has been removed.
	 * @param string        $local_destination  The local path to the destination folder.
	 * @param string        $remote_destination The remote path to the destination folder.
	 * @param array         $data               Data for the upgrade: what extension is being upgraded.
	 *
	 * @return true|WP_Error True on success, a WP_Error on failure.
	 */
	public function delete_old_extension( $removed, $local_destination, $remote_destination, $data ) {

		global $wp_filesystem;

		if ( is_wp_error( $removed ) ) {
			return $removed;
		}

		if ( empty( $data['wordpoints_extension'] ) ) {
			return new WP_Error( 'bad_request', $this->strings['bad_request'] );
		}

		$extensions_dir     = $wp_filesystem->find_folder( wordpoints_extensions_dir() );
		$this_extension_dir = trailingslashit( dirname( $extensions_dir . $data['wordpoints_extension'] ) );

		// Make sure it hasn't already been removed somehow.
		if ( ! $wp_filesystem->exists( $this_extension_dir ) ) {
			return $removed;
		}

		/*
		 * If the extension is in its own directory, recursively delete the directory.
		 * Do a base check on if the extension includes the directory separator AND that
		 * it's not the root extensions folder. If not, just delete the single file.
		 */
		if ( strpos( $data['wordpoints_extension'], '/' ) && $this_extension_dir !== $extensions_dir ) {
			$deleted = $wp_filesystem->delete( $this_extension_dir, true );
		} else {
			$deleted = $wp_filesystem->delete( $extensions_dir . $data['wordpoints_extension'] );
		}

		if ( ! $deleted ) {
			return new WP_Error( 'remove_old_failed', $this->strings['remove_old_failed'] );
		}

		return true;
	}

	/**
	 * Verifies the signature of the update package before unpacking it.
	 *
	 * @since 2.5.0
	 *
	 * @WordPress\filter upgrader_pre_download Added by self::upgrade().
	 *
	 * @param bool|WP_Error $return   True if we should do the upgrade, a WP_Error
	 *                                otherwise.
	 * @param string        $package  The package URL.
	 * @param WP_Upgrader   $upgrader The upgrader.
	 *
	 * @return bool|WP_Error A WP_Error on failure, otherwise nothing.
	 */
	public function verify_package_signature( $return, $package, $upgrader ) {

		if ( is_wp_error( $return ) ) {
			return $return;
		}

		if ( $upgrader !== $this ) {
			return $return;
		}

		// WARNING: In 3.0.0 we will change this to abort the update.
		if ( ! $this->wordpoints_extension_api instanceof WordPoints_Extension_Server_API_Updates_Signed_Ed25519I ) {

			_doing_it_wrong(
				__METHOD__
				, 'Extension server APIs should use digital signatures to verify packages.'
				, '2.5.0'
			);

			return $return;
		}

		$public_key = $this->wordpoints_extension_api->get_extension_public_key_ed25519(
			$this->wordpoints_extension_data
		);

		// WARNING: In 3.0.0 we will change this to abort the update.
		if ( ! $public_key ) {

			_doing_it_wrong(
				__METHOD__
				, 'Extension server APIs should use digital signatures to verify packages.'
				, '2.5.0'
			);

			return $return;
		}

		remove_filter( 'upgrader_pre_download', array( $this, 'verify_package_signature' ) );

		$download_file = $this->download_package( $package );

		add_filter( 'upgrader_pre_download', array( $this, 'verify_package_signature' ), 10, 3 );

		if ( is_wp_error( $download_file ) ) {
			return $download_file;
		}

		$this->skin->feedback( 'verifying_package' );

		$ed25519_check = wordpoints_verify_file_ed25519(
			$download_file
			, $public_key
			, $this->wordpoints_extension_api->get_extension_package_signature_ed25519(
				$this->wordpoints_extension_data
			)
		);

		if ( is_wp_error( $ed25519_check ) ) {

			/** @var WP_Error $ed25519_check */
			$ed25519_check->add(
				'verifying_package_failed'
				, $this->strings['verifying_package_failed']
			);

			return $ed25519_check;
		}

		return $download_file;
	}

} // End class WordPoints_Extension_Upgrader.

// EOF
