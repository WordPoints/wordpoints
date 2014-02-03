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

$points_types = wordpoints_get_points_types();

foreach ( $points_types as $slug => $settings ) {

	delete_metadata( 'user', 0, "wordpoints_points-{$slug}", '', true );
}

delete_metadata( 'user', 0, 'wordpoints_points_period_start', '', true );

if ( is_multisite() ) {

	delete_site_option( 'wordpoints_points_types' );
	delete_site_option( 'wordpoints_default_points_type' );
	delete_site_option( 'wordpoints_points_types_hooks' );

	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

	$original_blog_id = get_current_blog_id();

	foreach ( $blog_ids as $blog_id ) {

		switch_to_blog( $blog_id );

		$points_types = wordpoints_get_points_types();

		foreach ( $points_types as $slug => $settings ) {

			delete_metadata( 'comment', 0, "wordpoints_last_status-{$slug}", '', true );

			$prefix = $wpdb->get_blog_prefix();
			delete_metadata( 'user', 0, $prefix . "wordpoints_points-{$slug}", '', true );
			delete_metadata( 'user', 0, $prefix . 'wordpoints_points_period_start', '', true );
		}

		delete_option( 'wordpoints_points_types' );
		delete_option( 'wordpoints_default_points_type' );
		delete_option( 'wordpoints_points_types_hooks' );

		delete_option( 'wordpoints_hook-wordpoints_registration_points_hook' );
		delete_option( 'wordpoints_hook-wordpoints_post_points_hook' );
		delete_option( 'wordpoints_hook-wordpoints_comment_points_hook' );
		delete_option( 'wordpoints_hook-wordpoints_periodic_points_hook' );

		delete_option( 'widget_wordpoints_points_logs_widget' );
		delete_option( 'widget_wordpoints_top_users_widget' );
		delete_option( 'widget_wordpoints_points_widget' );
	}

	switch_to_blog( $original_blog_id );

	// See http://wordpress.stackexchange.com/a/89114/27757
	unset( $GLOBALS['_wp_switched_stack'] );

} else {

	delete_option( 'wordpoints_points_types' );
	delete_option( 'wordpoints_default_points_type' );
	delete_option( 'wordpoints_points_types_hooks' );

	delete_option( 'wordpoints_hook-wordpoints_registration_points_hook' );
	delete_option( 'wordpoints_hook-wordpoints_post_points_hook' );
	delete_option( 'wordpoints_hook-wordpoints_comment_points_hook' );
	delete_option( 'wordpoints_hook-wordpoints_periodic_points_hook' );

	delete_option( 'widget_wordpoints_points_logs_widget' );
	delete_option( 'widget_wordpoints_top_users_widget' );
	delete_option( 'widget_wordpoints_points_widget' );
}

// end of file /components/points/uninstall.php
