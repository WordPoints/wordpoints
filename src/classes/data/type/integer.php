<?php

/**
 * Integer data type class.
 *
 * @package WordPoints\Data_Types
 * @since 2.1.0
 */

/**
 * A handler for integer data.
 *
 * @since 2.1.0
 */
class WordPoints_Data_Type_Integer extends WordPoints_Data_Type {

	/**
	 * @since 2.1.0
	 */
	public function validate_value( $value ) {

		wordpoints_int( $value );

		if ( false === $value ) {
			return new WP_Error(
				'not_integer'
				// translators: Form field name.
				, __( '%s must be an integer.', 'wordpoints' )
			);
		}

		return $value;
	}
}

// EOF
