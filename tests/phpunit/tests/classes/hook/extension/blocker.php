<?php

/**
 * Test case for WordPoints_Hook_Extension_Blocker.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Extension_Blocker.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Extension_Blocker
 */
class WordPoints_Hook_Extension_Blocker_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test validating the settings.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_valid_settings
	 *
	 * @param mixed $settings Valid settings.
	 * @param mixed $expected The expected value for the validated settings.
	 */
	public function test_validate_settings( $settings, $expected = null ) {

		$extension = new WordPoints_Hook_Extension_Blocker();
		$validator = new WordPoints_Hook_Reaction_Validator( array() );
		$event_args = new WordPoints_Hook_Event_Args( array() );
		$event_args->set_validator( $validator );

		$slug   = $extension->get_slug();
		$result = $extension->validate_settings(
			array( $slug => $settings )
			, $validator
			, $event_args
		);

		$this->assertFalse( $validator->had_errors() );
		$this->assertSame( array(), $validator->get_field_stack() );
		$this->assertNull( $event_args->get_current() );

		if ( ! isset( $expected ) ) {
			$expected = $settings;
		}

		$this->assertSame( $expected, $result[ $slug ] );
	}

	/**
	 * Provides sets of valid settings for this extension.
	 *
	 * @since 2.1.0
	 *
	 * @return array The sets of valid settings.
	 */
	public function data_provider_valid_settings() {
		return array(
			'empty' => array( array() ),
			'one' => array( '1', true ),
			'zero' => array( '0', false ),
			'true' => array( true ),
			'false' => array( false ),
			'string_true' => array( 'true', true ),
			'string_false' => array( 'false', false ),
			'action_type_one' => array( array( 'test_fire' => '1' ), array( 'test_fire' => true ) ),
			'action_type_zero' => array( array( 'test_fire' => '0' ), array( 'test_fire' => false ) ),
			'action_type_true' => array( array( 'test_fire' => true ) ),
			'action_type_false' => array( array( 'test_fire' => false ) ),
		);
	}

	/**
	 * Test checking whether an event should hit the target.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_valid_settings_should_hit
	 *
	 * @param mixed $settings Settings for the extension.
	 */
	public function test_should_hit( $settings ) {

		$reaction = $this->factory->wordpoints->hook_reaction->create();
		$reaction->add_meta( 'blocker', $settings );

		$event_args = new WordPoints_Hook_Event_Args( array() );

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'test_fire' );

		$extension = new WordPoints_Hook_Extension_Blocker();

		$this->assertTrue( $extension->should_hit( $fire ) );

		$this->assertNull( $event_args->get_current() );
	}

	/**
	 * Provides sets of settings that should cause this extension to hit.
	 *
	 * @since 2.1.0
	 *
	 * @return array The sets of settings.
	 */
	public function data_provider_valid_settings_should_hit() {
		return array(
			'empty' => array( array() ),
			'false' => array( false ),
			'action_type_false' => array( array( 'test_fire' => false ) ),
			'action_type_other' => array( array( 'other' => true ) ),
		);
	}

	/**
	 * Test checking whether an event should hit the target when it should not.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_valid_settings_should_not_hit
	 *
	 * @param mixed $settings Settings for the extension.
	 */
	public function test_should_not_hit( $settings ) {

		$reaction = $this->factory->wordpoints->hook_reaction->create();
		$reaction->add_meta( 'blocker', $settings );

		$event_args = new WordPoints_Hook_Event_Args( array() );

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'test_fire' );

		$extension = new WordPoints_Hook_Extension_Blocker();

		$this->assertFalse( $extension->should_hit( $fire ) );

		$this->assertNull( $event_args->get_current() );
	}

	/**
	 * Provides sets of settings that should cause this extension to not hit.
	 *
	 * @since 2.1.0
	 *
	 * @return array The sets of settings.
	 */
	public function data_provider_valid_settings_should_not_hit() {
		return array(
			'true' => array( true ),
			'action_type_true' => array( array( 'test_fire' => true ) ),
		);
	}
}

// EOF
