<?php

/**
 * A test case for the ranks API.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Test that the ranks API works.
 *
 * @since 1.7.0
 *
 * @group ranks
 */
class WordPoints_Ranks_Test extends WordPoints_Ranks_UnitTestCase {

	/**
	 * Set up before the tests.
	 *
	 * @since 1.7.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		WordPoints_Rank_Types::register_type(
			__CLASS__
			, 'WordPoints_Test_Rank_Type'
		);

		WordPoints_Rank_Groups::register_group(
			__CLASS__
			, array( 'name' => __CLASS__ )
		);

		WordPoints_Rank_Groups::register_type_for_group( __CLASS__, __CLASS__ );
	}

	/**
	 * Clean up after each test.
	 *
	 * @since 1.7.0
	 */
	public static function tearDownAfterClass() {

		parent::tearDownAfterClass();

		WordPoints_Rank_Groups::deregister_group( __CLASS__ );
		WordPoints_Rank_Types::deregister_type( __CLASS__ );
	}

	/**
	 * Test that a valid rank type must be passed to add a rank.
	 *
	 * @since 1.7.0
	 */
	public function test_add_requires_valid_type() {

		$rank = wordpoints_add_rank(
			'Test Rank'
			, 'not_a_type'
			, 'test_group'
			, 1
			, array( 'test_meta' => 'ranks' )
		);

		$this->assertFalse( $rank );
	}

	/**
	 * Test that a valid rank group must be passed to add a rank.
	 *
	 * @since 1.7.0
	 */
	public function test_add_requires_valid_group() {

		$rank = wordpoints_add_rank(
			'Test Rank'
			, 'test_type'
			, 'not_a_group'
			, 1
			, array( 'test_meta' => 'ranks' )
		);

		$this->assertFalse( $rank );
	}

	/**
	 * Test that valid metadata must be passed to add a rank.
	 *
	 * @since 1.7.0
	 */
	public function test_add_requires_valid_metadata() {

		$rank = wordpoints_add_rank(
			'Test Rank'
			, 'test_type'
			, 'test_group'
			, 1
			, array( 'not' => 'ranks' )
		);

		$this->assertFalse( $rank );
	}

	/**
	 * Test adding a rank.
	 *
	 * @since 1.7.0
	 */
	public function test_add_rank() {

		$rank = wordpoints_add_rank(
			'Test Rank'
			, 'test_type'
			, 'test_group'
			, 1
			, array( 'test_meta' => 'ranks' )
		);

		$this->assertInternalType( 'int', $rank );

		$this->assertEquals(
			'ranks'
			, wordpoints_get_rank_meta( $rank, 'test_meta', true )
		);
	}

	/**
	 * Test deleting a rank.
	 *
	 * @since 1.7.0
	 */
	public function test_delete_rank() {

		$rank_id = $this->factory->wordpoints_rank->create();

		$result = wordpoints_delete_rank( $rank_id );

		$this->assertTrue( $result );
		$this->assertEquals( array(), wordpoints_get_rank_meta( $rank_id ) );
	}
}

// EOF
