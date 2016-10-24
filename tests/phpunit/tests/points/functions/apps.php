<?php

/**
 * Test case for the points apps functions.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.2.0
 */

/**
 * Tests the points apps functions.
 *
 * @since 2.2.0
 */
class WordPoints_Points_Apps_Functions_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test the points component registration function.
	 *
	 * @since 2.2.0
	 *
	 * @covers ::wordpoints_points_components_app_init
	 */
	public function test_components() {

		$this->mock_apps();

		$components = new WordPoints_App( 'test' );

		wordpoints_points_components_app_init( $components );

		$sub_apps = $components->sub_apps();
		$this->assertTrue( $sub_apps->is_registered( 'points' ) );
	}

	/**
	 * Test the points apps registration function.
	 *
	 * @since 2.2.0
	 *
	 * @covers ::wordpoints_points_apps_init
	 */
	public function test_points() {

		$this->mock_apps();

		$points_app = new WordPoints_App( 'test' );

		wordpoints_points_apps_init( $points_app );

		$sub_apps = $points_app->sub_apps();
		$this->assertTrue( $sub_apps->is_registered( 'logs' ) );
	}

	/**
	 * Test the points logs apps registration function.
	 *
	 * @since 2.2.0
	 *
	 * @covers ::wordpoints_points_logs_apps_init
	 */
	public function test_points_logs() {

		$this->mock_apps();

		$logs_app = new WordPoints_App( 'test' );

		wordpoints_points_logs_apps_init( $logs_app );

		$sub_apps = $logs_app->sub_apps();
		$this->assertTrue( $sub_apps->is_registered( 'views' ) );
	}

	/**
	 * Test the points logs view registration function.
	 *
	 * @since 2.2.0
	 *
	 * @covers ::wordpoints_points_logs_views_init
	 */
	public function test_points_logs_views() {

		$this->mock_apps();

		$views = new WordPoints_Class_Registry();

		wordpoints_points_logs_views_init( $views );

		$this->assertTrue( $views->is_registered( 'table' ) );
	}
}

// EOF
