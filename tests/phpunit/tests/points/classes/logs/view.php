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

		$this->assertEquals(
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

		$this->assertEquals(
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
			array( 'text' => 'Test searching.')
		);

		$query = new WordPoints_Points_Logs_Query;
		$view  = new WordPoints_PHPUnit_Mock_Points_Logs_View( 'test', $query );

		$view->search_term = 'search';

		$view->display();

		$this->assertEquals( '%search%', $query->get_arg( 'text' ) );

		$logs = $query->get();

		$this->assertEquals(
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
			array( 'text' => 'Test searching.')
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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
			array( 'blog_id' => false, 'orderby' => 'id' )
		);

		$view = new WordPoints_PHPUnit_Mock_Points_Logs_View( 'test', $query );

		$this->assertEquals( $log->blog_id, get_current_blog_id() );

		$view->display();

		$this->assertEquals(
			array(
				array( 'method' => 'get_search_term', 'args' => array() ),
				array( 'method' => 'get_page_number', 'args' => array() ),
				array( 'method' => 'get_per_page', 'args' => array() ),
				array( 'method' => 'before', 'args' => array() ),
				array( 'method' => 'log', 'args' => array( $other_log ), 'i' => 1, 'site_id' => $other_log->blog_id ),
				array( 'method' => 'log', 'args' => array( $log ), 'i' => 2, 'site_id' => $log->blog_id ),
				array( 'method' => 'after', 'args' => array() ),
			)
			, $view->calls
		);

		$this->assertEquals( $log->blog_id, get_current_blog_id() );
	}
}

// EOF
