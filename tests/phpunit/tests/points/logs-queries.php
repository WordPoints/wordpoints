<?php

/**
 * Test registration of points queries.
 *
 * @package WordPoints\Tests\Points
 * @since 1.0.0
 */

/**
 * Points log query test case.
 *
 * @since 1.0.0
 *
 * @group points
 * @group points_logs
 */
class WordPoints_Points_Log_Query_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test query registration.
	 *
	 * @since 1.0.0
	 */
	function test_query_registration() {

		$query      = 'test_query';
		$query_args = array( 'fields' => 'id' );

		wordpoints_register_points_logs_query( $query, $query_args );

		$this->assertTrue( wordpoints_is_points_logs_query( $query ) );

		$this->assertEquals(
			$query_args + array(
				'points_type'  => 'points',
				'user__not_in' => array(),
			)
			,wordpoints_get_points_logs_query_args( 'points', $query )
		);

		$this->assertInstanceOf( 'WordPoints_Points_Logs_Query', wordpoints_get_points_logs_query( 'points', $query ) );
	}

	/**
	 * Test that default queries are registered.
	 *
	 * @since 1.0.0
	 */
	function test_default_queries_registered() {

		$this->assertTrue( wordpoints_is_points_logs_query( 'default' ) );
		$this->assertTrue( wordpoints_is_points_logs_query( 'current_user' ) );
		$this->assertTrue( wordpoints_is_points_logs_query( 'network' ) );
	}

	/**
	 * Test the 'fields' query arg.
	 *
	 * @since 1.0.0
	 */
	function test_fields_query_arg() {

		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );

		$query = new WordPoints_Points_Logs_Query( array( 'fields' => 'user_id' ) );

		$result = $query->get();

		$this->assertObjectHasAttribute( 'user_id', array_shift( $result ) );
	}

	/**
	 * Test the 'limit' query arg.
	 *
	 * @since 1.0.0
	 */
	function test_limit_query_arg() {

		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );
		wordpoints_alter_points( $user_id, 10, 'points', 'test' );

		$query = new WordPoints_Points_Logs_Query( array( 'limit' => 1 ) );

		$this->assertEquals( 1, count( $query->get() ) );
	}

	/**
	 * Test the 'start' query arg.
	 *
	 * @since 1.0.0
	 */
	function test_start_query_arg() {

		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );
		wordpoints_alter_points( $user_id, 20, 'points', 'test' );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'start'   => 1,
				'limit'   => 2,
				'orderby' => 'id',
			)
		);

		$result = $query->get();

		$this->assertEquals( 1, count( $result ) );

		$result = current( $result );
		$this->assertEquals( 10, $result->points );
	}

	/**
	 * Test the 'orderby' and 'order' query args.
	 *
	 * @since 1.0.0
	 */
	function test_order_query_args() {

		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );
		wordpoints_alter_points( $user_id, 20, 'points', 'test' );

		$query = new WordPoints_Points_Logs_Query( array( 'orderby' => 'points' ) );

		$result = $query->get();

		$first  = array_shift( $result );
		$second = array_shift( $result );

		$this->assertGreaterThan( $second->points, $first->points );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'orderby' => 'points',
				'order'   => 'ASC',
			)
		);

		$result = $query->get();

		$first  = array_shift( $result );
		$second = array_shift( $result );

		$this->assertLessThan( $second->points, $first->points );
	}

	/**
	 * Test the 'id_*' query args.
	 *
	 * @since 1.2.0
	 */
	function test_id_query_args() {

		// Create a user and add some points to generate some logs.
		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );
		wordpoints_alter_points( $user_id, 20, 'points', 'test' );

		// Make sure that the two logs were added.
		$query = new WordPoints_Points_Logs_Query;

		$logs = $query->get();

		$this->assertEquals( 2, count( $logs ) );

		// Try the 'id__in' query arg.
		$query_2 = new WordPoints_Points_Logs_Query( array( 'id__in' => array( $logs[0]->id ) ) );

		$logs_2 = $query_2->get();

		$this->assertEquals( 1, count( $logs_2 ) );
		$this->assertEquals( $logs[0]->id, $logs_2[0]->id );

		// Try the 'id__not_in' query arg.
		$query_3 = new WordPoints_Points_Logs_Query( array( 'id__not_in' => array( $logs[0]->id ) ) );

		$logs_3 = $query_3->get();

		$this->assertEquals( 1, count( $logs_3 ) );
		$this->assertEquals( $logs[1]->id, $logs_3[0]->id );
	}

	/**
	 * Test the 'user_*' query args.
	 *
	 * @since 1.0.0
	 */
	function test_user_query_args() {

		$user_ids = $this->factory->user->create_many( 2 );

		wordpoints_alter_points( $user_ids[0], 10, 'points', 'test' );
		wordpoints_alter_points( $user_ids[1], 10, 'points', 'test' );

		$query_1 = new WordPoints_Points_Logs_Query( array( 'user_id' => $user_ids[0] ) );
		$this->assertEquals( 1, $query_1->count() );

		$query_2 = new WordPoints_Points_Logs_Query( array( 'user__in' => array( $user_ids[0] ) ) );
		$this->assertEquals( 1, $query_2->count() );

		$query_3 = new WordPoints_Points_Logs_Query( array( 'user__not_in' => $user_ids ) );
		$this->assertEquals( 0, $query_3->count() );
	}

	/**
	 * Test the 'points_type*' query args.
	 *
	 * @since 1.0.0
	 */
	function test_points_type_query_args() {

		wordpoints_add_points_type( array( 'name' => 'credits' ) );
		wordpoints_add_points_type( array( 'name' => 'tests' ) );

		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );
		wordpoints_alter_points( $user_id, 10, 'credits', 'test' );
		wordpoints_alter_points( $user_id, 10, 'tests', 'test' );

		$query_1 = new WordPoints_Points_Logs_Query( array( 'points_type' => 'points' ) );
		$this->assertEquals( 1, $query_1->count() );

		$query_2 = new WordPoints_Points_Logs_Query( array( 'points_type__in' => array( 'points', 'tests' ) ) );
		$this->assertEquals( 2, $query_2->count() );

		$query_3 = new WordPoints_Points_Logs_Query( array( 'points_type__not_in' => array( 'points', 'tests' ) ) );
		$this->assertEquals( 1, $query_3->count() );
	}

	/**
	 * Test the 'log_type*' query args.
	 *
	 * @since 1.0.0
	 */
	function test_log_type_query_args() {

		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );
		wordpoints_alter_points( $user_id, 10, 'points', 'test2' );
		wordpoints_alter_points( $user_id, 10, 'points', 'test3' );

		$query_1 = new WordPoints_Points_Logs_Query( array( 'log_type' => 'test' ) );
		$this->assertEquals( 1, $query_1->count() );

		$query_2 = new WordPoints_Points_Logs_Query( array( 'log_type__in' => array( 'test2', 'test3' ) ) );
		$this->assertEquals( 2, $query_2->count() );

		$query_3 = new WordPoints_Points_Logs_Query( array( 'log_type__not_in' => array( 'test2', 'test3' ) ) );
		$this->assertEquals( 1, $query_3->count() );
	}

	/**
	 * Test the 'points' and 'points_compare' query args.
	 *
	 * @since 1.0.0
	 */
	function test_points_query_args() {

		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );
		wordpoints_alter_points( $user_id, 15, 'points', 'test' );
		wordpoints_alter_points( $user_id, 20, 'points', 'test' );

		$query_1 = new WordPoints_Points_Logs_Query( array( 'points' => 10 ) );
		$this->assertEquals( 1, $query_1->count() );

		$query_2 = new WordPoints_Points_Logs_Query(
			array(
				'points' => 10,
				'points__compare' => '!=',
			)
		);
		$this->assertEquals( 2, $query_2->count() );

		$query_3 = new WordPoints_Points_Logs_Query(
			array(
				'points' => 10,
				'points__compare' => '>',
			)
		);
		$this->assertEquals( 2, $query_3->count() );

		$query_4 = new WordPoints_Points_Logs_Query(
			array(
				'points' => 10,
				'points__compare' => '<',
			)
		);
		$this->assertEquals( 0, $query_4->count() );

		$query_5 = new WordPoints_Points_Logs_Query(
			array(
				'points' => 10,
				'points__compare' => '<=',
			)
		);
		$this->assertEquals( 1, $query_5->count() );

		$query_6 = new WordPoints_Points_Logs_Query(
			array(
				'points' => 10,
				'points__compare' => '>=',
			)
		);
		$this->assertEquals( 3, $query_6->count() );
	}

	/**
	 * Test 'key' and 'value*' meta query args.
	 *
	 * @since 1.0.0
	 *
	 * @expectedDeprecated WordPoints_Points_Logs_Query::__construct
	 */
	public function test_key_and_value_meta_query_args() {

		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test', array( 'test1' => 1 ) );
		wordpoints_alter_points( $user_id, 20, 'points', 'test', array( 'test2' => 2, 'test3' => 1 ) );

		$query_1 = new WordPoints_Points_Logs_Query(
			array( 'meta_query' => array( 'key' => 'test1', 'value' => array() ) )
		);
		$this->assertEquals( 1, $query_1->count() );

		$query_2 = new WordPoints_Points_Logs_Query(
			array( 'meta_query' => array( 'value' => 1 ) )
		);
		$this->assertEquals( 2, $query_2->count() );

		$query_3 = new WordPoints_Points_Logs_Query(
			array( 'meta_query' => array( 'value__in' => array( 1, 2 ) ) )
		);
		$this->assertEquals( 3, $query_3->count() );

		$query_4 = new WordPoints_Points_Logs_Query(
			array( 'meta_query' => array( 'value__not_in' => array( 1 ) ) )
		);
		$this->assertEquals( 1, $query_4->count() );

		$query_5 = new WordPoints_Points_Logs_Query(
			array(
				'orderby'    => 'meta_value',
				'meta_query' => array(
					'relation' => 'OR',
					array( 'key' => 'test1' ),
					array( 'key' => 'test2' ),
				),
			)
		);

		$results = $query_5->get();

		$this->assertEquals( 2, count( $results ) );
		$this->assertEquals( 20, reset( $results )->points );
	}

	/**
	 * Test the 'date_query' args.
	 *
	 * This is just a very basic test to make sure that WP_Date_Query is indeed
	 * supported.
	 *
	 * @since 1.1.0
	 */
	public function test_date_query_arg() {

		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );
		wordpoints_alter_points( $user_id, 10, 'points', 'test' );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'date_query' => array(
					array(
						'after' => array(
							'second' => 59,
						),
					),
				),
			)
		);

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );

		$this->assertEquals( 0, $query->count() );
	}

	/**
	 * Test the blog_* query arg.
	 *
	 * @since 1.2.0
	 */
	public function test_blog_query_arg() {

		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Tests are not using multisite.' );
		}

		$user_id = $this->factory->user->create();
		$blog_id = $this->factory->blog->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );

		switch_to_blog( $blog_id );

		if ( ! is_wordpoints_network_active() ) {
			wordpoints_add_points_type( array( 'name' => 'points' ) );
		}

		wordpoints_alter_points( $this->factory->user->create(), 20, 'points', 'test' );

		restore_current_blog();

		$query = new WordPoints_Points_Logs_Query();

		$this->assertEquals( 1, $query->count() );
		$this->assertEquals( 10, $query->get( 'row' )->points );

		$query = new WordPoints_Points_Logs_Query( array( 'blog_id' => $blog_id ) );

		$this->assertEquals( 1, $query->count() );
		$this->assertEquals( 20, $query->get( 'row' )->points );

		$query = new WordPoints_Points_Logs_Query( array( 'blog_id' => false ) );

		$this->assertEquals( 2, $query->count() );

		$query = new WordPoints_Points_Logs_Query( array( 'blog__in' => array( 1, $blog_id ) ) );

		$this->assertEquals( 2, $query->count() );

		$query = new WordPoints_Points_Logs_Query( array( 'blog__not_in' => array( 1 ) ) );

		$this->assertEquals( 1, $query->count() );
		$this->assertEquals( 20, $query->get( 'row' )->points );
	}

	/**
	 * Test the log_text* query args.
	 *
	 * @since 1.6.0
	 */
	public function test_text_args() {

		$log_id_1 = $this->factory->wordpoints_points_log->create(
			array( 'text' => 'Test searching 100.' )
		);

		$log_id_2 = $this->factory->wordpoints_points_log->create(
			array( 'text' => 'A test with 100%.' )
		);

		$log_id_3 = $this->factory->wordpoints_points_log->create(
			array( 'text' => 'A test.' )
		);

		$query = new WordPoints_Points_Logs_Query( array( 'text' => 'A test.' ) );
		$this->assertEquals( 1, $query->count() );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'text' => 'A test.',
				'text__compare' => '!=',
			)
		);
		$this->assertEquals( 2, $query->count() );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'text' => 'A test%',
				'text__compare' => 'LIKE',
			)
		);
		$this->assertEquals( 2, $query->count() );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'text' => 'A test%',
				'text__compare' => 'NOT LIKE',
			)
		);
		$this->assertEquals( 1, $query->count() );

		$query = new WordPoints_Points_Logs_Query(
			array( 'text' => '%100\%%' )
		);
		$this->assertEquals( 1, $query->count() );
	}

	/**
	 * Test that the cache is cleared properly.
	 *
	 * @since 1.5.0
	 */
	public function test_caching() {

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		$query = wordpoints_get_points_logs_query( 'points' );

		// The cache should have been primed.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Get the results;
		$query->get();

		// The cache should have been used, no new query.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Get the count.
		$query->count();

		// The count should also be cached, so no query needed here either.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// New query.
		$query = wordpoints_get_points_logs_query( 'points' );

		// The cache should still be good, so no new query should have been made.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Now alter some points.
		wordpoints_alter_points( $this->factory->user->create(), 10, 'points', 'test' );

		// Get the query again.
		$query = wordpoints_get_points_logs_query( 'points' );

		// The cache should have been invalidated, and so another query made.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Test that the cache is invalidated per-points type.
	 *
	 * @since 1.5.0
	 */
	public function test_cache_is_per_points_type() {

		// Create a second points type.
		wordpoints_add_points_type( array( 'name' => 'credits' ) );

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Get the query for the 'points' points type.
		$query = wordpoints_get_points_logs_query( 'points' );

		// The cache should have been primed.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Get a query for the 'credits' points type.
		$query = wordpoints_get_points_logs_query( 'credits' );

		// A second query should have been made to prime this cache.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );

		// Now alter some points.
		wordpoints_alter_points( $this->factory->user->create(), 10, 'points', 'test' );

		// Get the 'points' query again.
		$query = wordpoints_get_points_logs_query( 'points' );

		// The cache should have been invalidated, and so another query made.
		$this->assertEquals( 3, $this->filter_was_called( 'query' ) );

		// Get the 'credits' query again.
		$query = wordpoints_get_points_logs_query( 'credits' );

		// The cache should still have been good, no need for another query.
		$this->assertEquals( 3, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Test that the cache is invalidated per-user.
	 *
	 * @since 1.5.0
	 */
	public function test_cache_is_per_user() {

		// Create two users.
		$user_ids = $this->factory->user->create_many( 2 );

		$old_user = wp_get_current_user();
		wp_set_current_user( $user_ids[0] );

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Get the query.
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );

		// The cache should have been primed.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Get a query for the second user.
		wp_set_current_user( $user_ids[1] );
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );

		// A second query should have been made to prime this cache.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );

		// Now alter the points of the first user.
		wordpoints_alter_points( $user_ids[0], 10, 'points', 'test' );

		// Get the query again for the first user.
		wp_set_current_user( $user_ids[0] );
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );

		// The cache should have been invalidated, and so another query made.
		$this->assertEquals( 3, $this->filter_was_called( 'query' ) );

		// Get the query for the second user again.
		wp_set_current_user( $user_ids[1] );
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );

		// The cache should still have been good, no need for another query.
		$this->assertEquals( 3, $this->filter_was_called( 'query' ) );

		wp_set_current_user( $old_user->ID );
	}

	/**
	 * Test that network queries are cache for the entire network.
	 *
	 * @since 1.5.0
	 */
	public function test_network_cache_is_network_wide() {

		if ( ! is_wordpoints_network_active() ) {
			$this->markTestSkipped( 'WordPoints must be network active.' );
		}

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Get the query.
		$query = wordpoints_get_points_logs_query( 'points', 'network' );

		// The cache should have been primed.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Create a second site.
		$site_id = $this->factory->blog->create();

		switch_to_blog( $site_id );
		// Get the query again.
		$query = wordpoints_get_points_logs_query( 'points', 'network' );

		// The cache should still be good, no query needed.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Now alter some points.
		wordpoints_alter_points( $this->factory->user->create(), 10, 'points', 'test' );
		restore_current_blog();

		// Get the query again on the first site.
		$query = wordpoints_get_points_logs_query( 'points', 'network' );

		// The cache should have been invalidated, and so a new query made.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Test that non-network queries are cached per-site.
	 *
	 * @since 1.5.0
	 */
	public function test_cache_is_per_site() {

		if ( ! is_wordpoints_network_active() ) {
			$this->markTestSkipped( 'WordPoints must be network active.' );
		}

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Get the query.
		$query = wordpoints_get_points_logs_query( 'points' );

		// The cache should have been primed.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Create a second site.
		$site_id = $this->factory->blog->create();

		switch_to_blog( $site_id );
		// Get the query again.
		$query = wordpoints_get_points_logs_query( 'points' );

		// A new query is needed for this site.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );

		// Now alter some points.
		wordpoints_alter_points( $this->factory->user->create(), 10, 'points', 'test' );
		restore_current_blog();

		// Get the query again on the first site.
		$query = wordpoints_get_points_logs_query( 'points' );

		// The cache should still be good, no new query needed.
		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Test the get_page() method.
	 *
	 * @since 1.6.0
	 */
	public function test_get_page() {

		$ids = $this->factory->wordpoints_points_log->create_many( 9 );

		$query = new WordPoints_Points_Logs_Query(
			array( 'orderby' => 'id', 'order' => 'ASC' )
		);

		$page_1 = $query->get_page( 1, 2 );

		$this->assertCount( 2, $page_1 );
		$this->assertEquals( $ids[0], $page_1[0]->id );
		$this->assertEquals( $ids[1], $page_1[1]->id );

		$page_3 = $query->get_page( 3, 2 );

		$this->assertCount( 2, $page_3 );
		$this->assertEquals( $ids[4], $page_3[0]->id );
		$this->assertEquals( $ids[5], $page_3[1]->id );

		$page_5 = $query->get_page( 5, 2 );
		$this->assertCount( 1, $page_5 );
		$this->assertEquals( $ids[8], $page_5[0]->id );

		$page_6 = $query->get_page( 6, 2 );
		$this->assertCount( 0, $page_6 );
	}

	/**
	 * Test get_page() with invalid arguments.
	 *
	 * @since 1.6.0
	 */
	public function test_get_page_invalid_args() {

		$query = new WordPoints_Points_Logs_Query;

		$this->assertFalse( $query->get_page( 0 ) );
		$this->assertFalse( $query->get_page( 5, 0 ) );
	}

	/**
	 * Test get_page() doesn't alter the main query.
	 *
	 * @since 1.6.0
	 */
	public function test_get_page_doesnt_alter_main_query() {

		$this->factory->wordpoints_points_log->create_many( 2 );

		$query = new WordPoints_Points_Logs_Query(
			array( 'orderby' => 'id', 'order' => 'ASC' )
		);

		$sql = $query->get_sql();

		$query->get_page( 1 );

		$this->assertEquals( $sql, $query->get_sql() );
	}

	/**
	 * Test that get_page() uses the cache.
	 *
	 * @since 1.6.0
	 */
	public function test_get_page_uses_cache() {

		$ids = $this->factory->wordpoints_points_log->create_many( 5 );

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Get the query.
		$query = new WordPoints_Points_Logs_Query(
			array( 'orderby' => 'id', 'order' => 'ASC' )
		);
		$query->prime_cache( __FUNCTION__ );

		// The cache should have been primed.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		// Get the first page.
		$page_1 = $query->get_page( 1, 2 );

		// The query shouldn't have been called again.
		$this->assertEquals( 1, $this->filter_was_called( 'query' ) );

		$this->assertCount( 2, $page_1 );
		$this->assertEquals( $ids[0], $page_1[0]->id );
		$this->assertEquals( $ids[1], $page_1[1]->id );
	}

	/**
	 * Test get_page() calculates pages relative to the 'start' argument.
	 *
	 * @since 1.6.0
	 */
	public function test_get_page_with_start() {

		$ids = $this->factory->wordpoints_points_log->create_many( 5 );

		$query = new WordPoints_Points_Logs_Query(
			array( 'start' => 2, 'orderby' => 'id', 'order' => 'ASC' )
		);

		$page_1 = $query->get_page( 1, 2 );

		$this->assertEquals( $ids[2], $page_1[0]->id );
		$this->assertEquals( $ids[3], $page_1[1]->id );

		$page_2 = $query->get_page( 2, 2 );

		$this->assertEquals( $ids[4], $page_2[0]->id );
	}

	/**
	 * Test that get_page() calculates with correct limit.
	 *
	 * @since 1.6.0
	 */
	public function test_get_page_with_limit() {

		$ids = $this->factory->wordpoints_points_log->create_many( 5 );

		$query = new WordPoints_Points_Logs_Query(
			array( 'limit' => 3, 'orderby' => 'id', 'order' => 'ASC' )
		);

		$this->assertCount( 2, $query->get_page( 1, 2 ) );
		$this->assertCount( 1, $query->get_page( 2, 2 ) );
	}

	/**
	 * Test that set_args() alters the queries arguments.
	 *
	 * @since 1.6.0
	 */
	public function test_set_args_alters_query_args() {

		$this->factory->wordpoints_points_log->create_many( 2 );

		$query = new WordPoints_Points_Logs_Query;

		$this->assertCount( 2, $query->get() );

		$query->set_args( array( 'limit' => 1 ) );

		$this->assertCount( 1, $query->get() );
	}

	/**
	 * Test that set_args() resets the cache.
	 *
	 * @since 1.6.0
	 */
	public function test_set_args_resets_cache() {

		$this->factory->wordpoints_points_log->create_many( 2 );

		$query = new WordPoints_Points_Logs_Query;
		$query->prime_cache( __METHOD__ );

		$this->assertCount( 2, $query->get() );

		$query->set_args( array( 'limit' => 1 ) );

		$this->assertCount( 1, $query->get() );
	}
}

// end of file.
