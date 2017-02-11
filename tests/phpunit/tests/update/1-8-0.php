<?php

/**
 * A test case for the update to 1.8.0.
 *
 * @package WordPoints\Tests
 * @since 1.8.0
 */

/**
 * Test that the plugin updates to 1.8.0 properly.
 *
 * @since 1.8.0
 *
 * @group update
 *
 * @covers WordPoints_Un_Installer::update_site_to_1_8_0
 */
class WordPoints_1_8_0_Update_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.0.0
	 */
	protected $previous_version = '1.7.0';

	/**
	 * Test that the installed site IDs are added to the DB option.
	 *
	 * @since 1.8.0
	 *
	 * @requires WordPress multisite
	 * @requires WordPoints !network-active
	 */
	public function test_installed_site_ids_added() {

		// Create a second site on the network.
		$blog_id = $this->factory->blog->create();

		// Check that the ID doesn't exist.
		$this->assertNotContains( $blog_id, get_site_option( 'wordpoints_installed_sites' ) );

		// Simulate the update.
		switch_to_blog( $blog_id );
		$this->update_wordpoints();
		restore_current_blog();

		// Check that the ID was added.
		$this->assertContainsSame( $blog_id, get_site_option( 'wordpoints_installed_sites' ) );
	}
}

// EOF
