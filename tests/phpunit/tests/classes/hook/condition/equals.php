<?php

/**
 * Test case for WordPoints_Hook_Condition_Equals.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Condition_Equals.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Condition_Equals
 */
class WordPoints_Hook_Condition_Equals_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test checking if the condition is met.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_values
	 *
	 * @param mixed $value A value.
	 */
	public function test_is_met( $value ) {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test_entity' );
		$entity->set_the_value( $value );

		$reactor = new WordPoints_PHPUnit_Mock_Hook_Reactor();
		$validator = new WordPoints_Hook_Reaction_Validator( array() );

		$event_args = new WordPoints_Hook_Event_Args( array() );
		$event_args->set_validator( $validator );
		$event_args->add_entity( $entity );
		$event_args->descend( $entity->get_slug() );

		$condition = new WordPoints_Hook_Condition_Equals();

		$this->assertTrue(
			$condition->is_met( array( 'value' => $value ), $event_args )
		);

		$this->assertEquals( $entity, $event_args->get_current() );

		$this->assertEquals(
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
	public function data_provider_values() {

		return array(
			'int' => array( 13 ),
			'string' => array( 'Testing' ),
			'true' => array( true ),
			'false' => array( false ),
			'array' => array( array( 2, 3 ) ),
		);
	}

	/**
	 * Test checking if the condition is met when its not.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_values_not_met
	 *
	 * @param mixed $value   A value.
	 * @param mixed $compare A second value.
	 */
	public function test_is_met_not( $value, $compare ) {

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
			$condition->is_met( array( 'value' => $compare ), $event_args )
		);

		$this->assertEquals( $entity, $event_args->get_current() );

		$this->assertEquals(
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
			'int' => array( 13, '13' ),
			'string' => array( 'Testing', 'testing' ),
			'true' => array( true, 1 ),
			'false' => array( false, '' ),
			'array' => array( array( 2, 3 ), array( 3, 2 ) ),
		);
	}
}

// EOF
