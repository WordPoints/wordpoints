<?php

/**
 * A test case for the registration points hook.
 *
 * @package WordPoints\Tests
 * @since 1.3.0
 */

/**
 * Test that registration points hook works as expected.
 *
 * Since 1.0.0 it was a part of WordPoints_Included_Points_Hooks_Test.
 *
 * @since 1.3.0
 *
 * @group points
 * @group points_hooks
 */
class WordPoints_Registration_Points_Hook_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test that points are awarded on registration.
	 *
	 * @since 1.3.0
	 */
	function test_points_awarded() {

		wordpointstests_add_points_hook( 'wordpoints_registration_points_hook', array( 'points' => 10 ) );

		$user_id = $this->factory->user->create();

		$this->assertEquals( 10, wordpoints_get_points( $user_id, 'points' ) );
	}
}
