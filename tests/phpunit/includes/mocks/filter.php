<?php

/**
 * Mock filter class.
 *
 * @package WordPoints\Tests
 * @since 2.0.0
 */

/**
 * Helper class for testing filters and actions.
 *
 * @since 2.0.0
 */
class WordPoints_Mock_Filter {

	/**
	 * The value to return when the filter is called.
	 *
	 * @since 2.0.0
	 *
	 * @var mixed
	 */
	public $return_value;

	/**
	 * The number of times the action/filter has been called.
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	public $call_count = 0;

	/**
	 * Lists of arguments the action/filter was called with.
	 *
	 * @since 2.0.0
	 *
	 * @var array[]
	 */
	public $calls = array();

	/**
	 * @since 2.0.0
	 *
	 * @param mixed $return_value The value to return when the filter is called.
	 */
	public function __construct( $return_value = null ) {
		$this->return_value = $return_value;
	}

	/**
	 * A method that can be hooked to a filter.
	 *
	 * The self::$return_value will be returned, if set.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $var The value being filtered.
	 *
	 * @return mixed The filtered value.
	 */
	public function filter( $var ) {

		$this->call_count++;
		$this->calls[] = func_get_args();

		if ( isset( $this->return_value ) ) {
			$var = $this->return_value;
		}

		return $var;
	}

	/**
	 * A method that can be hooked to an action.
	 *
	 * @since 2.0.0
	 */
	public function action() {

		$this->call_count++;
		$this->calls[] = func_get_args();
	}
}

// EOF
