<?php

/**
 * A test case for the ranks API.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Test that the ranks API works.
 *
 * @since 1.7.0
 *
 * @group ranks
 */
class WordPoints_Ranks_Test extends WordPoints_Ranks_UnitTestCase {

	/**
	 * Set up before the tests.
	 *
	 * @since 1.7.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		WordPoints_Rank_Types::register_type(
			__CLASS__
			, 'WordPoints_Test_Rank_Type'
		);

		WordPoints_Rank_Groups::register_group(
			__CLASS__
			, array( 'name' => __CLASS__ )
		);

		WordPoints_Rank_Groups::register_type_for_group( __CLASS__, __CLASS__ );
	}

	/**
	 * Clean up after each test.
	 *
	 * @since 1.7.0
	 */
	public static function tearDownAfterClass() {

		parent::tearDownAfterClass();

		WordPoints_Rank_Groups::deregister_group( __CLASS__ );
		WordPoints_Rank_Types::deregister_type( __CLASS__ );
	}

	/**
	 * Test that a valid rank type must be passed to add a rank.
	 *
	 * @since 1.7.0
	 */
	public function test_add_requires_valid_type() {

		$rank = wordpoints_add_rank(
			'Test Rank'
			, 'not_a_type'
			, 'test_group'
			, 1
			, array( 'test_meta' => 'ranks' )
		);

		$this->assertFalse( $rank );
	}

	/**
	 * Test that a valid rank group must be passed to add a rank.
	 *
	 * @since 1.7.0
	 */
	public function test_add_requires_valid_group() {

		$rank = wordpoints_add_rank(
			'Test Rank'
			, 'test_type'
			, 'not_a_group'
			, 1
			, array( 'test_meta' => 'ranks' )
		);

		$this->assertFalse( $rank );
	}

	/**
	 * Test that valid metadata must be passed to add a rank.
	 *
	 * @since 1.7.0
	 */
	public function test_add_requires_valid_metadata() {

		$rank = wordpoints_add_rank(
			'Test Rank'
			, 'test_type'
			, 'test_group'
			, 1
			, array( 'not' => 'ranks' )
		);

		$this->assertFalse( $rank );
	}

	/**
	 * Test adding a rank.
	 *
	 * @since 1.7.0
	 */
	public function test_add_rank() {

		$rank = wordpoints_add_rank(
			'Test Rank'
			, 'test_type'
			, 'test_group'
			, 1
			, array( 'test_meta' => 'ranks' )
		);

		$this->assertInternalType( 'int', $rank );

		$this->assertEquals(
			'ranks'
			, wordpoints_get_rank_meta( $rank, 'test_meta', true )
		);
	}

	/**
	 * Test that updating a rank requires a valid rank ID.
	 *
	 * @since 1.7.0
	 */
	public function test_update_rank_requires_valid_id() {

		$rank_id = $this->factory->wordpoints_rank->create();

		$result = wordpoints_update_rank(
			$rank_id + 5
			, 'A test'
			, __CLASS__
			, __CLASS__
			, 0
			, array( 'test_meta' => __CLASS__ )
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test that updating a rank requires a valid rank type.
	 *
	 * @since 1.7.0
	 */
	public function test_update_rank_requires_valid_type() {

		$rank_id = $this->factory->wordpoints_rank->create();

		$result = wordpoints_update_rank(
			$rank_id
			, 'A test'
			, 'not_a_type'
			, __CLASS__
			, 0
			, array( 'test_meta' => __CLASS__ )
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test that updating a rank requires a valid rank group.
	 *
	 * @since 1.7.0
	 */
	public function test_update_rank_requires_valid_group() {

		$rank_id = $this->factory->wordpoints_rank->create();

		$result = wordpoints_update_rank(
			$rank_id
			, 'A test'
			, __CLASS__
			, 'not_a_group'
			, 0
			, array( 'test_meta' => __CLASS__ )
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test that updating a rank requires valid meta.
	 *
	 * @since 1.7.0
	 */
	public function test_update_rank_requires_valid_meta() {

		$rank_id = $this->factory->wordpoints_rank->create();

		$result = wordpoints_update_rank(
			$rank_id
			, 'A test'
			, __CLASS__
			, __CLASS__
			, 0
			, array( 'not' => 'correct' )
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test updating a rank.
	 *
	 * @since 1.7.0
	 */
	public function test_update_rank() {

		$this->factory->wordpoints_rank->create_many(
			2
			, array( 'group' => __CLASS__, 'type' => __CLASS__ )
		);

		$rank_id = $this->factory->wordpoints_rank->create(
			array( 'group' => __CLASS__, 'type' => __CLASS__ )
		);

		$result = wordpoints_update_rank(
			$rank_id
			, 'A test'
			, __CLASS__
			, __CLASS__
			, 1
			, array( 'test_meta' => __CLASS__ )
		);

		$this->assertTrue( $result );

		$rank = wordpoints_get_rank( $rank_id );

		$rank_group = WordPoints_Rank_Groups::get_group( $rank->rank_group );

		$this->assertEquals( $rank_id, $rank->id );
		$this->assertEquals( 'A test', $rank->name );
		$this->assertEquals( __CLASS__, $rank->type );
		$this->assertEquals( __CLASS__, $rank->rank_group );
		$this->assertEquals( 1, $rank_group->get_rank_position( $rank_id ) );
	}

	/**
	 * Test deleting a rank.
	 *
	 * @since 1.7.0
	 */
	public function test_delete_rank() {

		$rank_id = $this->factory->wordpoints_rank->create();

		$result = wordpoints_delete_rank( $rank_id );

		$this->assertTrue( $result );
		$this->assertEquals( array(), wordpoints_get_rank_meta( $rank_id ) );
	}

	/**
	 * Test formatting a rank for display.
	 *
	 * @since 1.7.0
	 */
	public function test_format_rank() {

		$rank = $this->factory->wordpoints_rank->create_and_get();

		$this->listen_for_filter( 'wordpoints_format_rank' );

		$this->assertEquals(
			'<span class="wordpoints-rank">' . $rank->name . '</span>'
			, wordpoints_format_rank( $rank->ID, 'unittests' )
		);

		$this->assertEquals( 1, $this->filter_was_called( 'wordpoints_format_rank' ) );
	}

	/**
	 * Test formatting a rank with an invalid ID.
	 *
	 * @since 1.7.0
	 */
	public function test_format_invalid_rank() {

		$rank_id = $this->factory->wordpoints_rank->create();

		wordpoints_delete_rank( $rank_id );

		$this->assertFalse( wordpoints_format_rank( $rank_id, 'unittests' ) );
	}

	/**
	 * Test rank caching.
	 *
	 * @since 1.7.0
	 */
	public function test_ranks_are_cached() {

		// Listen for get-rank database queries.
		$this->listen_for_filter( 'query', array( $this, 'is_wordpoints_get_rank_query' ) );

		$rank_id = $this->factory->wordpoints_rank->create();

		// Get the rank.
		$rank = wordpoints_get_rank( $rank_id );

		// The database should have been queried once.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Get the rank again.
		$rank = wordpoints_get_rank( $rank_id );

		// The database should still have been called only once.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// The cache should be invalidated when the rank is updated.
		wordpoints_update_rank(
			$rank_id
			, $rank->name
			, $rank->type
			, $rank->rank_group
			, 1
			, array( 'test_meta' => true )
		);

		// Get the rank again.
		$rank = wordpoints_get_rank( $rank_id );

		// The database should have been queried again.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );

		// Delete the rank.
		wordpoints_delete_rank( $rank_id );

		// Get the rank again.
		$rank = wordpoints_get_rank( $rank_id );

		// The database should have been queried again.
		$this->assertEquals( 3, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Test user rank caching.
	 *
	 * @since 1.9.0
	 */
	public function test_user_ranks_cached() {

		$user_id = $this->user_id = $this->factory->user->create();
		$rank_id = $this->factory->wordpoints_rank->create();
		$rank_id_2 = $this->factory->wordpoints_rank->create(
			array( 'position' => 2 )
		);

		wordpoints_update_user_rank( $user_id, $rank_id );

		// Listen for get user rank database queries.
		$this->listen_for_filter(
			'query'
			, array( $this, 'is_wordpoints_user_rank_query' )
		);

		// Get the user's rank.
		$rank__id = wordpoints_get_user_rank( $user_id, $this->rank_group );

		$this->assertEquals( $rank_id, $rank__id );

		// The database should have been queried once.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Get the user's rank again.
		wordpoints_get_user_rank( $user_id, $this->rank_group );

		// The database should still have been called only once.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// The cache should be invalidated when the user's rank is updated.
		wordpoints_update_user_rank( $user_id, $rank_id_2 );

		// Get the user's rank again.
		$rank__id = wordpoints_get_user_rank( $user_id, $this->rank_group );

		// The database should have been queried again.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );

		$this->assertEquals( $rank_id_2, $rank__id );

		// Move the rank.
		$rank_id_3 = $this->factory->wordpoints_rank->create(
			array( 'position' => 3 )
		);

		WordPoints_Rank_Groups::get_group( $this->rank_group )
			->move_rank( $rank_id_2, 3 );

		// The database should have been queried again when checking if the user's
		// rank should possibly be increased.
		$this->assertEquals( 3, $this->filter_was_called( 'query' ) );

		// Get the rank again.
		$rank__id = wordpoints_get_user_rank( $user_id, $this->rank_group );

		// The user will end up on the same rank again, because the maybe increase
		// user rank function will always return true. This pushes them all the way
		// to the top, which will be this rank again.
		$this->assertEquals( $rank_id_2, $rank__id );

		// The database should have been queried again, since the cache is empty
		// from updating the users rank back to $rank_id_2.
		$this->assertEquals( 4, $this->filter_was_called( 'query' ) );

		// Delete the rank.
		wordpoints_delete_rank( $rank_id_2 );

		// Get the rank again.
		$rank__id = wordpoints_get_user_rank( $user_id, $this->rank_group );

		$this->assertEquals( $rank_id_3, $rank__id );

		// The database should have been queried again.
		$this->assertEquals( 5, $this->filter_was_called( 'query' ) );
	}

	/**
	 * @since 1.9.0
	 */
	public function is_wordpoints_user_rank_query( $sql ) {

		global $wpdb;

		return false !== strpos( $sql, "
					SELECT user_ranks.rank_id
					FROM {$wpdb->wordpoints_user_ranks} user_ranks
					LEFT JOIN {$wpdb->wordpoints_ranks} AS ranks
						ON ranks.id = user_ranks.rank_id
							AND ranks.rank_group = '{$this->rank_group}'
					WHERE user_ranks.user_id = {$this->user_id}"
		);
	}

	/**
	 * Test users with rank caching.
	 *
	 * @since 1.9.0
	 */
	public function test_users_with_rank_caching() {

		$user_id = $this->factory->user->create();
		$user_id_2 = $this->factory->user->create();
		$rank_id = $this->factory->wordpoints_rank->create();
		$rank_id_2 = $this->factory->wordpoints_rank->create(
			array( 'position' => 2 )
		);

		wordpoints_update_user_rank( $user_id, $rank_id );
		wordpoints_update_user_rank( $user_id_2, $rank_id_2 );

		// Listen for get user rank database queries.
		$this->listen_for_filter(
			'query'
			, array( $this, 'is_wordpoints_users_with_rank_query' )
		);

		// Get the users with this rank.
		$users = wordpoints_get_users_with_rank( $rank_id );

		$this->assertEquals( array( $user_id ), $users );

		// The database should have been queried once.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Get the users with this rank again.
		$users = wordpoints_get_users_with_rank( $rank_id );

		$this->assertEquals( array( $user_id ), $users );

		// The database should still have been queried just once.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Get the users with the second rank.
		$users = wordpoints_get_users_with_rank( $rank_id_2 );

		$this->assertContains( $user_id_2, $users );

		// The database should have been queried a second time.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );

		// Update the rank of the first user.
		wordpoints_update_user_rank( $user_id, $rank_id_2 );

		// Get the users with the first rank.
		$users = wordpoints_get_users_with_rank( $rank_id );

		$this->assertEquals( array(), $users );

		// The database should have been queried again since the cache for this rank
		// should have been cleared.
		$this->assertEquals( 3, $this->filter_was_called( 'query' ) );

		// Get the users with the second rank.
		$users = wordpoints_get_users_with_rank( $rank_id_2 );

		$this->assertContains( $user_id, $users );
		$this->assertContains( $user_id_2, $users );

		// The database should have been queried again for this rank as well.
		$this->assertEquals( 4, $this->filter_was_called( 'query' ) );

		// Now let's try deleting the rank.
		wordpoints_delete_rank( $rank_id_2 );

		// Get the cache for the deleted rank.
		$users = wp_cache_get( $rank_id_2, 'wordpoints_users_with_rank' );

		$this->assertEquals( false, $users );

		// Get the users for the other rank.
		$users = wordpoints_get_users_with_rank( $rank_id );

		$this->assertContains( $user_id, $users );
		$this->assertContains( $user_id_2, $users );

		// The database should be queried again since the users were moved to this
		// rank and therefore the cache should be cleared.
		$this->assertEquals( 5, $this->filter_was_called( 'query' ) );

		// Move the rank.
		$rank_id_3 = $this->factory->wordpoints_rank->create(
			array( 'position' => 2 )
		);

		WordPoints_Rank_Groups::get_group( $this->rank_group )
			->move_rank( $rank_id, 2 );

		// The database should be queried again, since the cache is invalidated and
		// then we get the users of the rank to check if there are any that need to
		// be moved up, and then the cache is invalidated again by updating the
		// user's ranks.
		$this->assertEquals( 6, $this->filter_was_called( 'query' ) );

		// Get the users for the rank.
		$users = wordpoints_get_users_with_rank( $rank_id );

		// The users will end up here again because the maybe increase rank will
		// always return true, and this is the highest rank once it is moved.
		$this->assertContains( $user_id, $users );
		$this->assertContains( $user_id_2, $users );

		// The database should be queried again.
		$this->assertEquals( 7, $this->filter_was_called( 'query' ) );
	}

	/**
	 * @since 1.9.0
	 */
	public function is_wordpoints_users_with_rank_query( $sql ) {

		global $wpdb;

		return false !== strpos( $sql, "
					SELECT `user_id`
					FROM `{$wpdb->wordpoints_user_ranks}`
					WHERE `rank_id` = "
		);
	}
}

// EOF
