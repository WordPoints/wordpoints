<?php

/**
 * Module installer class.
 *
 * @package WordPoints\Modules
 * @since 2.4.0
 */

/**
 * The WordPress upgraders.
 *
 * @since 1.1.0
 */
require_once ABSPATH . '/wp-admin/includes/class-wp-upgrader.php';

/**
 * Installs modules.
 *
 * This class is based on the WordPress Plugin_Upgrader class.
 *
 * @see WP_Upgrader The WP Upgrader class.
 *
 * @since 1.1.0
 */
class WordPoints_Module_Installer extends WP_Upgrader {

	/**
	 * The result of the install.
	 *
	 * @since 1.1.0
	 *
	 * @type bool|WP_Error $result
	 */
	public $result;

	/**
	 * Sets up the strings for a module install.
	 *
	 * @since 1.1.0
	 */
	protected function install_strings() {

		$install_strings = array(
			'no_package'           => esc_html__( 'Install package not available.', 'wordpoints' ),
			// translators: Extension package URL.
			'downloading_package'  => sprintf( esc_html__( 'Downloading install package from %s&#8230;', 'wordpoints' ), '<span class="code">%s</span>' ),
			'unpack_package'       => esc_html__( 'Unpacking the package&#8230;', 'wordpoints' ),
			'installing_package'   => esc_html__( 'Installing the extension&#8230;', 'wordpoints' ),
			'no_files'             => esc_html__( 'The extension contains no files.', 'wordpoints' ),
			'process_failed'       => esc_html__( 'Extension install failed.', 'wordpoints' ),
			'process_success'      => esc_html__( 'Extension installed successfully.', 'wordpoints' ),
			'mkdir_failed_modules' => esc_html__( 'Could not create the extensions directory.', 'wordpoints' ),
		);

		$this->strings = array_merge( $this->strings, $install_strings );
	}

	/**
	 * Install a module from a package.
	 *
	 * We hack the $wp_theme_directories variable so that the source and destination
	 * will be treated properly as a plugin/theme-like directory.
	 *
	 * @since 1.1.0
	 *
	 * @param array $args A list of arguments.
	 *
	 * @return array|WP_Error {@see WP_Upgrader::install_package()}
	 */
	public function install_package( $args = array() ) {

		global $wp_filesystem, $wp_theme_directories;

		$extensions_dir = wordpoints_extensions_dir();

		// Attempt to create the /wp-content/wordpoints-extensions directory if needed.
		if ( ! $wp_filesystem->exists( $extensions_dir ) ) {

			if ( $wp_filesystem->mkdir( $extensions_dir, FS_CHMOD_DIR ) ) {
				$wp_filesystem->put_contents( $extensions_dir . '/index.php', '<?php // Gold is silent.' );
			} else {
				return new WP_Error( 'mkdir_failed_modules', $this->strings['mkdir_failed_modules'], $extensions_dir );
			}
		}

		$wp_theme_directories[] = $extensions_dir;

		$result = parent::install_package( $args );

		$key = array_search( $extensions_dir, $wp_theme_directories, true );

		if ( false !== $key ) {
			unset( $wp_theme_directories[ $key ] );
		}

		return $result;
	}

	/**
	 * Installs a module.
	 *
	 * @since 1.1.0
	 * @since 2.4.0 The $args parameter was added with the $clear_update_cache arg.
	 *
	 * @param string $package URL or full local path of the zip package of the module
	 *                        source.
	 * @param array  $args    {
	 *        Optional arguments.
	 *
	 *        @type bool $clear_update_cache Whether the to clear the update cache.
	 *                                       The default is true.
	 * }
	 *
	 * @return bool|WP_Error True on success, false or a WP_Error on failure.
	 */
	public function install( $package, $args = array() ) {

		$args = wp_parse_args( $args, array( 'clear_update_cache' => true ) );

		$this->init();
		$this->install_strings();

		add_filter( 'upgrader_source_selection', array( $this, 'check_package' ) );

		$result = $this->run(
			array(
				'package'           => $package,
				'destination'       => wordpoints_extensions_dir(),
				'clear_destination' => false, // Do not overwrite files.
				'clear_working'     => true,
				'hook_extra'        => array(),
			)
		);

		remove_filter( 'upgrader_source_selection', array( $this, 'check_package' ) );

		if ( ! $result || is_wp_error( $result ) ) {
			return $result;
		}

		if ( ! $this->result || is_wp_error( $this->result ) ) {
			return $this->result;
		}

		// Force refresh of modules cache.
		wordpoints_clean_extensions_cache( $args['clear_update_cache'] );

		/**
		 * This action is documented in /wp-admin/includes/class-wp-upgrader.php.
		 */
		do_action(
			'upgrader_process_complete'
			, $this
			, array( 'action' => 'install', 'type' => 'wordpoints_extension' )
			, $package
		);

		return true;
	}

	/**
	 * Checks if the source package actually contains a module.
	 *
	 * @since 1.1.0
	 *
	 * @WordPress\filter upgrader_source_selection Added by self::install().
	 *
	 * @param string|WP_Error $source The path to the source package.
	 *
	 * @return string|WP_Error The path to the source package, or a WP_Error.
	 */
	public function check_package( $source ) {

		global $wp_filesystem;

		if ( is_wp_error( $source ) ) {
			return $source;
		}

		$working_directory = str_replace(
			$wp_filesystem->wp_content_dir()
			, trailingslashit( WP_CONTENT_DIR )
			, $source
		);

		if ( ! is_dir( $working_directory ) ) {
			return $source;
		}

		$modules_found = false;

		$files = glob( $working_directory . '*.php' );

		if ( false === $files ) {
			return $source;
		}

		foreach ( $files as $file ) {

			$module_data = wordpoints_get_module_data( $file, false, false );

			if ( ! empty( $module_data['name'] ) ) {
				$modules_found = true;
				break;
			}
		}

		if ( ! $modules_found ) {

			return new WP_Error(
				'incompatible_archive_no_modules'
				, $this->strings['incompatible_archive']
				, esc_html__( 'No valid extensions were found.', 'wordpoints' )
			);
		}

		return $source;
	}

	/**
	 * Gets the file which contains the module info.
	 *
	 * Not used within the class, but may be called by the skins.
	 *
	 * @since 1.1.0
	 *
	 * @return string|false The module path, or false on failure.
	 */
	public function module_info() {

		if ( ! is_array( $this->result ) || empty( $this->result['destination_name'] ) ) {
			return false;
		}

		$module = wordpoints_get_modules( '/' . $this->result['destination_name'] );

		if ( empty( $module ) ) {
			return false;
		}

		// Assume the requested module is the first in the list.
		$module_files = array_keys( $module );

		return $this->result['destination_name'] . '/' . $module_files[0];
	}

} // End class WordPoints_Module_Installer.

// EOF
