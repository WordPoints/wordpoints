<?php

/**
 * Simulate plugin usage.
 *
 * @package WP_Plugin_Uninstall_Tester
 * @since 0.1.0
 */

/**
 * Load the main plugin file as if it was active in plugin usage simulation.
 *
 * @since 0.1.0
 */
function _wp_plugin_unintsall_tester_load_plugin_file() {

	require $GLOBALS['argv'][1];
}

/**
 * Load the WordPress tests functions.
 *
 * We are loading this so that we can add our tests filter to load the plugin, using
 * tests_add_filter().
 *
 * @since 0.1.0
 */
require_once getenv( 'WP_TESTS_DIR' ) . 'includes/functions.php';

tests_add_filter( 'muplugins_loaded', '_wp_plugin_unintsall_tester_load_plugin_file' );

$simulation_file  = $argv[2];
$config_file_path = $argv[3];

require $config_file_path;

unset( $config_file_path );

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['HTTP_HOST'] = WP_TESTS_DOMAIN;
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

define( 'WP_USE_THEMES', false );

require ABSPATH . '/wp-settings.php';

require $simulation_file;
