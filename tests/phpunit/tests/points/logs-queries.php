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
 *
 * @covers WordPoints_Points_Logs_Query
 */
class WordPoints_Points_Log_Query_Test extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * Test query registration.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_register_points_logs_query
	 * @covers ::wordpoints_is_points_logs_query
	 * @covers ::wordpoints_get_points_logs_query_args
	 * @covers ::wordpoints_get_points_logs_query
	 */
	public function test_query_registration() {

		$query      = 'test_query';
		$query_args = array( 'fields' => 'id' );

		wordpoints_register_points_logs_query( $query, $query_args );

		$this->assertTrue( wordpoints_is_points_logs_query( $query ) );

		$this->assertSame(
			array(
				'user_id__not_in' => array(),
				'points_type'  => 'points',
			) + $query_args
			, wordpoints_get_points_logs_query_args( 'points', $query )
		);

		$this->assertInstanceOf( 'WordPoints_Points_Logs_Query', wordpoints_get_points_logs_query( 'points', $query ) );
	}

	/**
	 * Test that default queries are registered.
	 *
	 * @since 1.0.0
	 *
	 * @coversNothing
	 */
	public function test_default_queries_registered() {

		$this->assertTrue( wordpoints_is_points_logs_query( 'default' ) );
		$this->assertTrue( wordpoints_is_points_logs_query( 'current_user' ) );
		$this->assertTrue( wordpoints_is_points_logs_query( 'network' ) );
	}

	/**
	 * Test constructing the class.
	 *
	 * @since 2.3.0
	 */
	public function test_construct_defaults() {

		$query = new WordPoints_Points_Logs_Query();

		$this->assertSame( 0,      $query->get_arg( 'start' ) );
		$this->assertSame( 'DESC', $query->get_arg( 'order' ) );
		$this->assertSame( 'date', $query->get_arg( 'order_by' ) );
		$this->assertSame( 'LIKE', $query->get_arg( 'text__compare' ) );
	}

	/**
	 * Test constructing the class.
	 *
	 * @since 2.3.0
	 */
	public function test_construct_override_defaults() {

		$query = new WordPoints_Points_Logs_Query( array( 'order_by' => null ) );

		$this->assertNull( $query->get_arg( 'order_by' ) );
	}

	/**
	 * Test constructing the class with deprecated args.
	 *
	 * @since 2.3.0
	 *
	 * @expectedDeprecated WordPoints_Points_Logs_Query::__construct
	 */
	public function test_construct_with_deprecated_args() {

		$query = new WordPoints_Points_Logs_Query(
			array(
				'orderby'      => 'test',
				'user__in'     => 'test_user_in',
				'user__not_in' => 'test_user_not_in',
				'blog__in'     => 'test_blog_in',
				'blog__not_in' => 'test_blog_not_in',
			)
		);

		$this->assertSame( 'test', $query->get_arg( 'order_by' ) );
		$this->assertSame( 'test_user_in', $query->get_arg( 'user_id__in' ) );
		$this->assertSame( 'test_user_not_in', $query->get_arg( 'user_id__not_in' ) );
		$this->assertSame( 'test_blog_in', $query->get_arg( 'blog_id__in' ) );
		$this->assertSame( 'test_blog_not_in', $query->get_arg( 'blog_id__not_in' ) );
	}

	/**
	 * Test getting the deprecated args.
	 *
	 * @since 2.3.0
	 *
	 * @expectedDeprecated WordPoints_Points_Logs_Query::get_arg
	 */
	public function test_get_deprecated_args() {

		$query = new WordPoints_Points_Logs_Query(
			array(
				'order_by'        => 'test',
				'user_id__in'     => 'test_user_in',
				'user_id__not_in' => 'test_user_not_in',
				'blog_id__in'     => 'test_blog_in',
				'blog_id__not_in' => 'test_blog_not_in',
			)
		);

		$this->assertSame( 'test', $query->get_arg( 'orderby' ) );
		$this->assertSame( 'test_user_in', $query->get_arg( 'user__in' ) );
		$this->assertSame( 'test_user_not_in', $query->get_arg( 'user__not_in' ) );
		$this->assertSame( 'test_blog_in', $query->get_arg( 'blog__in' ) );
		$this->assertSame( 'test_blog_not_in', $query->get_arg( 'blog__not_in' ) );
	}

	/**
	 * Test setting the deprecated args.
	 *
	 * @since 2.3.0
	 *
	 * @expectedDeprecated WordPoints_Points_Logs_Query::set_args
	 */
	public function test_set_deprecated_args() {

		$query = new WordPoints_Points_Logs_Query();
		$query->set_args(
			array(
				'orderby'      => 'test',
				'user__in'     => 'test_user_in',
				'user__not_in' => 'test_user_not_in',
				'blog__in'     => 'test_blog_in',
				'blog__not_in' => 'test_blog_not_in',
			)
		);

		$this->assertSame( 'test', $query->get_arg( 'order_by' ) );
		$this->assertSame( 'test_user_in', $query->get_arg( 'user_id__in' ) );
		$this->assertSame( 'test_user_not_in', $query->get_arg( 'user_id__not_in' ) );
		$this->assertSame( 'test_blog_in', $query->get_arg( 'blog_id__in' ) );
		$this->assertSame( 'test_blog_not_in', $query->get_arg( 'blog_id__not_in' ) );
	}

	/**
	 * Test constructing the class with deprecated args.
	 *
	 * @since 2.3.0
	 *
	 * @expectedDeprecated WordPoints_Points_Logs_Query::convert_deprecated_arg_values
	 */
	public function test_construct_with_deprecated_fields_all() {

		$query = new WordPoints_Points_Logs_Query( array( 'fields' => 'all' ) );

		$this->assertNull( $query->get_arg( 'fields' ) );
	}

	/**
	 * Test setting the deprecated args.
	 *
	 * @since 2.3.0
	 *
	 * @expectedDeprecated WordPoints_Points_Logs_Query::convert_deprecated_arg_values
	 */
	public function test_set_deprecated_arg_fields_all() {

		$query = new WordPoints_Points_Logs_Query();
		$query->set_args( array( 'fields' => 'all' ) );

		$this->assertNull( $query->get_arg( 'fields' ) );
	}
	/**
	 * Test constructing the class with deprecated args.
	 *
	 * @since 2.3.0
	 *
	 * @expectedDeprecated WordPoints_Points_Logs_Query::convert_deprecated_arg_values
	 */
	public function test_construct_with_deprecated_orderby_none() {

		$query = new WordPoints_Points_Logs_Query( array( 'orderby' => 'none' ) );

		$this->assertNull( $query->get_arg( 'order_by' ) );
	}

	/**
	 * Test setting the deprecated args.
	 *
	 * @since 2.3.0
	 *
	 * @expectedDeprecated WordPoints_Points_Logs_Query::convert_deprecated_arg_values
	 */
	public function test_set_deprecated_arg_orderby_none() {

		$query = new WordPoints_Points_Logs_Query();
		$query->set_args( array( 'orderby' => 'none' ) );

		$this->assertNull( $query->get_arg( 'order_by' ) );
	}

	/**
	 * Test the 'fields' query arg.
	 *
	 * @since 1.0.0
	 */
	public function test_fields_query_arg() {

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
	public function test_limit_query_arg() {

		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );
		wordpoints_alter_points( $user_id, 10, 'points', 'test' );

		$query = new WordPoints_Points_Logs_Query( array( 'limit' => 1 ) );

		$this->assertSame( 1, count( $query->get() ) );
	}

	/**
	 * Test the 'start' query arg.
	 *
	 * @since 1.0.0
	 */
	public function test_start_query_arg() {

		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );
		wordpoints_alter_points( $user_id, 20, 'points', 'test' );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'start'    => 1,
				'limit'    => 2,
				'order_by' => 'id',
			)
		);

		$result = $query->get();

		$this->assertSame( 1, count( $result ) );

		$result = current( $result );
		$this->assertSame( 10, (int) $result->points );
	}

	/**
	 * Test the 'order_by' and 'order' query args.
	 *
	 * @since 1.0.0
	 */
	public function test_order_query_args() {

		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );
		wordpoints_alter_points( $user_id, 20, 'points', 'test' );

		$query = new WordPoints_Points_Logs_Query( array( 'order_by' => 'points' ) );

		$result = $query->get();

		$first  = array_shift( $result );
		$second = array_shift( $result );

		$this->assertGreaterThan( $second->points, $first->points );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'order_by' => 'points',
				'order'    => 'ASC',
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
	public function test_id_query_args() {

		// Create a user and add some points to generate some logs.
		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );
		wordpoints_alter_points( $user_id, 20, 'points', 'test' );

		// Make sure that the two logs were added.
		$query = new WordPoints_Points_Logs_Query;

		$logs = $query->get();

		$this->assertSame( 2, count( $logs ) );

		// Try the 'id__in' query arg.
		$query_2 = new WordPoints_Points_Logs_Query( array( 'id__in' => array( $logs[0]->id ) ) );

		$logs_2 = $query_2->get();

		$this->assertSame( 1, count( $logs_2 ) );
		$this->assertSame( $logs[0]->id, $logs_2[0]->id );

		// Try the 'id__not_in' query arg.
		$query_3 = new WordPoints_Points_Logs_Query( array( 'id__not_in' => array( $logs[0]->id ) ) );

		$logs_3 = $query_3->get();

		$this->assertSame( 1, count( $logs_3 ) );
		$this->assertSame( $logs[1]->id, $logs_3[0]->id );
	}

	/**
	 * Test the 'user_*' query args.
	 *
	 * @since 1.0.0
	 */
	public function test_user_query_args() {

		$user_ids = $this->factory->user->create_many( 2 );

		wordpoints_alter_points( $user_ids[0], 10, 'points', 'test' );
		wordpoints_alter_points( $user_ids[1], 10, 'points', 'test' );

		$query_1 = new WordPoints_Points_Logs_Query( array( 'user_id' => $user_ids[0] ) );
		$this->assertSame( 1, $query_1->count() );

		$query_2 = new WordPoints_Points_Logs_Query( array( 'user_id__in' => array( $user_ids[0] ) ) );
		$this->assertSame( 1, $query_2->count() );

		$query_3 = new WordPoints_Points_Logs_Query( array( 'user_id__not_in' => $user_ids ) );
		$this->assertSame( 0, $query_3->count() );
	}

	/**
	 * Test the 'points_type*' query args.
	 *
	 * @since 1.0.0
	 */
	public function test_points_type_query_args() {

		wordpoints_add_points_type( array( 'name' => 'credits' ) );
		wordpoints_add_points_type( array( 'name' => 'tests' ) );

		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );
		wordpoints_alter_points( $user_id, 10, 'credits', 'test' );
		wordpoints_alter_points( $user_id, 10, 'tests', 'test' );

		$query_1 = new WordPoints_Points_Logs_Query( array( 'points_type' => 'points' ) );
		$this->assertSame( 1, $query_1->count() );

		$query_2 = new WordPoints_Points_Logs_Query( array( 'points_type__in' => array( 'points', 'tests' ) ) );
		$this->assertSame( 2, $query_2->count() );

		$query_3 = new WordPoints_Points_Logs_Query( array( 'points_type__not_in' => array( 'points', 'tests' ) ) );
		$this->assertSame( 1, $query_3->count() );
	}

	/**
	 * Test the 'log_type*' query args.
	 *
	 * @since 1.0.0
	 */
	public function test_log_type_query_args() {

		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );
		wordpoints_alter_points( $user_id, 10, 'points', 'test2' );
		wordpoints_alter_points( $user_id, 10, 'points', 'test3' );

		$query_1 = new WordPoints_Points_Logs_Query( array( 'log_type' => 'test' ) );
		$this->assertSame( 1, $query_1->count() );

		$query_2 = new WordPoints_Points_Logs_Query( array( 'log_type__in' => array( 'test2', 'test3' ) ) );
		$this->assertSame( 2, $query_2->count() );

		$query_3 = new WordPoints_Points_Logs_Query( array( 'log_type__not_in' => array( 'test2', 'test3' ) ) );
		$this->assertSame( 1, $query_3->count() );
	}

	/**
	 * Test the 'points' and 'points_compare' query args.
	 *
	 * @since 1.0.0
	 */
	public function test_points_query_args() {

		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );
		wordpoints_alter_points( $user_id, 15, 'points', 'test' );
		wordpoints_alter_points( $user_id, 20, 'points', 'test' );

		$query_1 = new WordPoints_Points_Logs_Query( array( 'points' => 10 ) );
		$this->assertSame( 1, $query_1->count() );

		$query_2 = new WordPoints_Points_Logs_Query(
			array(
				'points' => 10,
				'points__compare' => '!=',
			)
		);
		$this->assertSame( 2, $query_2->count() );

		$query_3 = new WordPoints_Points_Logs_Query(
			array(
				'points' => 10,
				'points__compare' => '>',
			)
		);
		$this->assertSame( 2, $query_3->count() );

		$query_4 = new WordPoints_Points_Logs_Query(
			array(
				'points' => 10,
				'points__compare' => '<',
			)
		);
		$this->assertSame( 0, $query_4->count() );

		$query_5 = new WordPoints_Points_Logs_Query(
			array(
				'points' => 10,
				'points__compare' => '<=',
			)
		);
		$this->assertSame( 1, $query_5->count() );

		$query_6 = new WordPoints_Points_Logs_Query(
			array(
				'points' => 10,
				'points__compare' => '>=',
			)
		);
		$this->assertSame( 3, $query_6->count() );
	}

	/**
	 * Test the meta_query arg.
	 *
	 * @since 1.0.0
	 */
	public function test_meta_query_arg() {

		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test', array( 'test1' => 1 ) );
		wordpoints_alter_points( $user_id, 20, 'points', 'test', array( 'test2' => 2, 'test3' => 1 ) );

		$query_5 = new WordPoints_Points_Logs_Query(
			array(
				'order_by'   => 'meta_value',
				'meta_query' => array(
					'relation' => 'OR',
					array( 'key' => 'test1' ),
					array( 'key' => 'test2' ),
				),
			)
		);

		$results = $query_5->get();

		$this->assertSame( 2, count( $results ) );
		$this->assertSame( 20, (int) reset( $results )->points );
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

		$this->assertSame( 0, $query->count() );
	}

	/**
	 * Test the blog_* query arg.
	 *
	 * @since 1.2.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_blog_query_arg() {

		$current_blog_id = get_current_blog_id();

		$user_id = $this->factory->user->create();
		$blog_id = $this->factory->blog->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test' );

		switch_to_blog( $blog_id );

		if ( ! is_wordpoints_network_active() ) {
			wordpoints_add_points_type( array( 'name' => 'points' ) );
		}

		wordpoints_alter_points( $user_id, 20, 'points', 'test' );

		restore_current_blog();

		$query = new WordPoints_Points_Logs_Query();

		$this->assertSame( 1, $query->count() );
		$this->assertSame( '10', $query->get( 'row' )->points );

		$query = new WordPoints_Points_Logs_Query( array( 'blog_id' => $blog_id ) );

		$this->assertSame( 1, $query->count() );
		$this->assertSame( '20', $query->get( 'row' )->points );

		$query = new WordPoints_Points_Logs_Query( array( 'blog_id' => false ) );

		$this->assertSame( 2, $query->count() );

		$query = new WordPoints_Points_Logs_Query( array( 'blog_id__in' => array( $current_blog_id, $blog_id ) ) );

		$this->assertSame( 2, $query->count() );

		$query = new WordPoints_Points_Logs_Query( array( 'blog_id__not_in' => array( $current_blog_id ) ) );

		$this->assertSame( 1, $query->count() );
		$this->assertSame( '20', $query->get( 'row' )->points );
	}

	/**
	 * Test the log_text* query args.
	 *
	 * @since 1.6.0
	 */
	public function test_text_args() {

		$this->factory->wordpoints->points_log->create(
			array( 'text' => 'Test searching 100.' )
		);

		$this->factory->wordpoints->points_log->create(
			array( 'text' => 'A test with 100%.' )
		);

		$this->factory->wordpoints->points_log->create(
			array( 'text' => 'A test.' )
		);

		$query = new WordPoints_Points_Logs_Query( array( 'text' => 'A test.' ) );
		$this->assertSame( 1, $query->count() );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'text' => 'A test.',
				'text__compare' => '!=',
			)
		);
		$this->assertSame( 2, $query->count() );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'text' => 'A test%',
				'text__compare' => 'LIKE',
			)
		);
		$this->assertSame( 2, $query->count() );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'text' => 'A test%',
				'text__compare' => 'NOT LIKE',
			)
		);
		$this->assertSame( 1, $query->count() );

		$query = new WordPoints_Points_Logs_Query(
			array( 'text' => '%100\%%' )
		);
		$this->assertSame( 1, $query->count() );
	}

	/**
	 * Test that the cache is cleared properly.
	 *
	 * @since 1.5.0
	 */
	public function test_caching() {

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		$query = wordpoints_get_points_logs_query( 'points' );

		// No query yet.
		$this->assertSame( 0, $this->filter_was_called( 'query' ) );

		// Get the results;
		$query->get();

		// The cache should have been used, no new query.
		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		// Get the count.
		$query->count();

		// The count should also be cached, so no query needed here either.
		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		// New query.
		wordpoints_get_points_logs_query( 'points' );

		// The cache should still be good, so no new query should have been made.
		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		// Now alter some points.
		wordpoints_alter_points( $this->factory->user->create(), 10, 'points', 'test' );

		// Get the query again.
		$query = wordpoints_get_points_logs_query( 'points' );
		$query->get();

		// The cache should have been invalidated, and so another query made.
		$this->assertSame( 2, $this->filter_was_called( 'query' ) );
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

		// No query yet.
		$this->assertSame( 0, $this->filter_was_called( 'query' ) );

		$query->get();

		// Now there should have been a query.
		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		// Get a query for the 'credits' points type.
		$query = wordpoints_get_points_logs_query( 'credits' );
		$query->get();

		// A second query should have been made.
		$this->assertSame( 2, $this->filter_was_called( 'query' ) );

		// Now alter some points.
		wordpoints_alter_points( $this->factory->user->create(), 10, 'points', 'test' );

		// Get the 'points' query again.
		$query = wordpoints_get_points_logs_query( 'points' );
		$query->get();

		// The cache should have been invalidated, and so another query made.
		$this->assertSame( 3, $this->filter_was_called( 'query' ) );

		// Get the 'credits' query again.
		$query = wordpoints_get_points_logs_query( 'credits' );
		$query->get();

		// The cache should still have been good, no need for another query.
		$this->assertSame( 3, $this->filter_was_called( 'query' ) );
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

		// No query yet.
		$this->assertSame( 0, $this->filter_was_called( 'query' ) );

		$query->get();

		// Now there should have been a query.
		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		// Get a query for the second user.
		wp_set_current_user( $user_ids[1] );
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );
		$query->get();

		// A second query should have been made to prime this cache.
		$this->assertSame( 2, $this->filter_was_called( 'query' ) );

		// Now alter the points of the first user.
		wordpoints_alter_points( $user_ids[0], 10, 'points', 'test' );

		// Get the query again for the first user.
		wp_set_current_user( $user_ids[0] );
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );
		$query->get();

		// The cache should have been invalidated, and so another query made.
		$this->assertSame( 3, $this->filter_was_called( 'query' ) );

		// Get the query for the second user again.
		wp_set_current_user( $user_ids[1] );
		$query = wordpoints_get_points_logs_query( 'points', 'current_user' );
		$query->get();

		// The cache should still have been good, no need for another query.
		$this->assertSame( 3, $this->filter_was_called( 'query' ) );

		wp_set_current_user( $old_user->ID );
	}

	/**
	 * Test that network queries are cache for the entire network.
	 *
	 * @since 1.5.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_network_cache_is_network_wide() {

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Get the query.
		$query = wordpoints_get_points_logs_query( 'points', 'network' );

		// No query yet.
		$this->assertSame( 0, $this->filter_was_called( 'query' ) );

		$query->get();

		// Now there should have been a query.
		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		// Create a second site.
		$site_id = $this->factory->blog->create();

		switch_to_blog( $site_id );
		// Get the query again.
		$query = wordpoints_get_points_logs_query( 'points', 'network' );
		$query->get();

		// The cache should still be good, no query needed.
		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		// Now alter some points.
		wordpoints_alter_points( $this->factory->user->create(), 10, 'points', 'test' );
		restore_current_blog();

		// Get the query again on the first site.
		$query = wordpoints_get_points_logs_query( 'points', 'network' );
		$query->get();

		// The cache should have been invalidated, and so a new query made.
		$this->assertSame( 2, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Test that non-network queries are cached per-site.
	 *
	 * @since 1.5.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_cache_is_per_site() {

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Get the query.
		$query = wordpoints_get_points_logs_query( 'points' );

		// No query yet.
		$this->assertSame( 0, $this->filter_was_called( 'query' ) );

		$query->get();

		// Now there should have been a query.
		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		// Create a second site.
		$site_id = $this->factory->blog->create();

		switch_to_blog( $site_id );
		// Get the query again.
		$query = wordpoints_get_points_logs_query( 'points' );
		$query->get();

		// A new query is needed for this site.
		$this->assertSame( 2, $this->filter_was_called( 'query' ) );

		// Now alter some points.
		wordpoints_alter_points( $this->factory->user->create(), 10, 'points', 'test' );
		restore_current_blog();

		// Get the query again on the first site.
		$query = wordpoints_get_points_logs_query( 'points' );
		$query->get();

		// The cache should still be good, no new query needed.
		$this->assertSame( 2, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Test the get_page() method.
	 *
	 * @since 1.6.0
	 */
	public function test_get_page() {

		$ids = $this->factory->wordpoints->points_log->create_many( 9 );

		$query = new WordPoints_Points_Logs_Query(
			array( 'order_by' => 'id', 'order' => 'ASC' )
		);

		$page_1 = $query->get_page( 1, 2 );

		$this->assertCount( 2, $page_1 );
		$this->assertSame( $ids[0], (int) $page_1[0]->id );
		$this->assertSame( $ids[1], (int) $page_1[1]->id );

		$page_3 = $query->get_page( 3, 2 );

		$this->assertCount( 2, $page_3 );
		$this->assertSame( $ids[4], (int) $page_3[0]->id );
		$this->assertSame( $ids[5], (int) $page_3[1]->id );

		$page_5 = $query->get_page( 5, 2 );
		$this->assertCount( 1, $page_5 );
		$this->assertSame( $ids[8], (int) $page_5[0]->id );

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

		$this->factory->wordpoints->points_log->create_many( 2 );

		$query = new WordPoints_Points_Logs_Query(
			array( 'order_by' => 'id', 'order' => 'ASC' )
		);

		$sql = $query->get_sql();

		$query->get_page( 1 );

		$this->assertSame( $sql, $query->get_sql() );
	}

	/**
	 * Test that get_page() uses the cache.
	 *
	 * @since 1.6.0
	 */
	public function test_get_page_uses_cache() {

		$ids = $this->factory->wordpoints->points_log->create_many( 5 );

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Get the query.
		$query = new WordPoints_Points_Logs_Query(
			array( 'order_by' => 'id', 'order' => 'ASC' )
		);
		$query->prime_cache( __FUNCTION__ );

		// The cache should have been primed, but no query made.
		$this->assertSame( 0, $this->filter_was_called( 'query' ) );

		$query->get();

		// Now there should have been a query.
		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		// Get the first page.
		$page_1 = $query->get_page( 1, 2 );

		// The query shouldn't have been called again.
		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		$this->assertCount( 2, $page_1 );
		$this->assertSame( $ids[0], (int) $page_1[0]->id );
		$this->assertSame( $ids[1], (int) $page_1[1]->id );
	}

	/**
	 * Test that pages are cached separately.
	 *
	 * @since 1.9.0
	 */
	public function test_pages_cached_individually() {

		$ids = $this->factory->wordpoints->points_log->create_many( 5 );

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Get the query.
		$query = new WordPoints_Points_Logs_Query(
			array( 'order_by' => 'id', 'order' => 'ASC' )
		);
		$query->prime_cache( __METHOD__ );

		// The cache should have been primed, but no query made.
		$this->assertSame( 0, $this->filter_was_called( 'query' ) );

		// Get the first page.
		$page_1 = $query->get_page( 1, 2 );

		// The query shouldn't have been called again.
		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		$this->assertCount( 2, $page_1 );
		$this->assertSame( $ids[0], (int) $page_1[0]->id );
		$this->assertSame( $ids[1], (int) $page_1[1]->id );

		// Get the second page.
		$page_2 = $query->get_page( 2, 2 );

		// The query should have been called again.
		$this->assertSame( 2, $this->filter_was_called( 'query' ) );

		$this->assertCount( 2, $page_2 );
		$this->assertSame( $ids[2], (int) $page_2[0]->id );
		$this->assertSame( $ids[3], (int) $page_2[1]->id );

		// Get the whole query.
		$all = $query->get();

		// The query should have been called again.
		$this->assertSame( 3, $this->filter_was_called( 'query' ) );

		$this->assertCount( 5, $all );
	}

	/**
	 * Test get_page() calculates pages relative to the 'start' argument.
	 *
	 * @since 1.6.0
	 */
	public function test_get_page_with_start() {

		$ids = $this->factory->wordpoints->points_log->create_many( 5 );

		$query = new WordPoints_Points_Logs_Query(
			array( 'start' => 2, 'order_by' => 'id', 'order' => 'ASC' )
		);

		$page_1 = $query->get_page( 1, 2 );

		$this->assertSame( $ids[2], (int) $page_1[0]->id );
		$this->assertSame( $ids[3], (int) $page_1[1]->id );

		$page_2 = $query->get_page( 2, 2 );

		$this->assertSame( $ids[4], (int) $page_2[0]->id );
	}

	/**
	 * Test that get_page() calculates with correct limit.
	 *
	 * @since 1.6.0
	 */
	public function test_get_page_with_limit() {

		$this->factory->wordpoints->points_log->create_many( 5 );

		$query = new WordPoints_Points_Logs_Query(
			array( 'limit' => 3, 'order_by' => 'id', 'order' => 'ASC' )
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

		$this->factory->wordpoints->points_log->create_many( 2 );

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

		$this->factory->wordpoints->points_log->create_many( 2 );

		$query = new WordPoints_Points_Logs_Query;
		$query->prime_cache( __METHOD__ );

		$this->assertCount( 2, $query->get() );

		$query->set_args( array( 'limit' => 1 ) );

		$this->assertCount( 1, $query->get() );
	}
}

// EOF
