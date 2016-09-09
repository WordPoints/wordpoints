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

/**
 * The Composer generated autoloader.
 *
 * @since 2.2.0
 */
require_once( dirname( __FILE__ ) . '/../../../vendor/autoload_52.php' );

$loader = WordPoints_PHPUnit_Bootstrap_Loader::instance();
$loader->add_plugin( 'wordpoints/wordpoints.php', getenv( 'WORDPOINTS_NETWORK_ACTIVE' ) );
$loader->add_component( 'ranks' );

/**
 * Miscellaneous utility functions.
 *
 * Loaded before WordPress, for backward compatibility with pre-2.2.0.
 *
 * @since 1.0.0
 */
require_once WORDPOINTS_TESTS_DIR . '/includes/functions.php';

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
