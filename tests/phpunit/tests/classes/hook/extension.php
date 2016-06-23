<?php

/**
 * Test case for WordPoints_Hook_Extension.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Extension.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Extension
 */
class WordPoints_Hook_Extension_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test getting the extension slug.
	 *
	 * @since 2.1.0
	 */
	public function test_get_slug() {

		$extension = new WordPoints_PHPUnit_Mock_Hook_Extension();

		$this->assertEquals( 'test_extension', $extension->get_slug() );
	}

	/**
	 * Test validating the extension's settings.
	 *
	 * @since 2.1.0
	 */
	public function test_validate_settings() {

		$this->mock_apps();

		$extension = new WordPoints_PHPUnit_Mock_Hook_Extension();

		$action_type = 'test_fire';

		$settings = array(
			'test_extension' => array( $action_type => array( 'key' => 'value' ) ),
			'other_settings' => 'here',
		);

		$validator = new WordPoints_Hook_Reaction_Validator(
			array()
		);

		$event_args = new WordPoints_Hook_Event_Args( array() );

		$validated = $extension->validate_settings(
			$settings
			, $validator
			, $event_args
		);

		$this->assertEquals( $settings, $validated );

		$this->assertEquals(
			$settings['test_extension'][ $action_type ]
			, $extension->validations[0]['settings']
		);

		$this->assertEquals( $event_args, $extension->validations[0]['event_args'] );
		$this->assertEquals( $validator, $extension->validations[0]['validator'] );
		$this->assertEquals(
			array( 'test_extension', $action_type )
			, $extension->validations[0]['field_stack']
		);

		$this->assertEquals( array(), $validator->get_field_stack() );
	}

	/**
	 * Test validating the extension's settings when the key isn't set.
	 *
	 * @since 2.1.0
	 */
	public function test_validate_settings_not_set() {

		$this->mock_apps();

		$extension = new WordPoints_PHPUnit_Mock_Hook_Extension();

		$settings = array( 'other_settings' => 'here' );

		$validator = new WordPoints_Hook_Reaction_Validator(
			array()
		);

		$event_args = new WordPoints_Hook_Event_Args( array() );

		$validated = $extension->validate_settings(
			$settings
			, $validator
			, $event_args
		);

		$this->assertEquals( $settings, $validated );
		$this->assertEquals( array(), $extension->validations );
		$this->assertEquals( array(), $validator->get_field_stack() );
	}

	/**
	 * Test validating the extension's settings when they aren't an array.
	 *
	 * @since 2.1.0
	 */
	public function test_validate_settings_not_array() {

		$this->mock_apps();

		$extension = new WordPoints_PHPUnit_Mock_Hook_Extension();

		$settings = array(
			'test_extension' => 'invalid',
			'other_settings' => 'here',
		);

		$validator = new WordPoints_Hook_Reaction_Validator(
			array()
		);

		$event_args = new WordPoints_Hook_Event_Args( array() );

		$validated = $extension->validate_settings(
			$settings
			, $validator
			, $event_args
		);

		$this->assertEquals( $settings, $validated );

		$this->assertEmpty( $extension->validations );

		$errors = $validator->get_errors();

		$this->assertCount( 1, $errors );
		$this->assertEquals( array( 'test_extension' ), $errors[0]['field'] );

		$this->assertEquals( array(), $validator->get_field_stack() );
	}

	/**
	 * Test updating the extension's settings.
	 *
	 * @since 2.1.0
	 */
	public function test_update_settings() {

		$extension = new WordPoints_PHPUnit_Mock_Hook_Extension();

		$settings = array(
			'test_extension' => array( 'test_fire' => array( 'key' => 'value' ) ),
			'other_settings' => 'here',
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$extension->update_settings( $reaction, $settings );

		$this->assertEquals(
			$settings['test_extension']
			, $reaction->get_meta( 'test_extension' )
		);

		$this->assertFalse( $reaction->get_meta( 'other_settings' ) );
	}

	/**
	 * Test updating the extension's settings when the key is not set causes existing
	 * setting to be deleted.
	 *
	 * @since 2.1.0
	 */
	public function test_update_settings_not_set() {

		$extension = new WordPoints_PHPUnit_Mock_Hook_Extension();

		$settings = array( 'other_settings' => 'here' );

		$reaction = $this->factory->wordpoints->hook_reaction->create(
			array(
				'test_extension' => array(
					'test_fire' => array( 'key' => 'value' ),
				),
			)
		);

		$extension->update_settings( $reaction, $settings );

		$this->assertFalse( $reaction->get_meta( 'test_extension' ) );
		$this->assertFalse( $reaction->get_meta( 'other_settings' ) );
	}
}

// EOF
