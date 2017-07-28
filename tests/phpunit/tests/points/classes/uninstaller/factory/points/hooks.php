<?php

/**
 * Test case for WordPoints_Points_Uninstaller_Factory_Points_Hooks.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Points_Uninstaller_Factory_Points_Hooks.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Points_Uninstaller_Factory_Points_Hooks
 */
class WordPoints_Points_Uninstaller_Factory_Points_Hooks_Test
	extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * Tests getting the uninstall routines for a single site.
	 *
	 * @since 2.4.0
	 */
	public function test_get_for_single() {

		$factory = new WordPoints_Points_Uninstaller_Factory_Points_Hooks(
			array( 'single' => array( 'wordpoints_post_points_hook' ) )
		);

		$uninstallers = $factory->get_for_single();

		$hook = wordpointstests_add_points_hook( 'wordpoints_post_points_hook' );

		$this->assertCount( 1, $hook->get_instances() );

		$uninstallers[0]->run();

		$this->assertCount( 0, $hook->get_instances() );
	}

	/**
	 * Tests getting the uninstall routines for a site on the network.
	 *
	 * @since 2.4.0
	 */
	public function test_get_for_site() {

		$factory = new WordPoints_Points_Uninstaller_Factory_Points_Hooks(
			array( 'site' => array( 'wordpoints_post_points_hook' ) )
		);

		$uninstallers = $factory->get_for_site();

		$hook = wordpointstests_add_points_hook( 'wordpoints_post_points_hook' );

		$this->assertCount( 1, $hook->get_instances() );

		$uninstallers[0]->run();

		$this->assertCount( 0, $hook->get_instances() );
	}

	/**
	 * Tests getting the uninstall routines for the network.
	 *
	 * @since 2.4.0
	 */
	public function test_get_for_network() {

		$factory = new WordPoints_Points_Uninstaller_Factory_Points_Hooks(
			array( 'network' => array( 'wordpoints_post_points_hook' ) )
		);

		$uninstallers = $factory->get_for_network();

		WordPoints_Points_Hooks::set_network_mode( true );
		$hook = wordpointstests_add_points_hook( 'wordpoints_post_points_hook' );
		WordPoints_Points_Hooks::set_network_mode( false );

		$this->assertCount( 1, $hook->get_instances() );

		$uninstallers[0]->run();

		$this->assertCount( 0, $hook->get_instances() );
	}
}

// EOF
