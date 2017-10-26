<?php

/**
 * A test case for the WordPoints_User_Ranks_Query class.
 *
 * @package WordPoints\Tests
 * @since 2.4.0
 */

/**
 * Tests the WordPoints_User_Ranks_Query class.
 *
 * @since 2.4.0
 *
 * @group ranks
 *
 * @covers WordPoints_User_Ranks_Query
 */
class WordPoints_User_Ranks_Query_Test extends WordPoints_PHPUnit_TestCase_Ranks {

	/**
	 * Test the query arg defaults.
	 *
	 * @since 2.4.0
	 */
	public function test_defaults() {

		global $wpdb;

		$query = new WordPoints_User_Ranks_Query();

		$this->assertSame( $wpdb->blogid, $query->get_arg( 'blog_id' ) );
		$this->assertSame( $wpdb->siteid, $query->get_arg( 'site_id' ) );
	}

	/**
	 * Test that the query actually works.
	 *
	 * @since 2.4.0
	 */
	public function test_results() {

		$user_id = $this->factory->user->create();
		$rank_id = $this->factory->wordpoints->rank->create();

		wordpoints_update_user_rank( $user_id, $rank_id );

		$query   = new WordPoints_User_Ranks_Query( array( 'user_id' => $user_id ) );
		$results = $query->get();

		$this->assertCount( 1, $results );
		$this->assertSameProperties(
			(object) array(
				'id'         => $results[0]->id,
				'user_id'    => (string) $user_id,
				'rank_id'    => (string) $rank_id,
				'rank_group' => 'test_group',
				'blog_id'    => is_multisite() ? '1' : '0',
				'site_id'    => is_multisite() ? '1' : '0',
			)
			, $results[0]
		);
	}

	/**
	 * Test that the get() and count() methods return false if filling fails.
	 *
	 * @since 2.4.0
	 */
	public function test_maybe_fill_returns_false() {

		$mock = $this->createPartialMock(
			'WordPoints_User_Ranks_Query'
			, array( 'maybe_fill_in_base_rank_for_users' )
		);

		$mock->method( 'maybe_fill_in_base_rank_for_users' )->willReturn( false );

		$this->assertFalse( $mock->count() );
		$this->assertFalse( $mock->get() );
	}

	/**
	 * Tests that the maybe fill function returns true if no rank ID is set.
	 *
	 * @since 2.4.0
	 */
	public function test_maybe_fill_no_rank_id() {

		$query = new WordPoints_User_Ranks_Query();

		$this->assertTrue( $query->maybe_fill_in_base_rank_for_users() );
	}

	/**
	 * Tests that the maybe fill function returns true if the rank ID is invalid.
	 *
	 * @since 2.4.0
	 */
	public function test_maybe_fill_invalid_rank_id() {

		$query = new WordPoints_User_Ranks_Query();

		$this->assertTrue( $query->maybe_fill_in_base_rank_for_users( 982 ) );
	}

	/**
	 * Tests that the maybe fill function returns true if the rank type is not base.
	 *
	 * @since 2.4.0
	 */
	public function test_maybe_fill_not_base_rank() {

		$rank_id = $this->factory->wordpoints->rank->create();

		$query = new WordPoints_User_Ranks_Query();

		$this->assertTrue( $query->maybe_fill_in_base_rank_for_users( $rank_id ) );
	}

	/**
	 * Tests that the maybe fill function returns true if the rank is already filled.
	 *
	 * @since 2.4.0
	 */
	public function test_maybe_fill_already_filled() {

		$group   = WordPoints_Rank_Groups::get_group( $this->rank_group );
		$rank_id = $group->get_base_rank();

		update_option( 'wordpoints_filled_base_ranks', array( $rank_id => true ) );

		$query = new WordPoints_User_Ranks_Query();

		$this->assertTrue( $query->maybe_fill_in_base_rank_for_users( $rank_id ) );
	}

	/**
	 * Tests that the maybe fill function returns false if filling fails.
	 *
	 * @since 2.4.0
	 */
	public function test_maybe_fill_fails() {

		$group   = WordPoints_Rank_Groups::get_group( $this->rank_group );
		$rank_id = $group->get_base_rank();

		$mock = $this->createPartialMock(
			'WordPoints_User_Ranks_Query'
			, array( 'fill_in_base_rank_for_users' )
		);

		$mock->method( 'fill_in_base_rank_for_users' )->willReturn( false );

		$this->assertFalse( $mock->maybe_fill_in_base_rank_for_users( $rank_id ) );
	}

	/**
	 * Tests that the maybe fill function returns true if filling succeeds.
	 *
	 * @since 2.4.0
	 */
	public function test_maybe_fill_succeeds() {

		$group   = WordPoints_Rank_Groups::get_group( $this->rank_group );
		$rank_id = $group->get_base_rank();

		$mock = $this->createPartialMock(
			'WordPoints_User_Ranks_Query'
			, array( 'fill_in_base_rank_for_users' )
		);

		$mock->method( 'fill_in_base_rank_for_users' )->willReturn( true );

		$this->assertTrue( $mock->maybe_fill_in_base_rank_for_users( $rank_id ) );

		$this->assertSame(
			array( $rank_id => true )
			, get_option( 'wordpoints_filled_base_ranks' )
		);
	}

	/**
	 * Tests that it fills the base rank if needed.
	 *
	 * @since 2.4.0
	 */
	public function test_maybe_fills_if_needed() {

		$group = WordPoints_Rank_Groups::get_group( $this->rank_group );

		$base_rank_id = $group->get_base_rank();

		$group->remove_rank( $base_rank_id );

		// Create the users while the rank isn't registered so it won't be assigned
		// to them automatically when they are created.
		$user_ids = $this->factory->user->create_many( 2 );

		$group->add_rank( $base_rank_id, 0 );

		$query = new WordPoints_User_Ranks_Query(
			array( 'user_id__in' => $user_ids )
		);

		$results = $query->get();

		$this->assertCount( 0, $results );

		$query->maybe_fill_in_base_rank_for_users( $base_rank_id );

		$results = $query->get();

		$this->assertCount( 2, $results );
		$this->assertSameProperties(
			(object) array(
				'id'         => $results[0]->id,
				'user_id'    => (string) $user_ids[0],
				'rank_id'    => (string) $base_rank_id,
				'rank_group' => 'test_group',
				'blog_id'    => is_multisite() ? '1' : '0',
				'site_id'    => is_multisite() ? '1' : '0',
			)
			, $results[0]
		);
		$this->assertSameProperties(
			(object) array(
				'id'         => $results[1]->id,
				'user_id'    => (string) $user_ids[1],
				'rank_id'    => (string) $base_rank_id,
				'rank_group' => 'test_group',
				'blog_id'    => is_multisite() ? '1' : '0',
				'site_id'    => is_multisite() ? '1' : '0',
			)
			, $results[1]
		);
	}

	/**
	 * Tests that it fills the base rank based on the ID arg it set.
	 *
	 * @since 2.4.0
	 */
	public function test_maybe_fill_uses_rank_id_arg() {

		$group = WordPoints_Rank_Groups::get_group( $this->rank_group );

		$base_rank_id = $group->get_base_rank();

		$group->remove_rank( $base_rank_id );

		// Create the users while the rank isn't registered so it won't be assigned
		// to them automatically when they are created.
		$user_ids = $this->factory->user->create_many( 2 );

		$group->add_rank( $base_rank_id, 0 );

		$query = new WordPoints_User_Ranks_Query(
			array( 'user_id__in' => $user_ids )
		);

		$results = $query->get();

		$this->assertCount( 0, $results );

		$query->set_args( array( 'rank_id' => $base_rank_id ) );

		$results = $query->get();

		$this->assertCount( 2, $results );
		$this->assertSameProperties(
			(object) array(
				'id'         => $results[0]->id,
				'user_id'    => (string) $user_ids[0],
				'rank_id'    => (string) $base_rank_id,
				'rank_group' => 'test_group',
				'blog_id'    => is_multisite() ? '1' : '0',
				'site_id'    => is_multisite() ? '1' : '0',
			)
			, $results[0]
		);
		$this->assertSameProperties(
			(object) array(
				'id'         => $results[1]->id,
				'user_id'    => (string) $user_ids[1],
				'rank_id'    => (string) $base_rank_id,
				'rank_group' => 'test_group',
				'blog_id'    => is_multisite() ? '1' : '0',
				'site_id'    => is_multisite() ? '1' : '0',
			)
			, $results[1]
		);
	}

	/**
	 * Tests that only users for the current site are filled for base ranks.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_maybe_fill_multisite() {

		$base_rank = WordPoints_Rank_Groups::get_group( $this->rank_group )
			->get_base_rank();

		$user_id = $this->factory->user->create();

		switch_to_blog( $this->factory->blog->create() );
		$other_user_id = $this->factory->user->create();
		restore_current_blog();

		$query = new WordPoints_User_Ranks_Query(
			array(
				'fields'      => 'user_id',
				'user_id__in' => array( $user_id, $other_user_id ),
				'rank_id'     => $base_rank,
				'blog_id'     => null,
			)
		);

		$base_rank_users = $query->get( 'col' );

		$this->assertContainsSame( (string) $user_id, $base_rank_users );
		$this->assertNotContains( $other_user_id, $base_rank_users );
	}
}

// EOF
