<?php

/**
 * Tables 2.4.0 updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Updates ranks DB tables for 2.4.0.
 *
 * Updates the user ranks table.
 *
 * @since 2.4.0
 */
class WordPoints_Ranks_Updater_2_4_0_Tables implements WordPoints_RoutineI {

	/**
	 * @since 2.4.0
	 */
	public function run() {

		WordPoints_Rank_Types::init();

		global $wpdb;

		// Drop the defaults of 0 from these columns, it isn't needed.
		$wpdb->query(
			"
			ALTER TABLE {$wpdb->wordpoints_user_ranks}
				ALTER COLUMN `user_id` DROP DEFAULT,
				ALTER COLUMN `rank_id` DROP DEFAULT
			"
		); // WPCS: cache pass.

		// Add the new `rank_group`, `blog_id`, and `site_id` columns to the table.
		$wpdb->query(
			"
			ALTER TABLE {$wpdb->wordpoints_user_ranks}
				ADD COLUMN `rank_group` VARCHAR(255) NOT NULL
					AFTER `rank_id`,
				ADD COLUMN `blog_id` BIGINT(20) UNSIGNED NOT NULL
					AFTER `rank_group`,
				ADD COLUMN `site_id` BIGINT(20) UNSIGNED NOT NULL
					AFTER `blog_id`
			"
		); // WPCS: cache pass.

		// Fill in the new columns.
		$wpdb->query(
			"
			UPDATE `{$wpdb->wordpoints_user_ranks}` `user_ranks`
			INNER JOIN `{$wpdb->wordpoints_ranks}` `ranks`
				ON `ranks`.`id` = `user_ranks`.`rank_id`
			SET `user_ranks`.`rank_group` = `ranks`.`rank_group`,
				`user_ranks`.`blog_id` = `ranks`.`blog_id`,
				`user_ranks`.`site_id` = `ranks`.`site_id`
			"
		); // WPCS: cache pass.

		// Find all duplicate values, if there are any.
		$duplicates = $wpdb->get_results(
			"
			SELECT `user_ranks`.*
			FROM `{$wpdb->wordpoints_user_ranks}` `user_ranks`
			INNER JOIN (
				SELECT `rank_group`, `user_id`, `blog_id`, `site_id`
				FROM `{$wpdb->wordpoints_user_ranks}`
				GROUP BY `rank_group`
				HAVING COUNT(id) > 1
			) `duplicate`
				ON `duplicate`.`rank_group` = `user_ranks`.`rank_group`
					AND `duplicate`.`user_id` = `user_ranks`.`user_id`
					AND `duplicate`.`blog_id` = `user_ranks`.`blog_id`
					AND `duplicate`.`site_id` = `user_ranks`.`site_id`
			"
			, OBJECT_K
		); // WPCS: cache pass.

		if ( $duplicates ) {
			$this->remove_duplicates( $duplicates );
		}

		// Add the unique constraint.
		$wpdb->query(
			"
			ALTER TABLE {$wpdb->wordpoints_user_ranks}
				ADD CONSTRAINT UNIQUE user_rank (user_id,blog_id,site_id,rank_group(185))
			"
		); // WPCS: cache pass.
	}

	/**
	 * Remove duplicate entries found in the user ranks table.
	 *
	 * @since 2.4.0
	 *
	 * @param array[] $duplicates The duplicate rows.
	 */
	protected function remove_duplicates( $duplicates ) {

		global $wpdb;

		// Save a list of the duplicates, for reference.
		update_site_option(
			'wordpoints_ranks_2_4_0_update_duplicates'
			, $duplicates
		);

		// Delete the duplicates.
		$wpdb->query( // WPCS: unprepared SQL OK.
			"
			DELETE FROM `{$wpdb->wordpoints_user_ranks}`
			WHERE `id` IN (" . wordpoints_prepare__in( array_keys( $duplicates ), '%d' ) . ')
			'
		); // WPCS: cache OK.

		// Regenerate the ranks of the affected users.
		$rank_groups = array();

		if ( ! is_multisite() ) {

			foreach ( $duplicates as $duplicate ) {
				$rank_groups[ $duplicate->rank_group ][ $duplicate->user_id ] = $duplicate->user_id;
			}

			$this->regenerate_user_ranks( $rank_groups );

		} else {

			foreach ( $duplicates as $duplicate ) {

				$rank_groups
				[ $duplicate->site_id ]
				[ $duplicate->blog_id ]
				[ $duplicate->rank_group ]
				[ $duplicate->user_id ] = $duplicate->user_id;
			}

			/** @var WordPoints_Entity_Contexts $switcher */
			$switcher = wordpoints_entities()->get_sub_app( 'contexts' );
			$state    = new WordPoints_Multisite_Switched_State();

			foreach ( $rank_groups as $site_id => $blogs ) {

				$switcher->switch_to( array( 'network' => $site_id ) );
				$state->backup();

				foreach ( $blogs as $blog_id => $groups ) {

					switch_to_blog( $blog_id );

					$this->regenerate_user_ranks( $groups );
				}

				$state->restore();
				$switcher->switch_back();
			}

		} // End if ( ! is_multisite() ) else {}.
	}

	/**
	 * Regenerate ranks for users.
	 *
	 * @since 2.4.0
	 *
	 * @param int[][] $rank_groups The IDs of users whose ranks to regenerate,
	 *                             indexed by rank group.
	 */
	protected function regenerate_user_ranks( $rank_groups ) {

		foreach ( $rank_groups as $rank_group => $user_ids ) {

			$rank_group = WordPoints_Rank_Groups::get_group( $rank_group );

			if ( ! $rank_group ) {
				continue;
			}

			$base_rank = wordpoints_get_rank( $rank_group->get_base_rank() );

			if ( ! $base_rank ) {
				continue;
			}

			$rank_type = WordPoints_Rank_Types::get_type( $base_rank->type );

			if ( ! $rank_type ) {
				continue;
			}

			foreach ( $user_ids as $user_id ) {

				$new_rank = $rank_type->maybe_increase_user_rank(
					$user_id
					, $base_rank
				);

				wordpoints_update_user_rank( $user_id, $new_rank->ID );
			}
		}
	}
}

// EOF
