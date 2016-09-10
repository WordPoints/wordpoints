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

/**
 * Miscellaneous utility functions.
 *
 * Loaded before WordPress, for backward compatibility with pre-2.2.0.
 *
 * @since 1.0.0
 */
require_once WORDPOINTS_TESTS_DIR . '/includes/functions.php';

$autoloader_exists = class_exists( 'WordPoints_Dev_Lib_PHPUnit_Class_Autoloader' );

// The module bootstrap used to load the autoloader after WordPoints's bootstrap,
// which means that for back-compat, we can only use the new autoloader here if
// the module tests aren't running, or it is a newer version of the module bootstrap
// that loads the autoloader early so that it will be available here. Not doing
// this back-compat checking here will cause fatal errors when the module bootstrap
// tries to load the autoloader too late and the tests are being run against
// WordPoints >= 2.2.0.
if ( ! defined( 'RUNNING_WORDPOINTS_MODULE_TESTS' ) || $autoloader_exists ) {

	if ( ! $autoloader_exists ) {
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
		WORDPOINTS_TESTS_DIR . '/classes/'
		, 'WordPoints_PHPUnit_'
	);

} else {

	// Old version of the module tests are running, use the deprecated auotloader.
	spl_autoload_register( 'wordpoints_phpunit_autoloader' );
}

/**
 * The Composer generated autoloader.
 *
 * @since 2.2.0
 */
require_once( dirname( __FILE__ ) . '/../../../vendor/autoload_52.php' );

// For back-compat with old versions of the module bootstrap, expecting pre-2.2.0
// behavior. Newer versions of the module bootstrap have already loaded the loader,
// and expect us to use it.
if (
	! defined( 'RUNNING_WORDPOINTS_MODULE_TESTS' )
	|| class_exists( 'WordPoints_PHPUnit_Bootstrap_Loader', false )
) {

	$loader = WordPoints_PHPUnit_Bootstrap_Loader::instance();
	$loader->add_plugin(
		'wordpoints/wordpoints.php'
		, getenv( 'WORDPOINTS_NETWORK_ACTIVE' )
	);

	$loader->add_component( 'ranks' );
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
