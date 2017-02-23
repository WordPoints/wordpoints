<?php

/**
 * Decimal number data type class.
 *
 * @package WordPoints\Data_Types
 * @since 2.3.0
 */

/**
 * Handler for decimal number data.
 *
 * @since 2.3.0
 */
class WordPoints_Data_Type_Decimal_Number extends WordPoints_Data_Type {

	/**
	 * @since 2.3.0
	 */
	public function validate_value( $value ) {

		// filter_var() converts true to 1.0.
		if ( true === $value ) {
			$value = false;
		} else {
			$value = filter_var( $value, FILTER_VALIDATE_FLOAT );
		}

		if ( false === $value ) {
			return new WP_Error(
				'not_decimal_number'
				// translators: Form field name.
				, __( '%s must be a decimal number.', 'wordpoints' )
			);
		}

		return $value;
	}
}

// EOF
