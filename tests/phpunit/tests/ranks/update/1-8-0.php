<?php

/**
 * A test case for the ranks component update to 1.8.0.
 *
 * @package WordPoints\Tests
 * @since 1.8.0
 */

/**
 * Test that the ranks component updates to 1.8.0 properly.
 *
 * @since 1.8.0
 *
 * @group update
 */
class WordPoints_Ranks_1_8_0_Update_Test extends WordPoints_UnitTestCase {

	/**
	 * Test that the installed site IDs are added to the DB option.
	 *
	 * @since 1.8.0
	 */
	public function test_installed_site_ids_added() {

		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Multisite is required.' );
		}

		if ( is_wordpoints_network_active() ) {
			$this->markTestSkipped( 'WordPoints must not be network-active.' );
		}

		// Create a second site on the network.
		$blog_id = $this->factory->blog->create();

		// Check that the ID doesn't exist.
		$this->assertNotContains( $blog_id, get_site_option( 'wordpoints_ranks_installed_sites' ) );

		// Simulate the update.
		switch_to_blog( $blog_id );
		$this->update_component( 'ranks', '1.7.0' );
		restore_current_blog();

		// Check that the ID was added.
		$this->assertContains( $blog_id, get_site_option( 'wordpoints_ranks_installed_sites' ) );
	}
}

// EOF
