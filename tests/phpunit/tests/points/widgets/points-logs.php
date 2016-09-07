<?php

/**
 * A test case for the Points Logs widget.
 *
 * @package WordPoints\Tests
 * @since 1.9.0
 */

/**
 * Test the Points Logs widget.
 *
 * @since 1.9.0
 *
 * @group widgets
 *
 * @covers WordPoints_Points_Logs_Widget
 */
class WordPoints_Points_Logs_Widget_Test extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * @since 1.9.0
	 */
	protected $widget_class = 'WordPoints_Points_Logs_Widget';

	/**
	 * Test that and invalid points_type setting results in an error.
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

		// When the user is logged out no error should be displayed.
		wp_set_current_user( 0 );

		$this->assertEmpty(
			$this->get_widget_html( array( 'points_type' => 'invalid' ) )
		);
	}

	/**
	 * Test the default behaviour of the widget.
	 *
	 * @since 1.9.0
	 */
	public function test_defaults() {

		$this->factory->wordpoints_points_log->create_many( 11 );

		$xpath = $this->get_widget_xpath( array( 'points_type' => 'points' ) );

		$this->assertEquals( 10, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Test the number_logs setting.
	 *
	 * @since 1.9.0
	 */
	public function test_number_logs_setting() {

		$this->factory->wordpoints_points_log->create_many( 4 );

		$xpath = $this->get_widget_xpath(
			array( 'points_type' => 'points', 'number_logs' => 2 )
		);

		$this->assertEquals( 2, $xpath->query( '//tbody/tr' )->length );
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
				'number_logs' => '5dd',
				'points_type' => 'invalid',
			)
			, array()
		);

		$this->assertEquals( 'Title', $sanitized['title'] );
		$this->assertEquals( 10, $sanitized['number_logs'] );
		$this->assertEquals( 'points', $sanitized['points_type'] );
	}
}

// EOF
