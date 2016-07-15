<?php

/**
 * Testcase for WordPoints_Shortcode.
 *
 * @package WordPoints\Tests
 * @since 2.1.0
 */

/**
 * Test WordPoints_Shortcode.
 *
 * @since 2.1.0
 *
 * @group shortcodes
 *
 * @covers WordPoints_Shortcode
 */
class WordPoints_Shortcode_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test the constructor.
	 *
	 * @since 2.1.0
	 */
	public function test_construct() {

		$atts    = array( 'test' => 'value' );
		$content = 'Content';

		$shortcode = new WordPoints_PHPUnit_Mock_Shortcode( $atts, $content );

		$this->assertEquals( $atts, $shortcode->atts );
		$this->assertEquals( $content, $shortcode->content );
	}

	/**
	 * Test getting the shortcode name.
	 *
	 * @since 2.1.0
	 */
	public function test_get() {

		$shortcode = new WordPoints_PHPUnit_Mock_Shortcode( array(), '' );
		$shortcode->shortcode = 'test_shortcode';

		$this->assertEquals( $shortcode->shortcode, $shortcode->get() );
	}

	/**
	 * Test getting the shortcode name.
	 *
	 * @since 2.1.0
	 */
	public function test_expand_calls_filter() {

		$atts    = array( 'test' => 'value' );
		$pairs   = array( 'test' => 'default' );
		$content = 'Content';

		$shortcode = new WordPoints_PHPUnit_Mock_Shortcode( $atts, $content );
		$shortcode->shortcode = 'test_shortcode';
		$shortcode->pairs = $pairs;

		$filter = new WordPoints_Mock_Filter();
		add_filter(
			'wordpoints_user_supplied_shortcode_atts'
			, array( $filter, 'filter' )
			, 10
			, 6
		);

		$shortcode->expand();

		$this->assertEquals( 1, $filter->call_count );
		$this->assertEquals(
			array( $atts, $pairs, $atts, $shortcode->shortcode )
			, $filter->calls[0]
		);
	}
}

// EOF
