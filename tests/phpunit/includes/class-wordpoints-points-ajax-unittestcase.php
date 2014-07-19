<?php

/**
 * A test case parent for the points AJAX tests.
 *
 * @package WordPoints\Tests
 * @since 1.3.0
 */

/**
 * AJAX points unit test case.
 *
 * This test case creates the 'Points' points type on set up so that doesn't have to
 * be repeated in each of the tests.
 *
 * @since 1.3.0
 */
abstract class WordPoints_Points_AJAX_UnitTestCase extends WP_Ajax_UnitTestCase {

	/**
	 * Set up before the tests begin.
	 *
	 * @since 1.3.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		require_once WORDPOINTS_DIR . 'components/points/admin/includes/ajax.php';
	}

	/**
	 * Set up for the tests.
	 *
	 * @since 1.3.0
	 */
	public function setUp() {

		parent::setUp();

		WordPoints_Points_Hooks::set_network_mode( false );

		$points_data = array(
			'name'   => 'Points',
			'prefix' => '$',
			'suffix' => 'pts.',
		);

		wordpoints_add_network_option( 'wordpoints_points_types', array( 'points' => $points_data ) );

		// Unregister any stray hook instances.
		foreach ( WordPoints_Points_Hooks::get_all() as $hook_id => $hook ) {

			if ( $hook->get_number_by_id( $hook_id ) !== '0' ) {
				WordPoints_Points_Hooks::_unregister_hook( $hook_id );
			}
		}
	}

	/**
	 * Clean up after each test.
	 *
	 * @since 1.3.0
	 */
	public function tearDown() {

		WordPoints_Points_Hooks::set_network_mode( false );
	}
}
