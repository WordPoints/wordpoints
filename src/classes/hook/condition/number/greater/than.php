<?php

/**
 * Number greater than hook condition class.
 *
 * @package WordPoints\Hooks
 * @since 2.3.0
 */

/**
 * Represents a condition that requires a number to be greater than another number.
 *
 * @since 2.3.0
 */
class WordPoints_Hook_Condition_Number_Greater_Than
	extends WordPoints_Hook_Condition {

	/**
	 * @since 2.3.0
	 */
	public function get_title() {
		return __( 'Is Greater Than', 'wordpoints' );
	}

	/**
	 * @since 2.3.0
	 */
	public function get_settings_fields() {

		return array(
			'value' => array(
				'type'  => 'number',
				'label' => _x( 'Number', 'greater than hook condition label', 'wordpoints' ),
			),
		);
	}

	/**
	 * @since 2.3.0
	 */
	public function is_met( array $settings, WordPoints_Hook_Event_Args $args ) {

		$value = $args->get_current()->get_the_value();

		if ( ! is_numeric( $value ) ) {
			return false;
		}

		return $value > $settings['value'];
	}
}

// EOF
