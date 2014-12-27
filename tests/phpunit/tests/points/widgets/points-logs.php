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
 */
class WordPoints_Points_Logs_Widget_Test extends WordPoints_Points_UnitTestCase {

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

		wp_set_current_user( $this->factory->user->create() );

		$user = wp_get_current_user();
		$user->add_cap( 'edit_theme_options' );

		// https://core.trac.wordpress.org/ticket/28374
		$user->get_role_caps( 'edit_theme_options' );
		$user->update_user_level_from_caps( 'edit_theme_options' );

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
	}}

// EOF
