<?php

/**
 * A test case for the user rank API.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Test that the user ranks API works.
 *
 * @since 1.7.0
 *
 * @group ranks
 */
class WordPoints_User_Ranks_Test extends WordPoints_PHPUnit_TestCase_Ranks {

	/**
	 * Test that the user's rank defaults to the base rank of the group.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_get_user_rank
	 */
	public function test_user_rank_defaults_to_base() {

		$user_id = $this->factory->user->create();

		$rank = wordpoints_get_rank(
			wordpoints_get_user_rank( $user_id, $this->rank_group )
		);

		$this->assertInstanceOf( 'WordPoints_Rank', $rank );
		$this->assertSame( 'base', $rank->type );
	}

	/**
	 * Test getting a user's rank from an invalid group.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_get_user_rank
	 */
	public function test_get_invalid_group() {

		$user_id = $this->factory->user->create();

		// Test when the user has the default rank.
		$this->assertFalse( wordpoints_get_user_rank( $user_id, 'invalid' ) );

		// Test when the user has been assigned a rank.
		wordpoints_update_user_rank(
			$user_id
			, $this->factory->wordpoints_rank->create()
		);

		$this->assertFalse( wordpoints_get_user_rank( $user_id, 'invalid' ) );
	}

	/**
	 * Test getting a user's rank with an invalid user ID.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_get_user_rank
	 */
	public function test_get_invalid_id() {

		$this->assertFalse( wordpoints_get_user_rank( 0, $this->rank_group ) );
	}

	/**
	 * Test formatting a user's rank.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_get_formatted_user_rank
	 */
	public function test_format_user_rank() {

		$user_id = $this->factory->user->create();
		$rank    = $this->factory->wordpoints_rank->create_and_get();

		wordpoints_update_user_rank( $user_id, $rank->ID );

		$formatted = wordpoints_get_formatted_user_rank(
			$user_id
			, $this->rank_group
			, 'unittests'
		);

		$this->assertSame(
			'<span class="wordpoints-rank">' . $rank->name . '</span>'
			,  $formatted
		);
	}

	/**
	 * Test updating a user's rank.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_update_user_rank
	 */
	public function test_update_user_rank() {

		$rank_id = $this->factory->wordpoints_rank->create();
		$user_id = $this->factory->user->create();

		$result = wordpoints_update_user_rank( $user_id, $rank_id );

		$this->assertTrue( $result );

		$this->assertSame(
			$rank_id
			, wordpoints_get_user_rank( $user_id, $this->rank_group )
		);
	}

	/**
	 * Test update with a non-existent rank.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_update_user_rank
	 */
	public function test_update_invalid_rank() {

		$rank_id = $this->factory->wordpoints_rank->create();
		$user_id = $this->factory->user->create();

		wordpoints_delete_rank( $rank_id );

		$result = wordpoints_update_user_rank( $user_id, $rank_id );

		$this->assertFalse( $result );

		$this->assertNotEquals(
			$rank_id
			, wordpoints_get_user_rank( $user_id, $this->rank_group )
		);
	}

	/**
	 * Test updating with the same rank.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_update_user_rank
	 */
	public function test_update_same_rank() {

		$user_id = $this->factory->user->create();

		$result = wordpoints_update_user_rank(
			$user_id
			, wordpoints_get_user_rank( $user_id, $this->rank_group )
		);

		$this->assertTrue( $result );
	}

	/**
	 * Test updating user ranks in bulk.
	 *
	 * @since 2.4.0
	 *
	 * @covers ::wordpoints_update_users_to_rank
	 */
	public function test_update_users_to_rank() {

		$rank_id = $this->factory->wordpoints_rank->create();
		$user_ids = $this->factory->user->create_many( 2 );

		$old_rank_id = wordpoints_get_user_rank( $user_ids[0], $this->rank_group );

		wp_cache_set(
			$this->rank_group
			, array( $rank_id => '', $old_rank_id => '', 'other' => '' )
			, 'wordpoints_user_ranks'
		);

		wp_cache_set( $rank_id, array(), 'wordpoints_users_with_rank' );
		wp_cache_set( $old_rank_id, $user_ids, 'wordpoints_users_with_rank' );

		$mock = new WordPoints_PHPUnit_Mock_Filter();
		$mock->add_action( 'wordpoints_update_user_rank', 10, 6 );

		$result = wordpoints_update_users_to_rank( $user_ids, $rank_id, $old_rank_id );

		$this->assertTrue( $result );

		$this->assertSame( 2, $mock->call_count );
		$this->assertSame( array( $user_ids[0], $rank_id, $old_rank_id ), $mock->calls[0] );
		$this->assertSame( array( $user_ids[1], $rank_id, $old_rank_id ), $mock->calls[1] );

		$this->assertSame(
			array( 'other' => '' )
			, wp_cache_get( $this->rank_group, 'wordpoints_user_ranks' )
		);

		$this->assertFalse( wp_cache_get( $rank_id, 'wordpoints_users_with_rank' ) );
		$this->assertFalse( wp_cache_get( $old_rank_id, 'wordpoints_users_with_rank' ) );

		$this->assertSame(
			$rank_id
			, wordpoints_get_user_rank( $user_ids[0], $this->rank_group )
		);

		$this->assertSame(
			$rank_id
			, wordpoints_get_user_rank( $user_ids[1], $this->rank_group )
		);
	}

	/**
	 * Test updating user ranks in bulk.
	 *
	 * @since 2.4.0
	 *
	 * @covers ::wordpoints_update_users_to_rank
	 */
	public function test_update_users_rank_invalid() {

		$rank_id = $this->factory->wordpoints_rank->create();
		$user_ids = $this->factory->user->create_many( 2 );

		wordpoints_delete_rank( $rank_id );

		$old_rank_id = wordpoints_get_user_rank( $user_ids[0], $this->rank_group );

		$result = wordpoints_update_users_to_rank( $user_ids, $rank_id, $old_rank_id );

		$this->assertFalse( $result );

		$this->assertSame(
			$old_rank_id
			, wordpoints_get_user_rank( $user_ids[0], $this->rank_group )
		);

		$this->assertSame(
			$old_rank_id
			, wordpoints_get_user_rank( $user_ids[1], $this->rank_group )
		);
	}

	/**
	 * Test updating user ranks in bulk.
	 *
	 * @since 2.4.0
	 *
	 * @covers ::wordpoints_update_users_to_rank
	 */
	public function test_update_users_ranks_same() {

		$user_ids = $this->factory->user->create_many( 2 );

		$old_rank_id = wordpoints_get_user_rank( $user_ids[0], $this->rank_group );

		$result = wordpoints_update_users_to_rank( $user_ids, $old_rank_id, $old_rank_id );

		$this->assertTrue( $result );

		$this->assertSame(
			$old_rank_id
			, wordpoints_get_user_rank( $user_ids[0], $this->rank_group )
		);

		$this->assertSame(
			$old_rank_id
			, wordpoints_get_user_rank( $user_ids[1], $this->rank_group )
		);
	}

	/**
	 * Test updating user ranks in bulk.
	 *
	 * @since 2.4.0
	 *
	 * @covers ::wordpoints_update_users_to_rank
	 */
	public function test_update_users_ranks_set() {

		$rank_id = $this->factory->wordpoints_rank->create();
		$user_ids = $this->factory->user->create_many( 2 );

		$old_rank_id = wordpoints_get_user_rank( $user_ids[0], $this->rank_group );

		$result = wordpoints_update_users_to_rank( $user_ids, $rank_id, $old_rank_id );

		$this->assertTrue( $result );

		$result = wordpoints_update_users_to_rank( $user_ids, $old_rank_id, $rank_id );

		$this->assertTrue( $result );

		$this->assertSame(
			$old_rank_id
			, wordpoints_get_user_rank( $user_ids[0], $this->rank_group )
		);

		$this->assertSame(
			$old_rank_id
			, wordpoints_get_user_rank( $user_ids[1], $this->rank_group )
		);
	}

	/**
	 * Test getting all users with a rank.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_get_users_with_rank
	 */
	public function test_getting_all_users_with_rank() {

		// And two ranks.
		$rank_1 = $this->factory->wordpoints_rank->create();
		$rank_2 = $this->factory->wordpoints_rank->create();

		// Create three users.
		$user_ids = $this->factory->user->create_many( 3 );

		// Assign each of the ranks to one of the users.
		wordpoints_update_user_rank( $user_ids[0], $rank_1 );
		wordpoints_update_user_rank( $user_ids[1], $rank_2 );

		// We don't give a rank to the third user at all.

		// So only the user we gave the second rank should be returned.
		$this->assertSame(
			array( $user_ids[1] )
			, wordpoints_get_users_with_rank( $rank_2 )
		);

		$base_rank = WordPoints_Rank_Groups::get_group( $this->rank_group )
			->get_rank( 0 );

		$base_rank_users = wordpoints_get_users_with_rank( $base_rank );

		$this->assertContainsSame( $user_ids[2], $base_rank_users );
		$this->assertNotContains( $user_ids[0], $base_rank_users );
		$this->assertNotContains( $user_ids[1], $base_rank_users );
	}
}

// EOF
