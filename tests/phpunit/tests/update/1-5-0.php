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
 */
class WordPoints_1_5_0_Update_Test extends WordPoints_UnitTestCase {

	/**
	 * Test that custom capabilities are added to new sites.
	 *
	 * @since 1.5.0
	 */
	public function test_custom_caps_added_to_new_sites() {

		if ( ! is_wordpoints_network_active() ) {
			$this->markTestSkipped( 'WordPoints must be network-active.' );
			return;
		}

		// Create a second site on the network.
		remove_action( 'wpmu_new_blog', 'wordpoints_add_custom_caps_to_new_sites' );
		$blog_id = $this->factory->blog->create();
		add_action( 'wpmu_new_blog', 'wordpoints_add_custom_caps_to_new_sites' );

		// Check that the caps don't exist.
		switch_to_blog( $blog_id );
		$this->assertFalse( get_role( 'administrator' )->has_cap( 'install_wordpoints_modules' ) );
		restore_current_blog();

		// Simulate the update.
		$this->wordpoints_set_db_version( '1.4.0' );
		wordpoints_update();

		// Check that the custom caps were added to the new site.
		switch_to_blog( $blog_id );
		$this->assertTrue( get_role( 'administrator' )->has_cap( 'install_wordpoints_modules' ) );
		restore_current_blog();
	}
}

// EOF
