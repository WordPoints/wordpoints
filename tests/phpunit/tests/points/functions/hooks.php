<?php

/**
 * Test case for the points hooks functions.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests the points hooks functions.
 *
 * @since 2.1.0
 */
class WordPoints_Points_Hooks_Functions_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test the reactor registration function.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_points_hook_reactors_init
	 */
	public function test_reactors() {

		$this->mock_apps();

		$reactors = new WordPoints_Class_Registry_Persistent();

		wordpoints_points_hook_reactors_init( $reactors );

		$this->assertTrue( $reactors->is_registered( 'points' ) );
		$this->assertTrue( $reactors->is_registered( 'points_legacy' ) );
	}

	/**
	 * Test the reaction store registration function.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_points_hook_reaction_stores_init
	 *
	 * @requires WordPoints !network-active
	 */
	public function test_reaction_stores() {

		$this->mock_apps();

		$reaction_stores = new WordPoints_Class_Registry_Children();

		wordpoints_points_hook_reaction_stores_init( $reaction_stores );

		$this->assertTrue( $reaction_stores->is_registered( 'standard', 'points' ) );
		$this->assertFalse( $reaction_stores->is_registered( 'network', 'points' ) );
	}

	/**
	 * Test the reaction store registration function with WordPoints network active.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_points_hook_reaction_stores_init
	 *
	 * @requires WordPoints network-active
	 */
	public function test_reaction_stores_network_active() {

		$this->mock_apps();

		$reaction_stores = new WordPoints_Class_Registry_Children();

		wordpoints_points_hook_reaction_stores_init( $reaction_stores );

		$this->assertTrue( $reaction_stores->is_registered( 'standard', 'points' ) );
		$this->assertTrue( $reaction_stores->is_registered( 'network', 'points' ) );
	}

	/**
	 * Test the extension registration function.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_points_hook_extensions_init
	 */
	public function test_extensions() {

		$this->mock_apps();

		$extensions = new WordPoints_Class_Registry_Persistent();

		wordpoints_points_hook_extensions_init( $extensions );

		$this->assertTrue( $extensions->is_registered( 'points_legacy_reversals' ) );
		$this->assertTrue( $extensions->is_registered( 'points_legacy_repeat_blocker' ) );
	}

	/**
	 * Test the legacy post publish event registration function.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_points_register_legacy_post_publish_events
	 */
	public function test_legacy_post_publish_events() {

		$this->mock_apps();

		wordpoints_points_register_legacy_post_publish_events( 'post' );
		wordpoints_points_register_legacy_post_publish_events( 'page' );
		wordpoints_points_register_legacy_post_publish_events( 'attachment' );

		$events = wordpoints_hooks()->get_sub_app( 'events' );

		$this->assertTrue( $events->is_registered( 'points_legacy_post_publish\post' ) );
		$this->assertTrue( $events->is_registered( 'points_legacy_post_publish\page' ) );
		$this->assertFalse( $events->is_registered( 'points_legacy_post_publish\attachment' ) );
	}
}

// EOF
