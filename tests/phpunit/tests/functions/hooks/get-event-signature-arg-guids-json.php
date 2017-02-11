<?php

/**
 * Test case for wordpoints_hooks_get_event_signature_arg_guids_json().
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.3.0
 */

/**
 * Tests wordpoints_hooks_get_event_signature_arg_guids_json().
 *
 * @since 2.3.0
 *
 * @covers ::wordpoints_hooks_get_event_signature_arg_guids_json
 */
class Wordpoints_Hooks_Get_Event_Signature_Arg_GUIDs_JSON_Function_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test that it returns the GUID serialized as JSON.
	 *
	 * @since 2.3.0
	 */
	public function test_returns_json_guid() {

		$entity_slug = $this->factory->wordpoints->entity->create();

		$arg = new WordPoints_Hook_Arg( $entity_slug );

		$event_args = new WordPoints_Hook_Event_Args( array( $arg ) );
		$event_args->get_from_hierarchy( array( $entity_slug ) )->set_the_value( 5 );

		$guid = wordpoints_hooks_get_event_signature_arg_guids_json( $event_args );

		$this->assertSame( '{"test_entity":5,"test_context":1}', $guid );
	}

	/**
	 * Test that it returns an empty string if the event doesn't have a primary arg.
	 *
	 * @since 2.3.0
	 */
	public function test_no_signature_args() {

		$entity_slug = $this->factory->wordpoints->entity->create();

		$arg = new WordPoints_PHPUnit_Mock_Hook_Arg( $entity_slug );
		$arg->get_entity()->set_the_value( 5 );
		$arg->is_stateful = true;

		$event_args = new WordPoints_Hook_Event_Args( array( $arg ) );

		$guid = wordpoints_hooks_get_event_signature_arg_guids_json( $event_args );

		$this->assertSame( '', $guid );
	}

	/**
	 * Test that it returns the GUIDs serialized as JSON.
	 *
	 * @since 2.3.0
	 */
	public function test_multiple_signature_args() {

		$entity_slug = $this->factory->wordpoints->entity->create();

		$arg = new WordPoints_PHPUnit_Mock_Hook_Arg( $entity_slug );
		$arg_2 = new WordPoints_PHPUnit_Mock_Hook_Arg( 'another:' . $entity_slug );
		$arg->value = 5;
		$arg_2->value = 7;

		$event_args = new WordPoints_Hook_Event_Args( array( $arg, $arg_2 ) );

		$guids = wordpoints_hooks_get_event_signature_arg_guids_json( $event_args );

		// Note that the args have also been sorted.
		$this->assertSame(
			'{"another:test_entity":{"test_entity":7,"test_context":1},"test_entity":{"test_entity":5,"test_context":1}}'
			, $guids
		);
	}

	/**
	 * Test that it returns an empty string if the entity GUID isn't set.
	 *
	 * @since 2.3.0
	 */
	public function test_entity_guid_not_set() {

		$entity_slug = $this->factory->wordpoints->entity->create();

		wordpoints_entities()->get_sub_app( 'contexts' )->register(
			'test_context'
			, 'WordPoints_PHPUnit_Mock_Entity_Context_OutOfState'
		);

		$arg = new WordPoints_PHPUnit_Mock_Hook_Arg( $entity_slug );
		$arg->get_entity()->set_the_value( 5 );
		$arg->is_stateful = true;

		$event_args = new WordPoints_Hook_Event_Args( array( $arg ) );

		$guid = wordpoints_hooks_get_event_signature_arg_guids_json( $event_args );

		$this->assertSame( '', $guid );
	}
}

// EOF
