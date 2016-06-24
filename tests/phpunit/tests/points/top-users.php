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
 * @since 1.7.0 Assumes that one user is already in the DB that has no points.
 *
 * @group points
 *
 * @covers ::wordpoints_points_get_top_users
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

		// We expect just one user in the DB so if there are extra ones, delete them.
		$existing_users = get_users( array( 'fields' => 'ids' ) );

		if ( count( $existing_users ) > 1 ) {

			array_shift( $existing_users );
			array_map( array( $this, 'delete_user' ), $existing_users );
		}

		$this->user_ids = $this->factory->user->create_many( 3 );

		wordpoints_set_points( $this->user_ids[0], 40, 'points', 'test' );
		wordpoints_set_points( $this->user_ids[1], 30, 'points', 'test' );
		wordpoints_set_points( $this->user_ids[2], 20, 'points', 'test' );
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

		$this->assertEquals( $this->user_ids, $top_users );
	}

	/**
	 * Test that the cache works properly.
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

	/**
	 * Test that the cache is per-site when the plugin isn't network active.
	 *
	 * @since 1.10.2
	 *
	 * @requires WordPress multisite
	 * @requires WordPoints !network-active
	 */
	public function test_cache_per_site_on_multisite() {

		$this->listen_for_filter( 'query', array( $this, 'is_top_users_query' ) );

		wordpoints_points_get_top_users( 3, 'points' );

		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Run the query again.
		wordpoints_points_get_top_users( 3, 'points' );

		// Should have used the cache, so still just one database query.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Create a second site on the network.
		switch_to_blog( $this->factory->blog->create() );

		// We have to create a points type, because they are per-site in this case.
		$this->create_points_type();

		wordpoints_points_get_top_users( 3, 'points' );

		// Cache isn't good on this site, should be a new query.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );

		// Again.
		wordpoints_points_get_top_users( 3, 'points' );

		// Cache is still good.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );

		restore_current_blog();

		wordpoints_points_get_top_users( 3, 'points' );

		// Cache is still good.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Test that the cache is network-wide when the plugin is network active.
	 *
	 * @since 1.10.2
	 *
	 * @requires WordPoints network-active
	 */
	public function test_cache_network_wide_when_network_active() {

		$this->listen_for_filter( 'query', array( $this, 'is_top_users_query' ) );

		wordpoints_points_get_top_users( 3, 'points' );

		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Run the query again.
		wordpoints_points_get_top_users( 3, 'points' );

		// Should have used the cache, so still just one database query.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Create a second site on the network.
		$site_id = $this->factory->blog->create();

		switch_to_blog( $site_id );

		wordpoints_points_get_top_users( 3, 'points' );

		// Cache should still be good.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Again.
		wordpoints_points_get_top_users( 3, 'points' );

		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		restore_current_blog();

		wordpoints_points_get_top_users( 3, 'points' );

		// Cache is still good.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Test that excluded users are excluded from the top users.
	 *
	 * @since 1.6.1
	 */
	public function test_excluded_users_excluded() {

		wordpoints_update_network_option(
			'wordpoints_excluded_users'
			, array( $this->user_ids[2], $this->user_ids[0] )
		);

		$top_users = wordpoints_points_get_top_users( 5, 'points' );

		$this->assertEquals( array( $this->user_ids[1], 1 ), $top_users );
	}

	/**
	 * Test that the cache behaves correctly when a new user is added.
	 *
	 * @since 1.10.2
	 */
	public function test_cache_after_user_added() {

		// Run it so the cache is full.
		wordpoints_points_get_top_users( 10, 'points' );

		$user_id = $this->factory->user->create();

		$this->assertEquals(
			''
			, get_user_meta(
				$user_id
				, wordpoints_get_points_user_meta_key( 'points' )
				, true
			)
		);

		$this->assertContains(
			$user_id
			, wordpoints_points_get_top_users( 10, 'points' )
		);
	}

	/**
	 * Test that the cache behaves correctly when a new user is added and given points.
	 *
	 * @since 1.10.2
	 */
	public function test_cache_after_user_with_points_added() {

		// Run it so the cache is full.
		wordpoints_points_get_top_users( 2, 'points' );

		$user_id = $this->factory->user->create();

		wordpoints_set_points( $user_id, 500, 'points', 'test' );

		$top_users = wordpoints_points_get_top_users( 2, 'points' );
		$this->assertEquals( $user_id, $top_users[0] );
	}

	/**
	 * Test that the cache is cleared when a user is deleted.
	 *
	 * @since 1.10.2
	 */
	public function test_cache_cleared_when_user_deleted() {

		// Prime the cache.
		wordpoints_points_get_top_users( 3, 'points' );

		// When network active the user has to be deleted completely.
		if ( is_wordpoints_network_active() ) {
			wpmu_delete_user( $this->user_ids[0] );
		} else {
			wp_delete_user( $this->user_ids[0] );
		}

		$this->assertNotContains(
			$this->user_ids[0]
			, wordpoints_points_get_top_users( 3, 'points' )
		);
	}

	/**
	 * Test getting the top users with a user with no points and users with negative points.
	 *
	 * @since 1.10.2
	 */
	public function test_with_negative_points_and_no_points() {

		add_filter( 'wordpoints_points_minimum', array( $this, 'return_negative_50' ) );

		wordpoints_set_points( $this->user_ids[1], -5, 'points', 'test' );

		$this->assertEquals(
			array( $this->user_ids[0], $this->user_ids[2], 1, $this->user_ids[1] )
			, wordpoints_points_get_top_users( 10, 'points' )
		);
	}

	//
	// Helpers
	//

	/**
	 * Return -50.
	 *
	 * Useful for filters.
	 *
	 * @since 1.10.2
	 *
	 * @return int Negative 50.
	 */
	public function return_negative_50() {
		return -50;
	}
}

// EOF
