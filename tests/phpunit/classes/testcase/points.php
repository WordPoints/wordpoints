<?php

/**
 * Test case parent for the points tests.
 *
 * @package WordPoints\PHPUnit\Points
 * @since 2.2.0
 */

/**
 * Points unit test case.
 *
 * This test case creates the 'Points' points type on set up so that doesn't have to
 * be repeated in each of the tests.
 *
 * Since 1.0.0, this test case had been called WordPoints_Points_UnitTestCase.
 *
 * @since 2.2.0
 */
class WordPoints_PHPUnit_TestCase_Points extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 1.9.0 As part of WordPoints_Points_UnitTestCase.
	 * @since 2.2.0
	 */
	protected $wordpoints_component = 'points';

	/**
	 * Set up the points type.
	 *
	 * @since 1.0.0 As part of WordPoints_Points_UnitTestCase.
	 * @since 2.2.0
	 */
	public function setUp() {

		parent::setUp();

		WordPoints_Points_Hooks::set_network_mode( false );

		$this->create_points_type();
	}

	/**
	 * Clean up after each test.
	 *
	 * @since 1.3.0 As part of WordPoints_Points_UnitTestCase.
	 * @since 2.2.0
	 */
	public function tearDown() {

		WordPoints_Points_Hooks::set_network_mode( false );

		parent::tearDown();
	}

} // class WordPoints_PHPUnit_TestCase_Points

// EOF
