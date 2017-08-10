<?php

/**
 * A test case for the points component update to 1.5.0.
 *
 * @package WordPoints\Tests
 * @since 1.5.0
 */

/**
 * Test that the points component updates to 1.5.0 properly.
 *
 * @since 1.5.0
 *
 * @group points
 * @group update
 *
 * @covers WordPoints_Points_Installable::get_update_routines
 *
 * @expectedDeprecated WordPoints_Comment_Removed_Points_Hook::__construct
 * @expectedDeprecated WordPoints_Post_Delete_Points_Hook::__construct
 */
class WordPoints_Points_1_5_0_Update_Test extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * Test that custom capabilities are added to new sites.
	 *
	 * @since 1.5.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_custom_caps_added_to_new_sites() {

		// Create a second site on the network.
		remove_action( 'wpmu_new_blog', 'WordPoints_Installables::wpmu_new_blog' );
		$blog_id = $this->factory->blog->create();
		add_action( 'wpmu_new_blog', 'WordPoints_Installables::wpmu_new_blog' );

		// Check that the caps don't exist.
		switch_to_blog( $blog_id );
		$this->assertFalse( get_role( 'administrator' )->has_cap( 'set_wordpoints_points' ) );
		restore_current_blog();

		// Simulate the update.
		$this->update_component( 'points', '1.4.0' );

		// Check that the custom caps were added to the new site.
		switch_to_blog( $blog_id );

		$administrator = get_role( 'administrator' );
		$this->assertTrue( $administrator->has_cap( 'set_wordpoints_points' ) );
		$this->assertFalse( $administrator->has_cap( 'manage_wordpoints_points_types' ) );

		restore_current_blog();
	}
}

// EOF
