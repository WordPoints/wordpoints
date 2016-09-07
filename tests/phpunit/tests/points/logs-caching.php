<?php

/**
 * Test case for points log caching functions.
 *
 * @package WordPoints\Tests\Points
 * @since 2.0.0
 */

/**
 * Tests for points log caching functions.
 *
 * @since 2.0.0
 *
 * @group points
 * @group points_logs
 */
class WordPoints_Points_Logs_Caching_Test extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * Test that it doesn't clear the caches for any users by default.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_flush_points_logs_caches
	 */
	public function test_doesnt_clear_user_caches() {

		// Create two users.
		$user_ids = $this->factory->user->create_many( 2 );

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Get a query for the first user to prime the cache.
		wp_set_current_user( $user_ids[0] );
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );
		$query->get();

		// Now there should have been a query.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Get a query for the second user.
		wp_set_current_user( $user_ids[1] );
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );
		$query->get();

		// A second query should have been made to prime this cache.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );

		wordpoints_flush_points_logs_caches();

		// Get the query again for the first user.
		wp_set_current_user( $user_ids[0] );
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );
		$query->get();

		// The cache should still be good.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );

		// Get the query for the second user again.
		wp_set_current_user( $user_ids[1] );
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );
		$query->get();

		// The cache should still be good for this user too.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Test clearing the cache for a single user.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_flush_points_logs_caches
	 */
	public function test_clearing_cache_for_user() {

		// Create two users.
		$user_ids = $this->factory->user->create_many( 2 );

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Get a query for the first user to prime the cache.
		wp_set_current_user( $user_ids[0] );
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );
		$query->get();

		// Now there should have been a query.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Get a query for the second user.
		wp_set_current_user( $user_ids[1] );
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );
		$query->get();

		// A second query should have been made to prime this cache.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );

		wordpoints_flush_points_logs_caches( array( 'user_id' => $user_ids[0] ) );

		// Get the query again for the first user.
		wp_set_current_user( $user_ids[0] );
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );
		$query->get();

		// The cache should have been invalidated.
		$this->assertEquals( 3, $this->filter_was_called( 'query' ) );

		// Get the query for the second user again.
		wp_set_current_user( $user_ids[1] );
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );
		$query->get();

		// The cache for the second user should still be good.
		$this->assertEquals( 3, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Test that it clears the caches for all points types by default.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_flush_points_logs_caches
	 */
	public function test_clears_cache_for_all_points_types() {

		// Create a second points type.
		wordpoints_add_points_type( array( 'name' => 'credits' ) );

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Get the query for the 'points' points type to prime the cache.
		$query = wordpoints_get_points_logs_query( 'points' );
		$query->get();

		// Now there should have been a query.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Get a query for the 'credits' points type to prime the cache.
		$query = wordpoints_get_points_logs_query( 'credits' );
		$query->get();

		// A second query should have been made.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );

		wordpoints_flush_points_logs_caches();

		// Get the 'points' query again.
		$query = wordpoints_get_points_logs_query( 'points' );
		$query->get();

		// The cache should have been invalidated, and so another query made.
		$this->assertEquals( 3, $this->filter_was_called( 'query' ) );

		// Get the 'credits' query again.
		$query = wordpoints_get_points_logs_query( 'credits' );
		$query->get();

		// The cache should have been invalidated, and so another query made.
		$this->assertEquals( 4, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Test clearing the cache for a single points type.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_flush_points_logs_caches
	 */
	public function test_clearing_cache_for_points_type() {

		// Create a second points type.
		wordpoints_add_points_type( array( 'name' => 'credits' ) );

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Get the query for the 'points' points type to prime the cache.
		$query = wordpoints_get_points_logs_query( 'points' );
		$query->get();

		// Now there should have been a query.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Get a query for the 'credits' points type to prime the cache.
		$query = wordpoints_get_points_logs_query( 'credits' );
		$query->get();

		// A second query should have been made.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );

		wordpoints_flush_points_logs_caches( array( 'points_type' => 'points' ) );

		// Get the 'points' query again.
		$query = wordpoints_get_points_logs_query( 'points' );
		$query->get();

		// The cache should have been invalidated, and so another query made.
		$this->assertEquals( 3, $this->filter_was_called( 'query' ) );

		// Get the 'credits' query again.
		$query = wordpoints_get_points_logs_query( 'credits' );
		$query->get();

		// The cache should still have been good, no need for another query.
		$this->assertEquals( 3, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Test that it cleans the network caches too.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_flush_points_logs_caches
	 *
	 * @requires WordPoints network-active
	 */
	public function test_clears_network_cache() {

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Get the query.
		$query = wordpoints_get_points_logs_query( 'points', 'network' );
		$query->get();

		// Now there should have been a query.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Create a second site.
		$site_id = $this->factory->blog->create();

		switch_to_blog( $site_id );

		// Get the query again.
		$query = wordpoints_get_points_logs_query( 'points', 'network' );
		$query->get();

		// The cache should still be good, no query needed.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		wordpoints_flush_points_logs_caches();

		restore_current_blog();

		// Get the query again on the first site.
		$query = wordpoints_get_points_logs_query( 'points', 'network' );
		$query->get();

		// The cache should have been invalidated, and so a new query made.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Test that in only clears the non-network cache for the current site.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_flush_points_logs_caches
	 *
	 * @requires WordPoints network-active
	 */
	public function test_only_clears_current_site_cache() {

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Get the query.
		$query = wordpoints_get_points_logs_query( 'points' );
		$query->get();

		// Now there should have been a query.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Create a second site.
		$site_id = $this->factory->blog->create();

		switch_to_blog( $site_id );

		// Get the query again.
		$query = wordpoints_get_points_logs_query( 'points' );
		$query->get();

		// A new query is needed for this site.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );

		wordpoints_flush_points_logs_caches();

		restore_current_blog();

		// Get the query again on the first site.
		$query = wordpoints_get_points_logs_query( 'points' );
		$query->get();

		// The cache should still be good, no new query needed.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );
	}


	/**
	 * Test that it cleans the cache for the user.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_clean_points_logs_cache
	 */
	public function test_clean_cache_for_user() {

		// Create two users.
		$user_ids = $this->factory->user->create_many( 2 );

		// Create a second points type.
		wordpoints_add_points_type( array( 'name' => 'credits' ) );

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Get a query for the first user to prime the cache.
		wp_set_current_user( $user_ids[0] );
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );
		$query->get();

		// Now there should have been a query.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Get a query for the 'credits' points type to prime the cache.
		$query = wordpoints_get_points_logs_query( 'credits' );
		$query->get();

		// A second query should have been made to prime this cache.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );

		// Get a query for the second user.
		wp_set_current_user( $user_ids[1] );
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );
		$query->get();

		// A third query should have been made to prime this cache.
		$this->assertEquals( 3, $this->filter_was_called( 'query' ) );

		wordpoints_clean_points_logs_cache( $user_ids[0], 100, 'points' );

		// Get the query again for the first user.
		wp_set_current_user( $user_ids[0] );
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );
		$query->get();

		// The cache should have been invalidated.
		$this->assertEquals( 4, $this->filter_was_called( 'query' ) );

		// Get a query for the 'credits' points type.
		$query = wordpoints_get_points_logs_query( 'credits' );
		$query->get();

		// The cache should still be good.
		$this->assertEquals( 4, $this->filter_was_called( 'query' ) );

		// Get the query for the second user again.
		wp_set_current_user( $user_ids[1] );
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );
		$query->get();

		// The cache for the second user should still be good.
		$this->assertEquals( 4, $this->filter_was_called( 'query' ) );
	}
}

// EOF
