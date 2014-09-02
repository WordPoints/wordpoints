<?php

/**
 * Uninstall the plugin.
 *
 * Uninstallation, as apposed to deactivation, will remove all of the plugin's data.
 *
 * @package WordPoints
 * @since 1.0.0
 */

// Exit if we aren't being uninstalled.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Dependencies for the uninstall routine.
require_once dirname( __FILE__ ) . '/includes/uninstall-bootstrap.php';

/*
 * Uninstall modules.
 *
 * Note that modules aren't active when they are uninstalled, so they need to
 * include any dependencies in their uninstall.php files.
 */
wordpoints_deactivate_modules( wordpoints_get_array_option( 'wordpoints_active_modules', 'site' ) );

foreach ( array_keys( wordpoints_get_modules() ) as $module ) {

	wordpoints_uninstall_module( $module );
}

// Attempt to delete the modules directory.
global $wp_filesystem;

if ( $wp_filesystem instanceof WP_Filesystem ) {
	$wp_filesystem->delete( wordpoints_modules_dir(), true );
}

/*
 * Back compat < 1.7.0
 *
 * The below notes no longer apply.
 * --------------------------------
 *
 * Bulk 'deactivate' components. No other filters should be applied later than these
 * (e.g., after 99) for this hook - doing so could have unexpected results.
 *
 * We do this so that we can load them to call the uninstall hooks, without them
 * being active.
 */
add_filter( 'wordpoints_component_active', '__return_false', 100 );

$components = WordPoints_Components::instance();

// Back-compat < 1.7.0
$components->load();

// Uninstall the components.
foreach ( $components->get() as $component => $data ) {
	$components->uninstall( $component );
}

// Custom capabilities to remove.
$capabilities = array_keys( wordpoints_get_custom_caps() );

// Delete settings, remove custom caps, and clear the cache.
if ( is_multisite() ) {

	delete_site_option( 'wordpoints_data' );
	delete_site_option( 'wordpoints_active_components' );
	delete_site_option( 'wordpoints_excluded_users' );
	delete_site_option( 'wordpoints_sitewide_active_modules' );

	global $wpdb;

	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

	$original_blog_id = get_current_blog_id();

	foreach ( $blog_ids as $blog_id ) {

		switch_to_blog( $blog_id );

		delete_option( 'wordpoints_data' );
		delete_option( 'wordpoints_active_modules' );
		delete_option( 'wordpoints_active_components' );
		delete_option( 'wordpoints_excluded_users' );
		delete_option( 'wordpoints_recently_activated_modules' );

		wp_cache_delete( 'wordpoints_modules' );

		wordpoints_remove_custom_caps( $capabilities );
	}

	switch_to_blog( $original_blog_id );

	// See http://wordpress.stackexchange.com/a/89114/27757
	unset( $GLOBALS['_wp_switched_stack'] );
	$GLOBALS['switched'] = false;

} else {

	delete_option( 'wordpoints_data' );
	delete_option( 'wordpoints_active_modules' );
	delete_option( 'wordpoints_active_components' );
	delete_option( 'wordpoints_excluded_users' );
	delete_option( 'wordpoints_recently_activated_modules' );

	wp_cache_delete( 'wordpoints_modules' );

	wordpoints_remove_custom_caps( $capabilities );
}

// EOF
