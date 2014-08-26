<?php

/**
 * Functions to update the plugin.
 *
 * @package WordPoints
 * @since 1.3.0
 */

/**
 * Update the plugin to 1.3.0.
 *
 * @since 1.3.0
 */
function wordpoints_update_1_3_0() {

	$capabilities = wordpoints_get_custom_caps();

	if ( is_wordpoints_network_active() ) {

		global $wpdb;

		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

		foreach ( $blog_ids as $blog_id ) {

			switch_to_blog( $blog_id );
			wordpoints_add_custom_caps( $capabilities );
			restore_current_blog();
		}

	} else {

		wordpoints_add_custom_caps( $capabilities );
	}
}

// EOF
