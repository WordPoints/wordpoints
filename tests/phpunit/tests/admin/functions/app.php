<?php

/**
 * Test case for the admin apps functions.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests the admin apps functions.
 *
 * @since 2.1.0
 */
class WordPoints_Admin_Apps_Functions_Test extends WordPoints_PHPUnit_TestCase_Admin {

	/**
	 * Test the admin apps registration function.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_hooks_register_admin_apps
	 */
	public function test_register_apps() {

		$this->mock_apps();

		$apps = new WordPoints_App( 'test' );

		wordpoints_hooks_register_admin_apps( $apps );

		$this->assertTrue( $apps->sub_apps()->is_registered( 'admin' ) );

		/** @var WordPoints_App $app */
		$app = $apps->get_sub_app( 'admin' );

		$this->assertTrue( $app->sub_apps()->is_registered( 'screen' ) );
	}
}

// EOF
