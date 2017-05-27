<?php

/**
 * Module upgrader class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Upgrades modules.
 *
 * This class is based on the WordPress Plugin_Upgrader class, and is designed to
 * upgrade/install modules from a local zip, remote zip URL, or uploaded zip file.
 *
 * @see WP_Upgrader The WP Upgrader class.
 *
 * @since 2.4.0
 */
class WordPoints_Module_Upgrader extends WordPoints_Module_Installer {

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
	 * Sets up the strings for a module upgrade.
	 *
	 * @since 2.4.0
	 */
	protected function upgrade_strings() {

		$upgrade_strings = array(
			'up_to_date'                => esc_html__( 'The module is at the latest version.', 'wordpoints' ),
			'no_package'                => esc_html__( 'Update package not available.', 'wordpoints' ),
			'no_server'                 => esc_html__( 'That module cannot be updated, because there is no server specified to receive updates through.', 'wordpoints' ),
			'api_not_found'             => esc_html__( 'That module cannot be updated, because there is no API installed that can communicate with that server.', 'wordpoints' ),
			'api_updates_not_supported' => esc_html__( 'That module cannot be updated, because the API used to communicate with that server does not support updates.', 'wordpoints' ),
			// translators: Update package URL.
			'downloading_package'       => sprintf( esc_html__( 'Downloading update from %s&#8230;', 'wordpoints' ), '<span class="code">%s</span>' ),
			'unpack_package'            => esc_html__( 'Unpacking the update&#8230;', 'wordpoints' ),
			'remove_old'                => esc_html__( 'Removing the old version of the module&#8230;', 'wordpoints' ),
			'remove_old_failed'         => esc_html__( 'Could not remove the old module.', 'wordpoints' ),
			'process_failed'            => esc_html__( 'Module update failed.', 'wordpoints' ),
			'process_success'           => esc_html__( 'Module updated successfully.', 'wordpoints' ),
			'not_installed'             => esc_html__( 'That module cannot be updated, because it is not installed.', 'wordpoints' ),
		);

		$this->strings = array_merge( $this->strings, $upgrade_strings );
	}

	/**
	 * Upgrades a module.
	 *
	 * @since 2.4.0
	 *
	 * @param string $module_file Basename path to the module file.
	 * @param array  $args        {
	 *        Optional arguments.
	 *
	 *        @type bool $clear_update_cache Whether the to clear the update cache.
	 *                                       The default is true.
	 * }
	 *
	 * @return bool|WP_Error True on success, false or a WP_Error on failure.
	 */
	public function upgrade( $module_file, $args = array() ) {

		$args = $this->before_upgrades( $args );
		$result = $this->do_upgrade( $module_file );
		$this->after_upgrades( $module_file, $args );

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
	 * @param string[] $modules Array of basename paths to the modules.
	 * @param array    $args {
	 *
	 *        @type bool $clear_update_cache Whether the to clear the update cache.
	 *                                       Default is true.
	 * }
	 *
	 * @return array|false The result of each update, indexed by module, or false if
	 *                     unable to perform the upgrades.
	 */
	public function bulk_upgrade( $modules, $args = array() ) {

		$this->bulk = true;

		$args = $this->before_upgrades( $args );

		$this->skin->header();

		// Connect to the Filesystem first.
		if ( ! $this->fs_connect( array( WP_CONTENT_DIR, wordpoints_modules_dir() ) ) ) {

			$this->skin->footer();
			return false;
		}

		$this->skin->bulk_header();

		$this->maybe_start_maintenance_mode( $modules );

		$results = array();

		$this->update_count = count( $modules );
		$this->update_current = 0;

		foreach ( $modules as $module ) {

			$this->update_current++;

			$results[ $module ] = $this->do_upgrade( $module );

			// Prevent credentials auth screen from displaying multiple times.
			if ( false === $results[ $module ] && ! $this->bailed_early ) {
				break;
			}
		}

		$this->maintenance_mode( false );

		$this->skin->bulk_footer();
		$this->skin->footer();

		$this->after_upgrades( $modules, $args );

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

		add_filter( 'upgrader_clear_destination', array( $this, 'delete_old_module' ), 10, 4 );
		add_filter( 'upgrader_source_selection', array( $this, 'check_package' ) );
		add_filter( 'upgrader_source_selection', array( $this, 'correct_module_dir_name' ), 10, 4 );

		if ( ! $this->bulk ) {
			add_filter( 'upgrader_pre_install', array( $this, 'deactivate_module_before_upgrade' ), 10, 2 );
		}

		return $args;
	}

	/**
	 * Upgrades a module.
	 *
	 * This is the real meat of the upgrade functions.
	 *
	 * @since 2.4.0
	 *
	 * @param string $module_file Basename path to the module file.
	 *
	 * @return bool|array|WP_Error Returns true or an array on success, false or a
	 *                             WP_Error on failure.
	 */
	protected function do_upgrade( $module_file ) {

		$this->bailed_early = false;

		$modules = wordpoints_get_modules();

		if ( ! isset( $modules[ $module_file ] ) ) {
			$this->bail_early( 'not_installed' );
			return false;
		}

		$module_data = $modules[ $module_file ];

		if ( $this->skin instanceof WordPoints_Module_Upgrader_Skin_Bulk ) {
			$this->skin->set_module( $module_file );
		}

		if ( ! wordpoints_get_module_updates()->has_update( $module_file ) ) {
			$this->bail_early( 'up_to_date', 'feedback' );
			return true;
		}

		$server = wordpoints_get_server_for_module( $module_data );

		if ( ! $server ) {
			$this->bail_early( 'no_server' );
			return false;
		}

		$api = $server->get_api();

		if ( false === $api ) {
			$this->bail_early( 'api_not_found' );
			return false;
		}

		if ( ! $api instanceof WordPoints_Module_Server_API_Updates_InstallableI ) {
			$this->bail_early( 'api_updates_not_supported' );
			return false;
		}

		$module_data = new WordPoints_Module_Server_API_Module_Data(
			$module_data['ID']
			, $server
		);

		return $this->run(
			array(
				'package'           => $api->get_module_package_url( $module_data ),
				'destination'       => wordpoints_modules_dir(),
				'clear_destination' => true,
				'clear_working'     => true,
				'is_multi'          => $this->bulk,
				'hook_extra'        => array(
					'wordpoints_module' => $module_file,
				),
			)
		);

	} // End protected function do_upgrade().

	/**
	 * Cleans up after the upgrades.
	 *
	 * @since 2.4.0
	 *
	 * @param string|string[] $modules The module(s) being upgraded.
	 * @param array           $args    The arguments passed to the upgrader.
	 */
	protected function after_upgrades( $modules, $args ) {

		remove_filter( 'upgrader_source_selection', array( $this, 'check_package' ) );
		remove_filter( 'upgrader_source_selection', array( $this, 'correct_module_dir_name' ) );
		remove_filter( 'upgrader_clear_destination', array( $this, 'delete_old_module' ) );

		if ( ! $this->bulk ) {

			remove_filter( 'upgrader_pre_install', array( $this, 'deactivate_module_before_upgrade' ) );

			if ( ! $this->skin->result || is_wp_error( $this->skin->result ) ) {
				return;
			}
		}

		// Force refresh of module update cache.
		wordpoints_clean_modules_cache( $args['clear_update_cache'] );

		$details = array(
			'action' => 'update',
			'type'   => 'wordpoints_module',
			'bulk'   => $this->bulk,
		);

		/**
		 * This action is documented in /wp-admin/includes/class-wp-upgrader.php.
		 */
		do_action( 'upgrader_process_complete', $this, $details, $modules );
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
	 * - running Multisite and there are one or more modules specified, OR
	 * - a module with an update available is currently active.
	 *
	 * @since 2.4.0
	 *
	 * @param string[] $modules The modules being upgraded in bulk.
	 */
	public function maybe_start_maintenance_mode( $modules ) {

		if ( is_multisite() && ! empty( $modules ) ) {

			$this->maintenance_mode( true );

		} else {

			$updates = wordpoints_get_module_updates();

			foreach ( $modules as $module ) {

				if (
					is_wordpoints_module_active( $module )
					&& $updates->has_update( $module )
				) {
					$this->maintenance_mode( true );
					break;
				}
			}
		}
	}

	/**
	 * Makes sure a module is inactive before it is upgraded.
	 *
	 * @since 2.4.0
	 *
	 * @WordPress\filter upgrader_pre_install Added by self::upgrade().
	 *
	 * @param bool|WP_Error $return True if we should do the upgrade, a WP_Error otherwise.
	 * @param array         $data   Data about the upgrade: what module is being upgraded.
	 *
	 * @return bool|WP_Error A WP_Error on failure, otherwise nothing.
	 */
	public function deactivate_module_before_upgrade( $return, $data ) {

		if ( is_wp_error( $return ) ) {
			return $return;
		}

		if ( empty( $data['wordpoints_module'] ) ) {
			return new WP_Error( 'bad_request', $this->strings['bad_request'] );
		}

		if ( is_wordpoints_module_active( $data['wordpoints_module'] ) ) {

			// Deactivate the module silently (the actions won't be fired).
			wordpoints_deactivate_modules( array( $data['wordpoints_module'] ), true );
		}

		return $return;
	}

	/**
	 * Ensures that a module folder will have the correct name.
	 *
	 * @since 2.4.0
	 *
	 * @WordPress\filter upgrader_source_selection Added by self::upgrade().
	 *
	 * @param string      $source        The path to the module source.
	 * @param array       $remote_source The remote source of the module.
	 * @param WP_Upgrader $upgrader      The upgrader instance.
	 * @param array       $data          Data about the upgrade.
	 *
	 * @return string The module folder.
	 */
	public function correct_module_dir_name( $source, $remote_source, $upgrader, $data ) {

		global $wp_filesystem;

		if ( is_wp_error( $source ) ) {
			return $source;
		}

		if ( $upgrader !== $this || ! isset( $data['wordpoints_module'] ) ) {
			return $source;
		}

		$source_name = basename( $source );
		$module_name = dirname( $data['wordpoints_module'] );

		if ( '.' === $module_name || $source_name === $module_name ) {
			return $source;
		}

		$correct_source = dirname( $source ) . '/' . $module_name;

		$moved = $wp_filesystem->move( $source, $correct_source );

		if ( ! $moved ) {
			return new WP_Error( 'wordpoints_incorrect_source_name', $this->strings['incorrect_source_name'] );
		}

		return $correct_source;
	}

	/**
	 * Deletes the old module before installing the new one.
	 *
	 * @since 2.4.0
	 *
	 * @WordPress\filter upgrader_clear_destination Added by self::upgrade() and
	 *                                              self::bulk_upgrade().
	 *
	 * @param true|WP_Error $removed            Whether the destination folder has been removed.
	 * @param string        $local_destination  The local path to the destination folder.
	 * @param string        $remote_destination The remote path to the destination folder.
	 * @param array         $data               Data for the upgrade: what module is being upgraded.
	 *
	 * @return true|WP_Error True on success, a WP_Error on failure.
	 */
	public function delete_old_module( $removed, $local_destination, $remote_destination, $data ) {

		global $wp_filesystem;

		if ( is_wp_error( $removed ) ) {
			return $removed;
		}

		if ( empty( $data['wordpoints_module'] ) ) {
			return new WP_Error( 'bad_request', $this->strings['bad_request'] );
		}

		$modules_dir = $wp_filesystem->find_folder( wordpoints_modules_dir() );
		$this_module_dir = trailingslashit( dirname( $modules_dir . $data['wordpoints_module'] ) );

		// Make sure it hasn't already been removed somehow.
		if ( ! $wp_filesystem->exists( $this_module_dir ) ) {
			return $removed;
		}

		/*
		 * If the module is in its own directory, recursively delete the directory.
		 * Do a base check on if the module includes the directory separator AND that
		 * it's not the root modules folder. If not, just delete the single file.
		 */
		if ( strpos( $data['wordpoints_module'], '/' ) && $this_module_dir !== $modules_dir ) {
			$deleted = $wp_filesystem->delete( $this_module_dir, true );
		} else {
			$deleted = $wp_filesystem->delete( $modules_dir . $data['wordpoints_module'] );
		}

		if ( ! $deleted ) {
			return new WP_Error( 'remove_old_failed', $this->strings['remove_old_failed'] );
		}

		return true;
	}

} // End class WordPoints_Module_Upgrader.

// EOF
