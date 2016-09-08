<?php

/**
 * Set up environment for WordPoints tests suite.
 *
 * @package WordPoints\Tests
 * @since 1.0.0
 */

if ( ! getenv( 'WP_TESTS_DIR' ) ) {

	echo( '$_ENV["WP_TESTS_DIR"] is not set.' . PHP_EOL );
	exit( 1 );
}

/**
 * The WordPoints tests directory.
 *
 * @since 1.1.0
 *
 * @const WORDPOINTS_TESTS_DIR
 */
define( 'WORDPOINTS_TESTS_DIR', dirname( dirname( __FILE__ ) ) );

if ( ! class_exists( 'WordPoints_Dev_Lib_PHPUnit_Class_Autoloader' ) ) {
	/**
	 * Class autoloader for PHPUnit tests and helpers from the dev lib.
	 *
	 * @since 2.2.0
	 */
	require_once( WORDPOINTS_TESTS_DIR . '/../../dev-lib-wordpoints/phpunit/classes/class/autoloader.php' );
}

WordPoints_Dev_Lib_PHPUnit_Class_Autoloader::register_dir(
	WORDPOINTS_TESTS_DIR . '/tests/'
	, 'WordPoints_'
);

WordPoints_Dev_Lib_PHPUnit_Class_Autoloader::register_dir(
	WORDPOINTS_TESTS_DIR . '/tests/classes/'
	, 'WordPoints_'
);

WordPoints_Dev_Lib_PHPUnit_Class_Autoloader::register_dir(
	WORDPOINTS_TESTS_DIR . '/tests/points/classes/'
	, 'WordPoints_Points_'
);

WordPoints_Dev_Lib_PHPUnit_Class_Autoloader::register_dir(
	WORDPOINTS_TESTS_DIR . '/includes/classes/'
	, 'WordPoints_PHPUnit_'
);

if ( ! defined( 'RUNNING_WORDPOINTS_MODULE_TESTS' ) ) {
	/**
	 * The WP plugin uninstall testing functions.
	 *
	 * We need this so we can check if the uninstall tests are being run.
	 *
	 * @since 1.2.0
	 * @since 1.7.0 Only when not RUNNING_WORDPOINTS_MODULE_TESTS.
	 */
	require WORDPOINTS_TESTS_DIR . '/../../vendor/jdgrimes/wp-plugin-uninstall-tester/includes/functions.php';
}

/**
 * The WordPress tests functions.
 *
 * Clearly, WP_TESTS_DIR should be the path to the WordPress PHPUnit tests checkout.
 *
 * We are loading this so that we can add our tests filter to load the plugin, using
 * tests_add_filter().
 *
 * @since 1.0.0
 */
require_once getenv( 'WP_TESTS_DIR' ) . '/includes/functions.php';

/**
 * Miscellaneous utility functions.
 *
 * Among these is the one that manually loads the plugin. We need to hook it to
 * 'muplugins_loaded'.
 *
 * @since 1.0.0
 */
require_once WORDPOINTS_TESTS_DIR . '/includes/functions.php';

if (
	defined( 'RUNNING_WORDPOINTS_MODULE_TESTS' )
	&& (
		! function_exists( 'running_wordpoints_module_uninstall_tests' )
		|| ! running_wordpoints_module_uninstall_tests()
	)
) {

	tests_add_filter( 'muplugins_loaded', 'wordpointstests_manually_load_plugin' );

} elseif ( ! running_wp_plugin_uninstall_tests() ) {

	// If we aren't running the uninstall tests, we need to hook in to load the plugin.
	tests_add_filter( 'muplugins_loaded', 'wordpointstests_manually_load_plugin' );
}

/**
 * Sets up the WordPress test environment.
 *
 * We've got our action set up, so we can load this now, and viola, the tests begin.
 * Again, WordPress' PHPUnit test suite needs to be installed under the given path.
 *
 * @since 1.0.0
 */
require getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';

/**
 * Include the plugin's constants so that we can access the current version.
 *
 * @since 1.4.0
 */
require_once WORDPOINTS_TESTS_DIR . '/../../src/includes/constants.php';

if ( ! defined( 'RUNNING_WORDPOINTS_MODULE_TESTS' ) ) {
	/**
	 * The bootstrap for the uninstall tests.
	 *
	 * @since 1.2.0
	 * @since 1.7.0 Only when not RUNNING_WORDPOINTS_MODULE_TESTS.
	 */
	require WORDPOINTS_TESTS_DIR . '/../../vendor/jdgrimes/wp-plugin-uninstall-tester/bootstrap.php';
}

// Autoload deprecated classes, for back-compat.
spl_autoload_register( 'wordpoints_phpunit_deprecated_class_autoloader' );

$factory = WordPoints_PHPUnit_Factory::init();
$factory->register( 'entity', 'WordPoints_PHPUnit_Factory_For_Entity' );
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

global $EZSQL_ERROR;
$EZSQL_ERROR = new WordPoints_PHPUnit_Error_Handler_Database();

// https://core.trac.wordpress.org/ticket/25239
$_SERVER['SERVER_NAME'] = 'example.com';

// EOF
