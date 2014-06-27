<?php

/**
 * A test case for the wordpoints_points_get_top_users() function.
 *
 * @package WordPoints\Tests\Points
 * @since 1.5.0
 */

/**
 * Test that the wordpoints_points_get_top_users() function works.
 *
 * @since 1.5.0
 *
 * @group points
 */
class WordPoints_Points_Get_Top_Users_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * The top users created by self::setUp().
	 *
	 * @since 1.5.0
	 *
	 * @type int[] $user_ids
	 */
	private $user_ids;

	/**
	 * Set up for the tests by creating some users and awarding them points.
	 *
	 * @since 1.5.0
	 */
	public function setUp() {

		parent::setUp();

		$this->user_ids = $this->factory->user->create_many( 4 );

		wordpoints_set_points( $this->user_ids[0], 40, 'points', 'test' );
		wordpoints_set_points( $this->user_ids[1], 30, 'points', 'test' );
		wordpoints_set_points( $this->user_ids[2], 20, 'points', 'test' );
		wordpoints_set_points( $this->user_ids[3], 10, 'points', 'test' );
	}

	/**
	 * Test that the correct number of users are returned.
	 *
	 * @since 1.5.0
	 */
	public function test_limits_to_correct_number() {

		$top_users = wordpoints_points_get_top_users( 3, 'points' );

		$this->assertCount( 3, $top_users );
	}

	/**
	 * Test that the top users are returned in the correct order.
	 *
	 * @since 1.5.0
	 */
	public function test_top_users_returned() {

		$top_users = wordpoints_points_get_top_users( 3, 'points' );

		// We're only checking the 3 top users, remove te 4th one.
		unset( $this->user_ids[3] );

		$this->assertEquals( $this->user_ids, $top_users );
	}

	/**
	 * Test that the cache is works properly.
	 *
	 * @since 1.5.0
	 */
	public function test_caching() {

		$this->listen_for_filter( 'query', array( $this, 'is_top_users_query' ) );

		$top_users = wordpoints_points_get_top_users( 3, 'points' );

		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		$this->assertEquals( $this->user_ids[1], $top_users[1] );

		// Run the query again.
		$top_users = wordpoints_points_get_top_users( 3, 'points' );

		// Should have used the cache, so still just one database query.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Update the user's points.
		wordpoints_set_points( $this->user_ids[1], 50, 'points', 'test' );

		$top_users = wordpoints_points_get_top_users( 3, 'points' );

		// Cache should have been invalidated, and so a second database query used.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );

		// This should now be the top user.
		$this->assertEquals( $this->user_ids[1], $top_users[0] );

		// This time, get only the top 2 users.
		wordpoints_points_get_top_users( 2, 'points' );

		// The same cache can be used, so no new query is needed.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );

		// Now get 5 users (there are only 4).
		$top_users = wordpoints_points_get_top_users( 5, 'points' );

		// The old cache would have been insufficient, and so another query made.
		$this->assertEquals( 3, $this->filter_was_called( 'query' ) );

		$this->assertCount( 4, $top_users );

		// Running the 5 user query again shouldn't hit the database, even though
		// there are only 4 users in the cache.
		wordpoints_points_get_top_users( 5, 'points' );

		$this->assertEquals( 3, $this->filter_was_called( 'query' ) );
	}
}
