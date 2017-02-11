<?php

/**
 * Test case for WordPoints_Hook_Condition.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Condition.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Condition
 */
class WordPoints_Hook_Condition_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test checking if the condition settings are valid.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_valid_values
	 *
	 * @param string               $value The valid setting.
	 * @param WordPoints_Entityish $arg   The arg object.
	 */
	public function test_validate_settings( $value, $arg ) {

		$reactor = new WordPoints_PHPUnit_Mock_Hook_Reactor();
		$validator = new WordPoints_Hook_Reaction_Validator( array() );

		$condition = new WordPoints_PHPUnit_Mock_Hook_Condition();

		$settings = array( 'value' => $value );

		$validated_settings = $condition->validate_settings(
			$arg
			, $settings
			, $validator
		);

		$this->assertSame( $settings, $validated_settings );

		$this->assertFalse( $validator->had_errors() );
		$this->assertSame( array(), $validator->get_field_stack() );
	}

	/**
	 * Provides different values.
	 *
	 * @since 2.1.0
	 *
	 * @return array Possible values.
	 */
	public function data_provider_valid_values() {

		$attr_unknown_data_type = new WordPoints_PHPUnit_Mock_Entity_Attr( 'test' );
		$attr_unknown_data_type->set( 'data_type', 'unknown' );

		return array(
			'attr' => array( 'Test', new WordPoints_PHPUnit_Mock_Entity_Attr( 'test' ) ),
			'attr_unknown_data_type' => array( 'Test', $attr_unknown_data_type ),
			'entity' => array( 'test', new WordPoints_PHPUnit_Mock_Entity( 'test' ) ),
		);
	}

	/**
	 * Test checking if the condition settings are valid when they aren't.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_invalid_settings
	 *
	 * @param array                $settings The invalid settings.
	 * @param WordPoints_Entityish $arg      The arg object.
	 */
	public function test_validate_settings_invalid( $settings, $arg ) {

		$reactor = new WordPoints_PHPUnit_Mock_Hook_Reactor();
		$validator = new WordPoints_Hook_Reaction_Validator( array() );

		$condition = new WordPoints_PHPUnit_Mock_Hook_Condition();

		$validated_settings = $condition->validate_settings(
			$arg
			, $settings
			, $validator
		);

		$this->assertSame( $settings, $validated_settings );

		$errors = $validator->get_errors();

		$this->assertCount( 1, $errors );
		$this->assertSame( array( 'value' ), $errors[0]['field'] );

		$this->assertSame( array(), $validator->get_field_stack() );
	}

	/**
	 * Provides different sets of invalid settings.
	 *
	 * @since 2.1.0
	 *
	 * @return array Invalid settings.
	 */
	public function data_provider_invalid_settings() {

		$attr = new WordPoints_PHPUnit_Mock_Entity_Attr( 'test' );

		$entity_not_exists = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity_not_exists->set( 'getter', '__return_false' );

		return array(
			'no_value' => array( array(), $attr ),
			'empty_string_value' => array( array( 'value' => '' ), $attr ),
			'attr_wrong_data_type' => array( array( 'value' => array() ), $attr ),
			'entity_not_exists' => array( array( 'value' => 'test' ), $entity_not_exists ),
		);
	}
}

// EOF
