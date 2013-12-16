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

if ( ! defined( 'WORDPOINTS_DIR' ) ) {

	// This likely won't be set, since the plugin isn't active (main file isn't loaded).

	/**
	 * @see wordpoints.php
	 */
	define( 'WORDPOINTS_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WORDPOINTS_VERSION' ) ) {

	/**
	 * @see wordpoints.php
	 */
	define( 'WORDPOINTS_VERSION', '1.1.0' );
}

// Dependencies for the uninstall routine.
require_once WORDPOINTS_DIR . 'includes/functions.php';
require_once WORDPOINTS_DIR . 'includes/modules.php';
require_once WORDPOINTS_DIR . 'includes/class-wordpoints-components.php';

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
 * Bulk 'deactivate' components. No other filters should be applied later than these
 * (e.g., after 99) for this hook - doing so could have unexpected results.
 *
 * We do this so that we can load them to call the uninstall hooks, without them
 * being active.
 */
add_filter( 'wordpoints_component_active', '__return_false', 100 );

$components = WordPoints_Components::instance();

// Now for the components.
$components->load();

foreach ( $components->get() as $component => $data ) {

	/**
	 * Uninstall $component.
	 *
	 * @since 1.0.0
	 */
	do_action( "wordpoints_uninstall_component-{$component}" );
}

// Delete settings.
delete_option( 'wordpoints_data' );
delete_option( 'wordpoints_active_modules' );
delete_option( 'wordpoints_active_components' );
delete_option( 'wordpoints_excluded_users' );
delete_option( 'wordpoints_recently_activated_modules' );
delete_site_option( 'wordpoints_sitewide_active_modules' );

// Clear cache.
wp_cache_delete( 'wordpoints_modules' );

// end of file /uninstall.php
