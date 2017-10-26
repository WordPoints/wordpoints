<?php

/**
 * Test case for WordPoints_Installer_Caps.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Installer_Caps.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Installer_Caps
 */
class WordPoints_Installer_Caps_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests that the caps are added.
	 *
	 * @since 2.4.0
	 */
	public function test_adds_caps() {

		$installer = new WordPoints_Installer_Caps(
			array( 'some_cap' => 'manage_options' )
		);

		$installer->run();

		/** @var WP_Role $role */
		$role = wp_roles()->role_objects['administrator'];

		$this->assertTrue( $role->has_cap( 'manage_options' ) );
		$this->assertTrue( $role->has_cap( 'some_cap' ) );
	}
}

// EOF
