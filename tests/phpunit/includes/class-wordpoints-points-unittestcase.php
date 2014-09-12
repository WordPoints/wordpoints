<?php

/**
 * Test case parent for the points tests.
 *
 * @package WordPoints\Tests\Points
 * @since 1.0.0
 */

/**
 * Points unit test case.
 *
 * This test case creates the 'Points' points type on set up so that doesn't have to
 * be repeated in each of the tests.
 *
 * @since 1.0.0
 * @since 1.7.0 Now extends WordPoints_UnitTestCase, not WP_UnitTestCase directly.
 */
class WordPoints_Points_UnitTestCase extends WordPoints_UnitTestCase {

	/**
	 * Set up the points type.
	 *
	 * @since 1.0.0
	 */
	public function setUp() {

		parent::setUp();

		WordPoints_Points_Hooks::set_network_mode( false );

		$this->create_points_type();
	}

	/**
	 * Clean up after each test.
	 *
	 * @since 1.3.0
	 */
	public function tearDown() {

		WordPoints_Points_Hooks::set_network_mode( false );

		parent::tearDown();
	}

} // class WordPoints_Points_UnitTestCase

// EOF
