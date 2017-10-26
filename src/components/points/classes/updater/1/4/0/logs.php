<?php

/**
 * Logs 1.4.0 updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Updates the points logs to 1.4.0.
 *
 * @since 2.4.0
 */
class WordPoints_Points_Updater_1_4_0_Logs implements WordPoints_RoutineI {

	/**
	 * @since 2.4.0
	 */
	public function run() {

		global $wpdb;

		$post_ids = $wpdb->get_col(
			"
				SELECT wpplm.meta_value
				FROM {$wpdb->wordpoints_points_log_meta} AS wpplm
				LEFT JOIN {$wpdb->posts} AS p
					ON p.ID = wpplm.meta_value
				LEFT JOIN {$wpdb->wordpoints_points_logs} As wppl
					ON wppl.id = wpplm.log_id
				WHERE p.ID IS NULL
					AND wpplm.meta_key = 'post_id'
					AND wppl.log_type = 'comment_approve'
			"
		); // WPCS: cache pass.

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_comment_points_hook' );

		if ( $post_ids && is_array( $post_ids ) && $hook ) {
			foreach ( $post_ids as $post_id ) {
				$hook->clean_logs_on_post_deletion( $post_id );
			}
		}
	}
}

// EOF
