<?php

/**
 * Deprecated functions and classes.
 *
 * These functions should not be used, and may be removed in future versions of the
 * plugin.
 *
 * @package WordPoints
 * @since 1.1.0
 */

/**
 * Check if a table exists in the database.
 *
 * @since 1.0.0
 * @deprecated 1.7.0 No longer used.
 *
 * @uses $wpdb
 *
 * @param string $table The name of the table to check for.
 *
 * @return bool Whether the table exists.
 */
function wordpoints_db_table_exists( $table ) {

	_deprecated_function( __FUNCTION__, '1.7.0' );

	global $wpdb;

	$_table = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ); // Cache pass, WPCS.

	return ( $_table === $table ) ? true : false;
}

/**
 * Update the plugin.
 *
 * @since 1.3.0
 * @deprecated 2.0.0
 */
function wordpoints_update() {

	_deprecated_function( __FUNCTION__, '2.0.0' );

	WordPoints_Installables::maybe_do_updates();
}

/**
 * Add custom capabilities to new sites on creation when in network mode.
 *
 * @since 1.5.0
 *
 * @param int $blog_id The ID of the new site.
 */
function wordpoints_add_custom_caps_to_new_sites( $blog_id ) {

	_deprecated_function( __FUNCTION__, '2.0.0' );

	if ( ! is_wordpoints_network_active() ) {
		return;
	}

	switch_to_blog( $blog_id );
	wordpoints_add_custom_caps( wordpoints_get_custom_caps() );
	restore_current_blog();
}

// EOF
