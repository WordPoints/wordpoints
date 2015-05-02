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

// EOF
