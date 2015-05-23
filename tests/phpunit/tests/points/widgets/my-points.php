<?php

/**
 * A test case for the My Points widget.
 *
 * @package WordPoints\Tests
 * @since 1.9.0
 */

/**
 * Test that the My Points widget functions correctly.
 *
 * @since 1.9.0
 *
 * @group points
 * @group widgets
 *
 * @covers WordPoints_My_Points_Widget
 */
class WordPoints_My_Points_Widget_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * @since 1.9.0
	 */
	protected $widget_class = 'WordPoints_My_Points_Widget';

	/**
	 * Set up for each test.
	 *
	 * @since 1.9.0
	 */
	public function setUp() {

		parent::setUp();

		wp_set_current_user( $this->factory->user->create() );
	}

	/**
	 * Test behavior with the user not logged in and there is no alt text.
	 *
	 * @since 1.9.0
	 */
	public function test_user_not_logged_in_no_alt_text() {

		wp_set_current_user( 0 );

		$html = $this->get_widget_html(
			array( 'alt_text' => '', 'points_type' => 'points' )
		);

		$this->assertEmpty( $html );
	}

	/**
	 * Test that the alt text is displayed when the user is not logged in.
	 *
	 * @since 1.9.0
	 */
	public function test_alt_text() {

		wp_set_current_user( 0 );

		$xpath = $this->get_widget_xpath(
			array( 'alt_text' => 'Alt text', 'points_type' => 'points' )
		);

		$node = $xpath->query( '//div[@class = "wordpoints-points-widget-text"]' )
			->item( 0 );

		$this->assertEquals( 'Alt text', $node->textContent );
	}

	/**
	 * Test that the points logs aren't displayed when the user is not logged in.
	 *
	 * @since 1.10.1
	 *
	 * @covers WordPoints_My_Points_Widget::widget_body
	 */
	public function test_logged_out_no_points_logs() {

		wp_set_current_user( 0 );

		$xpath = $this->get_widget_xpath(
			array(
				'alt_text' => 'Alt text',
				'points_type' => 'points',
				'number_logs' => 3,
			)
		);

		$this->assertEquals( 0, $xpath->query( '//table' )->length );
	}

	/**
	 * Test the default behavior.
	 *
	 * @since 1.9.0
	 */
	public function test_defaults() {

		$this->factory->wordpoints_points_log->create_many(
			4
			, array( 'user_id' => get_current_user_id() )
		);

		$xpath = $this->get_widget_xpath( array( 'points_type' => 'points' ) );

		$node = $xpath->query( '//div[@class = "wordpoints-points-widget-text"]' )
			->item( 0 );

		$this->assertEquals( 'Points: $40pts.', $node->textContent );
		$this->assertEquals( 0, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Test that an invalid points_type setting results in an error.
	 *
	 * @since 1.9.0
	 */
	public function test_invalid_points_type_setting() {

		$this->give_current_user_caps( 'edit_theme_options' );

		$this->assertWordPointsWidgetError(
			$this->get_widget_html( array( 'points_type' => '' ) )
		);

		$this->assertWordPointsWidgetError(
			$this->get_widget_html( array( 'points_type' => 'invalid' ) )
		);
	}

	/**
	 * Test the text setting.
	 *
	 * @since 1.9.0
	 */
	public function test_text_setting() {

		$xpath = $this->get_widget_xpath(
			array( 'points_type' => 'points', 'text' => 'Widget %points% text' )
		);

		$nodes = $xpath->query( '//div[@class = "wordpoints-points-widget-text"]' );

		$this->assertEquals( 'Widget $0pts. text', $nodes->item( 0 )->textContent );
	}

	/**
	 * Test the number_logs setting.
	 *
	 * @since 1.9.0
	 */
	public function test_number_logs_setting() {

		$this->factory->wordpoints_points_log->create_many(
			4
			, array( 'user_id' => get_current_user_id() )
		);

		$xpath = $this->get_widget_xpath(
			array( 'points_type' => 'points', 'number_logs' => 3 )
		);

		$this->assertEquals( 3, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Test the update() method.
	 *
	 * @since 1.9.0
	 */
	public function test_update_method() {

		$widget = new $this->widget_class;

		$sanitized = $widget->update(
			array(
				'title'       => '<p>Title</p>',
				'text'        => '  Some text. ',
				'alt_text'    => ' Alt text.   ',
				'number_logs' => '5dd',
				'points_type' => 'invalid',
			)
			, array()
		);

		$this->assertEquals( 'Title', $sanitized['title'] );
		$this->assertEquals( 'Some text.', $sanitized['text'] );
		$this->assertEquals( 'Alt text.', $sanitized['alt_text'] );
		$this->assertEquals( 0, $sanitized['number_logs'] );
		$this->assertEquals( 'points', $sanitized['points_type'] );
	}
}

// EOF
