<?php

/**
 * A test case for the create rank Ajax action.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Test that the ranks screen Ajax create rank callback works correctly.
 *
 * @group ajax
 *
 * @since 1.7.0
 */
class WordPoints_Ranks_Screen_Create_Ajax_Test extends WordPoints_Ranks_Ajax_UnitTestCase {

	protected $ajax_action = 'wordpoints_admin_create_rank';

	/**
	 * Set up for each test.
	 *
	 * @since 1.7.0
	 */
	public function setUp() {

		parent::setUp();

		$this->_setRole( 'administrator' );

		$_POST = $this->rank_data = array(
			'nonce' => wp_create_nonce(
				"wordpoints_create_rank|{$this->rank_group}|{$this->rank_type}"
			),
			'group' => $this->rank_group,
			'type'  => $this->rank_type,
			'name'  => 'Tha Test',
			'order' => 1,
			'test_meta' => 'test',
		);
	}

	/**
	 * Test creating a rank as an administrator.
	 *
	 * @since 1.7.0
	 */
	public function test_as_administrator() {

		$response = $this->assertJSONSuccessResponse();

		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertInternalType( 'object', $response->data );

		$this->assertObjectHasAttribute( 'id', $response->data );

		$this->rank_data['id'] = $response->data->id;
		$this->rank_data['nonce'] = wp_create_nonce(
			"wordpoints_update_rank|{$this->rank_group}|{$response->data->id}"
		);
		$this->rank_data['delete_nonce'] = wp_create_nonce(
			"wordpoints_delete_rank|{$this->rank_group}|{$response->data->id}"
		);

		unset( $this->rank_data['group'] );

		$this->assertEquals( (object) $this->rank_data, $response->data );
	}

	/**
	 * Test creating a rank as a subscriber.
	 *
	 * @since 1.7.0
	 */
	public function test_as_subscriber() {

		$this->_setRole( 'subscriber' );

		$this->assertJSONErrorResponse();
	}

	/**
	 * Test that creating a rank requires a valid nonce.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_nonce() {

		$_POST['nonce'] = 'invalid';

		$this->assertJSONErrorResponse();
	}

	/**
	 * Test that creating a rank requires a valid group.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_group() {

		$_POST['group'] = 'invalid';

		$this->assertJSONErrorResponse();
	}

	/**
	 * Test that creating a rank requires a valid type.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_type() {

		$_POST['type'] = 'invalid';

		$this->assertJSONErrorResponse();
	}

	/**
	 * Test that creating a rank requires a valid name.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_name() {

		$_POST['name'] = '';

		$this->assertJSONErrorResponse();
	}

	/**
	 * Test that creating a rank requires a valid order.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_order() {

		$_POST['order'] = 'invalid';

		$this->assertJSONErrorResponse();
	}

	/**
	 * Test that creating a rank requires valid metadata.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_meta() {

		unset( $_POST['test_meta'] );

		$this->assertJSONErrorResponse();
	}
}

// EOF
