<?php

/**
 * Install the ranks component.
 *
 * This file is included by the wordpoints_ranks_component_activate() function when
 * the component is being activated for the first time.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

// Exit if accessed directly.
if ( ! isset( $wordpoints_data ) ) {
	exit;
}

/**
 * Include the upgrade script so that we can use dbDelta() to create the DBs.
 *
 * @since 1.7.0
 */
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

dbDelta( wordpoints_ranks_get_db_schema() );

$wordpoints_data['components']['ranks']['version'] = WORDPOINTS_VERSION;

wordpoints_update_network_option( 'wordpoints_data', $wordpoints_data );

// EOF
