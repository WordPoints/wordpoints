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

		parent::tearDown();
	}

	/**
	 * Test that the user ranks database table is updated.
	 *
	 * @since 2.4.0
	 */
	public function test_update_user_ranks_table() {

		global $wpdb;

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
				'blog_id' => is_multisite() ? $wpdb->blogid : '0',
				'site_id' => is_multisite() ? $wpdb->siteid : '0',
			)
			, $duplicates[ $user_rank_id_1 ]
		);

		$this->assertSameProperties(
			(object) array(
				'id' => (string) $user_rank_id_2,
				'user_id' => (string) $user_id,
				'rank_id' => (string) $third_rank_id,
				'rank_group' => 'points_type-points',
				'blog_id' => is_multisite() ? $wpdb->blogid : '0',
				'site_id' => is_multisite() ? $wpdb->siteid : '0',
			)
			, $duplicates[ $user_rank_id_2 ]
		);

		$this->assertSame(
			$rank_id
			, wordpoints_get_user_rank( $user_id, 'points_type-points' )
		);
	}
}

// EOF
