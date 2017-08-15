<?php

/**
 * A test case for the update to 1.5.0.
 *
 * @package WordPoints\Tests
 * @since 1.5.0
 */

/**
 * Test that the plugin updates to 1.5.0 properly.
 *
 * @since 1.5.0
 *
 * @group update
 *
 * @covers WordPoints_Installable_Core::get_update_routines
 */
class WordPoints_1_5_0_Update_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.0.0
	 */
	protected $previous_version = '1.4.0';

	/**
	 * Test that custom capabilities are added to new sites.
	 *
	 * @since 1.5.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_custom_caps_added_to_new_sites() {

		// Create a second site on the network.
		remove_action( 'wpmu_new_blog', 'wordpoints_installables_install_on_new_site' );
		$blog_id = $this->factory->blog->create();

		// Check that the caps don't exist.
		switch_to_blog( $blog_id );
		$this->assertFalse( get_role( 'administrator' )->has_cap( 'install_wordpoints_extensions' ) );
		restore_current_blog();

		// Simulate the update.
		$this->update_wordpoints();

		// Check that the custom caps were added to the new site.
		switch_to_blog( $blog_id );
		$this->assertTrue( get_role( 'administrator' )->has_cap( 'install_wordpoints_extensions' ) );
		restore_current_blog();
	}
}

// EOF
