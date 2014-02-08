<?php

/**
 * Set up environment for WordPoints tests suite.
 *
 * @package WordPoints\Tests
 * @since 1.0.0
 */

if ( ! getenv( 'WP_TESTS_DIR' ) ) {

	exit( '$_ENV["WP_TESTS_DIR"] is not set.' );
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
 * The WordPoints version.
 *
 * @since 1.2.0
 *
 * @const WORDPOINTS_TESTS_VERSION
 */
define( 'WORDPOINTS_TESTS_VERSION', '1.2.0' );

/**
 * The WP plugin uninstall testing bootstrap.
 *
 * We need this so we can check if the uninstall tests are being run.
 *
 * @since 1.2.0
 */
require_once WORDPOINTS_TESTS_DIR . '/includes/uninstall/includes/functions.php';

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
require_once getenv( 'WP_TESTS_DIR' ) . 'includes/functions.php';

/**
 * Miscellaneous utility functions.
 *
 * Among these is the one that manually loads the plugin. We need to hook it to
 * 'muplugins_loaded'.
 *
 * @since 1.0.0
 */
require_once WORDPOINTS_TESTS_DIR . '/includes/functions.php';

// If we aren't running the uninstall tests, we need to hook in to load the plugin.
if ( ! running_wp_plugin_uninstall_tests() ) {
	tests_add_filter( 'muplugins_loaded', 'wordpointstests_manually_load_plugin' );
}

/**
 * Checks which groups we are running, and gives helpful messages.
 *
 * @since 1.0.1
 */
require_once WORDPOINTS_TESTS_DIR . '/includes/class-wordpoints-phpunit-util-getopt.php';

new WordPoints_PHPUnit_Util_Getopt( $_SERVER['argv'] );

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
 * The bootstrap for the uninstall tests.
 *
 * @since 1.2.0
 */
require WORDPOINTS_TESTS_DIR . '/includes/uninstall/bootstrap.php';

/**
 * The WordPoints_Points_UnitTestCase class.
 *
 * @since 1.0.0
 */
require_once WORDPOINTS_TESTS_DIR . '/includes/class-wordpoints-points-unittestcase.php';

// end of file /tests/phpunit/includes/bootstrap.php
