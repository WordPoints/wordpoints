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
				'type'  => 'text',
				'label' => _x( 'Value', 'equals hook condition label', 'wordpoints' ),
			),
		);
	}

	/**
	 * @since 2.1.0
	 */
	public function is_met( array $settings, WordPoints_Hook_Event_Args $args ) {

		$arg   = $args->get_current();
		$value = $arg->get_the_value();

		// Validate attribute data types, to ensure that they are converted to the
		// correct underlying PHP types if necessary before the strict comparison
		// below.
		if ( $arg instanceof WordPoints_Entity_Attr ) {

			$data_type = wordpoints_apps()
				->get_sub_app( 'data_types' )
				->get( $arg->get_data_type() );

			// If we can't get the data type, proceed with the comparison anyway.
			if ( $data_type instanceof WordPoints_Data_TypeI ) {

				$value = $data_type->validate_value( $value );

				if ( is_wp_error( $value ) ) {
					return false;
				}
			}
		}

		return $settings['value'] === $value;
	}
}

// EOF
