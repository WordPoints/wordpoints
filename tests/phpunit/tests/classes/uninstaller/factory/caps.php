<?php

/**
 * Test case for WordPoints_Uninstaller_Factory_Caps.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Uninstaller_Factory_Caps.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Uninstaller_Factory_Caps
 */
class WordPoints_Uninstaller_Factory_Caps_Test extends WordPoints_PHPUnit_TestCase {

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

		$factory = new WordPoints_Uninstaller_Factory_Caps(
			array_keys( $custom_caps )
		);

		$uninstallers = $factory->get_for_single();
		$uninstallers[0]->run();

		$this->assertFalse( $role->has_cap( 'some_cap' ) );
	}

	/**
	 * Tests that it removes the capabilities.
	 *
	 * @since 2.4.0
	 */
	public function test_removes_caps_multisite() {

		$custom_caps = array( 'some_cap' => 'manage_options' );

		wordpoints_add_custom_caps( $custom_caps );

		/** @var WP_Role $role */
		$role = wp_roles()->role_objects['administrator'];

		$this->assertTrue( $role->has_cap( 'some_cap' ) );

		$factory = new WordPoints_Uninstaller_Factory_Caps(
			array_keys( $custom_caps )
		);

		$uninstallers = $factory->get_for_site();
		$uninstallers[0]->run();

		$this->assertFalse( $role->has_cap( 'some_cap' ) );
	}
}

// EOF
