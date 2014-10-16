<?php

/**
 * A test case for rank groups.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Test the rank groups API.
 *
 * @since 1.7.0
 *
 * @group ranks
 * @group rank_groups
 */
class WordPoints_Rank_Groups_Test extends WordPoints_Ranks_UnitTestCase {

	/**
	 * Clean up after each test.
	 *
	 * @since 1.7.0
	 */
	public function tearDown() {

		WordPoints_Rank_Groups::deregister_group( __CLASS__ );
		WordPoints_Rank_Types::deregister_type( __CLASS__ );

		parent::tearDown();
	}

	/**
	 * Test rank group registration.
	 *
	 * @since 1.7.0
	 */
	public function test_registration() {

		$result = WordPoints_Rank_Groups::register_group(
			__CLASS__
			, array( 'name' => __CLASS__ )
		);

		$this->assertTrue( $result );
		$this->assertTrue(
			WordPoints_Rank_Groups::is_group_registered( __CLASS__ )
		);

		$groups = WordPoints_Rank_Groups::get();
		$this->assertArrayHasKey( __CLASS__, $groups );
		$this->assertInstanceOf( 'WordPoints_Rank_Group', $groups[ __CLASS__ ] );
		$this->assertInstanceOf(
			'WordPoints_Rank_Group'
			, WordPoints_Rank_Groups::get_group( __CLASS__ )
		);

		$this->assertEquals( __CLASS__, $groups[ __CLASS__ ]->get_slug() );
	}

	/**
	 * Test that you can't register an already registered rank group.
	 *
	 * @since 1.7.0
	 */
	public function test_registering_registered_group() {

		$this->assertFalse(
			WordPoints_Rank_Groups::register_group(
				'test_group'
				, array( 'name' => 'Test' )
			)
		);
	}

	/**
	 * Test rank group deregistration.
	 *
	 * @since 1.7.0
	 */
	public function test_deregistration() {

		$this->assertTrue( WordPoints_Rank_Groups::deregister_group( 'test_group' ) );
		$this->assertArrayNotHasKey( 'test_group', WordPoints_Rank_Groups::get() );
		$this->assertFalse( WordPoints_Rank_Groups::get_group( 'test_group' ) );
		$this->assertFalse(
			WordPoints_Rank_Groups::is_group_registered( 'test_group' )
		);
	}

	/**
	 * Test that you can't deregister a rank group that isn't registered.
	 *
	 * @since 1.7.0
	 */
	public function test_deregistering_unregistered_group() {

		$this->assertFalse(
			WordPoints_Rank_Groups::deregister_group( 'not_registered' )
		);
	}

	/**
	 * Test registering a rank type for a group.
	 *
	 * @since 1.7.0
	 */
	public function test_register_type_for_group() {

		WordPoints_Rank_Types::register_type(
			__CLASS__
			, 'WordPoints_Test_Rank_Type'
		);

		$result = WordPoints_Rank_Groups::register_type_for_group(
			__CLASS__
			, 'test_group'
		);

		$this->assertTrue( $result );

		$this->assertTrue(
			WordPoints_Rank_Groups::is_type_registered_for_group(
				__CLASS__
				, 'test_group'
			)
		);

		$group = WordPoints_Rank_Groups::get_group( 'test_group' );

		$this->assertArrayHasKey( __CLASS__, $group->get_types() );

		$this->assertTrue( $group->has_type( __CLASS__ ) );
	}

	/**
	 * Test deregistering a rank type for a group.
	 *
	 * @since 1.7.0
	 */
	public function test_deregister_type_for_group() {

		$result = WordPoints_Rank_Groups::deregister_type_for_group(
			'test_type'
			, 'test_group'
		);

		$this->assertTrue( $result );

		$this->assertFalse(
			WordPoints_Rank_Groups::is_type_registered_for_group(
				'test_type'
				, 'test_group'
			)
		);

		$group = WordPoints_Rank_Groups::get_group( 'test_group' );

		$this->assertArrayNotHasKey( 'test_type', $group->get_types() );

		$this->assertFalse( $group->has_type( 'test_type' ) );
	}

	/**
	 * Test that a rank type is removed from a group when it is deregistered.
	 *
	 * @since 1.7.0
	 */
	public function test_rank_type_removed_from_group_when_deregistered() {

		WordPoints_Rank_Types::deregister_type( 'test_type' );

		$this->assertFalse(
			WordPoints_Rank_Groups::is_type_registered_for_group(
				'test_type'
				, 'test_group'
			)
		);

		$group = WordPoints_Rank_Groups::get_group( 'test_group' );

		$this->assertArrayNotHasKey( 'test_type', $group->get_types() );

		$this->assertFalse( $group->has_type( 'test_type' ) );
	}
}

// EOF
