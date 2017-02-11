<?php

/**
 * Test case for WordPoints_Hook_Extension_Repeat_Blocker.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Extension_Repeat_Blocker.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Extension_Repeat_Blocker
 */
class WordPoints_Hook_Extension_Repeat_Blocker_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * The slug of the extension being tested.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $extension_slug = 'repeat_blocker';

	/**
	 * The extension class being tested.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $extension_class = 'WordPoints_Hook_Extension_Repeat_Blocker';

	/**
	 * The hooks app.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hooks
	 */
	protected $hooks;

	/**
	 * The event args used in the tests.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_Event_Args
	 */
	protected $event_args;

	/**
	 * The mock extension used in the tests.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_PHPUnit_Mock_Hook_Extension
	 */
	protected $extension;

	/**
	 * The mock reactor used in the tests.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_PHPUnit_Mock_Hook_Reactor
	 */
	protected $reactor;

	/**
	 * Test validating the settings.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_valid_settings
	 *
	 * @param array $settings An array of valid settings.
	 */
	public function test_validate_settings( array $settings ) {

		/** @var WordPoints_Hook_Extension $extension */
		$extension = new $this->extension_class();
		$validator = new WordPoints_Hook_Reaction_Validator( array() );
		$event_args = new WordPoints_Hook_Event_Args( array() );
		$event_args->set_validator( $validator );

		$result = $extension->validate_settings( $settings, $validator, $event_args );

		$this->assertFalse( $validator->had_errors() );
		$this->assertSame( array(), $validator->get_field_stack() );
		$this->assertNull( $event_args->get_current() );

		$this->assertSame( $settings, $result );
	}

	/**
	 * Provides sets of valid settings for this extension.
	 *
	 * @since 2.1.0
	 *
	 * @return array The sets of valid settings.
	 */
	public function data_provider_valid_settings() {
		return array(
			'empty' => array( array() ),
			'one' => array( array( 'test_fire' => '1' ) ),
			'zero' => array( array( 'test_fire' => '0' ) ),
			'true' => array( array( 'test_fire' => true ) ),
			'false' => array( array( 'test_fire' => false ) ),
		);
	}

	/**
	 * Test checking whether an event should hit the target.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_valid_settings_should_hit
	 *
	 * @param array $settings Settings for the extension.
	 */
	public function test_should_hit( array $settings ) {

		$reaction = $this->factory->wordpoints->hook_reaction->create();
		$reaction->add_meta( $this->extension_slug, $settings );

		$event_args = new WordPoints_Hook_Event_Args( array() );

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'test_fire' );
		$fire->hit();

		/** @var WordPoints_Hook_Extension $extension */
		$extension = new $this->extension_class();

		$this->assertTrue( $extension->should_hit( $fire ) );

		$this->assertNull( $event_args->get_current() );
	}

	/**
	 * Provides sets of settings that should cause this extension to hit.
	 *
	 * @since 2.1.0
	 *
	 * @return array The sets of settings.
	 */
	public function data_provider_valid_settings_should_hit() {
		return array(
			'empty' => array( array() ),
			'false' => array( array( 'test_fire' => false ) ),
		);
	}

	/**
	 * Test checking whether an event should hit the target when it should not.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_valid_settings_should_not_hit
	 *
	 * @param array $settings Settings for the extension.
	 */
	public function test_should_not_hit( array $settings ) {

		$reaction = $this->factory->wordpoints->hook_reaction->create();
		$reaction->add_meta( $this->extension_slug, $settings );

		$event_args = new WordPoints_Hook_Event_Args( array() );

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'test_fire' );
		$fire->hit();

		/** @var WordPoints_Hook_Extension $extension */
		$extension = new $this->extension_class();

		$this->assertFalse( $extension->should_hit( $fire ) );

		$this->assertNull( $event_args->get_current() );
	}

	/**
	 * Provides sets of settings that should cause this extension to not hit.
	 *
	 * @since 2.1.0
	 *
	 * @return array The sets of settings.
	 */
	public function data_provider_valid_settings_should_not_hit() {
		return array(
			'true' => array( array( 'test_fire' => true ) ),
		);
	}

	/**
	 * Test that repeats are detected per-action type.
	 *
	 * @since 2.1.0
	 */
	public function test_repeats_detected_per_action_type() {

		$this->mock_apps();

		$hooks = wordpoints_hooks();
		$extensions = $hooks->get_sub_app( 'extensions' );
		$extensions->register( $this->extension_slug, $this->extension_class );
		$extensions->register(
			'test_extension'
			, 'WordPoints_PHPUnit_Mock_Hook_Extension'
		);

		$this->factory->wordpoints->hook_reactor->create();
		$this->factory->wordpoints->hook_reaction->create(
			array( $this->extension_slug => array( 'test_fire' => true, 'fire' => true ) )
		);

		$this->factory->wordpoints->entity->create();

		$event_args = new WordPoints_Hook_Event_Args(
			array( new WordPoints_PHPUnit_Mock_Hook_Arg( 'test_entity' ) )
		);

		$hooks->fire( 'test_event', $event_args, 'test_fire' );

		// The extension should have been checked.
		$extension = $extensions->get( 'test_extension' );

		$this->assertCount( 1, $extension->hit_checks );
		$this->assertCount( 1, $extension->hits );

		// The reactor should have been hit.
		$reactor = $hooks->get_sub_app( 'reactors' )->get( 'test_reactor' );

		$this->assertCount( 1, $reactor->hits );

		// Fire the event again.
		$hooks->fire( 'test_event', $event_args, 'test_fire' );

		// The extension should not have been checked a second time.
		$this->assertCount( 1, $extension->hit_checks );
		$this->assertCount( 1, $extension->hits );

		// The reactor should not have been hit a second time.
		$this->assertCount( 1, $reactor->hits );

		// Fire the event with a different action type.
		$hooks->fire( 'test_event', $event_args, 'fire' );

		// The extension should have been checked a second time.
		$this->assertCount( 2, $extension->hit_checks );
		$this->assertCount( 2, $extension->hits );

		// The reactor should have been hit a second time.
		$this->assertCount( 2, $reactor->hits );
	}

	/**
	 * Test that repeats are detected per-signature arg.
	 *
	 * @since 2.1.0
	 */
	public function test_repeats_detected_per_signature_arg_value() {

		$this->fire_event();

		// Modify the primary arg's value.
		$entities = $this->event_args->get_entities();
		$entities['test_entity']->set_the_value( 5 );

		$this->assertFireHitsAgain();
	}

	/**
	 * Test that repeats are detected per-signature arg.
	 *
	 * @since 2.3.0
	 */
	public function test_repeats_detected_per_signature_arg_value_multiple() {

		$this->mock_apps();

		$this->hooks = wordpoints_hooks();
		$extensions = $this->hooks->get_sub_app( 'extensions' );
		$extensions->register( $this->extension_slug, $this->extension_class );
		$extensions->register(
			'test_extension'
			, 'WordPoints_PHPUnit_Mock_Hook_Extension'
		);

		$this->factory->wordpoints->hook_reactor->create();
		$this->factory->wordpoints->hook_reaction->create(
			array( $this->extension_slug => array( 'test_fire' => true ) )
		);

		$this->factory->wordpoints->entity->create();

		$this->event_args = new WordPoints_Hook_Event_Args(
			array(
				new WordPoints_PHPUnit_Mock_Hook_Arg( 'test_entity' ),
				new WordPoints_PHPUnit_Mock_Hook_Arg( 'another:test_entity' ),
			)
		);

		$this->hooks->fire( 'test_event', $this->event_args, 'test_fire' );

		// The extension should have been checked.
		$this->extension = $extensions->get( 'test_extension' );

		$this->assertCount( 1, $this->extension->hit_checks );
		$this->assertCount( 1, $this->extension->hits );

		// The reactor should have been hit.
		$this->reactor = $this->hooks->get_sub_app( 'reactors' )->get( 'test_reactor' );

		$this->assertCount( 1, $this->reactor->hits );

		// Fire the event again.
		$this->hooks->fire( 'test_event', $this->event_args, 'test_fire' );

		// The extension should not have been checked a second time.
		$this->assertCount( 1, $this->extension->hit_checks );
		$this->assertCount( 1, $this->extension->hits );

		// The reactor should not have been hit a second time.
		$this->assertCount( 1, $this->reactor->hits );

		// Modify the second arg's value.
		$entities = $this->event_args->get_entities();
		$entities['another:test_entity']->set_the_value( 5 );

		$this->assertFireHitsAgain();
	}

	/**
	 * Test that repeats are detected per-reaction.
	 *
	 * @since 2.1.0
	 */
	public function test_repeats_detected_per_reaction() {

		$this->fire_event();

		// Create another reaction.
		$this->factory->wordpoints->hook_reaction->create();

		$this->assertFireHitsAgain();
	}

	/**
	 * Test that repeats are detected per-event.
	 *
	 * @since 2.1.0
	 */
	public function test_repeats_detected_per_event() {

		$this->fire_event();

		global $wpdb;

		// Change the event in the database.
		$wpdb->update(
			$wpdb->wordpoints_hook_hits
			, array( 'event' => 'other' )
			, array( 'id' => $wpdb->insert_id )
		);

		$this->assertFireHitsAgain();
	}

	/**
	 * Test that repeats are detected per-reactor.
	 *
	 * @since 2.1.0
	 */
	public function test_repeats_detected_per_reactor() {

		$this->fire_event();

		global $wpdb;

		// Change the event in the database.
		$wpdb->update(
			$wpdb->wordpoints_hook_hits
			, array( 'reactor' => 'other' )
			, array( 'id' => $wpdb->insert_id )
		);

		$this->assertFireHitsAgain();
	}

	/**
	 * Test that repeats are detected per-reaction store.
	 *
	 * @since 2.1.0
	 */
	public function test_repeats_detected_per_reaction_store() {

		$this->fire_event();

		global $wpdb;

		// Change the event in the database.
		$wpdb->update(
			$wpdb->wordpoints_hook_hits
			, array( 'reaction_store' => 'other' )
			, array( 'id' => $wpdb->insert_id )
		);

		$this->assertFireHitsAgain();
	}

	/**
	 * Test that repeats are detected per-reaction context ID.
	 *
	 * @since 2.1.0
	 */
	public function test_repeats_detected_per_reaction_context_id() {

		$this->fire_event();

		global $wpdb;

		// Change the event in the database.
		$wpdb->update(
			$wpdb->wordpoints_hook_hits
			, array( 'reaction_context_id' => 'other' )
			, array( 'id' => $wpdb->insert_id )
		);

		$this->assertFireHitsAgain();
	}

	//
	// Helpers
	//

	/**
	 * Fire an event.
	 *
	 * @since 2.1.0
	 */
	protected function fire_event() {

		$this->mock_apps();

		$this->hooks = wordpoints_hooks();
		$extensions = $this->hooks->get_sub_app( 'extensions' );
		$extensions->register( $this->extension_slug, $this->extension_class );
		$extensions->register(
			'test_extension'
			, 'WordPoints_PHPUnit_Mock_Hook_Extension'
		);

		$this->factory->wordpoints->hook_reactor->create();
		$this->factory->wordpoints->hook_reaction->create(
			array( $this->extension_slug => array( 'test_fire' => true ) )
		);

		$this->factory->wordpoints->entity->create();

		$this->event_args = new WordPoints_Hook_Event_Args(
			array( new WordPoints_PHPUnit_Mock_Hook_Arg( 'test_entity' ) )
		);

		$this->hooks->fire( 'test_event', $this->event_args, 'test_fire' );

		// The extension should have been checked.
		$this->extension = $extensions->get( 'test_extension' );

		$this->assertCount( 1, $this->extension->hit_checks );
		$this->assertCount( 1, $this->extension->hits );

		// The reactor should have been hit.
		$this->reactor = $this->hooks->get_sub_app( 'reactors' )->get( 'test_reactor' );

		$this->assertCount( 1, $this->reactor->hits );

		// Fire the event again.
		$this->hooks->fire( 'test_event', $this->event_args, 'test_fire' );

		// The extension should not have been checked a second time.
		$this->assertCount( 1, $this->extension->hit_checks );
		$this->assertCount( 1, $this->extension->hits );

		// The reactor should not have been hit a second time.
		$this->assertCount( 1, $this->reactor->hits );
	}

	/**
	 * Assert that firing the event again will lead to a second hit.
	 *
	 * @since 2.1.0
	 */
	protected function assertFireHitsAgain() {

		// Fire the event again.
		$this->hooks->fire( 'test_event', $this->event_args, 'test_fire' );

		// The extension should have been checked a second time.
		$this->assertCount( 2, $this->extension->hit_checks );
		$this->assertCount( 2, $this->extension->hits );

		// The reactor should have been hit a second time.
		$this->assertCount( 2, $this->reactor->hits );
	}
}

// EOF
