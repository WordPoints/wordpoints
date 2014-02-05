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

		$query = new WordPoints_Points_Logs_Query( array( 'start' => 1, 'limit' => 2 ) );

		$result = $query->get();
		$this->assertEquals( 1, count( $result ) );

		$result = current( $result );
		$this->assertEquals( 20, $result->points );
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
}

// end of file.
