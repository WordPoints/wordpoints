<?php

/**
 * A test case for the ranks component update to 2.4.0.
 *
 * @package WordPoints\Tests
 * @since 2.4.0
 */

/**
 * Test that the ranks component updates to 2.4.0 properly.
 *
 * @since 2.4.0
 *
 * @group ranks
 * @group update
 *
 * @covers WordPoints_Ranks_Un_Installer::update_network_to_2_4_0_alpha_4
 * @covers WordPoints_Ranks_Un_Installer::update_single_to_2_4_0_alpha_4
 * @covers WordPoints_Ranks_Un_Installer::delete_ranks_for_deleted_users
 * @covers WordPoints_Ranks_Un_Installer::update_user_ranks_table_to_2_4_0
 * @covers WordPoints_Ranks_Un_Installer::update_user_ranks_remove_duplicates_2_4_0
 * @covers WordPoints_Ranks_Un_Installer::regenerate_user_ranks_2_4_0
 */
class WordPoints_Ranks_2_4_0_Alpha_4_Update_Test extends WordPoints_PHPUnit_TestCase_Ranks {

	/**
	 * @since 2.4.0
	 */
	protected $previous_version = '2.3.0';

	/**
	 * @since 2.4.0
	 */
	public function setUp() {

		parent::setUp();

		$this->create_points_type();

		WordPoints_Rank_Groups::register_group(
			'points_type-points'
			, array( 'name' => 'Points' )
		);

		WordPoints_Rank_Types::register_type(
			'points-points'
			, 'WordPoints_Points_Rank_Type'
			, array( 'points_type' => 'points' )
		);

		WordPoints_Rank_Groups::register_type_for_group(
			'points-points'
			, 'points_type-points'
		);
	}

	/**
	 * @since 2.4.0
	 */
	public function tearDown() {

		WordPoints_Rank_Types::deregister_type( 'points-points' );
		WordPoints_Rank_Groups::deregister_group( 'points_type-points' );

		// Restore the DB version since we end up committing the changes.
		$this->set_component_db_version( 'ranks', WORDPOINTS_VERSION );

		global $wpdb;

		$wpdb->query( 'COMMIT' );

		parent::tearDown();
	}

	/**
	 * Reverts the table to the previous state, so that we can update it.
	 *
	 * @since 2.4.0
	 */
	public function revert_table() {

		global $wpdb;

		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
		remove_filter( 'query', array( $this, 'do_not_alter_tables' ) );

		$wpdb->query( "DROP TABLE {$wpdb->wordpoints_user_ranks}" );
		$wpdb->query(
			"
			CREATE TABLE {$wpdb->wordpoints_user_ranks} (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
				rank_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
				PRIMARY KEY  (id)
			)
			"
		);
	}

	/**
	 * Test that the user ranks database table is updated.
	 *
	 * @since 2.4.0
	 */
	public function test_update_user_ranks_table() {

		global $wpdb;

		$this->revert_table();

		// Simulate the update.
		$this->update_component();

		$this->assertTableHasColumn( 'rank_group', $wpdb->wordpoints_user_ranks );
		$this->assertTableHasColumn( 'blog_id', $wpdb->wordpoints_user_ranks );
		$this->assertTableHasColumn( 'site_id', $wpdb->wordpoints_user_ranks );

		$schema = $wpdb->get_var( "SHOW CREATE TABLE `{$wpdb->wordpoints_user_ranks}`", 1 );

		$this->assertNotContains( 'DEFAULT 0', $schema );
		$this->assertStringContains(
			'UNIQUE KEY `user_id` (`user_id`,`blog_id`,`site_id`,`rank_group`(185))'
			, $schema
		);
	}

	/**
	 * Test that duplicate user ranks are properly handled.
	 *
	 * @since 2.4.0
	 */
	public function test_update_user_ranks_table_duplicates() {

		global $wpdb;

		$another_rank_id = wordpoints_add_rank(
			'Rank 1'
			, 'points-points'
			, 'points_type-points'
			, 1
			, array( 'points_type' => 'points', 'points' => 30 )
		);

		$rank_id = wordpoints_add_rank(
			'Rank 2'
			, 'points-points'
			, 'points_type-points'
			, 2
			, array( 'points_type' => 'points', 'points' => 60 )
		);

		$third_rank_id = wordpoints_add_rank(
			'Rank 3'
			, 'points-points'
			, 'points_type-points'
			, 3
			, array( 'points_type' => 'points', 'points' => 90 )
		);

		$user_id = $this->factory->user->create();

		update_user_meta( $user_id, wordpoints_get_points_user_meta_key( 'points' ), 70 );

		$this->revert_table();

		$wpdb->insert(
			$wpdb->wordpoints_user_ranks
			, array( 'user_id' => $user_id, 'rank_id' => $another_rank_id )
		);

		$user_rank_id_1 = $wpdb->insert_id;

		$wpdb->insert(
			$wpdb->wordpoints_user_ranks
			, array( 'user_id' => $user_id, 'rank_id' => $third_rank_id )
		);

		$user_rank_id_2 = $wpdb->insert_id;

		// Simulate the update.
		$this->update_component();

		$duplicates = get_site_option( 'wordpoints_ranks_2_4_0_update_duplicates' );

		$this->assertCount( 2, $duplicates );

		$this->assertSameProperties(
			(object) array(
				'id' => (string) $user_rank_id_1,
				'user_id' => (string) $user_id,
				'rank_id' => (string) $another_rank_id,
				'rank_group' => 'points_type-points',
				'blog_id' => is_multisite() ? (string) $wpdb->blogid : '0',
				'site_id' => is_multisite() ? (string) $wpdb->siteid : '0',
			)
			, $duplicates[ $user_rank_id_1 ]
		);

		$this->assertSameProperties(
			(object) array(
				'id' => (string) $user_rank_id_2,
				'user_id' => (string) $user_id,
				'rank_id' => (string) $third_rank_id,
				'rank_group' => 'points_type-points',
				'blog_id' => is_multisite() ? (string) $wpdb->blogid : '0',
				'site_id' => is_multisite() ? (string) $wpdb->siteid : '0',
			)
			, $duplicates[ $user_rank_id_2 ]
		);

		$this->assertSame(
			$rank_id
			, wordpoints_get_user_rank( $user_id, 'points_type-points' )
		);
	}

	/**
	 * Tests that ranks for deleted users are deleted.
	 *
	 * @since 2.4.0
	 */
	public function test_delete_ranks_for_deleted_users() {

		global $wpdb;

		$rank_id = WordPoints_Rank_Groups::get_group( 'points_type-points' )
			->get_base_rank();

		$user_id = $this->factory->user->create();

		self::delete_user( $user_id );

		// Revert the table for create realism.
		$this->revert_table();

		// Give a rank to a nonexistent user.
		$wpdb->insert(
			$wpdb->wordpoints_user_ranks
			, array( 'user_id' => $user_id, 'rank_id' => $rank_id )
		);

		// Don't use blog and site ID fields, since they aren't in the table yet.
		$query = new WordPoints_User_Ranks_Query(
			array( 'user_id' => $user_id, 'blog_id' => null, 'site_id' => null )
		);

		$this->assertSame( 1, $query->count() );

		wp_cache_set(
			'points_type-points'
			, array( $rank_id => array( $user_id => true ) )
			, 'wordpoints_user_ranks'
		);

		// Simulate the update.
		$this->update_component();

		$this->assertSame( 0, $query->count() );

		$this->assertFalse(
			wp_cache_get( 'points_type-points', 'wordpoints_user_ranks' )
		);
	}

	/**
	 * Tests that ranks for users who have been removed from the blog are deleted.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_delete_ranks_for_deleted_users_multisite() {

		global $wpdb;

		$rank_id = WordPoints_Rank_Groups::get_group( 'points_type-points' )
			->get_base_rank();

		$user_id = $this->factory->user->create();

		remove_user_from_blog( $user_id );

		$blog_id = $this->factory->blog->create();
		switch_to_blog( $blog_id );
		$other_rank_id = $this->factory->wordpoints->rank->create();
		$other_user_id = $this->factory->user->create();
		restore_current_blog();

		// Revert the table for create realism.
		$this->revert_table();

		// Give a rank to a user who is no longer a member of this site.
		$wpdb->insert(
			$wpdb->wordpoints_user_ranks
			, array( 'user_id' => $user_id, 'rank_id' => $rank_id )
		);

		// And a rank to a user who is not a member of this site, but who has a
		// rank on another site.
		$wpdb->insert(
			$wpdb->wordpoints_user_ranks
			, array( 'user_id' => $other_user_id, 'rank_id' => $other_rank_id )
		);

		$query = new WordPoints_User_Ranks_Query(
			array(
				'fields'      => 'user_id',
				'user_id__in' => array( $user_id, $other_user_id ),
				'blog_id'     => null,
				'site_id'     => null,
			)
		);

		$this->assertSame( 2, $query->count() );

		// Simulate the update.
		$this->update_component();

		$this->assertSame( 1, $query->count() );
		$this->assertSame( (string) $other_user_id, $query->get( 'var' ) );

		// Clean up.
		wpmu_delete_blog( $blog_id, true );
	}
}

// EOF
