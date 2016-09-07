<?php

/**
 * Testcase for sanitization functions.
 *
 * @package WordPoints\Tests
 * @since 1.10.0
 */

/**
 * Tests for sanitization functions.
 *
 * @since 1.10.0
 */
class WordPoints_Sanitization_Functions_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test that the title is sanitized.
	 *
	 * @since 1.10.0
	 *
	 * @covers ::wordpoints_sanitize_wp_error
	 */
	public function test_wordpoints_sanitize_wp_error_sanitizes_title() {

		$error = new WP_Error( 'test', 'Testing', array( 'title' => '<script>alert("!");</script>' ) );
		$error = wordpoints_sanitize_wp_error( $error );
		$this->assertEquals( array( 'title' => 'alert("!");' ), $error->get_error_data() );
	}

	/**
	 * Test that it doesn't explode when there is no title.
	 *
	 * @since 1.10.0
	 *
	 * @covers ::wordpoints_sanitize_wp_error
	 */
	public function test_wordpoints_sanitize_wp_error_no_title() {

		$data = array( 'blah' => 'foo' );

		$error = new WP_Error( 'test', 'Testing', $data );
		$error = wordpoints_sanitize_wp_error( $error );

		$this->assertEquals( $data, $error->get_error_data() );
	}

	/**
	 * Test that it sanitizes the message.
	 *
	 * @since 1.10.0
	 *
	 * @covers ::wordpoints_sanitize_wp_error
	 */
	public function test_wordpoints_sanitize_wp_error_sanitizes_message() {

		$error = new WP_Error( 'test', '<script>alert("!");</script>' );
		$error = wordpoints_sanitize_wp_error( $error );
		$this->assertEquals( 'alert("!");', $error->get_error_message() );
	}

	/**
	 * Test that it sanitizes multiple messages if present.
	 *
	 * @since 1.10.0
	 *
	 * @covers ::wordpoints_sanitize_wp_error
	 */
	public function test_wordpoints_sanitize_wp_error_sanitizes_messages() {

		$error = new WP_Error( 'test', '<script>alert("!");</script>' );
		$error->add( 'test', '<a onclick="alert(window);">click</a>' );
		$error->add( 'foo', '<img src="l" onerror="alert(window);" />' );

		$error = wordpoints_sanitize_wp_error( $error );

		$this->assertEquals(
			array( 'alert("!");', '<a>click</a>', '' )
			, $error->get_error_messages()
		);
	}
}

// EOF
