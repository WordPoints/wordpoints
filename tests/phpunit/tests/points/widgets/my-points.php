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
 */
class WordPoints_My_Points_Widget_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * The class name of the widget type that this test is for.
	 *
	 * @since 1.9.0
	 *
	 * @type string $widget_class
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
	 * Get the HTML for a widget instance.
	 *
	 * @since 1.9.0
	 *
	 * @param array $instance The settings for the widget instance.
	 *
	 * @return string The HTML for this widget instance.
	 */
	protected function get_widget_html( array $instance = array(), array $args = array() ) {

		ob_start();
		the_widget( $this->widget_class, $instance, $args );
		return ob_get_clean();
	}

	/**
	 * Get the XPath query for a widget instance.
	 *
	 * @since 1.9.0
	 *
	 * @param array $instance The settings for the widget instance.
	 *
	 * @return
	 */
	protected function get_widget_xpath( array $instance = array() ) {

		$widget = $this->get_widget_html( $instance );

		$document = new DOMDocument;
		$document->loadHTML( $widget );
		$xpath    = new DOMXPath( $document );

		return $xpath;
	}

	/**
	 * Test behavior with the user not logged in and there is no alt text.
	 *
	 * @since 1.9.0
	 */
	public function test_user_not_logged_in_no_alt_text() {

		wp_set_current_user( 0 );

		$html = $this->get_widget_html( array( 'alt_text' => '' ) );

		$this->assertEmpty( $html );
	}

	/**
	 * Test that the alt text is dispalyed when the user is not loggged in.
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
	 * Test that and invalid points_type setting results in an error.
	 *
	 * @since 1.9.0
	 */
	public function test_invalid_points_type_setting() {

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
	 * Test thep number_logs setting.
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
}

// EOF
