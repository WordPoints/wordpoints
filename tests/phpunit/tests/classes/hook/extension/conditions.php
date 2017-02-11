<?php

/**
 * Test case for WordPoints_Hook_Extension_Conditions.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Extension_Conditions.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Extension_Conditions
 */
class WordPoints_Hook_Extension_Conditions_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test validating the settings.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_valid_condition_settings
	 *
	 * @param array $settings An array of valid settings.
	 */
	public function test_validate_settings( array $settings ) {

		$this->mock_apps();

		wordpoints_entities()->get_sub_app( 'children' )->register(
			'test_entity'
			, 'child'
			, 'WordPoints_PHPUnit_Mock_Entity_Attr'
		);

		$this->factory->wordpoints->hook_condition->create(
			array( 'slug' => 'test', 'data_type' => 'entity' )
		);

		$this->factory->wordpoints->hook_condition->create(
			array( 'slug' => 'test', 'data_type' => 'text' )
		);

		$extension = new WordPoints_Hook_Extension_Conditions();
		$validator = new WordPoints_Hook_Reaction_Validator( array() );
		$event_args = new WordPoints_Hook_Event_Args( array() );

		$event_args->add_entity(
			new WordPoints_PHPUnit_Mock_Entity( 'test_entity' )
		);

		$event_args->add_entity(
			new WordPoints_PHPUnit_Mock_Entity( 'another' )
		);

		$event_args->set_validator( $validator );

		$result = $extension->validate_settings( $settings, $validator, $event_args );

		$this->assertFalse( $validator->had_errors() );
		$this->assertEmpty( $validator->get_field_stack() );
		$this->assertNull( $event_args->get_current() );

		$this->assertSame( $settings, $result );
	}

	/**
	 * Test validating the settings when the condition also validates some sub
	 * -conditions.
	 *
	 * Previously when sub-conditions were being validated the event args object
	 * would get reset, resulting in issues including _doing_it_wrong() notices from
	 * ascend() being called when at the top of the (inadvertently reset) hierarchy.
	 *
	 * @since 2.1.0
	 */
	public function test_validate_settings_sub_conditions() {

		$settings = array(
			'conditions' => array(
				'fire' => array(
					'user' => array(
						'roles' => array(
							'user_role{}' => array(
								'_conditions' => array(
									array(
										'type' => 'contains',
										'settings' => array(
											'min' => 1,
											'conditions' => array(
												'user_role' => array(
													'_conditions' => array(
														array(
															'type' => 'equals',
															'settings' => array(
																'value' => 'administrator',
															),
														),
													),
												),
											),
										),
									),
								),
							),
						),
					),
				),
			),
		);

		$extension = wordpoints_hooks()->get_sub_app( 'extensions' )->get( 'conditions' );

		$validator = new WordPoints_Hook_Reaction_Validator( array() );
		$event_args = new WordPoints_Hook_Event_Args( array() );
		$event_args->add_entity( wordpoints_entities()->get( 'user' ) );
		$event_args->set_validator( $validator );

		$result = $extension->validate_settings( $settings, $validator, $event_args );

		$this->assertFalse( $validator->had_errors() );
		$this->assertEmpty( $validator->get_field_stack() );
		$this->assertNull( $event_args->get_current() );

		$this->assertSame( $settings, $result );
	}

	/**
	 * Test validating the settings when they are invalid.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_invalid_condition_settings
	 *
	 * @param array  $settings The settings, with one invalid or missing.
	 * @param string $invalid  The slug of the setting that is invalid or missing.
	 */
	public function test_validate_settings_invalid( array $settings, $invalid ) {

		$this->mock_apps();

		wordpoints_entities()->get_sub_app( 'children' )->register(
			'test_entity'
			, 'child'
			, 'WordPoints_PHPUnit_Mock_Entity_Child'
		);

		$this->factory->wordpoints->hook_condition->create(
			array( 'slug' => 'test', 'data_type' => 'entity' )
		);

		$this->factory->wordpoints->hook_condition->create(
			array( 'data_type' => 'text' )
		);

		$extension = new WordPoints_Hook_Extension_Conditions();
		$validator = new WordPoints_Hook_Reaction_Validator( array() );
		$event_args = new WordPoints_Hook_Event_Args( array() );

		$event_args->add_entity(
			new WordPoints_PHPUnit_Mock_Entity( 'test_entity' )
		);

		$event_args->set_validator( $validator );

		$result = $extension->validate_settings( $settings, $validator, $event_args );

		$this->assertTrue( $validator->had_errors() );

		$errors = $validator->get_errors();

		$this->assertCount( 1, $errors );
		$this->assertSame( $invalid, $errors[0]['field'] );

		$this->assertEmpty( $validator->get_field_stack() );
		$this->assertNull( $event_args->get_current() );

		if ( is_array( $settings['conditions']['test_fire'] ) ) {
			$this->assertSame( $settings, $result );
		} else {
			$this->assertSame( array(), $result['conditions']['test_fire'] );
		}
	}

	/**
	 * Test checking whether an event should hit the target.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_valid_condition_settings
	 *
	 * @param array $settings Reaction settings.
	 */
	public function test_should_hit( array $settings ) {

		$this->mock_apps();

		$extensions = wordpoints_hooks()->get_sub_app( 'extensions' );
		$extensions->register( 'conditions', 'WordPoints_Hook_Extension_Conditions' );
		$extension = $extensions->get( 'conditions' );

		wordpoints_hooks()->get_sub_app( 'events' )->get_sub_app( 'args' )->register(
			'test_event'
			, 'another'
			, 'WordPoints_PHPUnit_Mock_Hook_Arg'
		);

		$entities = wordpoints_entities();
		$entities->register( 'another', 'WordPoints_PHPUnit_Mock_Entity' );
		$entities->get_sub_app( 'children' )->register(
			'test_entity'
			, 'child'
			, 'WordPoints_PHPUnit_Mock_Entity_Attr'
		);

		$this->factory->wordpoints->hook_condition->create(
			array( 'slug' => 'test', 'data_type' => 'entity' )
		);

		$this->factory->wordpoints->hook_condition->create(
			array( 'slug' => 'test', 'data_type' => 'text' )
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create( $settings );
		$this->assertIsReaction( $reaction );

		$event_args = new WordPoints_Hook_Event_Args( array() );

		$event_args->add_entity(
			new WordPoints_PHPUnit_Mock_Entity( 'test_entity' )
		);

		$event_args->add_entity(
			new WordPoints_PHPUnit_Mock_Entity( 'another' )
		);

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'test_fire' );

		$this->assertTrue( $extension->should_hit( $fire ) );

		$this->assertNull( $event_args->get_current() );
	}

	/**
	 * Test checking whether an event should hit the target.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_unmet_conditions
	 *
	 * @param array $settings Reaction settings.
	 */
	public function test_should_hit_not( array $settings ) {

		$this->mock_apps();

		$extensions = wordpoints_hooks()->get_sub_app( 'extensions' );
		$extensions->register( 'conditions', 'WordPoints_Hook_Extension_Conditions' );
		$extension = $extensions->get( 'conditions' );

		wordpoints_entities()->get_sub_app( 'children' )->register(
			'test_entity'
			, 'child'
			, 'WordPoints_PHPUnit_Mock_Entity_Attr'
		);

		$this->factory->wordpoints->hook_condition->create(
			array( 'slug' => 'unmet', 'data_type' => 'text' )
		);

		$this->factory->wordpoints->hook_condition->create(
			array( 'slug' => 'unmet', 'data_type' => 'entity' )
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create( $settings );
		$this->assertIsReaction( $reaction );

		$event_args = new WordPoints_Hook_Event_Args( array() );

		$event_args->add_entity(
			new WordPoints_PHPUnit_Mock_Entity( 'test_entity' )
		);

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'test_fire' );

		$this->assertFalse( $extension->should_hit( $fire ) );

		$this->assertNull( $event_args->get_current() );
	}
}

// EOF
