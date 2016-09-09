<?php

/**
 * WordPoints loader class for the PHPUnit tests.
 *
 * @package WordPoints\PHPUnit
 * @since 2.2.0
 */

/**
 * Loads plugins, modules, and WordPoints components, for the PHPUnit tests.
 *
 * @since 2.2.0
 */
class WordPoints_PHPUnit_Bootstrap_Loader extends WPPPB_Loader {

	/**
	 * List of modules to be installed.
	 *
	 * @since 2.2.0
	 *
	 * @var array[]
	 */
	protected $modules = array();

	/**
	 * List of components to be installed.
	 *
	 * @since 2.2.0
	 *
	 * @var array[]
	 */
	protected $components = array();

	/**
	 * The main instance of the loader.
	 *
	 * @since 2.2.0
	 *
	 * @var WordPoints_PHPUnit_Bootstrap_Loader
	 */
	protected static $instance;

	/**
	 * Get the main instance of the loader.
	 *
	 * @since 2.2.0
	 *
	 * @return WordPoints_PHPUnit_Bootstrap_Loader The main instance.
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new WordPoints_PHPUnit_Bootstrap_Loader;
			parent::$instance = self::$instance;
		}

		return self::$instance;
	}

	//
	// Public methods.
	//

	/**
	 * Add a module to load.
	 *
	 * @since 2.2.0
	 *
	 * @param string $module       The basename slug of the module. Example:
	 *                             'module/module.php'.
	 * @param bool   $network_wide Whether to activate the module network-wide.
	 */
	public function add_module( $module, $network_wide = false ) {
		$this->modules[ $module ] = array( 'network_wide' => $network_wide );
	}

	/**
	 * Add a component to load.
	 *
	 * @since 2.2.0
	 *
	 * @param string $slug The slug of the component.
	 */
	public function add_component( $slug ) {
		$this->components[ $slug ] = array();
	}

	/**
	 * @since 2.2.0
	 */
	public function install_plugins() {

		if ( ! empty( $this->components ) ) {
			$this->add_php_file(
				WORDPOINTS_TESTS_DIR . '/includes/install-components.php'
				, 'after'
				, $this->components
			);
		}

		if ( ! empty( $this->modules ) ) {
			$this->add_php_file(
				WORDPOINTS_TESTS_DIR . '/includes/install-modules.php'
				, 'after'
				, $this->modules
			);
		}

		parent::install_plugins();
	}

	/**
	 * @since 2.2.0
	 */
	public function should_install_plugins() {

		if (
			function_exists( 'running_wordpoints_module_uninstall_tests' )
			&& running_wordpoints_module_uninstall_tests()
		) {
			return false;
		}

		return parent::should_install_plugins();
	}
}

// EOF
