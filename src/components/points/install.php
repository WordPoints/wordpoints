<?php

/**
 * Install the points component.
 *
 * This file is included by the wordpoints_points_component_activate() function when
 * the component is being activated for the first time.
 *
 * @package WordPoints\Points
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! isset( $wordpoints_data ) ) {
	exit;
}

global $wpdb;

/**
 * Include the upgrade script so that we can use dbDelta() to create the DBs.
 *
 * @since 1.0.0
 */
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

if ( ! wordpoints_db_table_exists( $wpdb->wordpoints_points_logs ) ) {

	// - Create the table for the points transaction logs.

	dbDelta(
		'
			CREATE TABLE ' . $wpdb->wordpoints_points_logs . '
			(
				id BIGINT(20) NOT NULL AUTO_INCREMENT,
				user_id BIGINT(20) NOT NULL,
				log_type VARCHAR(256) NOT NULL,
				points BIGINT(20) NOT NULL,
				points_type VARCHAR(256) NOT NULL,
				text LONGTEXT,
				blog_id SMALLINT(5) UNSIGNED NOT NULL,
				site_id SMALLINT(5) UNSIGNED NOT NULL,
				date DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",
				PRIMARY KEY id (id),
				KEY user_id (user_id),
				KEY points_type (points_type),
				KEY log_type (log_type)
			);
		'
	);
}

if ( ! wordpoints_db_table_exists( $wpdb->wordpoints_points_log_meta ) ) {

	// - Create the table to hold the metadata for points transations.

	dbDelta(
		'
			CREATE TABLE ' . $wpdb->wordpoints_points_log_meta . ' (
				meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				log_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
				meta_key VARCHAR(255) DEFAULT NULL,
				meta_value LONGTEXT,
				PRIMARY KEY meta_id (meta_id),
				KEY log_id (log_id),
				KEY meta_key (meta_key)
			);
		'
	);
}

// Add custom caps. Multisite is taken care of in the including function.
if ( ! is_multisite() ) {
	wordpoints_add_custom_caps( wordpoints_points_get_custom_caps() );
	add_option( 'wordpoints_default_points_type', '' );
}

$wordpoints_data['components']['points']['version'] = WORDPOINTS_VERSION;

wordpoints_update_network_option( 'wordpoints_data', $wordpoints_data );

// end of file /components/points/install.php
