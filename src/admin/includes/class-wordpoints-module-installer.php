<?php

/**
 * WordPoints module upgrader class.
 *
 * @package WordPoints\Modules
 * @since 1.1.0
 */

/**
 * The WordPres upgraders.
 */
require_once ABSPATH . '/wp-admin/includes/class-wp-upgrader.php';

/**
 * WordPoints module upgrader class.
 *
 * This class is based on the WordPress Plugin_Upgrader class, and is designed to
 * install modules from an uploaded zip file.
 *
 * @see WP_Upgrader The WP Upgrader class.
 *
 * @since 1.1.0
 */
class WordPoints_Module_Installer extends WP_Upgrader {

	//
	// Public Vars.
	//

	/**
	 * The result of the install.
	 *
	 * @since 1.1.0
	 *
	 * @type bool|WP_Error $result
	 */
	public $result;

	//
	// Private Methods.
	//

	/**
	 * Set up the strings for a module install.
	 *
	 * @since 1.1.0
	 */
	private function install_strings() {

		$install_strings = array(
			'no_package'           => esc_html__( 'Install package not available.', 'wordpoints' ),
			'unpack_package'       => esc_html__( 'Unpacking the package&#8230;', 'wordpoints' ),
			'installing_package'   => esc_html__( 'Installing the module&#8230;', 'wordpoints' ),
			'no_files'             => esc_html__( 'The module contains no files.', 'wordpoints' ),
			'process_failed'       => esc_html__( 'Module install failed.', 'wordpoints' ),
			'process_success'      => esc_html__( 'Module installed successfully.', 'wordpoints' ),
			'mkdir_failed_modules' => esc_html__( 'Could not create the modules directory.', 'wordpoints' ),
		);

		$this->strings = array_merge( $this->strings, $install_strings );
	}

	//
	// Public Methods.
	//

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

		$modules_dir = wordpoints_modules_dir();

		// Attempt to create the /wp-content/wordpoints-modules directory if needed.
		if ( ! $wp_filesystem->exists( $modules_dir ) ) {

			if ( $wp_filesystem->mkdir( $modules_dir, FS_CHMOD_DIR ) ) {
				$wp_filesystem->put_contents( $modules_dir . '/index.php', '<?php // Gold is silent.' );
			} else {
				return new WP_Error( 'mkdir_failed_modules', $this->strings['mkdir_failed_modules'], $modules_dir );
			}
		}

		$wp_theme_directories[] = $module_dir = wordpoints_modules_dir();

		$result = parent::install_package( $args );

		if ( false !== ( $key = array_search( $module_dir, $wp_theme_directories ) ) ) {

			unset( $wp_theme_directories[ $key ] );
		}

		return $result;
	}

	/**
	 * Install a module.
	 *
	 * @since 1.1.0
	 *
	 * @param string $package The path to the install package file.
	 *
	 * @return bool|WP_Error True on success, false or a WP_Error on failure.
	 */
	public function install( $package ) {

		$this->init();
		$this->install_strings();

		add_filter( 'upgrader_source_selection', array( $this, 'check_package' ) );

		$result = $this->run(
			array(
				'package'           => $package,
				'destination'       => wordpoints_modules_dir(),
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

		// Force refresh of plugin update information.
		wp_cache_delete( 'wordpoints_modules', 'wordpoints_modules' );

		return true;
	}

	/**
	 * Check the package.
	 *
	 * @since 1.1.0
	 *
	 * @filter upgrader_source_selection Added by the install() method.
	 *
	 * @uses $wp_filesystem
	 *
	 * @param string $source The path to the source package.
	 *
	 * @return string|WP_Error The path to the source package or a WP_Error.
	 */
	public function check_package( $source ) {

		global $wp_filesystem;

		if ( is_wp_error( $source ) ) {
			return $source;
		}

		$working_directory = str_replace( $wp_filesystem->wp_content_dir(), trailingslashit( WP_CONTENT_DIR ), $source );

		if ( ! is_dir( $working_directory ) ) {
			return $source;
		}

		$modules_found = false;

		foreach ( glob( $working_directory . '*.php' ) as $file ) {

			$module_data = wordpoints_get_module_data( $file, false, false );

			if ( ! empty( $module_data['name'] ) ) {
				$modules_found = true;
				break;
			}
		}

		if ( ! $modules_found ) {
			return new WP_Error( 'incompatible_archive_no_modules', $this->strings['incompatible_archive'], esc_html__( 'No valid modules were found.', 'wordpoints' ) );
		}

		return $source;
	}

	/**
	 * Get the file which contains the module info.
	 *
	 * Not used within the class, but is called by the installer skin.
	 *
	 * @since 1.1.0
	 *
	 * @return string|false The module path or false on failure.
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

} // class WordPoints_Module_Installer

// EOF
