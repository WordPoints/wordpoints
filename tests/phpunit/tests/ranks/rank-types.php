<?php

/**
 * A test case for rank types.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Test the rank types API.
 *
 * @since 1.7.0
 *
 * @group ranks
 * @group rank_types
 */
class WordPoints_Rank_Types_Test extends WordPoints_Ranks_UnitTestCase {

	/**
	 * Clean up after each test.
	 *
	 * @since 1.7.0
	 */
	public function tearDown() {

		WordPoints_Rank_Types::deregister_type( __CLASS__ );

		parent::tearDown();
	}

	/**
	 * Test rank type registration.
	 *
	 * @since 1.7.0
	 */
	public function test_registration() {

		$result = WordPoints_Rank_Types::register_type(
			__CLASS__
			, 'WordPoints_Test_Rank_Type'
		);

		$this->assertTrue( $result );
		$this->assertTrue(
			WordPoints_Rank_Types::is_type_registered( __CLASS__ )
		);

		$rank_types = WordPoints_Rank_Types::get();
		$this->assertArrayHasKey( __CLASS__, $rank_types );

		$this->assertInstanceOf(
			'WordPoints_Test_Rank_Type'
			, $rank_types[ __CLASS__ ]
		);

		$this->assertInstanceOf(
			'WordPoints_Test_Rank_Type'
			, WordPoints_Rank_Types::get_type( __CLASS__ )
		);

	}

	/**
	 * Test that you can't register an already registered rank type.
	 *
	 * @since 1.7.0
	 */
	public function test_registering_registered_type() {

		$result = WordPoints_Rank_Types::register_type(
			'test_type'
			, 'WordPoints_Test_Rank_Type'
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test rank type deregistration.
	 *
	 * @since 1.7.0
	 */
	public function test_deregistration() {

		$this->assertTrue( WordPoints_Rank_Types::deregister_type( 'test_type' ) );
		$this->assertArrayNotHasKey( 'test_type', WordPoints_Rank_Types::get() );
		$this->assertFalse( WordPoints_Rank_Types::get_type( 'test_type' ) );
		$this->assertFalse(
			WordPoints_Rank_Types::is_type_registered( 'test_type' )
		);
	}

	/**
	 * Test that you can't deregister a rank type that isn't registered.
	 *
	 * @since 1.7.0
	 */
	public function test_deregistering_unregistered_rank() {

		$this->assertFalse(
			WordPoints_Rank_Types::deregister_type( 'not_registered' )
		);
	}
}

// EOF
