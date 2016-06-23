<?php

/**
 * Database error handler for the PHPUnit tests.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Handles database errors in the PHPUnit tests.
 *
 * @since 2.1.0
 */
class WordPoints_PHPUnit_Error_Handler_Database implements ArrayAccess {

	/**
	 * Just a stub.
	 *
	 * @since 2.1.0
	 *
	 * @return false Always.
	 */
	public function offsetExists( $offset ) {
		return false;
	}

	/**
	 * Just a stub.
	 *
	 * @since 2.1.0
	 */
	public function offsetGet( $offset ) {}

	/**
	 * Triggers an error when it is added to the stack.
	 *
	 * @since 2.1.0
	 */
	public function offsetSet( $offset, $value ) {

		global $wpdb;

		if ( $wpdb->suppress_errors ) {
			return;
		}

		trigger_error(
			"WordPress Database Error: {$value['error_str']} [{$value['query']}]"
			, E_USER_WARNING
		);
	}

	/**
	 * Just a stub.
	 *
	 * @since 2.1.0
	 */
	public function offsetUnset( $offset ) {}
}

// EOF
