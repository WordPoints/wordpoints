<?php

/**
 * A test case for the points component update to 1.8.0.
 *
 * @package WordPoints\Tests
 * @since 1.8.0
 */

/**
 * Test that the points component updates to 1.8.0 properly.
 *
 * @since 1.8.0
 *
 * @group update
 *
 * @coversNothing
 *
 * @expectedDeprecated WordPoints_Comment_Removed_Points_Hook::__construct
 * @expectedDeprecated WordPoints_Post_Delete_Points_Hook::__construct
 */
class WordPoints_Points_1_8_0_Update_Test extends WordPoints_PHPUnit_TestCase_Points {

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
		$this->assertNotContains( $blog_id, get_site_option( 'wordpoints_points_installed_sites' ) );

		// Simulate the update.
		switch_to_blog( $blog_id );
		$this->update_component( 'points', '1.7.0' );
		restore_current_blog();

		// Check that the ID was added.
		$this->assertContainsSame( $blog_id, get_site_option( 'wordpoints_points_installed_sites' ) );
	}
}

// EOF
