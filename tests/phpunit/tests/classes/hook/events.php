<?php

/**
 * Test case for WordPoints_Hook_Events.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Events.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Events
 */
class WordPoints_Hook_Events_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test that it provides the expected sub-apps.
	 *
	 * @since 2.1.0
	 */
	public function test_sub_apps() {

		$events = new WordPoints_Hook_Events( 'test' );

		$this->assertInstanceOf(
			'WordPoints_Class_Registry_Children'
			, $events->get_sub_app( 'args' )
		);
	}

	/**
	 * Test registering an event without the the extra args is OK.
	 *
	 * @since 2.1.0
	 */
	public function test_register_args_optional() {

		$events = new WordPoints_Hook_Events( 'test' );

		$this->assertTrue(
			$events->register(
				'test'
				, 'WordPoints_PHPUnit_Mock_Hook_Event'
			)
		);
	}

	/**
	 * Test registering an event registers the event with the router.
	 *
	 * @since 2.1.0
	 */
	public function test_register_registers_event_with_router() {

		$apps = $this->mock_apps();

		$apps->sub_apps()->register( 'hooks', 'WordPoints_PHPUnit_Mock_Hooks' );

		$this->factory->wordpoints->hook_event->create();

		$this->factory->wordpoints->hook_action->create(
			array( 'action' => __CLASS__ )
		);

		$this->factory->wordpoints->hook_reaction->create();

		do_action( __CLASS__, 1, 2, 3 );

		/** @var WordPoints_PHPUnit_Mock_Hooks $hooks */
		$hooks = wordpoints_hooks();

		$this->assertCount( 1, $hooks->fires );
	}

	/**
	 * Test deregistering an event deregisters the event with the router.
	 *
	 * @since 2.1.0
	 */
	public function test_deregister_deregisters_event_with_router() {

		$apps = $this->mock_apps();

		$apps->sub_apps()->register( 'hooks', 'WordPoints_PHPUnit_Mock_Hooks' );

		$this->factory->wordpoints->hook_event->create();

		$this->factory->wordpoints->hook_action->create(
			array( 'action' => __CLASS__ )
		);

		$this->factory->wordpoints->hook_reaction->create();

		do_action( __CLASS__, 1, 2, 3 );

		/** @var WordPoints_PHPUnit_Mock_Hooks $hooks */
		$hooks = wordpoints_hooks();

		$this->assertCount( 1, $hooks->fires );

		$hooks->get_sub_app( 'events' )->deregister( 'test_event' );

		do_action( __CLASS__, 1, 2, 3 );

		$this->assertCount( 1, $hooks->fires );
	}

	/**
	 * Test registering an event registers the args.
	 *
	 * @since 2.1.0
	 */
	public function test_register_registers_args() {

		$this->factory->wordpoints->hook_event->create(
			array(
				'args' => array(
					'test_arg' => 'WordPoints_PHPUnit_Mock_Hook_Arg',
				),
			)
		);

		$args = wordpoints_hooks()->get_sub_app( 'events' )->get_sub_app( 'args' );

		$this->assertTrue( $args->is_registered( 'test_event', 'test_arg' ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Hook_Arg'
			, $args->get( 'test_event', 'test_arg' )
		);
	}

	/**
	 * Test deregistering an event deregisters the args.
	 *
	 * @since 2.1.0
	 */
	public function test_deregister_deregisters_arg() {

		$this->factory->wordpoints->hook_event->create(
			array(
				'args' => array(
					'test_arg' => 'WordPoints_PHPUnit_Mock_Hook_Arg',
				),
			)
		);

		$events = wordpoints_hooks()->get_sub_app( 'events' );
		$args   = $events->get_sub_app( 'args' );

		$this->assertTrue( $args->is_registered( 'test_event', 'test_arg' ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Hook_Arg'
			, $args->get( 'test_event', 'test_arg' )
		);

		$events->deregister( 'test_event' );

		$this->assertFalse( $args->is_registered( 'test_event', 'test_arg' ) );

		$this->assertFalse( $args->get( 'test_event', 'test_arg' ) );
	}

	/**
	 * Test deregistering an unregistered event works without error.
	 *
	 * @since 2.1.0
	 */
	public function test_deregister_unregistered() {

		$this->mock_apps();

		$events = wordpoints_hooks()->get_sub_app( 'events' );
		$args   = $events->get_sub_app( 'args' );

		$this->assertFalse( $args->is_registered( 'test_event', 'test_arg' ) );

		$events->deregister( 'test_event' );

		$this->assertFalse( $args->is_registered( 'test_event', 'test_arg' ) );
	}
}

// EOF
