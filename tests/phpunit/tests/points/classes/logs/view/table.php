<?php

/**
 * Test case for the WordPoints_Points_Logs_View_Table class.
 *
 * @package WordPoints\Tests\Points
 * @since 2.2.0
 */

/**
 * Tests the WordPoints_Points_Logs_View_Table class.
 *
 * @since 2.2.0
 *
 * @group points
 *
 * @covers WordPoints_Points_Logs_View_Table
 */
class WordPoints_Points_Logs_View_Table_Test
	extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * Set up for each test.
	 *
	 * @since 2.2.0
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
	 * @since 2.2.0
	 */
	public function tearDown() {

		unset(
			$_POST['wordpoints_points_logs_search']
			, $_GET['wordpoints_points_logs_per_page']
		);

		parent::tearDown();
	}

	/**
	 * Test the default table structure.
	 *
	 * @since 2.2.0
	 */
	public function test_table() {

		$this->factory->wordpoints->points_log->create_many( 4 );

		// Default is to paginate.
		$xpath = $this->get_xpath_for_view();

		$this->assertTableHasClass( 'wordpoints-points-logs', $xpath );
		$this->assertTableHasClass( 'widefat', $xpath );

		$rows = $xpath->query( '//tbody/tr' );

		$this->assertSame( 4, $rows->length );

		$this->assertStringContains(
			'odd'
			, $rows->item( 0 )->attributes->getNamedItem( 'class' )->nodeValue
		);

		$this->assertStringContains(
			'even'
			, $rows->item( 1 )->attributes->getNamedItem( 'class' )->nodeValue
		);

		$this->assertStringContains(
			'odd'
			, $rows->item( 2 )->attributes->getNamedItem( 'class' )->nodeValue
		);

		$this->assertStringContains(
			'even'
			, $rows->item( 3 )->attributes->getNamedItem( 'class' )->nodeValue
		);
	}

	/**
	 * Test the 'paginate' arg.
	 *
	 * @since 2.2.0
	 */
	public function test_paginate() {

		$this->factory->wordpoints->points_log->create_many( 4 );

		$_GET['wordpoints_points_logs_per_page'] = 3;

		// Default is to paginate.
		$xpath = $this->get_xpath_for_view();

		$this->assertSame( 3, $xpath->query( '//tbody/tr' )->length );

		// Should be paginated.
		$this->assertNotEquals(
			0
			, $xpath->query( '//a[@class = "page-numbers"]' )->length
		);

		$this->assertTableHasClass( 'paginate', $xpath );
	}

	/**
	 * Test the 'paginate' arg being false.
	 *
	 * @since 2.2.0
	 */
	public function test_no_paginate() {

		$this->factory->wordpoints->points_log->create_many( 4 );

		$_GET['wordpoints_points_logs_per_page'] = 3;

		// Default is to paginate.
		$xpath = $this->get_xpath_for_view( array( 'paginate' => false ) );

		$this->assertSame( 4, $xpath->query( '//tbody/tr' )->length );

		$this->assertSame(
			0
			, $xpath->query( '//a[@class = "page-numbers"]' )->length
		);

		$this->assertTableNotHasClass( 'paginate', $xpath );
	}

	/**
	 * Test the 'show_users' arg.
	 *
	 * @since 2.2.0
	 */
	public function test_show_users() {

		$this->factory->wordpoints->points_log->create_many( 4 );

		// The user column should be displayed by default.
		$xpath = $this->get_xpath_for_view();

		$this->assertSame( 4, $xpath->query( '//thead/tr/th' )->length );
	}

	/**
	 * Test the 'show_users' arg.
	 *
	 * @since 2.2.0
	 */
	public function test_no_show_users() {

		$this->factory->wordpoints->points_log->create_many( 4 );

		$xpath = $this->get_xpath_for_view( array( 'show_users' => false ) );

		$this->assertSame( 3, $xpath->query( '//thead/tr/th' )->length );
	}

	/**
	 * Test the 'searchable' arg.
	 *
	 * @since 2.2.0
	 */
	public function test_searchable() {

		// Create some data for the table to display.
		$this->factory->wordpoints->points_log->create_many( 2 );
		$this->factory->wordpoints->points_log->create_many( 2, array( 'text' => __METHOD__ ) );

		$_POST['wordpoints_points_logs_search'] = __METHOD__;

		// Default is searchable.
		$xpath = $this->get_xpath_for_view();

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

		$this->assertTableHasClass( 'searchable', $xpath );
	}

	/**
	 * Test the 'searchable' arg.
	 *
	 * @since 2.2.0
	 */
	public function test_no_searchable() {

		// Create some data for the table to display.
		$this->factory->wordpoints->points_log->create_many( 2 );
		$this->factory->wordpoints->points_log->create_many( 2, array( 'text' => __METHOD__ ) );

		$_POST['wordpoints_points_logs_search'] = __METHOD__;

		$xpath = $this->get_xpath_for_view( array( 'searchable' => false ) );

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

		$this->assertTableNotHasClass( 'searchable', $xpath );
	}

	/**
	 * Test the points column heading defaults to "Points".
	 *
	 * @since 2.2.0
	 */
	public function test_points_column_heading() {

		$xpath = $this->get_xpath_for_view();

		$this->assertSame(
			'Points'
			, $xpath->query( '//thead/tr/th' )->item( 1 )->textContent
		);
	}

	/**
	 * Test the points column heading uses the points type name if set.
	 *
	 * @since 2.2.0
	 */
	public function test_points_column_heading_points_type() {

		wordpoints_add_points_type( array( 'name' => 'Credits' ) );

		$xpath = $this->get_xpath_for_view(
			array()
			, array( 'points_type' => 'credits' )
		);

		$this->assertSame(
			'Credits'
			, $xpath->query( '//thead/tr/th' )->item( 1 )->textContent
		);
	}

	/**
	 * Test that the list of extra classes are passed through a filter.
	 *
	 * @since 2.2.0
	 */
	public function test_classes_filter() {

		$mock = new WordPoints_PHPUnit_Mock_Filter( array( 'test' ) );
		$mock->add_filter( 'wordpoints_points_logs_table_extra_classes' );

		$xpath = $this->get_xpath_for_view();

		$this->assertTableHasClass( 'test', $xpath );

		// These classes are also passed through the filter.
		$this->assertTableNotHasClass( 'paginated', $xpath );
		$this->assertTableNotHasClass( 'searchable', $xpath );
	}

	/**
	 * Test that the the user's name is passed through a filter.
	 *
	 * @since 2.2.0
	 */
	public function test_username_filter() {

		$this->factory->wordpoints->points_log->create();

		$mock = new WordPoints_PHPUnit_Mock_Filter( 'test' );
		$mock->add_filter( 'wordpoints_points_logs_table_username' );

		$xpath = $this->get_xpath_for_view();
		$nodes = $xpath->query( '//tr/td' );

		$this->assertStringMatchesFormat(
			'%atest%a'
			, $nodes->item( 0 )->textContent
		);
	}

	/**
	 * Test a message spanning all columns is displayed when there are no logs.
	 *
	 * @since 2.2.0
	 */
	public function test_no_logs() {

		$xpath = $this->get_xpath_for_view(
			array()
			, array( 'points_type' => 'nonexistent' )
		);

		$nodes = $xpath->query( '//tr/td' );

		$this->assertSame( 1, $nodes->length );

		$this->assertSame(
			'4'
			, $nodes->item( 0 )->attributes->getNamedItem( 'colspan' )->textContent
		);
	}

	/**
	 * Test a message spanning all columns is displayed when there are no logs.
	 *
	 * @since 2.2.0
	 */
	public function test_no_logs_no_show_users() {

		$xpath = $this->get_xpath_for_view(
			array( 'show_users' => false )
			, array( 'points_type' => 'nonexistent' )
		);

		$nodes = $xpath->query( '//tr/td' );

		$this->assertSame( 1, $nodes->length );

		$this->assertSame(
			'3'
			, $nodes->item( 0 )->attributes->getNamedItem( 'colspan' )->textContent
		);
	}

	//
	// Helpers.
	//

	/**
	 * Get the xPath object for a view.
	 *
	 * @since 2.2.0
	 *
	 * @param array $args       Settings to pass to the view.
	 * @param array $query_args Arguments to pass to the points logs query.
	 *
	 * @return DOMXPath An xPath object loaded with the HTML produced by the view.
	 */
	protected function get_xpath_for_view(
		array $args = array(),
		array $query_args = array()
	) {

		$view = new WordPoints_Points_Logs_View_Table(
			'table'
			, new WordPoints_Points_Logs_Query( $query_args )
			, $args
		);

		ob_start();
		$view->display();
		$html = ob_get_clean();

		$document = new DOMDocument;
		$document->loadHTML( $html );

		return new DOMXPath( $document );
	}

	/**
	 * Assert that the table has a particular class.
	 *
	 * @since 2.2.0
	 *
	 * @param string   $class The class the table is expected to have.
	 * @param DOMXPath $xpath The xPath object for the document that has the table.
	 */
	protected function assertTableHasClass( $class, $xpath ) {

		$table_classes = $xpath->query( '//table' )
			->item( 0 )
			->attributes
			->getNamedItem( 'class' )
			->nodeValue;

		$this->assertStringContains( $class, $table_classes );
	}

	/**
	 * Assert that the table does not have a particular class.
	 *
	 * @since 2.2.0
	 *
	 * @param string   $class The class the table is expected to not have.
	 * @param DOMXPath $xpath The xPath object for the document that has the table.
	 */
	protected function assertTableNotHasClass( $class, $xpath ) {

		$table_classes = $xpath->query( '//table' )
			->item( 0 )
			->attributes
			->getNamedItem( 'class' )
			->nodeValue;

		$this->assertNotContains( $class, $table_classes );
	}
}

// EOF
