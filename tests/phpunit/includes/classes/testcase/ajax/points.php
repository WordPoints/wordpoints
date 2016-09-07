<?php

/**
 * A test case parent for the points Ajax tests.
 *
 * @package WordPoints\PHPUnit\Points
 * @since 2.2.0
 */

/**
 * Ajax points unit test case.
 *
 * This test case creates the 'Points' points type on set up so that doesn't have to
 * be repeated in each of the tests.
 *
 * @since 1.3.0 As WordPoints_Points_AJAX_UnitTestCase.
 * @since 2.2.0
 */
abstract class WordPoints_PHPUnit_TestCase_Ajax_Points extends WordPoints_PHPUnit_TestCase_Ajax {

	/**
	 * @since 1.9.0 As part of WordPoints_Points_AJAX_UnitTestCase.
	 * @since 2.2.0
	 */
	protected $wordpoints_component = 'points';

	/**
	 * @since 2.0.0 As part of WordPoints_Points_AJAX_UnitTestCase.
	 * @since 2.2.0
	 */
	private static $included_functions = false;

	/**
	 * Set up before the tests begin.
	 *
	 * @since 1.3.0 As part of WordPoints_Points_AJAX_UnitTestCase.
	 * @since 2.2.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		if ( ! self::$included_functions ) {

			/**
			 * Admin-side functions.
			 *
			 * @since 1.3.0
			 */
			require_once( WORDPOINTS_DIR . '/components/points/admin/admin.php' );

			self::$included_functions = true;

			self::backup_hooks();
		}
	}

	/**
	 * Set up for the tests.
	 *
	 * @since 1.3.0 As part of WordPoints_Points_AJAX_UnitTestCase.
	 * @since 2.2.0
	 */
	public function setUp() {

		parent::setUp();

		WordPoints_Points_Hooks::set_network_mode( false );

		$points_data = array(
			'name'   => 'Points',
			'prefix' => '$',
			'suffix' => 'pts.',
		);

		wordpoints_add_maybe_network_option(
			'wordpoints_points_types'
			, array( 'points' => $points_data )
		);
	}

	/**
	 * Clean up after each test.
	 *
	 * @since 1.3.0 As part of WordPoints_Points_AJAX_UnitTestCase.
	 * @since 2.2.0
	 */
	public function tearDown() {

		WordPoints_Points_Hooks::set_network_mode( false );

		parent::tearDown();
	}

} // class WordPoints_PHPUnit_TestCase_Ajax_Points

// EOF
