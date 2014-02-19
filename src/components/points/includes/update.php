<?php

/**
 * Functions to update the points component.
 *
 * @package WordPoints\Points
 * @since 1.2.0
 */

/**
 * Clean up the database when updating to 1.2.0.
 *
 * @since 1.2.0
 */
function wordpoints_points_update_1_2_0() {

	global $wpdb;

	// If any users have been deleted, remove the logs for them.
	$log_ids = $wpdb->get_col(
		"
			SELECT wppl.id
			FROM {$wpdb->wordpoints_points_logs} AS wppl
			LEFT JOIN {$wpdb->users} as u
				ON wppl.user_id = u.ID
			WHERE u.ID IS NULL
		"
	);

	if ( $log_ids && is_array( $log_ids ) ) {

		$log_ids = implode( ',', array_map( 'absint', $log_ids ) );

		$wpdb->query(
			"
				DELETE
				FROM {$wpdb->wordpoints_points_logs}
				WHERE `id` IN ({$log_ids})
			"
		);

		$wpdb->query(
			"
				DELETE
				FROM {$wpdb->wordpoints_points_log_meta}
				WHERE `log_id` IN ({$log_ids})
			"
		);
	}

	// Regenerate the logs for deleted posts.
	$post_ids = $wpdb->get_col(
		"
			SELECT wpplm.meta_value
			FROM {$wpdb->wordpoints_points_log_meta} AS wpplm
			LEFT JOIN {$wpdb->posts} AS p
				ON p.ID = wpplm.meta_value
			WHERE p.ID IS NULL
				AND wpplm.meta_key = 'post_id'
		"
	);

	$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_post_points_hook' );

	if ( $post_ids && is_array( $post_ids ) && $hook ) {
		foreach ( $post_ids AS $post_id ) {
			$hook->clean_logs_on_post_deletion( $post_id );
		}
	}

	// Regenerate the logs for deleted comments.
	$comment_ids = $wpdb->get_col(
		"
			SELECT wpplm.meta_value
			FROM {$wpdb->wordpoints_points_log_meta} AS wpplm
			LEFT JOIN {$wpdb->comments} AS c
				ON c.comment_ID = wpplm.meta_value
			WHERE c.comment_ID IS NULL
				AND wpplm.meta_key = 'comment_id'
		"
	);

	$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_comment_points_hook' );

	if ( $comment_ids && is_array( $comment_ids ) && $hook ) {
		foreach ( $comment_ids AS $comment_id ) {
			$hook->clean_logs_on_comment_deletion( $comment_id );
		}
	}
}

/**
 * Update the points component to 1.3.0.
 *
 * @since 1.3.0
 */
function wordpoints_points_update_1_3_0() {

	// Add the custom caps to the desired roles.
	wordpoints_add_custom_caps( wordpoints_points_get_custom_caps() );
}
