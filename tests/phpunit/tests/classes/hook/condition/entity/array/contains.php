<?php

/**
 * Test case for WordPoints_Hook_Condition_Entity_Array_Contains.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Condition_Entity_Array_Contains.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Condition_Entity_Array_Contains
 */
class WordPoints_Hook_Condition_Entity_Array_Contains_Test
	extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test checking if the condition settings are valid.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_valid_settings
	 *
	 * @param string $settings The valid settings.
	 */
	public function test_validate_settings( $settings ) {

		$this->factory->wordpoints->entity->create();

		$entities = wordpoints_entities();

		$entities->get_sub_app( 'children' )->register(
			'test_entity'
			, 'child'
			, 'WordPoints_PHPUnit_Mock_Entity_Attr'
		);

		wordpoints_hooks()->get_sub_app( 'extensions' )->register(
			'conditions'
			, 'WordPoints_Hook_Extension_Conditions'
		);

		$this->factory->wordpoints->hook_condition->create(
			array( 'slug' => 'test', 'data_type' => 'entity' )
		);

		$this->factory->wordpoints->hook_condition->create(
			array( 'slug' => 'test', 'data_type' => 'text' )
		);

		$validator = new WordPoints_Hook_Reaction_Validator( array() );
		$event_args = new WordPoints_Hook_Event_Args( array() );
		$event_args->set_validator( $validator );
		$event_args->add_entity( $entities->get( 'test_entity' ) );

		$condition = new WordPoints_Hook_Condition_Entity_Array_Contains();

		$validated_settings = $condition->validate_settings(
			new WordPoints_Entity_Array( 'test_entity' )
			, $settings
			, $validator
		);

		$this->assertEquals( $settings, $validated_settings );

		$this->assertFalse( $validator->had_errors() );
		$this->assertEmpty( $validator->get_field_stack() );
		$this->assertNull( $event_args->get_current() );
	}

	/**
	 * Provides different sets of valid settings.
	 *
	 * @since 2.1.0
	 *
	 * @return array Possible settings.
	 */
	public function data_provider_valid_settings() {

		$return = $max_and_min = array(
			'empty' => array( array() ),
			'max_only' => array( array( 'max' => 4 ) ),
			'min_only' => array( array( 'min' => 1 ) ),
			'max_zero' => array( array( 'max' => 0 ) ),
			'min_zero' => array( array( 'min' => 0 ) ),
			'max_and_min' => array( array( 'min' => 1, 'max' => 4 ) ),
			'max_equals_min' => array( array( 'min' => 2, 'max' => 2 ) ),
		);

		$conditions = parent::data_provider_valid_condition_settings();

		unset( $conditions['none'], $conditions['two_entities'] );

		foreach ( $conditions as $key => $value ) {
			$return[ "conditions_{$key}" ] = array(
				array( 'conditions' => $value[0]['conditions']['test_fire'] ),
			);
		}

		return $return;
	}

	/**
	 * Test checking if the condition settings are valid when they aren't.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_invalid_settings
	 *
	 * @param array $settings     The invalid settings.
	 * @param array $invalid      The invalid field.
	 * @param bool  $expect_false Whether to expect the invalid max/min to be false.
	 */
	public function test_validate_settings_invalid( $settings, $invalid, $expect_false = false ) {

		$this->factory->wordpoints->entity->create();

		$entities = wordpoints_entities();

		$entities->get_sub_app( 'children' )->register(
			'test_entity'
			, 'child'
			, 'WordPoints_PHPUnit_Mock_Entity_Child'
		);

		wordpoints_hooks()->get_sub_app( 'extensions' )->register(
			'conditions'
			, 'WordPoints_Hook_Extension_Conditions'
		);

		$this->factory->wordpoints->hook_condition->create(
			array( 'slug' => 'test', 'data_type' => 'entity' )
		);

		$this->factory->wordpoints->hook_condition->create(
			array( 'data_type' => 'text' )
		);

		$validator = new WordPoints_Hook_Reaction_Validator( array() );
		$event_args = new WordPoints_Hook_Event_Args( array() );
		$event_args->set_validator( $validator );
		$event_args->add_entity( $entities->get( 'test_entity' ) );

		$condition = new WordPoints_Hook_Condition_Entity_Array_Contains();

		$validated_settings = $condition->validate_settings(
			new WordPoints_Entity_Array( 'test_entity' )
			, $settings
			, $validator
		);

		if ( ! isset( $settings['conditions'] ) || is_array( $settings['conditions'] ) ) {
			if ( $expect_false ) {
				$settings[ $invalid[0] ] = false;
			}

			$this->assertEquals( $settings, $validated_settings );
		} else {
			$this->assertSame( array(), $validated_settings['conditions'] );
		}

		$errors = $validator->get_errors();

		$this->assertCount( 1, $errors );
		$this->assertEquals( $invalid, $errors[0]['field'] );

		$this->assertEmpty( $validator->get_field_stack() );
		$this->assertNull( $event_args->get_current() );
	}

	/**
	 * Provides different sets of invalid settings.
	 *
	 * @since 2.1.0
	 *
	 * @return array Invalid settings.
	 */
	public function data_provider_invalid_settings() {

		$return = array(
			'invalid_max' => array( array( 'max' => -3 ), array( 'max' ), true ),
			'invalid_min' => array( array( 'min' => -1 ), array( 'min' ), true ),
			'max_less_than_min' => array( array( 'min' => 3, 'max' => 1 ), array( 'min' ) ),
		);

		$conditions = parent::data_provider_invalid_condition_settings();

		foreach ( $conditions as $key => $value ) {

			unset( $value[1][1] );

			$return[ "conditions_{$key}" ] = array(
				array( 'conditions' => $value[0]['conditions']['test_fire'] ),
				array_values( $value[1] ),
			);
		}

		return $return;
	}

	/**
	 * Test checking if the condition is met.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_valid_settings
	 *
	 * @param string $settings The settings for the condition.
	 */
	public function test_is_met( $settings ) {

		$this->factory->wordpoints->entity->create();

		$entities = wordpoints_entities();
		$children = $entities->get_sub_app( 'children' );

		$children->register(
			'test_entity'
			, 'child'
			, 'WordPoints_PHPUnit_Mock_Entity_Attr'
		);

		$children->register(
			'test_entity'
			, 'relationship'
			, 'WordPoints_PHPUnit_Mock_Entity_Relationship_Array'
		);

		wordpoints_hooks()->get_sub_app( 'extensions' )->register(
			'conditions'
			, 'WordPoints_Hook_Extension_Conditions'
		);

		$this->factory->wordpoints->hook_condition->create(
			array( 'slug' => 'test', 'data_type' => 'entity' )
		);

		$this->factory->wordpoints->hook_condition->create(
			array( 'slug' => 'test', 'data_type' => 'text' )
		);

		$validator = new WordPoints_Hook_Reaction_Validator( array() );
		$event_args = new WordPoints_Hook_Event_Args( array() );
		$event_args->set_validator( $validator );
		$event_args->add_entity( $entities->get( 'test_entity' ) );
		$event_args->descend( 'test_entity' );
		$event_args->descend( 'relationship' );
		$event_args->descend( 'test_entity{}' );

		$current = $event_args->get_current();

		if ( array( 'max' => 0 ) === $settings ) {
			$current->set_the_value( array() );
		} else {
			$current->set_the_value( array( 2, 445 ) );
		}

		$condition = new WordPoints_Hook_Condition_Entity_Array_Contains();

		$this->assertTrue( $condition->is_met( $settings, $event_args ) );

		$this->assertEquals(
			array( 'test_entity', 'relationship', 'test_entity{}' )
			, $validator->get_field_stack()
		);

		$this->assertEquals( $current, $event_args->get_current() );
	}

	/**
	 * Test checking if the condition is met when its not.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_unmet_settings
	 *
	 * @param string $settings The settings for the condition.
	 */
	public function test_is_met_not( $settings ) {

		$this->factory->wordpoints->entity->create();

		$entities = wordpoints_entities();
		$children = $entities->get_sub_app( 'children' );

		$children->register(
			'test_entity'
			, 'child'
			, 'WordPoints_PHPUnit_Mock_Entity_Attr'
		);

		$children->register(
			'test_entity'
			, 'relationship'
			, 'WordPoints_PHPUnit_Mock_Entity_Relationship_Array'
		);

		wordpoints_hooks()->get_sub_app( 'extensions' )->register(
			'conditions'
			, 'WordPoints_Hook_Extension_Conditions'
		);

		$this->factory->wordpoints->hook_condition->create(
			array( 'slug' => 'unmet', 'data_type' => 'text' )
		);

		$this->factory->wordpoints->hook_condition->create(
			array( 'slug' => 'unmet', 'data_type' => 'entity' )
		);

		$this->factory->wordpoints->hook_condition->create(
			array( 'slug' => 'test', 'data_type' => 'entity' )
		);

		$this->factory->wordpoints->hook_condition->create(
			array( 'slug' => 'test', 'data_type' => 'text' )
		);

		$validator = new WordPoints_Hook_Reaction_Validator( array() );
		$event_args = new WordPoints_Hook_Event_Args( array() );
		$event_args->set_validator( $validator );
		$event_args->add_entity( $entities->get( 'test_entity' ) );
		$event_args->descend( 'test_entity' );
		$event_args->descend( 'relationship' );
		$event_args->descend( 'test_entity{}' );

		$current = $event_args->get_current();
		$current->set_the_value( array( 2, 445 ) );

		$condition = new WordPoints_Hook_Condition_Entity_Array_Contains();

		$this->assertFalse( $condition->is_met( $settings, $event_args ) );

		$this->assertEquals(
			array( 'test_entity', 'relationship', 'test_entity{}' )
			, $validator->get_field_stack()
		);

		$this->assertEquals( $current, $event_args->get_current() );
	}

	/**
	 * Provides different values that should cause the value not to be met.
	 *
	 * @since 2.1.0
	 *
	 * @return array Possible values.
	 */
	public function data_provider_unmet_settings() {

		$return = array(
			'max_to_low' => array( array( 'max' => 1 ) ),
			'min_to_high' => array( array( 'min' => 3 ) ),
		);

		$conditions = parent::data_provider_unmet_conditions();

		foreach ( $conditions as $key => $value ) {

			$return[ "conditions_{$key}" ] = array(
				array( 'conditions' => $value[0]['conditions']['test_fire'] ),
			);

			$return[ "conditions_{$key}" ][0]['min'] = 1;
		}

		return $return;
	}
}

// EOF
