<?php

/**
 * A test case for the points rank type.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Test the points rank type.
 *
 * @since 1.7.0
 *
 * @group ranks
 * @group rank_types
 */
class WordPoints_Points_Rank_Type_Test extends WordPoints_Ranks_UnitTestCase {

	/**
	 * Set up before each test.
	 *
	 * @since 1.7.0
	 */
	public function setUp() {

		parent::setUp();

		$this->create_points_type();

		WordPoints_Rank_Groups::register_group(
			'points_type-points'
			, array( 'name' => 'Points' )
		);

		WordPoints_Rank_Types::register_type(
			'points-points'
			, 'WordPoints_Points_Rank_Type'
			, array( 'points_type' => 'points' )
		);

		WordPoints_Rank_Groups::register_type_for_group(
			'points-points'
			, 'points_type-points'
		);
	}

	/**
	 * Clean up after each test.
	 *
	 * @since 1.7.0
	 */
	public function tearDown() {

		WordPoints_Rank_Types::deregister_type( 'points-points' );
		WordPoints_Rank_Groups::deregister_group( 'points_type-points' );

		parent::tearDown();
	}

	/**
	 * Test a valid points type is required by this rank type.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_points_type() {

		$rank_type = WordPoints_Rank_Types::get_type( 'points-points' );

		$this->assertFalse(
			$rank_type->validate_rank_meta(
				array( 'points' => 10, 'points_type' => 'not' )
			)
		);
	}

	/**
	 * Test a valid number of points is required by this rank type.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_valid_points() {

		$rank_type = WordPoints_Rank_Types::get_type( 'points-points' );

		$this->assertWPError(
			$rank_type->validate_rank_meta(
				array( 'points' => 'NaN', 'points_type' => 'points' )
			)
		);
	}

	/**
	 * Test that the number of points must be above the minimum.
	 *
	 * @since 1.7.0
	 */
	public function test_requires_points_above_minimum() {

		$rank_type = WordPoints_Rank_Types::get_type( 'points-points' );

		$this->assertWPError(
			$rank_type->validate_rank_meta(
				array( 'points' => -45, 'points_type' => 'points' )
			)
		);
	}

	/**
	 * Test that the rank fires when points are awarded.
	 *
	 * @since 1.7.0
	 */
	public function test_transitions_when_points_awarded() {

		wordpoints_add_rank(
			'Rank 1'
			, 'points-points'
			, 'points_type-points'
			, 1
			, array( 'points_type' => 'points', 'points' => 30 )
		);

		$rank_id = wordpoints_add_rank(
			'Rank 2'
			, 'points-points'
			, 'points_type-points'
			, 2
			, array( 'points_type' => 'points', 'points' => 60 )
		);

		wordpoints_add_rank(
			'Rank 3'
			, 'points-points'
			, 'points_type-points'
			, 3
			, array( 'points_type' => 'points', 'points' => 90 )
		);

		$user_id = $this->factory->user->create();

		wordpoints_add_points( $user_id, 70, 'points', 'test' );

		$this->assertEquals(
			$rank_id
			, wordpoints_get_user_rank( $user_id, 'points_type-points' )
		);
	}

	/**
	 * Test that the rank fires when points are removed.
	 *
	 * @since 1.7.0
	 */
	public function test_transitions_when_points_removed() {

		$rank_id = wordpoints_add_rank(
			'Rank 1'
			, 'points-points'
			, 'points_type-points'
			, 1
			, array( 'points_type' => 'points', 'points' => 30 )
		);

		wordpoints_add_rank(
			'Rank 2'
			, 'points-points'
			, 'points_type-points'
			, 2
			, array( 'points_type' => 'points', 'points' => 60 )
		);

		$rank_3_id = wordpoints_add_rank(
			'Rank 3'
			, 'points-points'
			, 'points_type-points'
			, 3
			, array( 'points_type' => 'points', 'points' => 90 )
		);

		$user_id = $this->factory->user->create();

		wordpoints_add_points( $user_id, 100, 'points', 'test' );

		$this->assertEquals(
			$rank_3_id
			, wordpoints_get_user_rank( $user_id, 'points_type-points' )
		);

		wordpoints_set_points( $user_id, 40, 'points', 'test' );

		$this->assertEquals(
			$rank_id
			, wordpoints_get_user_rank( $user_id, 'points_type-points' )
		);
	}

	/**
	 * Test that ranks are refreshed when new ranks are added.
	 *
	 * @since 1.7.0
	 */
	public function test_transitions_when_rank_added() {

		$user_ids = $this->factory->user->create_many( 2 );

		wordpoints_set_points( $user_ids[0], 50, 'points', 'test' );

		$rank_id = wordpoints_add_rank(
			'Rank 1'
			, 'points-points'
			, 'points_type-points'
			, 1
			, array( 'points_type' => 'points', 'points' => 30 )
		);

		$this->assertEquals(
			$rank_id
			, wordpoints_get_user_rank( $user_ids[0], 'points_type-points' )
		);

		$this->assertEquals(
			WordPoints_Rank_Groups::get_group( 'points_type-points' )->get_rank( 0 )
			, wordpoints_get_user_rank( $user_ids[1], 'points_type-points' )
		);
	}

	/**
	 * Test that ranks are refreshed when a rank is updated.
	 *
	 * @since 1.7.0
	 */
	public function test_transitions_when_rank_updated() {

		$user_id = $this->factory->user->create();

		$rank_id = wordpoints_add_rank(
			'Rank 1'
			, 'points-points'
			, 'points_type-points'
			, 1
			, array( 'points_type' => 'points', 'points' => 30 )
		);

		wordpoints_set_points( $user_id, 50, 'points', 'test' );

		$this->assertEquals(
			$rank_id
			, wordpoints_get_user_rank( $user_id, 'points_type-points' )
		);

		wordpoints_update_rank(
			$rank_id
			, 'Rank 1'
			, 'points-points'
			, 'points_type-points'
			, 1
			, array( 'points_type' => 'points', 'points' => 60 )
		);

		$this->assertEquals(
			WordPoints_Rank_Groups::get_group( 'points_type-points' )->get_rank( 0 )
			, wordpoints_get_user_rank( $user_id, 'points_type-points' )
		);

		wordpoints_update_rank(
			$rank_id
			, 'Rank 1'
			, 'points-points'
			, 'points_type-points'
			, 1
			, array( 'points_type' => 'points', 'points' => 40 )
		);

		$this->assertEquals(
			$rank_id
			, wordpoints_get_user_rank( $user_id, 'points_type-points' )
		);
	}
}

// EOF
