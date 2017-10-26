<?php

/**
 * Set up environment for WordPoints tests suite.
 *
 * This file is deprecated as of WordPoints 2.3.0, and the PHPUnit bootstrap from
 * the dev-lib should be used instead.
 *
 * @package WordPoints\Tests
 * @since 1.0.0
 * @deprecated 2.3.0
 */

/**
 * The WordPoints tests directory.
 *
 * @since 1.1.0
 *
 * @const WORDPOINTS_TESTS_DIR
 */
define( 'WORDPOINTS_TESTS_DIR', dirname( dirname( __FILE__ ) ) );

/**
 * The WordPoints tests directory.
 *
 * @since 2.3.0
 *
 * @type string
 */
define( 'WORDPOINTS_DEV_LIB_PHPUNIT_DIR', dirname( dirname( WORDPOINTS_MODULE_TESTS_DIR ) ) . '/dev-lib/phpunit' );

/**
 * Miscellaneous utility functions.
 *
 * Loaded before WordPress, for backward compatibility with pre-2.2.0.
 *
 * @since 1.0.0
 */
require_once WORDPOINTS_TESTS_DIR . '/includes/functions.php';

// Later versions of the dev lib load this early, so we don't have to load it here.
if ( ! class_exists( 'WordPoints_Dev_Lib_PHPUnit_Class_Autoloader' ) ) {

	// We normally load it from our own version of the dev lib.
	$dir = WORDPOINTS_TESTS_DIR;

	// But when running the module tests, we load it from the module's version of the
	// dev lib. Not doing so will cause a fatal error later on when the old version
	// of the dev lib includes its version. If we'd loaded our version, the class
	// would already exist. For even older versions of the dev lib, the autoloader
	// won't be included with it at all, so it is OK for us load ours.
	if (
		defined( 'WORDPOINTS_MODULE_TESTS_DIR' )
		&& file_exists(
			WORDPOINTS_MODULE_TESTS_DIR
				. '/../../dev-lib/phpunit/classes/class/autoloader.php'
		)
	) {
		$dir = WORDPOINTS_MODULE_TESTS_DIR;
	}

	/**
	 * Class autoloader for PHPUnit tests and helpers from the dev lib.
	 *
	 * @since 2.2.0
	 */
	require_once $dir . '/../../dev-lib/phpunit/classes/class/autoloader.php';
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
	dirname( dirname( WORDPOINTS_TESTS_DIR ) ) . '/dev-lib/phpunit/classes/'
	, 'WordPoints_PHPUnit_'
);

// We don't include the autoloader for our Composer dependencies when running the
// module tests, because it might not have been generated, and shouldn't be needed.
if ( ! defined( 'RUNNING_WORDPOINTS_MODULE_TESTS' ) ) {
	/**
	 * The Composer generated autoloader.
	 *
	 * @since 2.2.0
	 */
	require_once WORDPOINTS_TESTS_DIR . '/../../vendor/autoload_52.php';
}

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

	$loader->load_wordpress();

} else {

	/**
	 * Sets up the WordPress test environment.
	 *
	 * @since 1.0.0
	 */
	require getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';
}

/**
 * Include the plugin's constants so that we can access the current version.
 *
 * @since 1.4.0
 */
require_once WORDPOINTS_TESTS_DIR . '/../../src/includes/constants.php';

// Autoload deprecated classes, for back-compat with pre-2.2.0.
spl_autoload_register( 'wordpoints_phpunit_deprecated_class_autoloader' );

// EOF
