<?php

/**
 * Logs 2.4.0 updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Updates the points logs to 2.4.0.
 *
 * @since 2.4.0
 */
class WordPoints_Points_Updater_2_1_4_Logs implements WordPoints_RoutineI {

	/**
	 * @since 2.4.0
	 */
	public function run() {

		$post_types         = $this->get_post_types();
		$reversal_log_types = $this->get_reversal_log_types( $post_types );
		$hits               = $this->get_hits_with_multiple_logs();

		$logs_to_delete = array();

		foreach ( $hits as $hit ) {

			$log_ids = explode( ',', $hit->log_ids );

			// If there weren't exactly two of them, we don't know what to do.
			if ( count( $log_ids ) !== 2 ) {
				continue;
			}

			$query = new WordPoints_Points_Logs_Query(
				array(
					'id__in'       => $log_ids,
					'log_type__in' => array_keys( $reversal_log_types ),
				)
			);

			// And if they aren't both the correct types, we don't know what to do.
			if ( $query->count() !== 2 ) {
				continue;
			}

			$original_log_ids = $this->get_original_log_ids( $log_ids );

			$post_publish_log_id         = min( array_keys( $original_log_ids ) );
			$post_update_log_id          = max( array_keys( $original_log_ids ) );
			$post_publish_reverse_log_id = $original_log_ids[ $post_publish_log_id ];
			$post_update_reverse_log_id  = $original_log_ids[ $post_update_log_id ];

			$query = new WordPoints_Points_Logs_Query(
				array( 'id__in' => array( $post_publish_reverse_log_id ) )
			);

			$post_publish_reverse_log = $query->get( 'row' );

			$post_type = str_replace(
				'reverse-post_publish\\'
				, ''
				, $post_publish_reverse_log->log_type
			);

			$post_id = wordpoints_get_points_log_meta(
				$post_publish_log_id
				, 'post\\' . $post_type
				, true
			);

			$post_id_2 = wordpoints_get_points_log_meta(
				$post_update_log_id
				, 'post\\' . $post_type
				, true
			);

			if ( $post_id !== $post_id_2 ) {
				continue;
			}

			$logs_to_delete[] = $post_publish_reverse_log_id;
			$logs_to_delete[] = $post_update_reverse_log_id;
			$logs_to_delete[] = $post_update_log_id;

			// Give the user their points back, as they were removed in error.
			$this->revert_log( $post_publish_reverse_log );

			$this->mark_unreversed( $post_publish_log_id );

			// Now clean up any later updates.
			$logs_to_delete = $this->clean_other_logs(
				$post_id
				, $post_type
				, $logs_to_delete
			);

		} // End foreach ( $hits ).

		// Now the legacy logs.
		$legacy_logs = $this->get_legacy_reactor_logs( $post_types );
		$post_ids    = $this->get_legacy_points_hook_post_ids();

		foreach ( $post_ids as $post_id ) {

			if ( ! isset( $legacy_logs[ $post_id ] ) ) {
				continue;
			}

			array_map( array( $this, 'revert_log' ), $legacy_logs[ $post_id ] );

			$logs_to_delete = array_merge(
				$logs_to_delete
				, wp_list_pluck( $legacy_logs[ $post_id ], 'id' )
			);

			unset( $legacy_logs[ $post_id ] );
		}

		foreach ( $legacy_logs as $logs ) {
			if ( count( $logs ) > 1 ) {

				// The first one is the original, so keep it.
				unset( $logs[0] );

				array_map( array( $this, 'revert_log' ), $logs );

				$logs_to_delete = array_merge(
					$logs_to_delete
					, wp_list_pluck( $logs, 'id' )
				);
			}
		}

		// Now delete the logs.
		if ( $logs_to_delete ) {
			$this->delete_logs( $logs_to_delete );
		}
	}

	/**
	 * Get the post types used by the hooks API.
	 *
	 * @since 2.4.0
	 *
	 * @return string[] The post types that we award points for.
	 */
	protected function get_post_types() {

		$post_types = get_post_types( array( 'public' => true ) );

		/**
		 * Filter which post types to register hook events for.
		 *
		 * @since 2.1.0
		 *
		 * @param string[] The post type slugs ("names").
		 */
		$post_types = apply_filters(
			'wordpoints_register_hook_events_for_post_types'
			, $post_types
		);

		return $post_types;
	}

	/**
	 * Get the slugs of the reversal events that we are interested in.
	 *
	 * @since 2.4.0
	 *
	 * @return array The slugs of the events.
	 */
	protected function get_reversal_log_types( $post_types ) {

		$event_slugs = array();

		foreach ( $post_types as $slug ) {
			$event_slugs[ "reverse-post_publish\\{$slug}" ] = true;
		}

		return $event_slugs;
	}

	/**
	 * Get all duplicate logs for a single hit.
	 *
	 * Finds all points logs where two logs are for the same hit, and returns the
	 * IDs of those hits and the IDs of the logs for each.
	 *
	 * @since 2.4.0
	 *
	 * @return object[] Array of rows, each row consisting of the `hit_id` and
	 *                  `log_ids`, the later being the IDs of all of the logs
	 *                  concatenated together using commas.
	 */
	protected function get_hits_with_multiple_logs() {

		global $wpdb;

		$hits = $wpdb->get_results(
			"
				SELECT `meta_value` AS `hit_id`, GROUP_CONCAT(`log_id`) AS `log_ids`
				FROM `{$wpdb->wordpoints_points_log_meta}`
				WHERE `meta_key` = 'hook_hit_id'
				GROUP BY `meta_value`
				HAVING COUNT(*) > 1
			"
		); // WPCS: cache OK.

		return $hits;
	}

	/**
	 * Get the IDs of the original logs for a bunch of reversal logs.
	 *
	 * @since 2.4.0
	 *
	 * @param int[] $log_ids  The IDs of the reversal logs.
	 *
	 * @return array The IDs of the original logs.
	 */
	protected function get_original_log_ids( $log_ids ) {

		$original_log_ids = array();

		foreach ( $log_ids as $log_id ) {

			$original_log_id = wordpoints_get_points_log_meta(
				$log_id
				, 'original_log_id'
				, true
			);

			$original_log_ids[ $original_log_id ] = $log_id;
		}

		return $original_log_ids;
	}

	/**
	 * Delete some points logs.
	 *
	 * The hits for the logs will also be deleted.
	 *
	 * @since 2.4.0
	 *
	 * @param int[] $log_ids The IDs of the logs to delete.
	 */
	protected function delete_logs( $log_ids ) {

		$hits_to_delete = array();

		global $wpdb;

		foreach ( $log_ids as $log_id ) {

			$hit_id = wordpoints_get_points_log_meta(
				$log_id
				, 'hook_hit_id'
				, true
			);

			if ( $hit_id ) {
				$hits_to_delete[] = $hit_id;
			}

			wordpoints_points_log_delete_all_metadata( $log_id );
		}

		$wpdb->query( // WPCS: unprepared SQL OK.
			"
				DELETE
				FROM `{$wpdb->wordpoints_points_logs}`
				WHERE `id` IN (" . wordpoints_prepare__in( $log_ids, '%d' ) . ')
			'
		);

		wordpoints_flush_points_logs_caches();

		// Now delete the hits.
		if ( $hits_to_delete ) {
			$this->delete_hits( $hits_to_delete );
		}
	}

	/**
	 * Delete some hook hits.
	 *
	 * @since 2.4.0
	 *
	 * @param int[] $hit_ids The IDs of the hits to delete.
	 */
	protected function delete_hits( $hit_ids ) {

		global $wpdb;

		foreach ( $hit_ids as $hit_id ) {

			$hit_ids[] = get_metadata(
				'wordpoints_hook_hit'
				, $hit_id
				, 'hook_hit_id'
				, true
			);

			delete_metadata( 'wordpoints_hook_hit', $hit_id, '', '', true );
		}

		$wpdb->query( // WPCS: unprepared SQL OK.
			"
				DELETE
				FROM `{$wpdb->wordpoints_hook_hits}`
				WHERE `id` IN (" . wordpoints_prepare__in( $hit_ids, '%d' ) . ')
			'
		); // WPCS: cache OK.
	}

	/**
	 * Cleans out any other logs relating to a post.
	 *
	 * @since 2.4.0
	 *
	 * @param int    $post_id        The ID of the post to clean logs for.
	 * @param string $post_type      The post type that this post is of.
	 * @param int[]  $logs_to_delete A list of logs that are being deleted.
	 *
	 * @return int[] The list of logs to be deleted.
	 */
	protected function clean_other_logs( $post_id, $post_type, $logs_to_delete ) {

		$query = new WordPoints_Points_Logs_Query(
			array(
				'order'        => 'ASC',
				'order_by'     => 'id',
				'id__not_in'   => $logs_to_delete,
				'log_type__in' => array( 'post_publish\\' . $post_type ),
				'meta_query'   => array(
					array(
						'key'   => 'post\\' . $post_type,
						'value' => $post_id,
					),
				),
			)
		);

		$other_logs = $query->get();

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'reverse-post_publish\\' . $post_type,
				'id__not_in' => $logs_to_delete,
				'meta_query' => array(
					array(
						'key'     => 'original_log_id',
						'value'   => wp_list_pluck( $other_logs, 'id' ),
						'compare' => 'IN',
					),
				),
			)
		);

		$reversal_logs = array();

		foreach ( $query->get() as $log ) {

			$original_log_id = wordpoints_get_points_log_meta(
				$log->id
				, 'original_log_id'
				, true
			);

			$reversal_logs[ $original_log_id ] = $log;
		}

		foreach ( $other_logs as $index => $log ) {

			// If this is a log that was reversed within less than a second or so
			// of its occurrence, it is almost certainly the result of another
			// update, and it is just cluttering things up.
			if (
				isset( $reversal_logs[ $log->id ] )
				&& strtotime( $reversal_logs[ $log->id ]->date ) - strtotime( $log->date ) < 2
			) {
				$logs_to_delete[] = $log->id;
				$logs_to_delete[] = $reversal_logs[ $log->id ]->id;
			}
		}

		return $logs_to_delete;
	}

	/**
	 * Give a user back the points that a log removed.
	 *
	 * @since 2.4.0
	 *
	 * @param object $log The points log object.
	 */
	protected function revert_log( $log ) {

		add_filter( 'wordpoints_points_log', '__return_false' );

		wordpoints_alter_points(
			$log->user_id
			, -$log->points
			, $log->points_type
			, $log->log_type
		);

		remove_filter( 'wordpoints_points_log', '__return_false' );
	}

	/**
	 * Mark a points log as unreversed.
	 *
	 * @since 2.4.0
	 *
	 * @param int $log_id The ID of the log to mark as unreversed.
	 */
	protected function mark_unreversed( $log_id ) {

		wordpoints_delete_points_log_meta( $log_id, 'auto_reversed' );

		$hit_id = wordpoints_get_points_log_meta( $log_id, 'hook_hit_id', true );

		delete_metadata( 'wordpoints_hook_hit', $hit_id, 'reverse_fired' );
	}

	/**
	 * Get all of the legacy logs grouped by post ID.
	 *
	 * @since 2.4.0
	 *
	 * @param string[] $post_types The post types to retrieve logs for.
	 *
	 * @return array[] The logs, grouped by post ID.
	 */
	protected function get_legacy_reactor_logs( $post_types ) {

		$legacy_log_types = array();

		foreach ( $post_types as $post_type ) {
			$legacy_log_types[] = "points_legacy_post_publish\\{$post_type}";
		}

		$query = new WordPoints_Points_Logs_Query(
			array( 'log_type__in' => $legacy_log_types, 'order' => 'ASC' )
		);

		$legacy_logs = array();

		foreach ( $query->get() as $legacy_log ) {

			$post_type = str_replace(
				'points_legacy_post_publish\\'
				, ''
				, $legacy_log->log_type
			);

			$post_id = wordpoints_get_points_log_meta(
				$legacy_log->id
				, "post\\{$post_type}"
				, true
			);

			$legacy_logs[ $post_id ][] = $legacy_log;
		}

		return $legacy_logs;
	}

	/**
	 * Get the post IDs from the old points hooks logs.
	 *
	 * @since 2.4.0
	 *
	 * @return int[] The post IDs for the points hooks logs.
	 */
	protected function get_legacy_points_hook_post_ids() {

		global $wpdb;

		$post_ids = $wpdb->get_col(
			"
				SELECT `meta_value`
				FROM `{$wpdb->wordpoints_points_log_meta}` AS `meta`
				INNER JOIN `{$wpdb->wordpoints_points_logs}` AS `log`
					ON `log`.`id` = `meta`.`log_id`
				WHERE `meta_key` = 'post_id'
					AND `log`.`log_type` = 'post_publish'
			"
		); // WPCS: cache OK.

		return $post_ids;
	}
}

// EOF
