<?php

/**
 * Test case for WordPoints_Hook_Reaction_Validator.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Reaction_Validator.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Reaction_Validator
 */
class WordPoints_Hook_Reaction_Validator_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test constructing the validator with an array of settings.
	 *
	 * @since 2.1.0
	 */
	public function test_construct_with_settings() {

		$settings = array(
			'key'     => 'value',
			'reactor' => 'test_reactor',
			'event'   => 'test_event',
		);

		$validator = new WordPoints_Hook_Reaction_Validator( $settings );

		$this->assertFalse( $validator->get_reaction() );
		$this->assertSame( $settings, $validator->get_settings() );
		$this->assertFalse( $validator->get_id() );

		$this->assertSame( 'test_event', $validator->get_event_slug() );
		$this->assertSame( 'test_reactor', $validator->get_reactor_slug() );
		$this->assertSame( 'value', $validator->get_meta( 'key' ) );
		$this->assertNull( $validator->get_meta( 'nonexistent' ) );
		$this->assertSame( $settings, $validator->get_all_meta() );
	}

	/**
	 * Test constructing the validator with a reaction object.
	 *
	 * @since 2.1.0
	 */
	public function test_construct_with_reaction() {

		$settings = array(
			'event'   => 'test_event',
			'reactor' => 'test_reactor',
			'target'  => array( 'test_entity' ),
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create_and_get(
			$settings
		);

		$validator = new WordPoints_Hook_Reaction_Validator( $reaction );

		$this->assertSame( $reaction, $validator->get_reaction() );
		$this->assertSame( $reaction->get_all_meta(), $validator->get_settings() );
		$this->assertSame( $reaction->get_id(), $validator->get_id() );

		$this->assertSame( 'test_event', $validator->get_event_slug() );
		$this->assertSame( 'test_reactor', $validator->get_reactor_slug() );
		$this->assertSame( array( 'test_entity' ), $validator->get_meta( 'target' ) );
		$this->assertNull( $validator->get_meta( 'nonexistent' ) );
		$this->assertSame( $settings, $validator->get_all_meta() );
	}

	/**
	 * Test validating a reaction.
	 *
	 * @since 2.1.0
	 */
	public function test_validate() {

		$settings = array(
			'event'   => $this->factory->wordpoints->hook_event->create(),
			'reactor' => $this->factory->wordpoints->hook_reactor->create(),
			'target'  => array( 'test_entity' ),
		);

		$mock = new WordPoints_PHPUnit_Mock_Filter();
		add_filter( 'wordpoints_hook_reaction_validate', array( $mock, 'filter' ), 10, 3 );

		$validator = new WordPoints_Hook_Reaction_Validator( $settings );

		$result = $validator->validate();

		$this->assertSame( $settings, $result );

		$event_args = $validator->get_event_args();
		$this->assertInstanceOf( 'WordPoints_Hook_Event_Args', $event_args );

		$this->assertSame( 1, $mock->call_count );
		$this->assertSame(
			array( $settings, $validator, $event_args )
			, $mock->calls[0]
		);

		$this->assertSame( array(), $validator->get_errors() );
		$this->assertFalse( $validator->had_errors() );
		$this->assertSame( array(), $validator->get_field_stack() );

		$entities = $event_args->get_entities();
		$this->assertCount( 1, $entities );
		$this->assertArrayHasKey( 'test_entity', $entities );
	}

	/**
	 * Test validating a reaction with no event specified.
	 *
	 * @since 2.1.0
	 */
	public function test_validate_no_event() {

		$settings = array(
			'reactor' => $this->factory->wordpoints->hook_reactor->create(),
			'target'  => array( 'test_entity' ),
		);

		$this->factory->wordpoints->hook_event->create();

		$mock = new WordPoints_PHPUnit_Mock_Filter();
		add_filter( 'wordpoints_hook_reaction_validate', array( $mock, 'filter' ), 10, 3 );

		$validator = new WordPoints_Hook_Reaction_Validator( $settings );

		$result = $validator->validate();

		$this->assertSame( $settings, $result );

		$this->assertSame( 0, $mock->call_count );

		$this->assertTrue( $validator->had_errors() );

		$errors = $validator->get_errors();
		$this->assertCount( 1, $errors );
		$this->assertSame( array( 'event' ), $errors[0]['field'] );

		$this->assertSame( array(), $validator->get_field_stack() );

		$this->assertNull( $validator->get_event_args() );
	}

	/**
	 * Test validating a reaction with an unregistered event.
	 *
	 * @since 2.1.0
	 */
	public function test_validate_unregistered_event() {

		$settings = array(
			'event'   => 'test_event',
			'reactor' => $this->factory->wordpoints->hook_reactor->create(),
			'target'  => array( 'test_entity' ),
		);

		$mock = new WordPoints_PHPUnit_Mock_Filter();
		add_filter( 'wordpoints_hook_reaction_validate', array( $mock, 'filter' ), 10, 3 );

		$validator = new WordPoints_Hook_Reaction_Validator( $settings );

		$result = $validator->validate();

		$this->assertSame( $settings, $result );

		$this->assertSame( 0, $mock->call_count );

		$this->assertTrue( $validator->had_errors() );

		$errors = $validator->get_errors();
		$this->assertCount( 1, $errors );
		$this->assertSame( array( 'event' ), $errors[0]['field'] );

		$this->assertSame( array(), $validator->get_field_stack() );

		$this->assertNull( $validator->get_event_args() );
	}

	/**
	 * Test validating a reaction with no reactor specified.
	 *
	 * @since 2.1.0
	 */
	public function test_validate_no_reactor() {

		$settings = array(
			'event'  => $this->factory->wordpoints->hook_event->create(),
			'target' => array( 'test_entity' ),
		);

		$this->factory->wordpoints->hook_reactor->create();

		$mock = new WordPoints_PHPUnit_Mock_Filter();
		add_filter( 'wordpoints_hook_reaction_validate', array( $mock, 'filter' ), 10, 3 );

		$validator = new WordPoints_Hook_Reaction_Validator( $settings );

		$result = $validator->validate();

		$this->assertSame( $settings, $result );

		$this->assertSame( 0, $mock->call_count );

		$this->assertTrue( $validator->had_errors() );

		$errors = $validator->get_errors();
		$this->assertCount( 1, $errors );
		$this->assertSame( array( 'reactor' ), $errors[0]['field'] );

		$this->assertSame( array(), $validator->get_field_stack() );

		$this->assertNull( $validator->get_event_args() );
	}

	/**
	 * Test validating a reaction with an unregistered reactor.
	 *
	 * @since 2.1.0
	 */
	public function test_validate_unregistered_reactor() {

		$settings = array(
			'event'   => $this->factory->wordpoints->hook_event->create(),
			'reactor' => 'test_reactor',
			'target'  => array( 'test_entity' ),
		);

		$mock = new WordPoints_PHPUnit_Mock_Filter();
		add_filter( 'wordpoints_hook_reaction_validate', array( $mock, 'filter' ), 10, 3 );

		$validator = new WordPoints_Hook_Reaction_Validator( $settings );

		$result = $validator->validate();

		$this->assertSame( $settings, $result );

		$this->assertSame( 0, $mock->call_count );

		$this->assertTrue( $validator->had_errors() );

		$errors = $validator->get_errors();
		$this->assertCount( 1, $errors );
		$this->assertSame( array( 'reactor' ), $errors[0]['field'] );

		$this->assertSame( array(), $validator->get_field_stack() );

		$this->assertNull( $validator->get_event_args() );
	}

	/**
	 * Test validating a reaction when some of the reactor settings are invalid.
	 *
	 * We use the target as an easy example.
	 *
	 * @since 2.1.0
	 */
	public function test_validate_invalid_target() {

		$settings = array(
			'reactor' => $this->factory->wordpoints->hook_reactor->create(),
			'event'   => $this->factory->wordpoints->hook_event->create(),
		);

		$mock = new WordPoints_PHPUnit_Mock_Filter();
		add_filter( 'wordpoints_hook_reaction_validate', array( $mock, 'filter' ), 10, 3 );

		$validator = new WordPoints_Hook_Reaction_Validator( $settings );

		$result = $validator->validate();

		$this->assertSame( $settings, $result );

		$event_args = $validator->get_event_args();
		$this->assertInstanceOf( 'WordPoints_Hook_Event_Args', $event_args );

		$this->assertSame( 1, $mock->call_count );
		$this->assertSame(
			array( $settings, $validator, $event_args )
			, $mock->calls[0]
		);

		$this->assertTrue( $validator->had_errors() );

		$errors = $validator->get_errors();
		$this->assertCount( 1, $errors );
		$this->assertSame( array( 'target' ), $errors[0]['field'] );

		$this->assertSame( array(), $validator->get_field_stack() );

		$entities = $event_args->get_entities();
		$this->assertCount( 1, $entities );
		$this->assertArrayHasKey( 'test_entity', $entities );
	}

	/**
	 * Test validating a reaction checks the extensions.
	 *
	 * @since 2.1.0
	 */
	public function test_validate_extension_checked() {

		$action_type = 'test_fire';

		$settings = array(
			'event'          => $this->factory->wordpoints->hook_event->create(),
			'reactor'        => $this->factory->wordpoints->hook_reactor->create(),
			'target'         => array( 'test_entity' ),
			'test_extension' => array( $action_type => array( 'key' => 'value' ) ),
		);

		$extensions = wordpoints_hooks()->get_sub_app( 'extensions' );
		$extensions->register(
			'test_extension'
			, 'WordPoints_PHPUnit_Mock_Hook_Extension'
		);

		$mock = new WordPoints_PHPUnit_Mock_Filter();
		add_filter( 'wordpoints_hook_reaction_validate', array( $mock, 'filter' ), 10, 3 );

		$validator = new WordPoints_Hook_Reaction_Validator( $settings );

		$result = $validator->validate();

		$this->assertSame( $settings, $result );

		$event_args = $validator->get_event_args();
		$this->assertInstanceOf( 'WordPoints_Hook_Event_Args', $event_args );

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $extension */
		$extension = $extensions->get( 'test_extension' );

		$this->assertCount( 1, $extension->validations );

		$this->assertSame(
			array(
				'settings'    => $settings['test_extension'][ $action_type ],
				'validator'   => $validator,
				'event_args'  => $event_args,
				'field_stack' => array( 'test_extension', $action_type ),
			)
			, $extension->validations[0]
		);

		$this->assertSame( 1, $mock->call_count );
		$this->assertSame(
			array( $settings, $validator, $event_args )
			, $mock->calls[0]
		);

		$this->assertSame( array(), $validator->get_errors() );
		$this->assertFalse( $validator->had_errors() );
		$this->assertSame( array(), $validator->get_field_stack() );

		$entities = $event_args->get_entities();
		$this->assertCount( 1, $entities );
		$this->assertArrayHasKey( 'test_entity', $entities );
	}

	/**
	 * Test validating a reaction checks the extensions.
	 *
	 * @since 2.1.0
	 */
	public function test_validate_extension_failed() {

		$action_type = 'test_fire';

		$settings = array(
			'event'          => $this->factory->wordpoints->hook_event->create(),
			'reactor'        => $this->factory->wordpoints->hook_reactor->create(),
			'target'         => array( 'test_entity' ),
			'test_extension' => array( $action_type => array( 'fail' => 'Testing.' ) ),
		);

		$extensions = wordpoints_hooks()->get_sub_app( 'extensions' );
		$extensions->register(
			'test_extension'
			, 'WordPoints_PHPUnit_Mock_Hook_Extension'
		);

		$mock = new WordPoints_PHPUnit_Mock_Filter();
		add_filter( 'wordpoints_hook_reaction_validate', array( $mock, 'filter' ), 10, 3 );

		$validator = new WordPoints_Hook_Reaction_Validator( $settings );

		$result = $validator->validate();

		// Any modifications made by the extension should be preserved.
		// (The extension returns an empty array for its settings on failure.)
		$settings['test_extension'] = array( $action_type => array() );

		$this->assertSame( $settings, $result );

		$event_args = $validator->get_event_args();
		$this->assertInstanceOf( 'WordPoints_Hook_Event_Args', $event_args );

		/** @var WordPoints_PHPUnit_Mock_Hook_Extension $extension */
		$extension = $extensions->get( 'test_extension' );

		$this->assertSame(
			array(
				'settings'    => array( 'fail' => 'Testing.' ),
				'validator'   => $validator,
				'event_args'  => $event_args,
				'field_stack' => array( 'test_extension', $action_type ),
			)
			, $extension->validations[0]
		);

		$this->assertSame( 1, $mock->call_count );
		$this->assertSame(
			array( $settings, $validator, $event_args )
			, $mock->calls[0]
		);

		$this->assertTrue( $validator->had_errors() );

		$errors = $validator->get_errors();
		$this->assertCount( 1, $errors );
		$this->assertSame(
			array( 'test_extension', $action_type, 'fail' )
			, $errors[0]['field']
		);

		$entities = $event_args->get_entities();
		$this->assertCount( 1, $entities );
		$this->assertArrayHasKey( 'test_entity', $entities );
	}

	/**
	 * Test adding an error.
	 *
	 * @since 2.1.0
	 */
	public function test_add_error() {

		$validator = new WordPoints_Hook_Reaction_Validator( array() );

		$this->assertFalse( $validator->had_errors() );

		$validator->add_error( 'Testing.' );

		$this->assertTrue( $validator->had_errors() );

		$this->assertSame(
			array( array( 'message' => 'Testing.', 'field' => array() ) )
			, $validator->get_errors()
		);
	}

	/**
	 * Test adding an error with a field stack.
	 *
	 * @since 2.1.0
	 */
	public function test_add_error_field_stack() {

		$validator = new WordPoints_Hook_Reaction_Validator( array() );

		$validator->push_field( 'test' );

		$validator->add_error( 'Testing.' );

		$this->assertSame(
			array( array( 'message' => 'Testing.', 'field' => array( 'test' ) ) )
			, $validator->get_errors()
		);
	}

	/**
	 * Test adding an error with a field.
	 *
	 * @since 2.1.0
	 */
	public function test_add_error_field() {

		$validator = new WordPoints_Hook_Reaction_Validator( array() );

		$validator->add_error( 'Testing.', 'test' );

		$this->assertSame(
			array( array( 'message' => 'Testing.', 'field' => array( 'test' ) ) )
			, $validator->get_errors()
		);
	}

	/**
	 * Test adding an error with a field when a field stack is present.
	 *
	 * @since 2.1.0
	 */
	public function test_add_error_field_and_field_stack() {

		$validator = new WordPoints_Hook_Reaction_Validator( array() );

		$validator->push_field( 'test' );

		$validator->add_error( 'Testing.', 'test_again' );

		$this->assertSame(
			array(
				array(
					'message' => 'Testing.',
					'field'   => array( 'test', 'test_again' ),
				),
			)
			, $validator->get_errors()
		);
	}

	/**
	 * Test adding an error throws an exception when failing fast.
	 *
	 * @since 2.1.0
	 *
	 * @expectedException WordPoints_Hook_Validator_Exception
	 *
	 * @throws WordPoints_Hook_Validator_Exception A validator exception.
	 */
	public function test_add_error_fail_fast() {

		$validator = new WordPoints_Hook_Reaction_Validator( array(), true );

		try {

			$validator->add_error( 'Testing.', 'test' );

		} catch ( WordPoints_Hook_Validator_Exception $e ) {

			$this->assertSame(
				array( array( 'message' => 'Testing.', 'field' => array( 'test' ) ) )
				, $validator->get_errors()
			);

			throw $e;
		}
	}

	/**
	 * Test popping, pushing, and getting the field stack.
	 *
	 * @since 2.1.0
	 */
	public function test_get_field_stack() {

		$validator = new WordPoints_Hook_Reaction_Validator( array() );

		$this->assertSame( array(), $validator->get_field_stack() );

		$validator->push_field( 'test' );

		$this->assertSame( array( 'test' ), $validator->get_field_stack() );

		$validator->push_field( 'child' );

		$this->assertSame( array( 'test', 'child' ), $validator->get_field_stack() );

		$validator->pop_field();

		$this->assertSame( array( 'test' ), $validator->get_field_stack() );

		$validator->push_field( 'b' );

		$this->assertSame( array( 'test', 'b' ), $validator->get_field_stack() );

		$validator->pop_field();

		$this->assertSame( array( 'test' ), $validator->get_field_stack() );

		$validator->pop_field();

		$this->assertSame( array(), $validator->get_field_stack() );
	}

	/**
	 * Test popping a field from the field stack when there is none.
	 *
	 * @since 2.1.0
	 */
	public function test_pop_field_no_stack() {

		$validator = new WordPoints_Hook_Reaction_Validator( array() );

		$this->assertSame( array(), $validator->get_field_stack() );

		$validator->pop_field();

		$this->assertSame( array(), $validator->get_field_stack() );
	}
}

// EOF
