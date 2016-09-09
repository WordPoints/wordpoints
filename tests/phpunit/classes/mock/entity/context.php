<?php

/**
 * Mock entity context class for the PHPUnit tests.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Mock entity context class for the PHPUnit tests.
 *
 * @since 2.1.0
 */
class WordPoints_PHPUnit_Mock_Entity_Context extends WordPoints_Entity_Context {

	/**
	 * The ID of the current context.
	 *
	 * @since 2.1.0
	 *
	 * @var string|int
	 */
	public static $current_id = 1;

	/**
	 * @since 2.1.0
	 */
	public function get_current_id() {
		return self::$current_id;
	}

	/**
	 * Set a protected property's value.
	 *
	 * @since 2.1.0
	 *
	 * @param string $var   The property name.
	 * @param mixed  $value The property value.
	 */
	public function set( $var, $value ) {
		$this->$var = $value;
	}
}

// EOF
