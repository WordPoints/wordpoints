<?php

/**
 * A test case for the points component update to 1.3.0.
 *
 * @package WordPoints\Tests
 * @since 1.4.0
 */

/**
 * Test that the points component updates to 1.3.0 properly.
 *
 * @since 1.4.0
 *
 * @group points
 * @group update
 */
class WordPoints_Points_1_3_0_Update_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test the update to 1.3.0.
	 *
	 * Since 1.3.0 this was a part of the WordPoints_Points_Update_Test, which was
	 * split up to give each version its own testcase.
	 *
	 * @since 1.4.0
	 */
	public function test_custom_caps_added() {

		// Remove the custom capabilities.
		wordpoints_remove_custom_caps( array_keys( wordpoints_points_get_custom_caps() ) );

		// Make sure that was successful.
		$administrator = get_role( 'administrator' );
		$this->assertFalse( $administrator->has_cap( 'set_wordpoints_points' ) );
		$this->assertFalse( $administrator->has_cap( 'manage_wordpoints_points_types' ) );

		// Now simulate the update.
		$this->set_points_db_version();
		wordpoints_points_component_update();

		// Check that the custom capabilities were added.
		$this->assertTrue( $administrator->has_cap( 'set_wordpoints_points' ) );
		$this->assertEquals(
			! is_wordpoints_network_active()
			, $administrator->has_cap( 'manage_wordpoints_points_types' )
		);
	}
}
