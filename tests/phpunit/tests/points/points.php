<?php

/**
 * Test the points component.
 *
 * @package WordPoints\Tests\Points
 * @since 1.0.0
 */

/**
 * Points test case.
 *
 * @since 1.0.0
 *
 * @group points
 */
class WordPoints_Points_Test extends WordPoints_Points_UnitTestCase {

	//
	// wordpoints_get_points()
	//

	/**
	 * The ID of a user that may be used in the tests.
	 *
	 * @since 1.0.0
	 *
	 * @type int $user_id
	 */
	private $user_id;

	/**
	 * Set up user.
	 *
	 * @since 1.0.0
	 */
	public function setUp() {

		parent::setUp();

		$this->user_id = $this->factory->user->create();
	}

	/**
	 * Test behavior with nonexistant points type.
	 *
	 * @since 1.0.0
	 */
	public function test_false_if_nonexistant_points_type() {

		$this->assertFalse( wordpoints_get_points( $this->user_id, 'idontexist' ) );
	}

	/**
	 * Test behavior for an invalid $user_id.
	 *
	 * @since 1.0.0
	 */
	public function test_false_if_invalid_user_id() {

		$this->assertFalse( wordpoints_get_points( 0, 'points' ) );
	}

	/**
	 * Test behavior with no points awarded yet.
	 *
	 * @since 1.0.0
	 */
	public function test_zero_if_no_points() {

		$this->assertEquals( 0, wordpoints_get_points( $this->user_id, 'points' ) );
	}

	/**
	 * Test behavior with points awarded.
	 *
	 * @since 1.0.0
	 */
	public function test_returns_points() {

		update_user_meta( $this->user_id, 'wordpoints_points-points', 23 );

		$this->assertEquals( 23, wordpoints_get_points( $this->user_id, 'points' ) );
	}

	//
	// wordpoints_get_points_minimum()
	//

	/**
	 * Test that the default is 0.
	 *
	 * @since 1.0.0
	 */
	public function test_default_minimum_is_0() {

		$this->assertEquals( 0, wordpoints_get_points_minimum( 'points' ) );
		$this->assertEquals( 0, wordpoints_get_points_above_minimum( $this->factory->user->create(), 'points' ) );
	}

	/**
	 * Test that the 'wordpoint_points_minimum' filter is working.
	 *
	 * @since 1.0.0
	 */
	public function test_wordpoints_points_minimum_filter() {

		add_filter( 'wordpoints_points_minimum', array( $this, 'minimum_filter' ) );

		$this->assertEquals( -50, wordpoints_get_points_minimum( 'points' ) );
		$this->assertEquals( 50, wordpoints_get_points_above_minimum( $this->factory->user->create(), 'points' ) );

		remove_filter( 'wordpoints_points_minimum', array( $this, 'minimum_filter' ) );
	}

	/**
	 * Sample filter.
	 *
	 * @since 1.0.0
	 */
	public function minimum_filter() {

		return -50;
	}

	//
	// wordpoints_format_points()
	//

	/**
	 * Test that the result is unaltered by defualt.
	 *
	 * @since 1.0.0
	 */
	public function test_default_format() {

		$this->assertEquals( '$5pts.', wordpoints_format_points( 5, 'points', 'testing' ) );
	}

	/**
	 * Test that the 'wordpoints_points_display' filter is called.
	 *
	 * @since 1.0.0
	 */
	public function test_format_filter() {

		add_filter( 'wordpoints_format_points', array( $this, 'format_filter' ), 10, 3 );

		$this->assertEquals( '5points', wordpoints_format_points( 5, 'points', 'testing' ) );

		remove_filter( 'wordpoints_format_points', array( $this, 'format_filter' ), 10, 3 );
	}

	/**
	 * Sample flilter.
	 *
	 * @since 1.0.0
	 */
	public function format_filter( $formatted, $points, $type ) {

		return $points . $type;
	}

	//
	// wordpoints_alter_points()
	//

	/**
	 * Test set, alter, add and subtract.
	 *
	 * @since 1.0.0
	 */
	public function test_points_altering() {

		wordpoints_set_points( $this->user_id, 50, 'points', 'test' );
		$this->assertEquals( 50, wordpoints_get_points( $this->user_id, 'points' ) );

		wordpoints_subtract_points( $this->user_id, 5, 'points', 'test' );
		$this->assertEquals( 45, wordpoints_get_points( $this->user_id, 'points' ) );

		wordpoints_add_points( $this->user_id, 10, 'points', 'test' );
		$this->assertEquals( 55, wordpoints_get_points( $this->user_id, 'points' ) );

		wordpoints_alter_points( $this->user_id, 5, 'points', 'test' );
		$this->assertEquals( 60, wordpoints_get_points( $this->user_id, 'points' ) );

		wordpoints_alter_points( $this->user_id, -10, 'points', 'test' );
		$this->assertEquals( 50, wordpoints_get_points( $this->user_id, 'points' ) );

		wordpoints_alter_points( $this->user_id, -60, 'points', 'test' );
		$this->assertEquals( 0, wordpoints_get_points( $this->user_id, 'points' ) );
	}

	//
	// wordpoints_add_points()
	//

	/**
	 * Test add() won't subtract.
	 *
	 * @since 1.0.0
	 */
	public function test_add_wont_subtract() {

		$this->assertFalse( wordpoints_add_points( $this->user_id, -5, 'points', 'test' ) );
	}

	//
	// wordpoints_subtract_points()
	//

	/**
	 * Test that subtract() won't add.
	 *
	 * @since 1.0.0
	 */
	public function test_subtract_wont_add() {

		$this->assertFalse( wordpoints_subtract_points( $this->user_id, -5, 'points', 'test' ) );
	}
}

// end of file /tests/test-points.php
