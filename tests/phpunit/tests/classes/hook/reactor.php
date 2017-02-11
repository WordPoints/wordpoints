<?php

/**
 * Test case for WordPoints_Hook_Reactor.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Reactor.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Reactor
 */
class WordPoints_Hook_Reactor_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test getting the slug.
	 *
	 * @since 2.1.0
	 */
	public function test_get_slug() {

		$reactor = new WordPoints_PHPUnit_Mock_Hook_Reactor();

		$reactor->slug = 'test_reactor';

		$this->assertSame( 'test_reactor', $reactor->get_slug() );
	}

	/**
	 * Test getting the arg types.
	 *
	 * @since 2.1.0
	 */
	public function test_get_arg_types() {

		$reactor = new WordPoints_PHPUnit_Mock_Hook_Reactor();

		$reactor->arg_types = array( 'test_entity', 'another' );

		$this->assertSame( $reactor->arg_types, $reactor->get_arg_types() );
	}

	/**
	 * Test getting the arg types returns an array even when the value is a string.
	 *
	 * @since 2.1.0
	 */
	public function test_get_arg_types_string() {

		$reactor = new WordPoints_PHPUnit_Mock_Hook_Reactor();

		$reactor->arg_types = 'test_entity';

		$this->assertSame(
			array( $reactor->arg_types )
			, $reactor->get_arg_types()
		);
	}

	/**
	 * Test getting the reactor context.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPoints !network-active
	 */
	public function test_get_context() {

		$reactor = new WordPoints_PHPUnit_Mock_Hook_Reactor;

		$this->assertSame( 'site', $reactor->get_context() );
	}

	/**
	 * Test getting the reactor context when WordPoints is network active.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_get_context_network_active() {

		$reactor = new WordPoints_PHPUnit_Mock_Hook_Reactor;

		$this->assertSame( 'network', $reactor->get_context() );
	}

	/**
	 * Test getting the settings fields.
	 *
	 * @since 2.1.0
	 */
	public function test_get_settings_fields() {

		$reactor = new WordPoints_PHPUnit_Mock_Hook_Reactor();

		$reactor->settings_fields = array( 'field' => array() );

		$this->assertSame(
			$reactor->settings_fields
			, $reactor->get_settings_fields()
		);
	}

	/**
	 * Test validating the settings.
	 *
	 * @since 2.1.0
	 */
	public function test_validate_settings() {

		$reactor = new WordPoints_PHPUnit_Mock_Hook_Reactor();
		$validator = new WordPoints_Hook_Reaction_Validator( array() );
		$event_args = new WordPoints_Hook_Event_Args( array() );

		$event_args->add_entity(
			new WordPoints_PHPUnit_Mock_Entity( 'test_entity' )
		);

		$event_args->set_validator( $validator );

		$settings = array(
			'target' => array( 'test_entity' ),
			'key' => 'value',
		);

		$result = $reactor->validate_settings( $settings, $validator, $event_args );

		$this->assertFalse( $validator->had_errors() );
		$this->assertSame( array(), $validator->get_field_stack() );
		$this->assertNull( $event_args->get_current() );

		$this->assertSame( $settings, $result );
	}

	/**
	 * Test validating the settings when the target isn't set.
	 *
	 * @since 2.1.0
	 */
	public function test_validate_settings_target_not_set() {

		$reactor = new WordPoints_PHPUnit_Mock_Hook_Reactor();
		$validator = new WordPoints_Hook_Reaction_Validator( array() );
		$event_args = new WordPoints_Hook_Event_Args( array() );

		$event_args->add_entity(
			new WordPoints_PHPUnit_Mock_Entity( 'test_entity' )
		);

		$event_args->set_validator( $validator );

		$settings = array(
			'key' => 'value',
		);

		$result = $reactor->validate_settings( $settings, $validator, $event_args );

		$this->assertTrue( $validator->had_errors() );

		$errors = $validator->get_errors();

		$this->assertCount( 1, $errors );
		$this->assertSame( array( 'target' ), $errors[0]['field'] );

		$this->assertSame( array(), $validator->get_field_stack() );
		$this->assertNull( $event_args->get_current() );

		$this->assertSame( $settings, $result );
	}

	/**
	 * Test validating the settings when the target isn't an array.
	 *
	 * @since 2.1.0
	 */
	public function test_validate_settings_target_not_array() {

		$reactor = new WordPoints_PHPUnit_Mock_Hook_Reactor();
		$validator = new WordPoints_Hook_Reaction_Validator( array() );
		$event_args = new WordPoints_Hook_Event_Args( array() );

		$event_args->add_entity(
			new WordPoints_PHPUnit_Mock_Entity( 'test_entity' )
		);

		$event_args->set_validator( $validator );

		$settings = array(
			'target' => 'test_entity',
			'key' => 'value',
		);

		$result = $reactor->validate_settings( $settings, $validator, $event_args );

		$this->assertTrue( $validator->had_errors() );

		$errors = $validator->get_errors();

		$this->assertCount( 1, $errors );
		$this->assertSame( array( 'target' ), $errors[0]['field'] );

		$this->assertSame( array(), $validator->get_field_stack() );
		$this->assertNull( $event_args->get_current() );

		$this->assertSame( $settings, $result );
	}

	/**
	 * Test validating the settings when the target isn't a valid hierarchy.
	 *
	 * @since 2.1.0
	 */
	public function test_validate_settings_target_not_valid_hierarchy() {

		$reactor = new WordPoints_PHPUnit_Mock_Hook_Reactor();
		$validator = new WordPoints_Hook_Reaction_Validator( array() );
		$event_args = new WordPoints_Hook_Event_Args( array() );

		$event_args->add_entity(
			new WordPoints_PHPUnit_Mock_Entity( 'test_entity' )
		);

		$event_args->set_validator( $validator );

		$settings = array(
			'target' => array( 'another_entity' ),
			'key' => 'value',
		);

		$result = $reactor->validate_settings( $settings, $validator, $event_args );

		$this->assertTrue( $validator->had_errors() );

		$errors = $validator->get_errors();

		$this->assertCount( 2, $errors );
		$this->assertSame( array(), $errors[0]['field'] );
		$this->assertSame( array( 'target' ), $errors[1]['field'] );

		$this->assertSame( array(), $validator->get_field_stack() );
		$this->assertNull( $event_args->get_current() );

		$this->assertSame( $settings, $result );
	}

	/**
	 * Test validating the settings when the target isn't of the correct type(s).
	 *
	 * @since 2.1.0
	 */
	public function test_validate_settings_target_not_correct_type() {

		$reactor = new WordPoints_PHPUnit_Mock_Hook_Reactor();
		$validator = new WordPoints_Hook_Reaction_Validator( array() );
		$event_args = new WordPoints_Hook_Event_Args( array() );

		$event_args->add_entity(
			new WordPoints_PHPUnit_Mock_Entity( 'another_entity' )
		);

		$event_args->set_validator( $validator );

		$settings = array(
			'target' => array( 'another_entity' ),
			'key' => 'value',
		);

		$result = $reactor->validate_settings( $settings, $validator, $event_args );

		$this->assertTrue( $validator->had_errors() );

		$errors = $validator->get_errors();

		$this->assertCount( 1, $errors );
		$this->assertSame( array( 'target' ), $errors[0]['field'] );

		$this->assertSame( array(), $validator->get_field_stack() );
		$this->assertNull( $event_args->get_current() );

		$this->assertSame( $settings, $result );
	}

	/**
	 * Test updating the settings updates the target.
	 *
	 * @since 2.1.0
	 */
	public function test_update_settings() {

		$reactor = $this->factory->wordpoints->hook_reactor->create_and_get();
		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$this->assertSame(
			array( 'test_entity' )
			, $reaction->get_meta( 'target' )
		);

		$settings = array(
			'target' => array( 'another_entity' ),
			'key' => 'value',
		);

		$reactor->update_settings( $reaction, $settings );

		$this->assertSame(
			$settings['target']
			, $reaction->get_meta( 'target' )
		);
	}
}

// EOF
