<?php

/**
 * User ranks 2.4.0 updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Updates user ranks for 2.4.0.
 *
 * Deletes ranks for users who are no longer members of this site.
 *
 * @since 2.4.0
 */
class WordPoints_Ranks_Updater_2_4_0_User_Ranks implements WordPoints_RoutineI {

	/**
	 * @since 2.4.0
	 */
	public function run() {

		global $wpdb;

		$query = new WP_User_Query(
			array( 'fields' => 'ID', 'count_total' => false, 'orderby' => 'ID' )
		);

		$wpdb->query( // WPCS: unprepared SQL OK.
			$wpdb->prepare( // WPCS: unprepared SQL OK.
				"
					DELETE FROM `{$wpdb->wordpoints_user_ranks}`
					WHERE `user_id` NOT IN (
						{$query->request}
					)
						AND `blog_id` = %d
						AND `site_id` = %d
				"
				, $wpdb->blogid
				, $wpdb->siteid
			)
		);

		foreach ( WordPoints_Rank_Groups::get() as $rank_group ) {
			wp_cache_delete( $rank_group->slug, 'wordpoints_user_ranks' );
		}
	}
}

// EOF
