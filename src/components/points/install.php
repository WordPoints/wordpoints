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

/**
 * Include the upgrade script so that we can use dbDelta() to create the DBs.
 *
 * @since 1.0.0
 */
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

dbDelta( wordpoints_points_get_db_schema() );

// Add custom caps. Multisite is taken care of in the including function.
if ( ! is_multisite() ) {
	wordpoints_add_custom_caps( wordpoints_points_get_custom_caps() );
	add_option( 'wordpoints_default_points_type', '' );
}

$wordpoints_data['components']['points']['version'] = WORDPOINTS_VERSION;

wordpoints_update_network_option( 'wordpoints_data', $wordpoints_data );

// EOF
