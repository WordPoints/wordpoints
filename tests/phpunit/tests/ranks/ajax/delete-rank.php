<?php

/**
 * A test case for the delete rank Ajax action.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Test that the ranks screen Ajax delete rank callback works correctly.
 *
 * @group ajax
 *
 * @since 1.7.0
 */
class WordPoints_Ranks_Screen_Delete_Ajax_Test extends WordPoints_Ranks_Ajax_UnitTestCase {

	protected $ajax_action = 'wordpoints_admin_delete_rank';

	/**
	 * Set up for each test.
	 *
	 * @since 1.7.0
	 */
	public function setUp() {

		parent::setUp();

		$this->_setRole( 'administrator' );

		$this->rank_id = wordpoints_add_rank(
			'Test Name'
			, $this->rank_type
			, $this->rank_group
			, 1
			, array( 'test_meta' => 'ss' )
		);

		$_POST = array(
			'id' => $this->rank_id,
			'nonce' => wp_create_nonce(
				"wordpoints_delete_rank|{$this->rank_group}|{$this->rank_id}"
			),
			'group' => $this->rank_group,
		);
	}

	/**
	 * Test deleting a rank as an administrator.
	 *
	 * @since 1.7.0
	 */
	public function test_as_administrator() {

		$response = $this->assertJSONSuccessResponse();

		$this->assertFalse( wordpoints_get_rank( $this->rank_id ) );
	}

	/**
	 * Test deleting a rank as a subscriber.
	 *
	 * @since 1.7.0
	 */
	public function test_as_subscriber() {

		$this->_setRole( 'subscriber' );

		$this->assertJSONErrorResponse();
	}

	/**
	 * Test that deleting a rank requires a valid nonce.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_nonce() {

		$_POST['nonce'] = 'invalid';

		$this->assertJSONErrorResponse();
	}

	/**
	 * Test that deleting a rank requires a valid group.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_group() {

		$_POST['group'] = 'invalid';

		$this->assertJSONErrorResponse();
	}

	/**
	 * Test that deleting a rank requires a valid rank ID.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_id() {

		$_POST['id'] = 0;

		$this->assertJSONErrorResponse();
	}
}

// EOF
