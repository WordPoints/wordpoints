<?php

/**
 * Uninstall the points component.
 *
 * @package WordPoints\Points
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Dependencies for the uninstall process.
require_once WORDPOINTS_DIR . '/components/points/includes/functions.php';

global $wpdb;

$wpdb->query( 'DROP TABLE IF EXISTS `' . $wpdb->wordpoints_points_logs . '`' );
$wpdb->query( 'DROP TABLE IF EXISTS `' . $wpdb->wordpoints_points_log_meta . '`' );

foreach ( wordpoints_get_points_types() as $slug => $settings ) {

	delete_metadata( 'user', 0, "wordpoints_points-{$slug}", '', true );
	delete_metadata( 'comment', 0, "wordpoints_last_status-{$slug}", '', true );
}

delete_metadata( 'user', 0, 'wordpoints_points_period_start', '', true );

// Points Types.
delete_option( 'wordpoints_points_types' );
delete_option( 'wordpoints_default_points_type' );

// Points hooks.
delete_option( 'wordpoints_points_hooks' );
delete_option( 'wordpoints_points_types_hooks' );
delete_option( 'wordpoints_hook-wordpoints_registration_points_hook' );
delete_option( 'wordpoints_hook-wordpoints_post_points_hook' );
delete_option( 'wordpoints_hook-wordpoints_comment_points_hook' );
delete_option( 'wordpoints_hook-wordpoints_periodic_points_hook' );

// Widgets.
delete_option( 'widget_wordpoints_points_logs_widget' );
delete_option( 'widget_wordpoints_top_users_widget' );
delete_option( 'widget_wordpoints_points_widget' );

// end of file /components/points/uninstall.php
