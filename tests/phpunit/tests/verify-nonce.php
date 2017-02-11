<?php

/**
 * A test case for wordpoints_verify_nonce().
 *
 * @package WordPoints\Tests
 * @since 1.9.0
 */

/**
 * Test wordpoints_verify_nonce().
 *
 * @since 1.9.0
 *
 * @covers ::wordpoints_verify_nonce
 */
class WordPoints_Verify_Nonce_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 1.9.0
	 */
	public function tearDown() {

		$_POST = array();
		$_GET = array();

		parent::tearDown();
	}

	/**
	 * Test it with a valid nonce.
	 *
	 * @since 1.9.0
	 */
	public function test_with_valid_nonce() {

		$_GET['nonce'] = wp_create_nonce( 'action' );

		$this->assertSame( 1, wordpoints_verify_nonce( 'nonce', 'action' ) );
	}

	/**
	 * Test that if the nonce key isn't set, false is returned.
	 *
	 * @since 1.9.0
	 */
	public function test_bad_nonce_key() {

		$this->assertFalse( wordpoints_verify_nonce( 'notthere', 'action' ) );
	}

	/**
	 * Test with an invalid nonce.
	 *
	 * @since 1.9.0
	 */
	public function test_invalid_nonce() {

		$_GET['nonce'] = wp_create_nonce( 'action' );

		$this->assertFalse( wordpoints_verify_nonce( 'nonce', 'invalid' ) );
	}

	/**
	 * Test with action format.
	 *
	 * @since 1.9.0
	 */
	public function test_action_format() {

		$_GET['nonce'] = wp_create_nonce( 'action-1_test' );
		$_GET['a_number'] = 1;
		$_GET['some_string'] = 'test';

		$is_valid = wordpoints_verify_nonce(
			'nonce'
			, 'action-%d_%s'
			, array( 'a_number', 'some_string' )
		);

		$this->assertSame( 1, $is_valid );
	}

	/**
	 * Test with action format with a string for $format_values.
	 *
	 * @since 2.0.0
	 */
	public function test_action_format_string_format_value() {

		$_GET['nonce'] = wp_create_nonce( 'action-test' );
		$_GET['some_string'] = 'test';

		$is_valid = wordpoints_verify_nonce(
			'nonce'
			, 'action-%s'
			, 'some_string'
		);

		$this->assertSame( 1, $is_valid );
	}

	/**
	 * Test supports post requests.
	 *
	 * @since 1.9.0
	 */
	public function test_post_request() {

		$_POST['nonce'] = wp_create_nonce( 'action' );

		$is_valid = wordpoints_verify_nonce( 'nonce', 'action', null, 'post' );

		$this->assertSame( 1, $is_valid );
	}

	/**
	 * Test that false is returned if any of the format values aren't set.
	 *
	 * @since 1.9.0
	 */
	public function test_unset_format_value() {

		$_GET['nonce'] = wp_create_nonce( 'action-1_test' );
		$_GET['a_number'] = 1;

		$is_valid = wordpoints_verify_nonce(
			'nonce'
			, 'action-%d_%s'
			, array( 'a_number', 'some_string' )
		);

		$this->assertFalse( $is_valid );
	}
}

// EOF
