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
 *
 * @covers WordPoints_Periodic_Points_Hook
 */
class WordPoints_Periodic_Points_Hook_Test extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * Test the defaults.
	 *
	 * @since 2.1.0
	 */
	public function test_defaults() {

		$hook = wordpointstests_add_points_hook( 'wordpoints_periodic_points_hook' );

		$this->assertInstanceOf( 'WordPoints_Periodic_Points_Hook', $hook );

		$this->assertSame(
			array(
				1 => array(
					'period' => DAY_IN_SECONDS,
					'points' => 10,
				),
			)
			, $hook->get_instances()
		);
	}

	/**
	 * Test the points are awarded.
	 *
	 * @since 1.3.0
	 */
	public function test_points_awarded() {

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
		$this->assertSame( 10, wordpoints_get_points( $user_id, 'points' ) );

		$hook->hook();
		$this->assertSame( 10, wordpoints_get_points( $user_id, 'points' ) );

		// Time machine!
		$global = ( ! is_multisite() || is_wordpoints_network_active() );
		update_user_option( $user_id, 'wordpoints_points_period_start', current_time( 'timestamp' ) - DAY_IN_SECONDS, $global );

		$hook->hook();
		$this->assertSame( 20, wordpoints_get_points( $user_id, 'points' ) );
	}
}

// EOF
