<?php

/**
 * Bootstrap to load the classes.
 *
 * Include this in the bootstrap for you plugin tests.
 *
 * @package WP_Plugin_Uninstall_Tester
 * @since 0.1.0
 */

$dir = dirname( __FILE__ );

/**
 * The commandline options parser.
 */
require_once $dir . '/includes/wp-plugin-uninstall-tester-phpunit-util-getopt.php';

/**
 * General functions.
 */
require_once $dir . '/includes/functions.php';

/**
 * The plugin install/uninstall test case.
 */
require_once $dir . '/includes/wp-plugin-uninstall-unittestcase.php';

/**
 * Table exists constraint.
 */
require_once $dir . '/includes/constraints/is-table-existant.php';

/**
 * Table non-existant constraint.
 */
require_once $dir . '/includes/constraints/table-is-non-existant.php';

/**
 * No rows with prefix constraint.
 */
require_once $dir . '/includes/constraints/no-rows-with-prefix.php';

unset( $dir );
