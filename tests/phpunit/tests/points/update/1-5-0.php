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
 */
class WordPoints_Points_1_5_0_Update_Test extends WordPoints_Points_UnitTestCase {

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
		remove_action( 'wpmu_new_blog', 'wordpoints_points_add_custom_caps_to_new_sites' );
		$blog_id = $this->factory->blog->create();
		add_action( 'wpmu_new_blog', 'wordpoints_points_add_custom_caps_to_new_sites' );

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
