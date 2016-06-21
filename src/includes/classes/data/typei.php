<?php

/**
 * Data type interface.
 *
 * @package WordPoints\Data_Types
 * @since 2.1.0
 */

/**
 * Defines the API for representing a data type.
 *
 * The data type API builds on top of PHP's built-in primitive types (int, string,
 * array, etc.), to provide an interface for handling more specialized types of data.
 *
 * @since 2.1.0
 */
interface WordPoints_Data_TypeI {

	/**
	 * Get the slug of this data type.
	 *
	 * @since 2.1.0
	 *
	 * @return string The slug of this data type.
	 */
	public function get_slug();

	/**
	 * Validate that a value is of this type.
	 *
	 * If the value is not of this type but can be converted to this type, it may be
	 * converted.
	 *
	 * When a WP_Error object is returned, the error message may include a place-
	 * holder for the name of the value being validated. So you should pass the
	 * message through sprintf() with the value name.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $value The value to validate.
	 *
	 * @return mixed|WP_Error The validated value or a WP_Error on failure.
	 */
	public function validate_value( $value );
}

// EOF
