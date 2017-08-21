<?php

/**
 * Test case for WordPoints_Hook_Router.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Router.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Router
 */
class WordPoints_Hook_Router_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test registering an action with the router.
	 *
	 * @since 2.1.0
	 */
	public function test_add_action() {

		$router = new WordPoints_Hook_Router();

		$router->add_action( 'test', array( 'action' => __METHOD__ ) );

		$this->assertSame(
			10
			, has_action( __METHOD__, array( $router, __METHOD__ . ',10' ) )
		);
	}

	/**
	 * Test registering an action with a specific priority.
	 *
	 * @since 2.1.0
	 */
	public function test_add_action_priority() {

		$router = new WordPoints_Hook_Router();

		$router->add_action(
			'test'
			, array( 'action' => __METHOD__, 'priority' => 15 )
		);

		$this->assertSame(
			15
			, has_action( __METHOD__, array( $router, __METHOD__ . ',15' ) )
		);
	}

	/**
	 * Test deregistering an action.
	 *
	 * @since 2.1.0
	 */
	public function test_remove_action() {

		$router = new WordPoints_Hook_Router();

		$router->add_action( 'test', array( 'action' => __METHOD__ ) );

		$this->assertSame(
			10
			, has_action( __METHOD__, array( $router, __METHOD__ . ',10' ) )
		);

		$router->remove_action( 'test' );

		$this->assertFalse(
			has_action( __METHOD__, array( $router, __METHOD__ . ',10' ) )
		);
	}

	/**
	 * Test deregistering an action with a specific priority.
	 *
	 * @since 2.1.0
	 */
	public function test_remove_action_priority() {

		$router = new WordPoints_Hook_Router();

		$router->add_action(
			'test'
			, array( 'action' => __METHOD__, 'priority' => 15 )
		);

		$this->assertSame(
			15
			, has_action( __METHOD__, array( $router, __METHOD__ . ',15' ) )
		);

		$router->remove_action( 'test' );

		$this->assertFalse(
			has_action( __METHOD__, array( $router, __METHOD__ . ',15' ) )
		);
	}

	/**
	 * Test deregistering an action when others are still registered doesn't unhook
	 * the router.
	 *
	 * @since 2.1.0
	 */
	public function test_remove_action_still_others() {

		$router = new WordPoints_Hook_Router();

		$router->add_action( 'test', array( 'action' => __METHOD__ ) );
		$router->add_action( 'test_2', array( 'action' => __METHOD__ ) );

		$this->assertSame(
			10
			, has_action( __METHOD__, array( $router, __METHOD__ . ',10' ) )
		);

		$router->remove_action( 'test' );

		$this->assertSame(
			10
			, has_action( __METHOD__, array( $router, __METHOD__ . ',10' ) )
		);
	}

	/**
	 * Test routing an action with the router.
	 *
	 * @since 2.1.0
	 */
	public function test_route_action() {

		$slug = $this->factory->wordpoints->hook_action->create(
			array( 'action' => __CLASS__ )
		);

		$this->assertSame( 'test_action', $slug );

		$result = $this->factory->wordpoints->hook_reaction->create();

		$this->assertIsReaction( $result );

		do_action( __CLASS__, 1, 2, 3 );

		$reactor = wordpoints_hooks()->get_sub_app( 'reactors' )->get( 'test_reactor' );

		$this->assertCount( 1, $reactor->hits );
	}

	/**
	 * Test routing a nonexistent action with the router.
	 *
	 * @since 2.1.0
	 */
	public function test_route_nonexistent_action() {

		$router = new WordPoints_Hook_Router();

		$this->assertNull( $router->{'action,10'}() );
	}

	/**
	 * Test that the first argument is returned, in case a filter is being routed.
	 *
	 * @since 2.1.0
	 */
	public function test_route_filter() {

		$router = new WordPoints_Hook_Router();

		$this->assertSame( 'arg', $router->{'filter,10'}( 'arg' ) );
	}

	/**
	 * Test routing an action with no registered events.
	 *
	 * @since 2.1.0
	 */
	public function test_route_action_no_events() {

		$apps = $this->mock_apps();

		$apps->sub_apps()->register( 'hooks', 'WordPoints_PHPUnit_Mock_Hooks' );

		$slug = $this->factory->wordpoints->hook_action->create(
			array( 'action' => __CLASS__ )
		);

		$this->assertSame( 'test_action', $slug );

		do_action( __CLASS__, 1, 2, 3 );

		/** @var WordPoints_PHPUnit_Mock_Hooks $hooks */
		$hooks = wordpoints_hooks();

		$this->assertCount( 0, $hooks->fires );
	}

	/**
	 * Test routing an action with an invalid action class.
	 *
	 * @since 2.1.0
	 */
	public function test_route_action_invalid_action() {

		$apps = $this->mock_apps();

		$apps->sub_apps()->register( 'hooks', 'WordPoints_PHPUnit_Mock_Hooks' );

		$slug = $this->factory->wordpoints->hook_action->create(
			array(
				'action' => __CLASS__,
				'class'  => 'WordPoints_PHPUnit_Mock_Object',
			)
		);

		$this->assertSame( 'test_action', $slug );

		$this->factory->wordpoints->hook_event->create();

		do_action( __CLASS__, 1, 2, 3 );

		/** @var WordPoints_PHPUnit_Mock_Hooks $hooks */
		$hooks = wordpoints_hooks();

		$this->assertCount( 0, $hooks->fires );
	}

	/**
	 * Test routing an action for an unregistered event.
	 *
	 * @since 2.1.0
	 */
	public function test_route_action_unregistered_event() {

		$apps = $this->mock_apps();

		$apps->sub_apps()->register( 'hooks', 'WordPoints_PHPUnit_Mock_Hooks' );

		$slug = $this->factory->wordpoints->hook_action->create(
			array( 'action' => __CLASS__ )
		);

		$this->assertSame( 'test_action', $slug );

		/** @var WordPoints_PHPUnit_Mock_Hooks $hooks */
		$hooks = wordpoints_hooks();

		$hooks->get_sub_app( 'router' )->add_event_to_action( 'test_event', 'test_action' );

		do_action( __CLASS__, 1, 2, 3 );

		$this->assertCount( 0, $hooks->fires );
	}

	/**
	 * Test routing an action with an event with no args.
	 *
	 * @since 2.1.0
	 */
	public function test_route_action_no_event_args() {

		$apps = $this->mock_apps();

		$apps->sub_apps()->register( 'hooks', 'WordPoints_PHPUnit_Mock_Hooks' );

		$slug = $this->factory->wordpoints->hook_action->create(
			array( 'action' => __CLASS__ )
		);

		$this->assertSame( 'test_action', $slug );

		$this->factory->wordpoints->hook_event->create();

		/** @var WordPoints_PHPUnit_Mock_Hooks $hooks */
		$hooks = wordpoints_hooks();

		// Deregister the arg.
		$hooks->get_sub_app( 'events' )->get_sub_app( 'args' )->deregister_children( 'test_event' );

		do_action( __CLASS__, 1, 2, 3 );

		$this->assertCount( 0, $hooks->fires );
	}

	/**
	 * Test adding an action without specifying the action arg.
	 *
	 * @since 2.1.0
	 */
	public function test_add_action_no_action() {

		$apps = $this->mock_apps();

		$apps->sub_apps()->register( 'hooks', 'WordPoints_PHPUnit_Mock_Hooks' );

		$slug = $this->factory->wordpoints->hook_action->create(
			array( 'action' => null )
		);

		$this->assertSame( 'test_action', $slug );

		$this->factory->wordpoints->hook_event->create();

		/** @var WordPoints_PHPUnit_Mock_Hooks $hooks */
		$hooks = wordpoints_hooks();

		$this->assertCount( 0, $hooks->fires );
	}

	/**
	 * Test adding an action with a specific number of required args.
	 *
	 * @since 2.1.0
	 */
	public function test_add_action_arg_number() {

		$apps = $this->mock_apps();
		$entities = wordpoints_entities();

		$apps->sub_apps()->register( 'hooks', 'WordPoints_PHPUnit_Mock_Hooks' );

		$slug = $this->factory->wordpoints->hook_action->create(
			array(
				'action'     => __CLASS__,
				'arg_number' => 2,
				'data'   => array(
					'arg_index' => array(
						'1:test_entity' => 0,
						'2:test_entity' => 1,
					),
				),
			)
		);

		$this->assertSame( 'test_action', $slug );

		$this->factory->wordpoints->hook_event->create(
			array(
				'args' => array(
					'1:test_entity' => 'WordPoints_Hook_Arg',
					'2:test_entity' => 'WordPoints_Hook_Arg',
					'3:test_entity' => 'WordPoints_Hook_Arg',
				),
			)
		);

		$entities->register(
			'test_entity'
			, 'WordPoints_PHPUnit_Mock_Entity'
		);

		do_action( __CLASS__, 1, 2, 3 );

		/** @var WordPoints_PHPUnit_Mock_Hooks $hooks */
		$hooks = wordpoints_hooks();

		$this->assertCount( 1, $hooks->fires );

		/** @var WordPoints_Hook_Event_Args $event_args */
		$event_args = $hooks->fires[0]['event_args'];
		$entities   = $event_args->get_entities();

		$this->assertSame( 1, $entities['1:test_entity']->get_the_value() );
		$this->assertSame( 2, $entities['2:test_entity']->get_the_value() );
		$this->assertSame( null, $entities['3:test_entity']->get_the_value() );
	}

	/**
	 * Test adding an action with an arg index determines the arg number from that.
	 *
	 * @since 2.1.0
	 */
	public function test_add_action_arg_number_from_index() {

		$apps = $this->mock_apps();
		$entities = wordpoints_entities();

		$apps->sub_apps()->register( 'hooks', 'WordPoints_PHPUnit_Mock_Hooks' );

		$slug = $this->factory->wordpoints->hook_action->create(
			array(
				'action' => __CLASS__,
				'data'   => array(
					'arg_index' => array(
						'1:test_entity' => 0,
						'2:test_entity' => 1,
					),
				),
			)
		);

		$this->assertSame( 'test_action', $slug );

		$this->factory->wordpoints->hook_event->create(
			array(
				'args' => array(
					'1:test_entity' => 'WordPoints_Hook_Arg',
					'2:test_entity' => 'WordPoints_Hook_Arg',
					'3:test_entity' => 'WordPoints_Hook_Arg',
				),
			)
		);

		$entities->register(
			'test_entity'
			, 'WordPoints_PHPUnit_Mock_Entity'
		);

		do_action( __CLASS__, 1, 2, 3 );

		/** @var WordPoints_PHPUnit_Mock_Hooks $hooks */
		$hooks = wordpoints_hooks();

		$this->assertCount( 1, $hooks->fires );

		/** @var WordPoints_Hook_Event_Args $event_args */
		$event_args = $hooks->fires[0]['event_args'];
		$entities   = $event_args->get_entities();

		$this->assertSame( 1, $entities['1:test_entity']->get_the_value() );
		$this->assertSame( 2, $entities['2:test_entity']->get_the_value() );
		$this->assertSame( null, $entities['3:test_entity']->get_the_value() );
	}

	/**
	 * Test adding an action with requirements determines the arg number from that.
	 *
	 * @since 2.1.0
	 */
	public function test_add_action_arg_number_from_requirements() {

		$apps = $this->mock_apps();
		$entities = wordpoints_entities();

		$apps->sub_apps()->register( 'hooks', 'WordPoints_PHPUnit_Mock_Hooks' );

		$slug = $this->factory->wordpoints->hook_action->create(
			array(
				'action' => __CLASS__,
				'data'   => array(
					'arg_index' => array(
						'1:test_entity' => 0,
					),
					'requirements' => array(
						1 => 2,
					),
				),
			)
		);

		$this->assertSame( 'test_action', $slug );

		$this->factory->wordpoints->hook_event->create(
			array(
				'args' => array(
					'1:test_entity' => 'WordPoints_Hook_Arg',
					'2:test_entity' => 'WordPoints_Hook_Arg',
					'3:test_entity' => 'WordPoints_Hook_Arg',
				),
			)
		);

		$entities->register(
			'test_entity'
			, 'WordPoints_PHPUnit_Mock_Entity'
		);

		do_action( __CLASS__, 1, 0, 3 );

		/** @var WordPoints_PHPUnit_Mock_Hooks $hooks */
		$hooks = wordpoints_hooks();

		$this->assertCount( 0, $hooks->fires );

		do_action( __CLASS__, 1, 2, 3 );

		$this->assertCount( 1, $hooks->fires );

		/** @var WordPoints_Hook_Event_Args $event_args */
		$event_args = $hooks->fires[0]['event_args'];
		$entities   = $event_args->get_entities();

		$this->assertSame( 1, $entities['1:test_entity']->get_the_value() );
		$this->assertSame( null, $entities['2:test_entity']->get_the_value() );
		$this->assertSame( null, $entities['3:test_entity']->get_the_value() );
	}

	/**
	 * Test adding an event to an action.
	 *
	 * @since 2.1.0
	 */
	public function test_add_event_to_action() {

		$router = new WordPoints_Hook_Router();
		$router->add_event_to_action( 'test_event', 'test_action' );

		$this->assertSame(
			array(
				'test_action' => array( 'fire' => array( 'test_event' => true ) ),
			)
			, $router->get_event_index()
		);
	}

	/**
	 * Test adding an event to an action when specifying an action type.
	 *
	 * @since 2.1.0
	 */
	public function test_add_event_to_action_action_type() {

		$router = new WordPoints_Hook_Router();
		$router->add_event_to_action( 'test_event', 'test_action', 'test_type' );

		$this->assertSame(
			array(
				'test_action' => array(
					'test_type' => array( 'test_event' => true ),
				),
			)
			, $router->get_event_index()
		);
	}

	/**
	 * Test removing an event from an action.
	 *
	 * @since 2.1.0
	 */
	public function test_remove_event_from_action() {

		$router = new WordPoints_Hook_Router();
		$router->add_event_to_action( 'test_event', 'test_action' );
		$router->add_event_to_action( 'another_event', 'test_action' );

		$router->remove_event_from_action( 'test_event', 'test_action' );

		$this->assertSame(
			array(
				'test_action' => array( 'fire' => array( 'another_event' => true ) ),
			)
			, $router->get_event_index()
		);
	}

	/**
	 * Test removing an event from an action when specifying an action type.
	 *
	 * @since 2.1.0
	 */
	public function test_remove_event_from_action_action_type() {

		$router = new WordPoints_Hook_Router();
		$router->add_event_to_action( 'test_event', 'test_action', 'test_type' );
		$router->add_event_to_action( 'another_event', 'test_action', 'test_type' );

		$router->remove_event_from_action( 'test_event', 'test_action', 'test_type' );

		$this->assertSame(
			array(
				'test_action' => array(
					'test_type' => array( 'another_event' => true ),
				),
			)
			, $router->get_event_index()
		);
	}

	/**
	 * Test removing an event from an action when none are registered.
	 *
	 * @since 2.1.0
	 */
	public function test_remove_event_from_action_none() {

		$router = new WordPoints_Hook_Router();

		$router->remove_event_from_action( 'test_event', 'test_action' );

		$this->assertSame( array(), $router->get_event_index() );
	}

	/**
	 * Test getting the event index when no events have been added yet.
	 *
	 * @since 2.1.0
	 */
	public function test_get_event_index_none() {

		$router = new WordPoints_Hook_Router();

		$this->assertSame( array(), $router->get_event_index() );
	}
}

// EOF
