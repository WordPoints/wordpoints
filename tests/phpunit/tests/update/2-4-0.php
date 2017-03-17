<?php

/**
 * A test case for the plugin update to 2.4.0.
 *
 * @package WordPoints\Tests
 * @since 2.4.0
 */

/**
 * Test that the plugin updates to 2.4.0 properly.
 *
 * @since 2.4.0
 *
 * @group update
 *
 * @covers WordPoints_Un_Installer::update_single_to_2_4_0_alpha_2
 * @covers WordPoints_Un_Installer::update_site_to_2_4_0_alpha_2
 */
class WordPoints_2_4_0_Update_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.4.0
	 */
	protected $previous_version = '2.3.0';

	/**
	 * Test that the new custom caps are added.
	 *
	 * @since 2.4.0
	 */
	public function test_adds_new_custom_caps() {

		wordpoints_remove_custom_caps( array( 'update_wordpoints_modules' ) );

		$administrator = get_role( 'administrator' );
		$this->assertFalse( $administrator->has_cap( 'update_wordpoints_modules' ) );

		// Simulate the update.
		$this->update_wordpoints();

		// Check that the capabilities were added.
		$administrator = get_role( 'administrator' );
		$this->assertTrue( $administrator->has_cap( 'update_wordpoints_modules' ) );
	}
}

// EOF
