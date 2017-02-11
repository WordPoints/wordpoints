<?php

/**
 * Test case for WordPoints_Hook_Condition_String_Contains.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Condition_String_Contains.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Condition_String_Contains
 */
class WordPoints_Hook_Condition_String_Contains_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test checking if the condition is met.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_values_is_met
	 *
	 * @param string $setting The string expected to be contained.
	 * @param string $value   A value.
	 */
	public function test_is_met( $setting, $value ) {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test_entity' );
		$entity->set_the_value( $value );

		$reactor = new WordPoints_PHPUnit_Mock_Hook_Reactor();
		$validator = new WordPoints_Hook_Reaction_Validator( array() );

		$event_args = new WordPoints_Hook_Event_Args( array() );
		$event_args->set_validator( $validator );
		$event_args->add_entity( $entity );
		$event_args->descend( $entity->get_slug() );

		$condition = new WordPoints_Hook_Condition_String_Contains();

		$this->assertTrue(
			$condition->is_met( array( 'value' => $setting ), $event_args )
		);

		$this->assertSame( $entity, $event_args->get_current() );

		$this->assertSame(
			array( $entity->get_slug() )
			, $validator->get_field_stack()
		);
	}

	/**
	 * Provides different values.
	 *
	 * @since 2.1.0
	 *
	 * @return array Possible values.
	 */
	public function data_provider_values_is_met() {

		return array(
			'same' => array( 'Test', 'Test' ),
			'start' => array( 'Test', 'Testing' ),
			'middle' => array( 'Test', 'A Test.' ),
			'end' => array( 'test', 'attest' ),
		);
	}

	/**
	 * Test checking if the condition is met when its not.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_values_not_met
	 *
	 * @param string $setting The string expected to be contained.
	 * @param string $value   A value.
	 */
	public function test_is_met_not( $setting, $value ) {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test_entity' );
		$entity->set_the_value( $value );

		$reactor = new WordPoints_PHPUnit_Mock_Hook_Reactor();
		$validator = new WordPoints_Hook_Reaction_Validator( array() );

		$event_args = new WordPoints_Hook_Event_Args( array() );
		$event_args->set_validator( $validator );
		$event_args->add_entity( $entity );
		$event_args->descend( $entity->get_slug() );

		$condition = new WordPoints_Hook_Condition_Equals();

		$this->assertFalse(
			$condition->is_met( array( 'value' => $setting ), $event_args )
		);

		$this->assertSame( $entity, $event_args->get_current() );

		$this->assertSame(
			array( $entity->get_slug() )
			, $validator->get_field_stack()
		);
	}

	/**
	 * Provides different values that should cause the value not to be met.
	 *
	 * @since 2.1.0
	 *
	 * @return array Possible values.
	 */
	public function data_provider_values_not_met() {

		return array(
			'case_sensitive' => array( 'Test', 'test' ),
			'similar' => array( 'Test', 'T esting' ),
		);
	}
}

// EOF
