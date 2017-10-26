<?php

/**
 * Testcase for the [wordpoints_points_logs] shortcode.
 *
 * @package WordPoints\Tests\Points
 * @since 1.4.0
 */

/**
 * Test the [wordpoints_points_logs] shortcode.
 *
 * Since 1.0.0 this was a part of the WordPoints_Points_Shortcodes_Test, which was
 * split into a separate testcase for each shortcode.
 *
 * @since 1.4.0
 *
 * @group points
 * @group shortcodes
 *
 * @covers WordPoints_Points_Shortcode_Logs
 */
class WordPoints_Points_Logs_Shortcode_Test extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * Set up for each test.
	 *
	 * @since 1.6.0
	 */
	public function setUp() {

		parent::setUp();

		unset(
			$_POST['wordpoints_points_logs_search']
			, $_GET['wordpoints_points_logs_per_page']
		);
	}

	/**
	 * Clean up after each test.
	 *
	 * @since 1.6.0
	 */
	public function tearDown() {

		unset(
			$_POST['wordpoints_points_logs_search']
			, $_GET['wordpoints_points_logs_per_page']
		);

		parent::tearDown();
	}

	/**
	 * Test that the [wordpoints_points_logs] shortcode exists.
	 *
	 * @since 1.4.0
	 *
	 * @coversNothing
	 */
	public function test_shortcode_exists() {

		$this->assertTrue( shortcode_exists( 'wordpoints_points_logs' ) );
	}

	/**
	 * Test that the old version of the class is deprecated.
	 *
	 * @since 2.3.0
	 *
	 * @covers WordPoints_Points_Logs_Shortcode
	 *
	 * @expectedDeprecated WordPoints_Points_Logs_Shortcode::__construct
	 */
	public function test_deprecated_version() {

		new WordPoints_Points_Logs_Shortcode( array(), '' );
	}

	/**
	 * Test the 'datatables' attribute.
	 *
	 * @since 1.4.0
	 * @since 1.6.0 The datatables attribute is deprecated, but maps to 'paginate'.
	 */
	public function test_datatables_attribute() {

		// Create some data for the table to display.
		$this->factory->wordpoints->points_log->create_many( 4 );

		$_GET['wordpoints_points_logs_per_page'] = 3;

		// Default datatable.
		$html = $this->do_shortcode(
			'wordpoints_points_logs'
			, array( 'points_type' => 'points' )
		);

		$document = new DOMDocument();
		$document->loadHTML( $html );
		$xpath = new DOMXPath( $document );

		$table_classes = $xpath->query( '//table' )
			->item( 0 )
			->attributes
			->getNamedItem( 'class' )
			->nodeValue;

		$this->assertStringContains( 'wordpoints-points-logs', $table_classes );
		$this->assertStringContains( 'widefat', $table_classes );

		$this->assertSame( 3, $xpath->query( '//tbody/tr' )->length );

		// Should be paginated.
		$this->assertNotEquals(
			0
			, $xpath->query( '//a[@class = "page-numbers"]' )->length
		);

		// Non-datatable, no pagination.
		$document = new DOMDocument();
		$document->loadHTML(
			$this->do_shortcode(
				'wordpoints_points_logs'
				, array( 'points_type' => 'points', 'datatables' => '0' )
			)
		);
		$xpath = new DOMXPath( $document );

		$this->assertSame(
			0
			, $xpath->query( '//a[@class = "page-numbers"]' )->length
		);

	} // End public function test_datatables_attribute().

	/**
	 * Test the 'paginate' attribute.
	 *
	 * @since 1.6.0
	 */
	public function test_paginate_attribute() {

		// Create some data for the table to display.
		$this->factory->wordpoints->points_log->create_many( 4 );

		$_GET['wordpoints_points_logs_per_page'] = 3;

		// Default datatable.
		$html = $this->do_shortcode(
			'wordpoints_points_logs'
			, array( 'points_type' => 'points' )
		);

		$document = new DOMDocument();
		$document->loadHTML( $html );
		$xpath = new DOMXPath( $document );

		$table_classes = $xpath->query( '//table' )
			->item( 0 )
			->attributes
			->getNamedItem( 'class' )
			->nodeValue;

		$this->assertStringContains( 'wordpoints-points-logs', $table_classes );
		$this->assertStringContains( 'widefat', $table_classes );

		$this->assertSame( 3, $xpath->query( '//tbody/tr' )->length );

		// Should be paginated.
		$this->assertNotEquals(
			0
			, $xpath->query( '//a[@class = "page-numbers"]' )->length
		);

		// Non-datatable, no pagination.
		$document = new DOMDocument();
		$document->loadHTML(
			$this->do_shortcode(
				'wordpoints_points_logs'
				, array( 'points_type' => 'points', 'paginate' => '0' )
			)
		);
		$xpath = new DOMXPath( $document );

		$this->assertSame(
			0
			, $xpath->query( '//a[@class = "page-numbers"]' )->length
		);

	} // End public function test_paginate_attribute().

	/**
	 * Test the 'show_users' attribute.
	 *
	 * @since 1.4.0
	 */
	public function test_show_users_attribute() {

		// Create some data for the table to display.
		$user_id = $this->factory->user->create();

		for ( $i = 1; $i < 5; $i++ ) {

			wordpoints_add_points( $user_id, 10, 'points', 'test' );
		}

		// The user column should be displayed by default.
		$document = new DOMDocument();
		$document->loadHTML(
			$this->do_shortcode(
				'wordpoints_points_logs'
				, array( 'points_type' => 'points' )
			)
		);
		$xpath = new DOMXPath( $document );

		$this->assertSame( 4, $xpath->query( '//thead/tr/th' )->length );

		// Check that it is hidden.
		$document = new DOMDocument();
		$document->loadHTML(
			$this->do_shortcode(
				'wordpoints_points_logs'
				, array( 'points_type' => 'points', 'show_users' => 0 )
			)
		);
		$xpath = new DOMXPath( $document );

		$this->assertSame( 3, $xpath->query( '//thead/tr/th' )->length );

	} // End public function test_show_users_attribute().

	/**
	 * Check failures with a normal user display nothing.
	 *
	 * @since 1.4.0
	 */
	public function test_displays_nothing_to_normal_user_on_fail() {

		$old_current_user = wp_get_current_user();
		$new_current_user = wp_set_current_user( $this->factory->user->create() );
		$new_current_user->set_role( 'subscriber' );

		$this->assertSame(
			''
			, $this->do_shortcode(
				'wordpoints_points_logs'
				, array( 'points_type' => 'idontexist' )
			)
		);

		$this->assertSame(
			''
			, $this->do_shortcode(
				'wordpoints_points_logs'
				, array( 'points_type' => 'points', 'query' => 'invalid' )
			)
		);

		wp_set_current_user( $old_current_user->ID );
	}

	/**
	 * Check failures with an admin user dispaly an error.
	 *
	 * @since 1.4.0
	 */
	public function test_displays_error_to_admin_user_on_fail() {

		$old_current_user = wp_get_current_user();
		$new_current_user = wp_set_current_user( $this->factory->user->create() );
		$new_current_user->set_role( 'administrator' );

		$this->assertWordPointsShortcodeError(
			$this->do_shortcode(
				'wordpoints_points_logs'
				, array( 'points_type' => 'idontexist' )
			)
		);

		$this->assertWordPointsShortcodeError(
			$this->do_shortcode(
				'wordpoints_points_logs'
				, array( 'points_type' => 'points', 'query' => 'invalid' )
			)
		);

		wp_set_current_user( $old_current_user->ID );
	}

	/**
	 * Test the 'searchable' attribute.
	 *
	 * @since 1.6.0
	 */
	public function test_searchable_attribute() {

		// Create some data for the table to display.
		$this->factory->wordpoints->points_log->create_many( 2 );
		$this->factory->wordpoints->points_log->create_many( 2, array( 'text' => __METHOD__ ) );

		$_POST['wordpoints_points_logs_search'] = __METHOD__;

		// Default is searchable.
		$html = $this->do_shortcode(
			'wordpoints_points_logs'
			, array( 'points_type' => 'points' )
		);

		$document = new DOMDocument();
		$document->loadHTML( $html );
		$xpath = new DOMXPath( $document );

		$table_classes = $xpath->query( '//table' )
			->item( 0 )
			->attributes
			->getNamedItem( 'class' )
			->nodeValue;

		$this->assertStringContains( 'wordpoints-points-logs', $table_classes );
		$this->assertStringContains( 'widefat', $table_classes );

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );

		// Should be searchable.
		$this->assertSame(
			1
			, $xpath->query( '//div[@class = "wordpoints-points-logs-search"]' )
				->length
		);

		// Should display 'searching for' text.
		$this->assertSame(
			1
			, $xpath->query( '//div[@class = "wordpoints-points-logs-searching"]' )
				->length
		);

		$html = $this->do_shortcode(
			'wordpoints_points_logs'
			, array( 'points_type' => 'points', 'searchable' => '0' )
		);

		$document = new DOMDocument();
		$document->loadHTML( $html );
		$xpath = new DOMXPath( $document );

		// Non-searchable.
		$this->assertSame(
			0
			, $xpath->query( '//div[@class = "wordpoints-points-logs-search"]' )
				->length
		);

		$this->assertSame(
			0
			, $xpath->query( '//div[@class = "wordpoints-points-logs-searching"]' )
				->length
		);

	} // End public function test_searchable_attribute().
}

// EOF
