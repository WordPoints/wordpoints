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
 */
class WordPoints_Points_UnitTestCase extends WP_UnitTestCase {

	/**
	 * The default points data set up for each test.
	 *
	 * @since 1.0.0
	 *
	 * @type array $points_data
	 */
	protected $points_data;

	/**
	 * Set up the points type.
	 *
	 * @since 1.0.0
	 */
	public function setUp() {

		parent::setUp();

		$this->points_data = array(
			'name'   => 'Points',
			'prefix' => '$',
			'suffix' => 'pts.',
		);

		add_option( 'wordpoints_points_types', array( 'points' => $this->points_data ) );

		WordPoints_Points_Types::_reset();
	}
}

// end of file /tests/class-wordpoints-points-unittestcase.php
