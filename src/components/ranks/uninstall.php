<?php

/**
 * Uninstall the ranks component.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if the component has installed.
$wordpoints_data = wordpoints_get_array_option( 'wordpoints_data', 'network' );

if ( ! isset( $wordpoints_data['components']['ranks']['version'] ) ) {
	// The component hasn't been installed.
	return;
}

// Include dependencies.
include_once( WORDPOINTS_DIR . 'components/ranks/includes/constants.php' );

global $wpdb;

$wpdb->query( 'DROP TABLE IF EXISTS `' . $wpdb->wordpoints_ranks . '`' );
$wpdb->query( 'DROP TABLE IF EXISTS `' . $wpdb->wordpoints_rankmeta . '`' );
$wpdb->query( 'DROP TABLE IF EXISTS `' . $wpdb->wordpoints_user_ranks . '`' );

// EOF
