<?php

/**
 * A test case for the points hooks API.
 *
 * @package WordPoints\Tests
 * @since 1.2.0
 */

/**
 * Test that the points hooks API functions properly.
 *
 * @since 1.2.0
 *
 * @group points
 * @group points_hooks
 */
class WordPoints_Points_Hooks_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test the get and save hooks functions.
	 *
	 * @since 1.2.0
	 */
	function test_get_and_save() {

		wordpointstests_add_points_hook( 'wordpoints_registration_points_hook', array( 'points' => 10 ) );

		$points_types_hooks = WordPoints_Points_Hooks::get_points_types_hooks();

		$this->assertEquals(
			array( 'points' => array( 0 => 'wordpoints_registration_points_hook-1' ) )
			, $points_types_hooks
		);

		$this->assertEquals( $points_types_hooks, get_option( 'wordpoints_points_types_hooks' ) );

		WordPoints_Points_Hooks::save_points_types_hooks( array() );

		$this->assertEquals( array(), get_option( 'wordpoints_points_types_hooks' ) );

		// Network mode.
		WordPoints_Points_Hooks::set_network_mode( true );

		wordpointstests_add_points_hook( 'wordpoints_registration_points_hook', array( 'points' => 10 ) );

		$points_types_hooks = WordPoints_Points_Hooks::get_points_types_hooks();

		$this->assertEquals(
			array( 'points' => array( 0 => 'wordpoints_registration_points_hook-1' ) )
			, $points_types_hooks
		);

		$this->assertEquals( $points_types_hooks, get_site_option( 'wordpoints_points_types_hooks' ) );

		WordPoints_Points_Hooks::save_points_types_hooks( array() );

		$this->assertEquals( array(), get_site_option( 'wordpoints_points_types_hooks' ) );

		WordPoints_Points_Hooks::set_network_mode( false );

		WordPoints_Points_Hooks::save_points_types_hooks( $points_types_hooks );

		$this->assertEquals( $points_types_hooks, WordPoints_Points_Hooks::get_points_types_hooks() );
	}

	/**
	 * Test that awarded points include network and standard hooks.
	 *
	 * @since 1.2.0
	 */
	public function test_network_and_standard_hooks_fired() {

		// Set up.
		WordPoints_Points_Hooks::set_network_mode( true );
		wordpointstests_add_points_hook( 'wordpoints_registration_points_hook', array( 'points' => 10 ) );
		WordPoints_Points_Hooks::set_network_mode( false );
		wordpointstests_add_points_hook( 'wordpoints_registration_points_hook', array( 'points' => 10 ) );

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_registration_points_hook' );

		// Test retrieving all instances.
		$instances = array( 1 => array( 'points' => 10 ) );

		if ( is_multisite() ) {
			$instances['network_1'] = array( 'points' => 10 );
		}

		$this->assertEquals( $instances, $hook->get_instances() );

		// Standard instances only.
		$this->assertEquals( array( 1 => array( 'points' => 10 ) ), $hook->get_instances( 'standard' ) );

		// Network instances only.
		if ( is_multisite() ) {
			$network_instances = array( 1 => array( 'points' => 10 ) );
		} else {
			$network_instances = array( 0 => array() );
		}

		$this->assertEquals( $network_instances, $hook->get_instances( 'network' ) );

		// Make sure points are awarded.
		$user_id = $this->factory->user->create();

		$points = ( is_multisite() ) ? 20 : 10;

		$this->assertEquals( $points, wordpoints_get_points( $user_id, 'points' ) );
	}
}
