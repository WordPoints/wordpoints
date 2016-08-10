<?php

/**
 * Mock hook condition class for the PHPUnit tests.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Mock hook condition for the PHPUnit tests.
 *
 * @since 2.1.0
 */
class WordPoints_PHPUnit_Mock_Hook_Condition extends WordPoints_Hook_Condition {

	/**
	 * @since 2.1.0
	 */
	protected $slug = 'test_condition';

	/**
	 * @since 2.1.0
	 */
	protected $settings_fields = array(
		'value' => array( 'label' => 'Value' ),
	);

	/**
	 * @since 2.1.0
	 */
	public function is_met( array $settings, WordPoints_Hook_Event_Args $args ) {
		return true;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_title() {
		return 'Test Condition';
	}

	/**
	 * @since 2.1.0
	 */
	public function get_settings_fields() {
		return $this->settings_fields;
	}
}

// EOF
