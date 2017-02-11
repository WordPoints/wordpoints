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

		$this->assertSame( $atts, $shortcode->atts );
		$this->assertSame( $content, $shortcode->content );
	}

	/**
	 * Test the constructor.
	 *
	 * @since 2.2.0
	 */
	public function test_construct_with_shortcode_name() {

		$atts    = array( 'test' => 'value' );
		$content = 'Content';
		$name    = 'testing';

		$shortcode = new WordPoints_PHPUnit_Mock_Shortcode( $atts, $content, $name );

		$this->assertSame( $atts, $shortcode->atts );
		$this->assertSame( $content, $shortcode->content );
		$this->assertSame( $name, $shortcode->shortcode );
	}

	/**
	 * Test getting the shortcode name.
	 *
	 * @since 2.1.0
	 */
	public function test_get() {

		$shortcode = new WordPoints_PHPUnit_Mock_Shortcode( array(), '' );
		$shortcode->shortcode = 'test_shortcode';

		$this->assertSame( $shortcode->shortcode, $shortcode->get() );
	}

	/**
	 * Test that the attributes are filtered in expand().
	 *
	 * @since 2.1.0
	 */
	public function test_expand_calls_filter() {

		$atts    = array( 'test' => 'value' );
		$pairs   = array( 'test' => 'default' );
		$content = 'Content';

		$shortcode = new WordPoints_PHPUnit_Mock_Shortcode( $atts, $content );
		$shortcode->pairs = $pairs;

		$filtered_atts = array( 'test' => 'filtered' );
		$filter        = new WordPoints_PHPUnit_Mock_Filter( $filtered_atts );
		add_filter(
			'wordpoints_user_supplied_shortcode_atts'
			, array( $filter, 'filter' )
			, 10
			, 6
		);

		$shortcode->expand();

		$this->assertSame( 1, $filter->call_count );
		$this->assertSame(
			array( $atts, $pairs, $atts, $shortcode->shortcode )
			, $filter->calls[0]
		);

		$this->assertSame( $filtered_atts, $shortcode->atts );
		$this->assertSame( $pairs, $shortcode->pairs );
	}

	/**
	 * Test that the user ID attribute.
	 *
	 * @since 2.1.0
	 */
	public function test_user_id_attr() {

		$user_id = $this->factory->user->create();

		$atts    = array( 'user_id' => $user_id );
		$pairs   = array( 'user_id' => '' );
		$content = 'Content';

		$shortcode = new WordPoints_PHPUnit_Mock_Shortcode( $atts, $content );
		$shortcode->pairs = $pairs;

		$shortcode->expand();

		$this->assertSame( $user_id, $shortcode->atts['user_id'] );
	}

	/**
	 * Test that the user ID attribute defaults to the current user.
	 *
	 * @since 2.1.0
	 */
	public function test_user_id_attr_current_user_by_default() {

		$user_id = $this->factory->user->create();

		wp_set_current_user( $user_id );

		$atts    = array();
		$pairs   = array( 'user_id' => '' );
		$content = 'Content';

		$shortcode = new WordPoints_PHPUnit_Mock_Shortcode( $atts, $content );
		$shortcode->pairs = $pairs;

		$shortcode->expand();

		$this->assertSame( $user_id, $shortcode->atts['user_id'] );
	}

	/**
	 * Test that the user ID attribute defaults to the current user.
	 *
	 * @since 2.1.0
	 */
	public function test_user_id_attr_current_user_if_empty() {

		$user_id = $this->factory->user->create();

		wp_set_current_user( $user_id );

		$atts    = array( 'user_id' => '' );
		$pairs   = array( 'user_id' => '' );
		$content = 'Content';

		$shortcode = new WordPoints_PHPUnit_Mock_Shortcode( $atts, $content );
		$shortcode->pairs = $pairs;

		$shortcode->expand();

		$this->assertSame( $user_id, $shortcode->atts['user_id'] );
	}

	/**
	 * Test that the user ID attribute defaults to the current user.
	 *
	 * @since 2.1.0
	 */
	public function test_user_id_attr_only_supported_if_in_pairs() {

		$user_id = $this->factory->user->create();

		wp_set_current_user( $user_id );

		$atts    = array( 'user_id' => '' );
		$pairs   = array( 'test' => 'default' );
		$content = 'Content';

		$shortcode = new WordPoints_PHPUnit_Mock_Shortcode( $atts, $content );
		$shortcode->pairs = $pairs;

		$shortcode->expand();

		$this->assertSame( $pairs, $shortcode->atts );
	}

	/**
	 * Test that the user ID attribute defaults to the current user.
	 *
	 * @since 2.1.0
	 */
	public function test_user_id_attr_supports_post_author() {

		global $post;

		$user_id = $this->factory->user->create();
		$post = $this->factory->post->create_and_get(
			array( 'post_author' => $user_id )
		);

		$atts    = array( 'user_id' => 'post_author' );
		$pairs   = array( 'user_id' => '' );
		$content = 'Content';

		$shortcode = new WordPoints_PHPUnit_Mock_Shortcode( $atts, $content );
		$shortcode->pairs = $pairs;

		$shortcode->expand();

		$this->assertSame( (string) $user_id, $shortcode->atts['user_id'] );
	}

	/**
	 * Test the post_author value for the user_id attribute with no current post.
	 *
	 * @since 2.1.0
	 */
	public function test_post_author_user_id_no_post() {

		unset( $GLOBALS['post'] );

		$atts    = array( 'user_id' => 'post_author' );
		$pairs   = array( 'user_id' => '' );
		$content = 'Content';

		$shortcode = new WordPoints_PHPUnit_Mock_Shortcode( $atts, $content );
		$shortcode->pairs = $pairs;

		$result = $shortcode->expand();

		$this->assertSame( '', $result );
		$this->assertSame( $atts, $shortcode->atts );
	}

	/**
	 * Test the post_author value for user_id with no current post as an admin.
	 *
	 * @since 2.1.0
	 */
	public function test_post_author_user_id_no_post_admin() {

		unset( $GLOBALS['post'] );

		$this->give_current_user_caps( 'manage_options' );

		$atts    = array( 'user_id' => 'post_author' );
		$pairs   = array( 'user_id' => '' );
		$content = 'Content';

		$shortcode = new WordPoints_PHPUnit_Mock_Shortcode( $atts, $content );
		$shortcode->pairs = $pairs;

		$result = $shortcode->expand();

		$this->assertWordPointsShortcodeError( $result );
		$this->assertSame( $atts, $shortcode->atts );
	}

	/**
	 * Test using an unrecognized value for the user_id attribute.
	 *
	 * @since 2.1.0
	 */
	public function test_user_id_attr_unrecognized() {

		$atts    = array( 'user_id' => 'test' );
		$pairs   = array( 'user_id' => '' );
		$content = 'Content';

		$shortcode = new WordPoints_PHPUnit_Mock_Shortcode( $atts, $content );
		$shortcode->pairs = $pairs;

		$result = $shortcode->expand();

		$this->assertSame( '', $result );
		$this->assertSame( array( 'user_id' => false ), $shortcode->atts );
	}

	/**
	 * Test using an unrecognized value for the user_id attribute.
	 *
	 * @since 2.1.0
	 */
	public function test_user_id_attr_unrecognized_admin() {

		$this->give_current_user_caps( 'manage_options' );

		$atts    = array( 'user_id' => 'test' );
		$pairs   = array( 'user_id' => '' );
		$content = 'Content';

		$shortcode = new WordPoints_PHPUnit_Mock_Shortcode( $atts, $content );
		$shortcode->pairs = $pairs;

		$result = $shortcode->expand();

		$this->assertWordPointsShortcodeError( $result );
		$this->assertSame( array( 'user_id' => false ), $shortcode->atts );
	}
}

// EOF
