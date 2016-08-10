<?php

/**
 * Mock hook arg class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Mock hook arg for using in the PHPUnit tests.
 *
 * @since 2.1.0
 */
class WordPoints_PHPUnit_Mock_Hook_Arg extends WordPoints_Hook_Arg {

	/**
	 * @since 2.1.0
	 */
	public $is_stateful = false;

	/**
	 * @since 2.1.0
	 */
	public $value = 1;

	/**
	 * @since 2.1.0
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_title() {
		return $this->get_entity()->get_title();
	}
}

// EOF
