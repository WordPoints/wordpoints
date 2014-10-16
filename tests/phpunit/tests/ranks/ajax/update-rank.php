<?php

/**
 * A test case for the update rank Ajax action.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Test that the ranks screen Ajax update rank callback works correctly.
 *
 * @group ajax
 *
 * @since 1.7.0
 */
class WordPoints_Ranks_Screen_Update_Ajax_Test extends WordPoints_Ranks_Ajax_UnitTestCase {

	protected $ajax_action = 'wordpoints_admin_update_rank';

	/**
	 * Set up for each test.
	 *
	 * @since 1.7.0
	 */
	public function setUp() {

		parent::setUp();

		$this->_setRole( 'administrator' );

		$rank_id = wordpoints_add_rank(
			'Test Name'
			, $this->rank_type
			, $this->rank_group
			, 1
			, array( 'test_meta' => 'ss' )
		);

		$_POST = $this->rank_data = array(
			'id'    => $rank_id,
			'nonce' => wp_create_nonce(
				"wordpoints_update_rank|{$this->rank_group}|{$rank_id}"
			),
			'group' => $this->rank_group,
			'type'  => $this->rank_type,
			'name'  => 'Tha Test',
			'order' => 1,
			'test_meta' => 'test',
		);
	}

	/**
	 * Test updating a rank as an administrator.
	 *
	 * @since 1.7.0
	 */
	public function test_as_administrator() {

		$response = $this->assertJSONSuccessResponse();

		$rank = wordpoints_get_rank( $this->rank_data['id'] );

		$this->assertEquals( $this->rank_data['id'], $rank->ID );
		$this->assertEquals( $this->rank_data['name'], $rank->name );
		$this->assertEquals( $this->rank_data['test_meta'], $rank->test_meta );
	}

	/**
	 * Test updating a rank as a subscriber.
	 *
	 * @since 1.7.0
	 */
	public function test_as_subscriber() {

		$this->_setRole( 'subscriber' );

		$this->assertJSONErrorResponse();
	}

	/**
	 * Test that updating a rank requires a valid nonce.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_nonce() {

		$_POST['nonce'] = 'invalid';

		$this->assertJSONErrorResponse();
	}

	/**
	 * Test that updating a rank requires a valid group.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_group() {

		$_POST['group'] = 'invalid';

		$this->assertJSONErrorResponse();
	}

	/**
	 * Test that updating a rank requires a valid ID.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_id() {

		$_POST['id'] = 0;

		$this->assertJSONErrorResponse();
	}

	/**
	 * Test that updating a rank requires a valid type.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_type() {

		$_POST['type'] = 'invalid';

		$this->assertJSONErrorResponse();
	}

	/**
	 * Test that updating a rank requires a valid name.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_name() {

		$_POST['name'] = '';

		$this->assertJSONErrorResponse();
	}

	/**
	 * Test that updating a rank requires a valid order.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_order() {

		$_POST['order'] = 'invalid';

		$this->assertJSONErrorResponse();
	}
}

// EOF
