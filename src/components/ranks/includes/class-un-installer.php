<?php

/**
 * Class for un/installing the ranks component.
 *
 * @package WordPoints
 * @since 1.8.0
 */

/**
 * Un/installs the ranks component.
 *
 * @since 1.8.0
 */
class WordPoints_Ranks_Un_Installer extends WordPoints_Un_Installer_Base {

	//
	// Protected Vars.
	//

	/**
	 * @since 2.0.0
	 */
	protected $type = 'component';

	/**
	 * @since 1.8.0
	 */
	protected $updates = array(
		'1.8.0' => array( /*      -      */ 'site' => true, /*      -      */ ),
		'2.0.0' => array( 'single' => true, /*     -     */ 'network' => true ),
		'2.4.0-alpha-4' => array( 'single' => true, 'site' => true, 'network' => true ),
	);

	/**
	 * @since 2.0.0
	 */
	protected $schema = array(
		'global' => array(
			'tables' => array(
				'wordpoints_ranks' => '
					id BIGINT(20) NOT NULL AUTO_INCREMENT,
					name VARCHAR(255) NOT NULL,
					type VARCHAR(255) NOT NULL,
					rank_group VARCHAR(255) NOT NULL,
					blog_id SMALLINT(5) UNSIGNED NOT NULL,
					site_id SMALLINT(5) UNSIGNED NOT NULL,
					PRIMARY KEY  (id),
					KEY type (type(191)),
					KEY site (blog_id,site_id)',
				'wordpoints_rankmeta' => '
					meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					wordpoints_rank_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
					meta_key VARCHAR(255) DEFAULT NULL,
					meta_value LONGTEXT,
					PRIMARY KEY  (meta_id),
					KEY wordpoints_rank_id (wordpoints_rank_id)',
				'wordpoints_user_ranks' => '
					id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					user_id BIGINT(20) UNSIGNED NOT NULL,
					rank_id BIGINT(20) UNSIGNED NOT NULL,
					rank_group VARCHAR(255) NOT NULL,
					blog_id BIGINT(20) UNSIGNED NOT NULL,
					site_id BIGINT(20) UNSIGNED NOT NULL,
					PRIMARY KEY  (id),
					UNIQUE KEY (user_id,blog_id,site_id,rank_group(185))',
			),
		),
	);

	/**
	 * @since 2.0.0
	 */
	protected $uninstall = array(
		'local' => array(
			'options' => array(
				'wordpoints_filled_base_ranks',
				'wordpoints_rank_group-%',
			),
		),
	);

	/**
	 * @since 1.8.0
	 */
	protected function before_update() {

		if ( $this->network_wide ) {
			unset( $this->updates['1_8_0'] );
		}
	}

	/**
	 * @since 2.1.0
	 */
	protected function skip_per_site_install() {
		return self::SKIP_INSTALL;
	}

	/**
	 * @since 1.8.0
	 */
	protected function load_dependencies() {

		include_once( WORDPOINTS_DIR . 'components/ranks/includes/constants.php' );
	}

	/**
	 * Update a site to 1.8.0.
	 *
	 * @since 1.8.0
	 */
	protected function update_site_to_1_8_0() {
		$this->add_installed_site_id();
	}

	/**
	 * Update a site to 2.0.0.
	 *
	 * @since 2.0.0
	 */
	protected function update_network_to_2_0_0() {

		global $wpdb;

		// So that we can change tables to utf8mb4, we need to shorten the index
		// lengths to less than 767 bytes;
		$wpdb->query(
			"
			ALTER TABLE {$wpdb->wordpoints_ranks}
			DROP INDEX type,
			ADD INDEX type(type(191))
			"
		); // WPCS: cache pass.

		$this->maybe_update_tables_to_utf8mb4( 'global' );
	}

	/**
	 * Update a single site to 2.0.0.
	 *
	 * @since 2.0.0
	 */
	protected function update_single_to_2_0_0() {
		$this->update_network_to_2_0_0();
	}

	/**
	 * Update a multisite network to 2.4.0.
	 *
	 * @since 2.4.0
	 */
	protected function update_network_to_2_4_0_alpha_4() {

		WordPoints_Rank_Types::init();

		$this->update_user_ranks_table_to_2_4_0();
	}

	/**
	 * Update a site on the network to 2.4.0.
	 *
	 * @since 2.4.0
	 */
	protected function update_site_to_2_4_0_alpha_4() {
		$this->delete_ranks_for_deleted_users();
	}

	/**
	 * Update a single site to 2.4.0.
	 *
	 * @since 2.4.0
	 */
	protected function update_single_to_2_4_0_alpha_4() {

		WordPoints_Rank_Types::init();

		$this->update_user_ranks_table_to_2_4_0();
		$this->delete_ranks_for_deleted_users();
	}

	/**
	 * Deletes ranks for users who are no longer members of this site.
	 *
	 * @since 2.4.0
	 */
	protected function delete_ranks_for_deleted_users() {

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

	/**
	 * Update the user ranks table to 2.4.0.
	 *
	 * @since 2.4.0
	 */
	protected function update_user_ranks_table_to_2_4_0() {

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
			$this->update_user_ranks_remove_duplicates_2_4_0( $duplicates );
		}

		// Add the unique constraint.
		$wpdb->query(
			"
			ALTER TABLE {$wpdb->wordpoints_user_ranks}
				ADD CONSTRAINT UNIQUE (user_id,blog_id,site_id,rank_group(185))
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
	protected function update_user_ranks_remove_duplicates_2_4_0( $duplicates ) {

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

			$this->regenerate_user_ranks_2_4_0( $rank_groups );

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
			$state = new WordPoints_Multisite_Switched_State();

			foreach ( $rank_groups as $site_id => $blogs ) {

				$switcher->switch_to( array( 'network' => $site_id ) );
				$state->backup();

				foreach ( $blogs as $blog_id => $groups ) {

					switch_to_blog( $blog_id );

					$this->regenerate_user_ranks_2_4_0( $groups );
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
	protected function regenerate_user_ranks_2_4_0( $rank_groups ) {

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

return 'WordPoints_Ranks_Un_Installer';

// EOF
