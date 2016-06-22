<?php

/**
 * Test case for WordPoints_Hooks.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hooks.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hooks
 */
class WordPoints_Hooks_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test that it calls the wordpoints_hooks_init action when it is constructed.
	 *
	 * @since 2.1.0
	 */
	public function test_does_action_on_construct() {

		$mock = new WordPoints_Mock_Filter;

		add_action( 'wordpoints_init_app-hooks', array( $mock, 'action' ) );

		$hooks = new WordPoints_Hooks( 'hooks' );

		$this->assertEquals( 1, $mock->call_count );

		$this->assertTrue( $hooks === $mock->calls[0][0] );
	}

	/**
	 * Test that it registers the sub-apps when it is constructed.
	 *
	 * @since 2.1.0
	 */
	public function test_registers_sub_apps_on_construct() {

		$hooks = new WordPoints_Hooks( 'hooks' );

		$this->assertInstanceOf( 'WordPoints_Hook_Router', $hooks->get_sub_app( 'router' ) );
		$this->assertInstanceOf( 'WordPoints_Hook_Actions', $hooks->get_sub_app( 'actions' ) );
		$this->assertInstanceOf( 'WordPoints_Hook_Events', $hooks->get_sub_app( 'events' ) );
		$this->assertInstanceOf( 'WordPoints_Class_Registry_Persistent', $hooks->get_sub_app( 'reactors' ) );
		$this->assertInstanceOf( 'WordPoints_Class_Registry_Children', $hooks->get_sub_app( 'reaction_stores' ) );
		$this->assertInstanceOf( 'WordPoints_Class_Registry_Persistent', $hooks->get_sub_app( 'extensions' ) );
		$this->assertInstanceOf( 'WordPoints_Class_Registry_Children', $hooks->get_sub_app( 'conditions' ) );
	}

	/**
	 * Test setting the current mode.
	 *
	 * @since 2.1.0
	 */
	public function test_set_current_mode() {

		$hooks = new WordPoints_Hooks( 'hooks' );

		$hooks->set_current_mode( 'standard' );

		$this->assertEquals( 'standard', $hooks->get_current_mode() );

		$hooks->set_current_mode( 'network' );

		$this->assertEquals( 'network', $hooks->get_current_mode() );
	}

	/**
	 * Test that current mode is 'standard' by default.
	 *
	 * @since 2.1.0
	 */
	public function test_standard_mode_by_default() {

		$hooks = new WordPoints_Hooks( 'hooks' );

		$this->assertEquals( 'standard', $hooks->get_current_mode() );
	}

	/**
	 * Test that current mode is 'network' by default in the network admin.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_network_mode_on_if_network_admin() {

		$this->set_network_admin();

		$hooks = new WordPoints_Hooks( 'hooks' );

		$this->assertEquals( 'network', $hooks->get_current_mode() );
	}

	/**
	 * Test getting a reaction store.
	 *
	 * @since 2.1.0
	 */
	public function test_get_reaction_store() {

		$slug = $this->factory->wordpoints->hook_reaction_store->create();

		$reaction_store = wordpoints_hooks()->get_reaction_store( $slug );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Hook_Reaction_Store'
			, $reaction_store
		);

		$this->assertEquals( $slug, $reaction_store->get_slug() );
	}

	/**
	 * Test getting an unregistered reaction store.
	 *
	 * @since 2.1.0
	 */
	public function test_get_reaction_store_unregistered() {

		$this->assertFalse(
			wordpoints_hooks()->get_reaction_store( 'unregistered' )
		);
	}

	/**
	 * Test getting a reaction store when out of context.
	 *
	 * @since 2.1.0
	 */
	public function test_get_reaction_store_out_of_context() {

		$slug = $this->factory->wordpoints->hook_reaction_store->create(
			array(
				'class' => 'WordPoints_PHPUnit_Mock_Hook_Reaction_Store_Contexted',
			)
		);

		wordpoints_entities()->get_sub_app( 'contexts' )->register(
			'test_context'
			, 'WordPoints_PHPUnit_Mock_Entity_Context_OutOfState'
		);

		$this->assertFalse( wordpoints_hooks()->get_reaction_store( $slug ) );
	}

	/**
	 * Test firing an event.
	 *
	 * @since 2.1.0
	 */
	public function test_fire_event() {

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $extension */
		$extension = $this->factory->wordpoints->hook_extension->create_and_get();

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $other_extension */
		$other_extension = $this->factory->wordpoints->hook_extension->create_and_get(
			array( 'slug' => 'another' )
		);

		/** @var WordPoints_PHPUnit_Mock_Hook_Reactor $reactor */
		$reactor = $this->factory->wordpoints->hook_reactor->create_and_get();
		$reactions = $this->factory->wordpoints->hook_reaction->create_many( 2 );

		/** @var WordPoints_PHPUnit_Mock_Hook_Reactor $other_reactor */
		$other_reactor = $this->factory->wordpoints->hook_reactor->create_and_get(
			array( 'slug' => 'another' )
		);

		$other_reaction = $this->factory->wordpoints->hook_reaction->create(
			array( 'reactor' => 'another' )
		);

		$this->fire_event();

		// The extensions should have each been checked.
		$this->assertCount( 3, $extension->hit_checks );
		$this->assertCount( 3, $extension->hits );

		$this->assertCount( 3, $other_extension->hit_checks );
		$this->assertCount( 3, $other_extension->hits );

		// The reactors should have been hit.
		$this->assertCount( 2, $reactor->hits );

		$this->assertHitsLogged( array( 'reaction_id' => $reactions[0]->get_id() ) );
		$this->assertHitsLogged( array( 'reaction_id' => $reactions[1]->get_id() ) );

		$this->assertCount( 1, $other_reactor->hits );

		$this->assertHitsLogged(
			array( 'reactor' => 'another', 'reaction_id' => $other_reaction->get_id() )
		);
	}

	/**
	 * Test firing an event.
	 *
	 * @since 2.1.0
	 */
	public function test_fire_event_reaction_store_out_of_context() {

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $extension */
		$extension = $this->factory->wordpoints->hook_extension->create_and_get();

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $other_extension */
		$other_extension = $this->factory->wordpoints->hook_extension->create_and_get(
			array( 'slug' => 'another' )
		);

		$slug = $this->factory->wordpoints->hook_reaction_store->create(
			array(
				'slug' => 'contexted',
				'class' => 'WordPoints_PHPUnit_Mock_Hook_Reaction_Store_Contexted',
			)
		);

		/** @var WordPoints_PHPUnit_Mock_Hook_Reactor $reactor */
		$reactor = $this->factory->wordpoints->hook_reactor->create_and_get();
		$reactions = $this->factory->wordpoints->hook_reaction->create_many(
			2
			, array( 'reaction_store' => $slug )
		);

		/** @var WordPoints_PHPUnit_Mock_Hook_Reactor $other_reactor */
		$other_reactor = $this->factory->wordpoints->hook_reactor->create_and_get(
			array( 'slug' => 'another' )
		);

		$other_reaction = $this->factory->wordpoints->hook_reaction->create(
			array( 'reactor' => 'another' )
		);

		wordpoints_entities()->get_sub_app( 'contexts' )->register(
			'test_context'
			, 'WordPoints_PHPUnit_Mock_Entity_Context_OutOfState'
		);

		$this->fire_event();

		// The extensions should have each been checked.
		$this->assertCount( 1, $extension->hit_checks );
		$this->assertCount( 1, $extension->hits );

		$this->assertCount( 1, $other_extension->hit_checks );
		$this->assertCount( 1, $other_extension->hits );

		// The first reactor should not have been hit.
		$this->assertCount( 0, $reactor->hits );

		$this->assertHitsLogged( array( 'reaction_id' => $reactions[0]->get_id() ), 0 );
		$this->assertHitsLogged( array( 'reaction_id' => $reactions[1]->get_id() ), 0 );

		// The other reactor should.
		$this->assertCount( 1, $other_reactor->hits );

		$this->assertHitsLogged(
			array( 'reactor' => 'another', 'reaction_id' => $other_reaction->get_id() )
		);
	}

	/**
	 * Test firing an event when one reactor doesn't listen for this action type.
	 *
	 * @since 2.1.0
	 */
	public function test_fire_event_different_action_type() {

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $extension */
		$extension = $this->factory->wordpoints->hook_extension->create_and_get();

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $other_extension */
		$other_extension = $this->factory->wordpoints->hook_extension->create_and_get(
			array( 'slug' => 'another' )
		);

		/** @var WordPoints_PHPUnit_Mock_Hook_Reactor $reactor */
		$reactor = $this->factory->wordpoints->hook_reactor->create_and_get();
		$reactor->action_types = array( 'other' );

		$reactions = $this->factory->wordpoints->hook_reaction->create_many( 2 );

		/** @var WordPoints_PHPUnit_Mock_Hook_Reactor $other_reactor */
		$other_reactor = $this->factory->wordpoints->hook_reactor->create_and_get(
			array( 'slug' => 'another' )
		);

		$other_reaction = $this->factory->wordpoints->hook_reaction->create(
			array( 'reactor' => 'another' )
		);

		$this->fire_event();

		// The extensions should have each been checked.
		$this->assertCount( 1, $extension->hit_checks );
		$this->assertCount( 1, $extension->hits );

		$this->assertCount( 1, $other_extension->hit_checks );
		$this->assertCount( 1, $other_extension->hits );

		// The first reactor should not have been hit.
		$this->assertCount( 0, $reactor->hits );

		$this->assertHitsLogged( array( 'reaction_id' => $reactions[0]->get_id() ), 0 );
		$this->assertHitsLogged( array( 'reaction_id' => $reactions[1]->get_id() ), 0 );

		// The other reactor should.
		$this->assertCount( 1, $other_reactor->hits );

		$this->assertHitsLogged(
			array( 'reactor' => 'another', 'reaction_id' => $other_reaction->get_id() )
		);
	}

	/**
	 * Test firing an event when no reactors are registered.
	 *
	 * @since 2.1.0
	 */
	public function test_fire_event_no_reactors() {

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $extension */
		$extension = $this->factory->wordpoints->hook_extension->create_and_get();

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $other_extension */
		$other_extension = $this->factory->wordpoints->hook_extension->create_and_get(
			array( 'slug' => 'another' )
		);

		$this->fire_event();

		// The extensions should not have been checked.
		$this->assertCount( 0, $extension->hit_checks );
		$this->assertCount( 0, $extension->hits );

		$this->assertCount( 0, $other_extension->hit_checks );
		$this->assertCount( 0, $other_extension->hits );
	}

	/**
	 * Test firing an event when one reactor doesn't have any reactions for it.
	 *
	 * @since 2.1.0
	 */
	public function test_fire_event_no_reactions() {

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $extension */
		$extension = $this->factory->wordpoints->hook_extension->create_and_get();

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $other_extension */
		$other_extension = $this->factory->wordpoints->hook_extension->create_and_get(
			array( 'slug' => 'another' )
		);

		/** @var WordPoints_PHPUnit_Mock_Hook_Reactor $reactor */
		$reactor = $this->factory->wordpoints->hook_reactor->create_and_get();

		$reactions = $this->factory->wordpoints->hook_reaction->create_many( 2 );

		/** @var WordPoints_PHPUnit_Mock_Hook_Reactor $other_reactor */
		$other_reactor = $this->factory->wordpoints->hook_reactor->create_and_get(
			array( 'slug' => 'another' )
		);

		$this->fire_event();

		// The extensions should have each been checked.
		$this->assertCount( 2, $extension->hit_checks );
		$this->assertCount( 2, $extension->hits );

		$this->assertCount( 2, $other_extension->hit_checks );
		$this->assertCount( 2, $other_extension->hits );

		// The reactors should have been hit.
		$this->assertCount( 2, $reactor->hits );

		$this->assertHitsLogged( array( 'reaction_id' => $reactions[0]->get_id() ) );
		$this->assertHitsLogged( array( 'reaction_id' => $reactions[1]->get_id() ) );

		$this->assertCount( 0, $other_reactor->hits );
	}

	/**
	 * Test firing an event when there are no extensions.
	 *
	 * @since 2.1.0
	 */
	public function test_fire_event_no_extensions() {

		$this->factory->wordpoints->hook_reactor->create();
		$reactions = $this->factory->wordpoints->hook_reaction->create_many( 2 );

		$this->factory->wordpoints->hook_reactor->create(
			array( 'slug' => 'another' )
		);

		$other_reaction = $this->factory->wordpoints->hook_reaction->create(
			array( 'reactor' => 'another' )
		);

		$this->fire_event();

		$hooks = wordpoints_hooks();

		// The reactors should have been hit.
		$reactors = $hooks->get_sub_app( 'reactors' );
		$reactor  = $reactors->get( 'test_reactor' );

		$this->assertCount( 2, $reactor->hits );

		$this->assertHitsLogged( array( 'reaction_id' => $reactions[0]->get_id() ) );
		$this->assertHitsLogged( array( 'reaction_id' => $reactions[1]->get_id() ) );

		$reactor = $reactors->get( 'another' );

		$this->assertCount( 1, $reactor->hits );

		$this->assertHitsLogged(
			array( 'reactor' => 'another', 'reaction_id' => $other_reaction->get_id() )
		);
	}

	/**
	 * Test firing an event when a reaction has invalid settings.
	 *
	 * @since 2.1.0
	 */
	public function test_fire_event_invalid_reaction() {

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $extension */
		$extension = $this->factory->wordpoints->hook_extension->create_and_get();

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $other_extension */
		$other_extension = $this->factory->wordpoints->hook_extension->create_and_get(
			array( 'slug' => 'another' )
		);

		/** @var WordPoints_PHPUnit_Mock_Hook_Reactor $reactor */
		$reactor = $this->factory->wordpoints->hook_reactor->create_and_get();

		$this->factory->wordpoints->hook_reaction->create(
			array(
				'test_extension' => array( 'test_fire' => array( 'fail' => true ) ),
			)
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		/** @var WordPoints_PHPUnit_Mock_Hook_Reactor $other_reactor */
		$other_reactor = $this->factory->wordpoints->hook_reactor->create_and_get(
			array( 'slug' => 'another' )
		);

		$other_reaction = $this->factory->wordpoints->hook_reaction->create(
			array( 'reactor' => 'another' )
		);

		$this->fire_event();

		// The extensions should have each been checked.
		$this->assertCount( 2, $extension->hit_checks );
		$this->assertCount( 2, $extension->hits );

		$this->assertCount( 2, $other_extension->hit_checks );
		$this->assertCount( 2, $other_extension->hits );

		// The reactors should have been hit.
		$this->assertCount( 1, $reactor->hits );

		$this->assertHitsLogged( array( 'reaction_id' => $reaction->get_id() ) );

		$this->assertCount( 1, $other_reactor->hits );

		$this->assertHitsLogged(
			array( 'reactor' => 'another', 'reaction_id' => $other_reaction->get_id() )
		);
	}

	/**
	 * Test firing an event that an extension aborts.
	 *
	 * @since 2.1.0
	 */
	public function test_fire_event_extension_aborted() {

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $extension */
		$extension = $this->factory->wordpoints->hook_extension->create_and_get();

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $other_extension */
		$other_extension = $this->factory->wordpoints->hook_extension->create_and_get(
			array( 'slug' => 'another' )
		);

		/** @var WordPoints_PHPUnit_Mock_Hook_Reactor $reactor */
		$reactor = $this->factory->wordpoints->hook_reactor->create_and_get();
		$this->factory->wordpoints->hook_reaction->create_many( 2 );

		/** @var WordPoints_PHPUnit_Mock_Hook_Reactor $other_reactor */
		$other_reactor = $this->factory->wordpoints->hook_reactor->create_and_get(
			array( 'slug' => 'another' )
		);

		$this->factory->wordpoints->hook_reaction->create(
			array( 'reactor' => 'another' )
		);

		$extension->should_hit = false;

		$this->fire_event();

		// The extensions should have each been checked.
		$this->assertCount( 3, $extension->hit_checks );
		$this->assertCount( 0, $extension->hits );

		$this->assertCount( 0, $other_extension->hit_checks );
		$this->assertCount( 0, $other_extension->hits );

		// The reactors should have been hit.
		$this->assertCount( 0, $reactor->hits );
		$this->assertCount( 0, $other_reactor->hits );
	}

	/**
	 * Test firing an event twice will hit twice.
	 *
	 * @since 2.1.0
	 */
	public function test_fire_event_twice() {

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $extension */
		$extension = $this->factory->wordpoints->hook_extension->create_and_get();

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $other_extension */
		$other_extension = $this->factory->wordpoints->hook_extension->create_and_get(
			array( 'slug' => 'another' )
		);

		/** @var WordPoints_PHPUnit_Mock_Hook_Reactor $reactor */
		$reactor = $this->factory->wordpoints->hook_reactor->create_and_get();
		$this->factory->wordpoints->hook_reaction->create();

		$this->factory->wordpoints->entity->create();

		$this->fire_event();

		// The extensions should have been checked.
		$this->assertCount( 1, $extension->hit_checks );
		$this->assertCount( 1, $extension->hits );

		$this->assertCount( 1, $other_extension->hit_checks );
		$this->assertCount( 1, $other_extension->hits );

		// The reactor should have been hit.
		$this->assertCount( 1, $reactor->hits );

		// Fire the event again.
		$this->fire_event();

		// The extension should have been checked a second time.
		$this->assertCount( 2, $extension->hit_checks );
		$this->assertCount( 2, $extension->hits );

		$this->assertCount( 2, $other_extension->hit_checks );
		$this->assertCount( 2, $other_extension->hits );

		// The reactor should have been hit a second time.
		$this->assertCount( 2, $reactor->hits );
	}

	/**
	 * Test firing an event when one of the extensions is a miss listener.
	 *
	 * @since 2.1.0
	 */
	public function test_fire_event_with_miss_listener() {

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension_Miss_Listener $extension */
		$extension = $this->factory->wordpoints->hook_extension->create_and_get(
			array( 'class' => 'WordPoints_PHPUnit_Mock_Hook_Extension_Miss_Listener' )
		);

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $other_extension */
		$other_extension = $this->factory->wordpoints->hook_extension->create_and_get(
			array( 'slug' => 'another' )
		);

		/** @var WordPoints_PHPUnit_Mock_Hook_Reactor $reactor */
		$reactor = $this->factory->wordpoints->hook_reactor->create_and_get();
		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$this->fire_event();

		// The extensions should have each been checked.
		$this->assertCount( 1, $extension->hit_checks );
		$this->assertCount( 1, $extension->hits );
		$this->assertCount( 0, $extension->misses );

		$this->assertCount( 1, $other_extension->hit_checks );
		$this->assertCount( 1, $other_extension->hits );

		// The reactor should have been hit.
		$this->assertCount( 1, $reactor->hits );

		$this->assertHitsLogged( array( 'reaction_id' => $reaction->get_id() ) );

		// Block one of the extensions from allowing a hit this time.
		$other_extension->should_hit = false;

		$this->fire_event();

		// The extensions should not have hit again.
		$this->assertCount( 2, $extension->hit_checks );
		$this->assertCount( 1, $extension->hits );
		$this->assertCount( 1, $extension->misses );

		$this->assertCount( 2, $other_extension->hit_checks );
		$this->assertCount( 1, $other_extension->hits );

		// The reactor should not have been hit again.
		$this->assertCount( 1, $reactor->hits );

		// There should still be just one hit in the logs.
		$this->assertHitsLogged( array( 'reaction_id' => $reaction->get_id() ) );
	}

	/**
	 * Fire an event.
	 *
	 * @since 2.1.0
	 */
	public function fire_event() {

		$args = new WordPoints_Hook_Event_Args( array() );

		wordpoints_hooks()->fire( 'test_event', $args, 'test_fire' );
	}
}

// EOF
