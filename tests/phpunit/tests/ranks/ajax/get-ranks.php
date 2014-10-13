<?php

/**
 * A test case for the get ranks Ajax action.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Test that the ranks screen Ajax get ranks callback works correctly.
 *
 * @group ajax
 *
 * @since 1.7.0
 */
class WordPoints_Ranks_Screen_Get_Ajax_Test extends WordPoints_Ranks_Ajax_UnitTestCase {

	protected $ajax_action = 'wordpoints_admin_get_ranks';

	/**
	 * Set up for each test.
	 *
	 * @since 1.7.0
	 */
	public function setUp() {

		parent::setUp();

		$this->_setRole( 'administrator' );

		$_POST['nonce'] = wp_create_nonce( "wordpoints_get_ranks-{$this->rank_group}" );
		$_POST['group'] = $this->rank_group;
	}

	/**
	 * Test getting ranks succeeds for an administrator.
	 *
	 * @since 1.7.0
	 */
	public function test_get_as_admin() {

		$response = $this->assertJSONSuccessResponse();

		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertInternalType( 'array', $response->data );

		$this->assertCount( 1, $response->data );
	}

	/**
	 * Test that getting ranks fails for a subscriber.
	 *
	 * @since 1.7.0
	 */
	public function test_as_subscriber() {

		$this->_setRole( 'subscriber' );

		$this->assertJSONErrorResponse();
	}

	/**
	 * Test that getting ranks requires a valid nonce.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_nonce() {

		$_POST['nonce'] = 'invalid';

		$response = $this->assertJSONErrorResponse();
	}

	/**
	 * Test that getting ranks requires a valid group.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_group() {

		$_POST['group'] = 'invalid';

		$response = $this->assertJSONErrorResponse();
	}
}

// EOF
