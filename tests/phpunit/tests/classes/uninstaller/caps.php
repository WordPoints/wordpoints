<?php

/**
 * Test case for WordPoints_Uninstaller_Caps.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Uninstaller_Caps.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Uninstaller_Caps
 */
class WordPoints_Uninstaller_Caps_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests that it removes the capabilities.
	 *
	 * @since 2.4.0
	 */
	public function test_removes_caps() {

		$custom_caps = array( 'some_cap' => 'manage_options' );

		wordpoints_add_custom_caps( $custom_caps );

		/** @var WP_Role $role */
		$role = wp_roles()->role_objects['administrator'];

		$this->assertTrue( $role->has_cap( 'some_cap' ) );

		$uninstaller = new WordPoints_Uninstaller_Caps(
			array_keys( $custom_caps )
		);

		$uninstaller->run();

		$this->assertFalse( $role->has_cap( 'some_cap' ) );
	}
}

// EOF
