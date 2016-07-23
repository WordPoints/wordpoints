<?php

/**
 * Equals hook condition class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Represents a condition that requires a value to be equal to a predefined value.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Condition_Equals extends WordPoints_Hook_Condition {

	/**
	 * @since 2.1.0
	 */
	public function get_title() {
		return __( 'Equals', 'wordpoints' );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_settings_fields() {

		return array(
			'value' => array(
				'type' => 'text',
				'label' => _x( 'Value', 'equals hook condition label', 'wordpoints' ),
			),
		);
	}

	/**
	 * @since 2.1.0
	 */
	public function is_met( array $settings, WordPoints_Hook_Event_Args $args ) {

		return $settings['value'] === $args->get_current()->get_the_value();
	}
}

// EOF
