<?php

/**
 * Test case for the apps functions.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests the apps functions.
 *
 * @since 2.1.0
 */
class WordPoints_Apps_Functions_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test initializing the API registers the actions.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_apps_init
	 */
	public function test_init() {

		$apps = new WordPoints_App( 'test' );

		wordpoints_apps_init( $apps );

		$sub_apps = $apps->sub_apps();

		$this->assertTrue( $sub_apps->is_registered( 'hooks' ) );
		$this->assertTrue( $sub_apps->is_registered( 'entities' ) );
		$this->assertTrue( $sub_apps->is_registered( 'data_types' ) );
	}

	/**
	 * Test getting the apps.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_apps
	 */
	public function test_get_apps() {

		$this->mock_apps();

		$this->assertInstanceOf( 'WordPoints_App', wordpoints_apps() );
	}

	/**
	 * Test getting the apps when they haven't been initialized.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_apps
	 */
	public function test_get_apps_not_initialized() {

		$this->mock_apps();

		WordPoints_App::$main = null;

		$this->assertInstanceOf( 'WordPoints_App', wordpoints_apps() );
	}
}

// EOF
