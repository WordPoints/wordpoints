<?php

/**
 * Test case for WordPoints_Hook_Condition_Number_Less_Than.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.3.0
 */

/**
 * Tests WordPoints_Hook_Condition_Number_Less_Than.
 *
 * @since 2.3.0
 *
 * @covers WordPoints_Hook_Condition_Number_Less_Than
 */
class WordPoints_Hook_Condition_Number_Less_Than_Test
	extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test checking if the condition is met.
	 *
	 * @since 2.3.0
	 *
	 * @dataProvider data_provider_values
	 *
	 * @param mixed $value   A value.
	 * @param mixed $compare A second value.
	 */
	public function test_is_met( $value, $compare ) {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test_entity' );
		$entity->set_the_value( $compare );

		$validator = new WordPoints_Hook_Reaction_Validator( array() );

		$event_args = new WordPoints_Hook_Event_Args( array() );
		$event_args->set_validator( $validator );
		$event_args->add_entity( $entity );
		$event_args->descend( $entity->get_slug() );

		$condition = new WordPoints_Hook_Condition_Number_Less_Than();

		$this->assertTrue(
			$condition->is_met( array( 'value' => $value ), $event_args )
		);

		$this->assertSame( $entity, $event_args->get_current() );

		$this->assertSame(
			array( $entity->get_slug() )
			, $validator->get_field_stack()
		);
	}

	/**
	 * Provides different values that should cause the condition to be met.
	 *
	 * @since 2.3.0
	 *
	 * @return array Possible values.
	 */
	public function data_provider_values() {

		// Setting, arg value.
		return array(
			'int'          => array( 13, 5 ),
			'float'        => array( 13.7, 13.3 ),
			'string_int'   => array( 13, '5' ),
			'string_float' => array( 13.7, '13.3' ),
		);
	}

	/**
	 * Test checking if the condition is met when its not.
	 *
	 * @since 2.3.0
	 *
	 * @dataProvider data_provider_values_not_met
	 *
	 * @param mixed $value   A value.
	 * @param mixed $compare A second value.
	 */
	public function test_is_met_not( $value, $compare ) {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test_entity' );
		$entity->set_the_value( $compare );

		$validator = new WordPoints_Hook_Reaction_Validator( array() );

		$event_args = new WordPoints_Hook_Event_Args( array() );
		$event_args->set_validator( $validator );
		$event_args->add_entity( $entity );
		$event_args->descend( $entity->get_slug() );

		$condition = new WordPoints_Hook_Condition_Number_Less_Than();

		$this->assertFalse(
			$condition->is_met( array( 'value' => $value ), $event_args )
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
	 * @since 2.3.0
	 *
	 * @return array Possible values.
	 */
	public function data_provider_values_not_met() {

		// Setting, arg value.
		return array(
			'equal'   => array( 13, 13 ),
			'greater' => array( 13, 14 ),
			'string'  => array( 13, 'testing' ),
			'true'    => array( 13, true ),
			'false'   => array( 13, false ),
			'array'   => array( 13, array( 3, 2 ) ),
		);
	}
}

// EOF
