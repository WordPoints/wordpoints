<?php

/**
 * Install the a plugin remotely.
 *
 * @package WP_Plugin_Uninstall_Tester
 * @since 0.1.0
 */

// wp-load.php tries to redifine ABSPATH.
error_reporting( E_ALL & ~E_NOTICE );

$plugin_file      = $argv[1];
$install_function = $argv[2];
$config_file_path = $argv[3];

require $config_file_path;

unset( $config_file_path );

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['HTTP_HOST'] = WP_TESTS_DOMAIN;
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

define( 'WP_USE_THEMES', false );

require ABSPATH . '/wp-load.php';

require $plugin_file;

add_action( 'activate_' . $plugin_file, $install_function );

do_action( 'activate_' . $plugin_file, false );
