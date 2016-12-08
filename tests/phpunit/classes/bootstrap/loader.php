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
	 * List of functions to call indexed by action slug.
	 *
	 * @since 2.2.0
	 *
	 * @var callable[]
	 */
	protected $actions;

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
	 * @since 2.2.0
	 */
	public function __construct() {

		parent::__construct();

		$this->add_action(
			'after_load_wordpress'
			, array( $this, 'init_wordpoints_factory' )
		);

		$this->add_action(
			'after_load_wordpress'
			, array( $this, 'throw_errors_for_database_errors' )
		);

		$this->add_action(
			'after_load_wordpress'
			, array( $this, 'clean_database' )
		);
	}

	/**
	 * Hook a function to a custom action.
	 *
	 * These aren't related to the WordPress actions, but are a similar concept.
	 *
	 * @since 2.2.0
	 *
	 * @param string   $action   The action to hook the function to
	 * @param callable $function The function to hook to this action.
	 */
	public function add_action( $action, $function ) {
		$this->actions[ $action ][] = $function;
	}

	/**
	 * Calls all of the functions hooked to an action.
	 *
	 * @since 2.2.0
	 *
	 * @param string $action The action to fire.
	 */
	public function do_action( $action ) {

		if ( ! isset( $this->actions[ $action ] ) ) {
			return;
		}

		foreach ( $this->actions[ $action ] as $function ) {
			call_user_func( $function );
		}
	}

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

	/**
	 * @since 2.2.0
	 */
	public function running_uninstall_tests() {

		if ( ! defined( 'RUNNING_WORDPOINTS_MODULE_TESTS' ) ) {
			return parent::running_uninstall_tests();
		}

		static $uninstall_tests;

		if ( ! isset( $uninstall_tests ) ) {

			ob_start();
			$uninstall_tests = parent::running_uninstall_tests();
			ob_end_clean();

			if ( ! $uninstall_tests ) {
				echo 'Not running module install/uninstall tests... To execute these, use -c phpunit.uninstall.xml.dist.' . PHP_EOL;
			} else {
				echo 'Running module install/uninstall tests...' . PHP_EOL;
			}
		}

		return $uninstall_tests;
	}

	/**
	 * Loads WordPress and its test environment.
	 *
	 * @since 2.2.0
	 */
	public function load_wordpress() {

		$this->do_action( 'before_load_wordpress' );

		parent::load_wordpress();

		$this->do_action( 'after_load_wordpress' );
	}

	/**
	 * Initialize the WordPoints PHPUnit factory.
	 *
	 * @since 2.2.0
	 */
	public function init_wordpoints_factory() {

		$factory = WordPoints_PHPUnit_Factory::init();
		$factory->register( 'entity', 'WordPoints_PHPUnit_Factory_For_Entity' );
		$factory->register( 'entity_context', 'WordPoints_PHPUnit_Factory_For_Entity_Context' );
		$factory->register( 'hook_reaction', 'WordPoints_PHPUnit_Factory_For_Hook_Reaction' );
		$factory->register( 'hook_reaction_store', 'WordPoints_PHPUnit_Factory_For_Hook_Reaction_Store' );
		$factory->register( 'hook_reactor', 'WordPoints_PHPUnit_Factory_For_Hook_Reactor' );
		$factory->register( 'hook_extension', 'WordPoints_PHPUnit_Factory_For_Hook_Extension' );
		$factory->register( 'hook_event', 'WordPoints_PHPUnit_Factory_For_Hook_Event' );
		$factory->register( 'hook_action', 'WordPoints_PHPUnit_Factory_For_Hook_Action' );
		$factory->register( 'hook_condition', 'WordPoints_PHPUnit_Factory_For_Hook_Condition' );
		$factory->register( 'points_log', 'WordPoints_PHPUnit_Factory_For_Points_Log' );
		$factory->register( 'post_type', 'WordPoints_PHPUnit_Factory_For_Post_Type' );
		$factory->register( 'rank', 'WordPoints_PHPUnit_Factory_For_Rank' );
		$factory->register( 'user_role', 'WordPoints_PHPUnit_Factory_For_User_Role' );

		$this->do_action( 'init_wordpoints_factory' );
	}

	/**
	 * Causes database errors to be converted into actual PHP errors.
	 *
	 * @since 2.2.0
	 */
	public function throw_errors_for_database_errors() {

		global $EZSQL_ERROR;

		$EZSQL_ERROR = new WordPoints_PHPUnit_Error_Handler_Database();
	}

	/**
	 * Remove cruft from the database that will interfere with the tests.
	 *
	 * @since 2.2.0
	 */
	public function clean_database() {
		delete_site_transient( 'wordpoints_all_site_ids' );
	}
}

// EOF
