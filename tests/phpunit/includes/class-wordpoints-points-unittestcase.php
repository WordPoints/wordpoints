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

		WordPoints_Points_Hooks::set_network_mode( false );

		$this->points_data = array(
			'name'   => 'Points',
			'prefix' => '$',
			'suffix' => 'pts.',
		);

		wordpoints_add_network_option( 'wordpoints_points_types', array( 'points' => $this->points_data ) );
	}

	/**
	 * Clean up after each test.
	 *
	 * @since 1.3.0
	 */
	public function tearDown() {

		WordPoints_Points_Hooks::set_network_mode( false );
	}

	/**
	 * Set the version of the points component.
	 *
	 * @since 1.4.0
	 *
	 * @param string $version The version to set. Defaults to 1.0.0.
	 */
	protected function set_points_db_version( $version = '1.0.0' ) {

		$wordpoints_data = wordpoints_get_network_option( 'wordpoints_data' );
		$wordpoints_data['components']['points']['version'] = $version;
		wordpoints_update_network_option( 'wordpoints_data', $wordpoints_data );
	}

	/**
	 * Get the version of the points component.
	 *
	 * @since 1.4.0
	 *
	 * @return string The version of the points component.
	 */
	protected function get_points_db_version() {

		$wordpoints_data = wordpoints_get_network_option( 'wordpoints_data' );

		return ( isset( $wordpoints_data['components']['points']['version'] ) )
			? $wordpoints_data['components']['points']['version']
			: '';
	}
}

// end of file /tests/class-wordpoints-points-unittestcase.php
