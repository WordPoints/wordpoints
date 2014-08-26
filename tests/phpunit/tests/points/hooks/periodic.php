<?php

/**
 * A test case for the periodic points hook.
 *
 * @package WordPoints\Tests
 * @since 1.3.0
 */

/**
 * Test the periodic points hook.
 *
 * Since 1.0.0 it was a part of WordPoints_Included_Points_Hooks_Test.
 *
 * @since 1.3.0
 *
 * @group points
 * @group points_hooks
 */
class WordPoints_Periodic_Points_Hook_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test the points are awarded.
	 *
	 * @since 1.3.0
	 */
	function test_points_awarded() {

		$hook = wordpointstests_add_points_hook(
			'wordpoints_periodic_points_hook'
			, array(
				'period' => DAY_IN_SECONDS,
				'points' => 10,
			)
		);

		$this->assertInstanceOf( 'WordPoints_Periodic_Points_Hook', $hook );

		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$hook->hook();
		$this->assertEquals( 10, wordpoints_get_points( $user_id, 'points' ) );

		$hook->hook();
		$this->assertEquals( 10, wordpoints_get_points( $user_id, 'points' ) );

		// Time machine!
		$global = ( ! is_multisite() || is_wordpoints_network_active() );
		update_user_option( $user_id, 'wordpoints_points_period_start', current_time( 'timestamp' ) - DAY_IN_SECONDS, $global );

		$hook->hook();
		$this->assertEquals( 20, wordpoints_get_points( $user_id, 'points' ) );
	}
}

// EOF
