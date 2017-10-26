<?php

/**
 * Logs 1.2.0 updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Updates points logs for 1.2.0.
 *
 * @since 2.4.0
 */
class WordPoints_Points_Updater_1_2_0_Logs implements WordPoints_RoutineI {

	/**
	 * @since 2.4.0
	 */
	public function run() {

		$this->remove_logs_for_deleted_users();
		$this->regenerate_logs_for_deleted_posts();
		$this->regenerate_logs_for_deleted_comments();
	}

	/**
	 * Remove the points logs of users who have been deleted.
	 *
	 * @since 2.4.0
	 */
	protected function remove_logs_for_deleted_users() {

		global $wpdb;

		$log_ids = $wpdb->get_col(
			"
				SELECT wppl.id
				FROM {$wpdb->wordpoints_points_logs} AS wppl
				LEFT JOIN {$wpdb->users} as u
					ON wppl.user_id = u.ID
				WHERE u.ID IS NULL
			"
		); // WPCS: cache pass.

		if ( $log_ids && is_array( $log_ids ) ) {

			$wpdb->query( // WPCS: unprepared SQL OK
				"
					DELETE
					FROM {$wpdb->wordpoints_points_logs}
					WHERE `id` IN (" . implode( ',', array_map( 'absint', $log_ids ) ) . ')
				'
			); // WPCS: cache pass (points logs weren't cached until 1.5.0).

			foreach ( $log_ids as $log_id ) {
				wordpoints_points_log_delete_all_metadata( $log_id );
			}
		}
	}

	/**
	 * Regenerate the points logs for deleted posts.
	 *
	 * @since 2.4.0
	 */
	protected function regenerate_logs_for_deleted_posts() {

		global $wpdb;

		$post_ids = $wpdb->get_col(
			"
				SELECT wpplm.meta_value
				FROM {$wpdb->wordpoints_points_log_meta} AS wpplm
				LEFT JOIN {$wpdb->posts} AS p
					ON p.ID = wpplm.meta_value
				WHERE p.ID IS NULL
					AND wpplm.meta_key = 'post_id'
			"
		); // WPCS: cache pass.

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_post_points_hook' );

		if ( $post_ids && is_array( $post_ids ) && $hook ) {
			foreach ( $post_ids as $post_id ) {
				$hook->clean_logs_on_post_deletion( $post_id );
			}
		}
	}

	/**
	 * Regenerate the points logs for deleted comments.
	 *
	 * @since 2.4.0
	 */
	protected function regenerate_logs_for_deleted_comments() {

		global $wpdb;

		$comment_ids = $wpdb->get_col(
			"
				SELECT wpplm.meta_value
				FROM {$wpdb->wordpoints_points_log_meta} AS wpplm
				LEFT JOIN {$wpdb->comments} AS c
					ON c.comment_ID = wpplm.meta_value
				WHERE c.comment_ID IS NULL
					AND wpplm.meta_key = 'comment_id'
			"
		); // WPCS: cache pass.

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_comment_points_hook' );

		if ( $comment_ids && is_array( $comment_ids ) && $hook ) {
			foreach ( $comment_ids as $comment_id ) {
				$hook->clean_logs_on_comment_deletion( $comment_id );
			}
		}
	}
}

// EOF
