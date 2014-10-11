<?php

/**
 * A test case for the WordPoints_Rank_Group class.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Test that the WordPoints_Rank_Group class functions properly.
 *
 * @since 1.7.0
 *
 * @group ranks
 * @group rank_groups
 */
class WordPoints_Rank_Group_Test extends WordPoints_Ranks_UnitTestCase {

	/**
	 * Clean up after each test.
	 *
	 * @since 1.7.0
	 */
	public function tearDown() {

		WordPoints_Rank_Types::deregister_type( __CLASS__ );
		WordPoints_Rank_Groups::deregister_group( __CLASS__ );

		parent::tearDown();
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

		$group = WordPoints_Rank_Groups::get_group( 'test_group' );

		$this->assertTrue( $group->add_type( __CLASS__ ) );

		$this->assertTrue(
			WordPoints_Rank_Groups::is_type_registered_for_group(
				__CLASS__
				, 'test_group'
			)
		);

		$this->assertArrayHasKey( __CLASS__, $group->get_types() );

		$this->assertTrue( $group->has_type( __CLASS__ ) );

		// Test that you can't add an already added type.
		$this->assertFalse( $group->add_type( __CLASS__ ) );
	}

	/**
	 * Test deregistering a rank type for a group.
	 *
	 * @since 1.7.0
	 */
	public function test_deregister_type_for_group() {

		$group = WordPoints_Rank_Groups::get_group( 'test_group' );

		$this->assertTrue( $group->remove_type( 'test_type' ) );

		$this->assertFalse(
			WordPoints_Rank_Groups::is_type_registered_for_group(
				'test_type'
				, 'test_group'
			)
		);

		$this->assertArrayNotHasKey( 'test_type', $group->get_types() );

		$this->assertFalse( $group->has_type( 'test_type' ) );

		// Test that a unregistered group can't be deregistered.
		$this->assertFalse( $group->remove_type( 'test_type' ) );
	}

	/**
	 * Test adding a rank to a group.
	 *
	 * @since 1.7.0
	 */
	public function test_add_rank() {

		$group = WordPoints_Rank_Groups::get_group( 'test_group' );

		$rank_id = $this->factory->wordpoints_rank->create();

		$this->assertEquals( $rank_id, $group->get_rank( 1 ) );
		$this->assertEquals( 1, $group->get_rank_position( $rank_id ) );
	}

	/**
	 * Test that you can't add the same rank to a group twice.
	 *
	 * @since 1.7.0
	 */
	public function test_no_duplicate_ranks() {

		$rank_id = $this->factory->wordpoints_rank->create();
		$group = WordPoints_Rank_Groups::get_group( 'test_group' );

		$group->add_rank( $rank_id, 1 );

		$this->assertFalse( $group->add_rank( $rank_id, 2 ) );
	}

	/**
	 * Test adding a rank in the middle.
	 *
	 * @since 1.7.0
	 */
	public function test_adding_rank() {

		$group = WordPoints_Rank_Groups::get_group( 'test_group' );

		$rank_1 = $this->factory->wordpoints_rank->create( array( 'position' => 1 ) );
		$rank_2 = $this->factory->wordpoints_rank->create( array( 'position' => 2 ) );
		$rank_3 = $this->factory->wordpoints_rank->create( array( 'position' => 2 ) );

		$this->assertEquals( $rank_1, $group->get_rank( 1 ) );
		$this->assertEquals( $rank_2, $group->get_rank( 3 ) );
		$this->assertEquals( $rank_3, $group->get_rank( 2 ) );
	}

	/**
	 * Test that a rank added after the end is just added on the end.
	 *
	 * @since 1.7.0
	 */
	public function test_adding_rank_after_end() {

		$group = WordPoints_Rank_Groups::get_group( 'test_group' );

		$rank_id = $this->factory->wordpoints_rank->create(
			array( 'position' => 5 )
		);

		$this->assertEquals( $rank_id, $group->get_rank( 1 ) );
		$this->assertEquals( 1, $group->get_rank_position( $rank_id ) );
	}

	/**
	 * Test moving a rank.
	 *
	 * @since 1.7.0
	 */
	public function test_moving_rank() {

		$group = WordPoints_Rank_Groups::get_group( 'test_group' );

		$rank_1 = $this->factory->wordpoints_rank->create( array( 'position' => 1 ) );
		$rank_2 = $this->factory->wordpoints_rank->create( array( 'position' => 2 ) );

		$result = $group->move_rank( $rank_1, 2 );

		$this->assertTrue( $result );
		$this->assertEquals( 2, $group->get_rank_position( $rank_1 ) );
		$this->assertEquals( 1, $group->get_rank_position( $rank_2 ) );
	}

	/**
	 * Test removing a rank from a group.
	 *
	 * @since 1.7.0
	 */
	public function test_removing_rank() {

		$rank_id = $this->factory->wordpoints_rank->create();

		$group = WordPoints_Rank_Groups::get_group( 'test_group' );

		$result = $group->remove_rank( $rank_id );

		$this->assertTrue( $result );

		$this->assertEquals( false, $group->get_rank( 1 ) );
		$this->assertEquals( false, $group->get_rank_position( $rank_id ) );
	}

	/**
	 * Test removing a rank that isn't there.
	 *
	 * @since 1.7.0
	 */
	public function test_removing_nonexisting_rank() {

		WordPoints_Rank_Groups::register_group(
			__CLASS__
			, array( 'name' => __CLASS__ )
		);
		WordPoints_Rank_Groups::register_type_for_group( 'test_type', __CLASS__ );

		$rank_id = $this->factory->wordpoints_rank->create(
			array( 'group' => __CLASS__ )
		);

		$group = WordPoints_Rank_Groups::get_group( 'test_group' );

		$this->assertFalse( $group->remove_rank( $rank_id ) );
	}

	/**
	 * Test saving a list of ranks.
	 *
	 * @since 1.7.0
	 */
	public function test_save_ranks() {

		$rank_ids = $this->factory->wordpoints_rank->create_many( 3 );
		$group = WordPoints_Rank_Groups::get_group( 'test_group' );

		$result = $group->save_ranks( $rank_ids );

		$this->assertTrue( $result );
		$this->assertEquals( $rank_ids, $group->get_ranks() );
	}

	/**
	 * Test saving a list of ranks with muxed-up key values.
	 *
	 * @since 1.7.0
	 */
	public function test_save_ranks_with_missing_ranks() {

		$rank_ids = $this->factory->wordpoints_rank->create_many( 3 );
		$group = WordPoints_Rank_Groups::get_group( 'test_group' );

		unset( $rank_ids[1] );

		$result = $group->save_ranks( $rank_ids );

		$this->assertTrue( $result );
		$this->assertEquals( array_values( $rank_ids ), $group->get_ranks() );
	}

	/**
	 * Test that save_ranks() removes duplicates.
	 *
	 * @since 1.7.0
	 */
	public function test_save_ranks_with_duplicates() {

		$rank_ids = $this->factory->wordpoints_rank->create_many( 2 );
		$group = WordPoints_Rank_Groups::get_group( 'test_group' );

		$rank_ids[] = $rank_ids[0];

		$this->assertFalse( $group->save_ranks( $rank_ids ) );
	}
}

// EOF
