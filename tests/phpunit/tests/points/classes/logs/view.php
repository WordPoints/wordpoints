<?php

/**
 * Test case for the WordPoints_Points_Logs_View class.
 *
 * @package WordPoints\Tests\Points
 * @since 2.2.0
 */

/**
 * Tests the WordPoints_Points_Logs_View class.
 *
 * @since 2.2.0
 *
 * @group points
 *
 * @covers WordPoints_Points_Logs_View
 */
class WordPoints_Points_Logs_View_Test extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * Test that calls the display methods in the correct order.
	 *
	 * @since 2.2.0
	 */
	public function test_display() {

		$this->factory->wordpoints->points_log->create_many( 2 );

		$query = new WordPoints_Points_Logs_Query;
		$logs = $query->get();

		$view = new WordPoints_PHPUnit_Mock_Points_Logs_View( 'test', $query );

		$view->display();

		$this->assertSameProperties( $logs[0], $view->calls[4]['args'][0] );
		$this->assertSameProperties( $logs[1], $view->calls[5]['args'][0] );

		$logs[0] = $view->calls[4]['args'][0];
		$logs[1] = $view->calls[5]['args'][0];

		$this->assertSame(
			array(
				array( 'method' => 'get_search_term', 'args' => array() ),
				array( 'method' => 'get_page_number', 'args' => array() ),
				array( 'method' => 'get_per_page', 'args' => array() ),
				array( 'method' => 'before', 'args' => array() ),
				array( 'method' => 'log', 'args' => array( $logs[0] ), 'i' => 1, 'site_id' => 1 ),
				array( 'method' => 'log', 'args' => array( $logs[1] ), 'i' => 2, 'site_id' => 1 ),
				array( 'method' => 'after', 'args' => array() ),
			)
			, $view->calls
		);
	}

	/**
	 * Test that calls the methods in the correct order when there are no logs.
	 *
	 * @since 2.2.0
	 */
	public function test_display_no_logs() {

		$query = new WordPoints_Points_Logs_Query;
		$view  = new WordPoints_PHPUnit_Mock_Points_Logs_View( 'test', $query );

		$view->display();

		$this->assertSame(
			array(
				array( 'method' => 'get_search_term', 'args' => array() ),
				array( 'method' => 'get_page_number', 'args' => array() ),
				array( 'method' => 'get_per_page', 'args' => array() ),
				array( 'method' => 'before', 'args' => array() ),
				array( 'method' => 'no_logs', 'args' => array() ),
				array( 'method' => 'after', 'args' => array() ),
			)
			, $view->calls
		);
	}

	/**
	 * Test that using a search term.
	 *
	 * @since 2.2.0
	 */
	public function test_search_term() {

		$this->factory->wordpoints->points_log->create();
		$this->factory->wordpoints->points_log->create(
			array( 'text' => 'Test searching.' )
		);

		$query = new WordPoints_Points_Logs_Query;
		$view  = new WordPoints_PHPUnit_Mock_Points_Logs_View( 'test', $query );

		$view->search_term = 'search';

		$view->display();

		$this->assertSame( '%search%', $query->get_arg( 'text' ) );

		$logs = $query->get();

		$this->assertSameProperties( $logs[0], $view->calls[4]['args'][0] );

		$logs[0] = $view->calls[4]['args'][0];

		$this->assertSame(
			array(
				array( 'method' => 'get_search_term', 'args' => array() ),
				array( 'method' => 'get_page_number', 'args' => array() ),
				array( 'method' => 'get_per_page', 'args' => array() ),
				array( 'method' => 'before', 'args' => array() ),
				array( 'method' => 'log', 'args' => array( $logs[0] ), 'i' => 1, 'site_id' => 1 ),
				array( 'method' => 'after', 'args' => array() ),
			)
			, $view->calls
		);
	}

	/**
	 * Test that that searching can be disabled.
	 *
	 * @since 2.2.0
	 */
	public function test_search_term_not_searchable() {

		$this->factory->wordpoints->points_log->create();
		$this->factory->wordpoints->points_log->create(
			array( 'text' => 'Test searching.' )
		);

		$query = new WordPoints_Points_Logs_Query;
		$view  = new WordPoints_PHPUnit_Mock_Points_Logs_View(
			'test'
			, $query
			, array( 'searchable' => false )
		);

		$view->search_term = 'search';

		$view->display();

		$this->assertEmpty( $query->get_arg( 'text' ) );

		$logs = $query->get();

		$this->assertSameProperties( $logs[0], $view->calls[3]['args'][0] );
		$this->assertSameProperties( $logs[1], $view->calls[4]['args'][0] );

		$logs[0] = $view->calls[3]['args'][0];
		$logs[1] = $view->calls[4]['args'][0];

		$this->assertSame(
			array(
				array( 'method' => 'get_page_number', 'args' => array() ),
				array( 'method' => 'get_per_page', 'args' => array() ),
				array( 'method' => 'before', 'args' => array() ),
				array( 'method' => 'log', 'args' => array( $logs[0] ), 'i' => 1, 'site_id' => 1 ),
				array( 'method' => 'log', 'args' => array( $logs[1] ), 'i' => 2, 'site_id' => 1 ),
				array( 'method' => 'after', 'args' => array() ),
			)
			, $view->calls
		);
	}

	/**
	 * Test that setting the page number.
	 *
	 * @since 2.2.0
	 */
	public function test_page_number() {

		$this->factory->wordpoints->points_log->create_many( 4 );

		$query = new WordPoints_Points_Logs_Query;
		$view  = new WordPoints_PHPUnit_Mock_Points_Logs_View( 'test', $query );

		$view->page_number = 2;

		$view->display();

		$logs = $query->get();

		$this->assertSameProperties( $logs[3], $view->calls[4]['args'][0] );

		$logs[3] = $view->calls[4]['args'][0];

		$this->assertSame(
			array(
				array( 'method' => 'get_search_term', 'args' => array() ),
				array( 'method' => 'get_page_number', 'args' => array() ),
				array( 'method' => 'get_per_page', 'args' => array() ),
				array( 'method' => 'before', 'args' => array() ),
				array( 'method' => 'log', 'args' => array( $logs[3] ), 'i' => 1, 'site_id' => 1 ),
				array( 'method' => 'after', 'args' => array() ),
			)
			, $view->calls
		);
	}

	/**
	 * Test that setting the page number.
	 *
	 * @since 2.2.0
	 */
	public function test_page_number_not_paginated() {

		$this->factory->wordpoints->points_log->create_many( 4 );

		$query = new WordPoints_Points_Logs_Query;
		$view  = new WordPoints_PHPUnit_Mock_Points_Logs_View(
			'test'
			, $query
			, array( 'paginate' => false )
		);

		$view->page_number = 2;

		$view->display();

		$logs = $query->get();

		$this->assertSameProperties( $logs[0], $view->calls[2]['args'][0] );
		$this->assertSameProperties( $logs[1], $view->calls[3]['args'][0] );
		$this->assertSameProperties( $logs[2], $view->calls[4]['args'][0] );
		$this->assertSameProperties( $logs[3], $view->calls[5]['args'][0] );

		$logs[0] = $view->calls[2]['args'][0];
		$logs[1] = $view->calls[3]['args'][0];
		$logs[2] = $view->calls[4]['args'][0];
		$logs[3] = $view->calls[5]['args'][0];

		$this->assertSame(
			array(
				array( 'method' => 'get_search_term', 'args' => array() ),
				array( 'method' => 'before', 'args' => array() ),
				array( 'method' => 'log', 'args' => array( $logs[0] ), 'i' => 1, 'site_id' => 1 ),
				array( 'method' => 'log', 'args' => array( $logs[1] ), 'i' => 2, 'site_id' => 1 ),
				array( 'method' => 'log', 'args' => array( $logs[2] ), 'i' => 3, 'site_id' => 1 ),
				array( 'method' => 'log', 'args' => array( $logs[3] ), 'i' => 4, 'site_id' => 1 ),
				array( 'method' => 'after', 'args' => array() ),
			)
			, $view->calls
		);
	}

	/**
	 * Test that that it skips restricted logs.
	 *
	 * @since 2.2.0
	 */
	public function test_restricted() {

		$this->mock_apps();

		$this->factory->wordpoints->points_log->create();

		$this->factory->wordpoints->points_log->create(
			array( 'log_type' => 'hidden' )
		);

		$log = $this->factory->wordpoints->points_log->create_and_get();

		$this->factory->wordpoints->points_log->create(
			array( 'log_type' => 'hidden' )
		);

		$query = new WordPoints_Points_Logs_Query( array( 'order_by' => 'id' ) );
		$view  = new WordPoints_PHPUnit_Mock_Points_Logs_View( 'test', $query );

		wordpoints_component( 'points' )
			->get_sub_app( 'logs' )
			->get_sub_app( 'viewing_restrictions' )
			->register(
				'hidden'
				, 'hide'
				, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction'
			);

		$view->display();

		$this->assertSameProperties( $log, $view->calls[4]['args'][0] );

		$log = $view->calls[4]['args'][0];

		$this->assertSame(
			array(
				array( 'method' => 'get_search_term', 'args' => array() ),
				array( 'method' => 'get_page_number', 'args' => array() ),
				array( 'method' => 'get_per_page', 'args' => array() ),
				array( 'method' => 'before', 'args' => array() ),
				array( 'method' => 'log', 'args' => array( $log ), 'i' => 1, 'site_id' => 1 ),
				array( 'method' => 'after', 'args' => array() ),
			)
			, $view->calls
		);
	}

	/**
	 * Test that it skips logs restricted by the filters.
	 *
	 * @since 2.2.0
	 *
	 * @expectedDeprecated wordpoints_user_can_view_points_log
	 */
	public function test_restricted_legacy_filters() {

		add_filter( 'wordpoints_user_can_view_points_log', '__return_false' );

		$this->mock_apps();

		$this->factory->wordpoints->points_log->create(
			array( 'log_type' => 'hidden' )
		);

		$this->factory->wordpoints->points_log->create_and_get();

		$this->factory->wordpoints->points_log->create(
			array( 'log_type' => 'hidden' )
		);

		$this->factory->wordpoints->points_log->create();

		$query = new WordPoints_Points_Logs_Query;
		$view  = new WordPoints_PHPUnit_Mock_Points_Logs_View( 'test', $query );
		$view->display();

		$this->assertSame(
			array(
				array( 'method' => 'get_search_term', 'args' => array() ),
				array( 'method' => 'get_page_number', 'args' => array() ),
				array( 'method' => 'get_per_page', 'args' => array() ),
				array( 'method' => 'before', 'args' => array() ),
				array( 'method' => 'after', 'args' => array() ),
			)
			, $view->calls
		);
	}

	/**
	 * Test that it skips logs restricted by the filters.
	 *
	 * @since 2.2.0
	 *
	 * @expectedDeprecated wordpoints_user_can_view_points_log-hidden
	 */
	public function test_returns_false_if_specific_filter_returns_false() {

		add_filter(
			'wordpoints_user_can_view_points_log-hidden'
			, '__return_false'
		);

		$this->mock_apps();

		$this->factory->wordpoints->points_log->create();

		$this->factory->wordpoints->points_log->create(
			array( 'log_type' => 'hidden' )
		);

		$log = $this->factory->wordpoints->points_log->create_and_get();

		$this->factory->wordpoints->points_log->create(
			array( 'log_type' => 'hidden' )
		);

		$query = new WordPoints_Points_Logs_Query( array( 'order_by' => 'id' ) );
		$view  = new WordPoints_PHPUnit_Mock_Points_Logs_View( 'test', $query );
		$view->display();

		$this->assertSameProperties( $log, $view->calls[4]['args'][0] );

		$log = $view->calls[4]['args'][0];

		$this->assertSame(
			array(
				array( 'method' => 'get_search_term', 'args' => array() ),
				array( 'method' => 'get_page_number', 'args' => array() ),
				array( 'method' => 'get_per_page', 'args' => array() ),
				array( 'method' => 'before', 'args' => array() ),
				array( 'method' => 'log', 'args' => array( $log ), 'i' => 1, 'site_id' => 1 ),
				array( 'method' => 'after', 'args' => array() ),
			)
			, $view->calls
		);
	}

	/**
	 * Test behavior on multisite.
	 *
	 * @since 2.2.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_multisite() {

		$log = $this->factory->wordpoints->points_log->create_and_get();

		$site_id = $this->factory->blog->create();

		switch_to_blog( $site_id );
		$other_log = $this->factory->wordpoints->points_log->create_and_get();
		restore_current_blog();

		$query = new WordPoints_Points_Logs_Query(
			array( 'blog_id' => false, 'order_by' => 'id' )
		);

		$view = new WordPoints_PHPUnit_Mock_Points_Logs_View( 'test', $query );

		$this->assertSame( (int) $log->blog_id, get_current_blog_id() );

		$view->display();

		$this->assertSameProperties( $log, $view->calls[5]['args'][0] );
		$this->assertSameProperties( $other_log, $view->calls[4]['args'][0] );

		$log       = $view->calls[5]['args'][0];
		$other_log = $view->calls[4]['args'][0];

		$this->assertSame(
			array(
				array( 'method' => 'get_search_term', 'args' => array() ),
				array( 'method' => 'get_page_number', 'args' => array() ),
				array( 'method' => 'get_per_page', 'args' => array() ),
				array( 'method' => 'before', 'args' => array() ),
				array( 'method' => 'log', 'args' => array( $other_log ), 'i' => 1, 'site_id' => (int) $other_log->blog_id ),
				array( 'method' => 'log', 'args' => array( $log ), 'i' => 2, 'site_id' => (int) $log->blog_id ),
				array( 'method' => 'after', 'args' => array() ),
			)
			, $view->calls
		);

		$this->assertSame( (int) $log->blog_id, get_current_blog_id() );
	}
}

// EOF
