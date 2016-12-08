<?php

/**
 * Mock filter class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.2.0
 */

/**
 * Helper class for testing filters and actions.
 *
 * @since 2.0.0 As WordPoints_Mock_Filter.
 * @since 2.2.0
 */
class WordPoints_PHPUnit_Mock_Filter {

	/**
	 * The value to return when the filter is called.
	 *
	 * @since 2.0.0 As part of WordPoints_Mock_Filter.
	 * @since 2.2.0
	 *
	 * @var mixed
	 */
	public $return_value;

	/**
	 * The number of times the action/filter has been called.
	 *
	 * @since 2.0.0 As part of WordPoints_Mock_Filter.
	 * @since 2.2.0
	 *
	 * @var int
	 */
	public $call_count = 0;

	/**
	 * Lists of arguments the action/filter was called with.
	 *
	 * @since 2.0.0 As part of WordPoints_Mock_Filter.
	 * @since 2.2.0
	 *
	 * @var array[]
	 */
	public $calls = array();

	/**
	 * A function to check the filter against before counting the call.
	 *
	 * @since 2.0.0 As part of WordPoints_Mock_Filter.
	 * @since 2.2.0
	 *
	 * @var callable
	 */
	public $count_callback;

	/**
	 * The IDs of the current users each time the hook was called.
	 *
	 * @since 2.1.0 As part of WordPoints_Mock_Filter.
	 * @since 2.2.0
	 *
	 * @see self::listen_for_current_user()
	 *
	 * @var int[]
	 */
	public $current_user = array();

	/**
	 * @since 2.0.0 As part of WordPoints_Mock_Filter.
	 * @since 2.2.0
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
	 * @since 2.0.0 As part of WordPoints_Mock_Filter.
	 * @since 2.2.0
	 *
	 * @param mixed $var The value being filtered.
	 *
	 * @return mixed The filtered value.
	 */
	public function filter( $var = null ) {

		if ( ! $this->count_callback || call_user_func( $this->count_callback, $var ) ) {
			$this->call_count++;
			$this->calls[] = func_get_args();
		}

		if ( isset( $this->return_value ) ) {
			$var = $this->return_value;
		}

		return $var;
	}

	/**
	 * A method that can be hooked to an action.
	 *
	 * @since 2.0.0 As part of WordPoints_Mock_Filter.
	 * @since 2.2.0
	 *
	 * @param mixed $var The first action argument.
	 */
	public function action( $var = null ) {

		if ( ! $this->count_callback || call_user_func( $this->count_callback, $var ) ) {
			$this->call_count++;
			$this->calls[] = func_get_args();
		}
	}

	/**
	 * Listen to an action.
	 *
	 * @since 2.1.0 As part of WordPoints_Mock_Filter.
	 * @since 2.2.0
	 *
	 * @param string $action        The name of the action to listen to.
	 * @param int    $priority      The priority of this callback.
	 * @param int    $accepted_args The number of args to accept.
	 */
	public function add_action( $action, $priority = 10, $accepted_args = 1 ) {
		add_action( $action, array( $this, 'action' ), $priority, $accepted_args );
	}

	/**
	 * Listen to a filter.
	 *
	 * @since 2.1.0 As part of WordPoints_Mock_Filter.
	 * @since 2.2.0
	 *
	 * @param string $filter        The name of the filter to listen to.
	 * @param int    $priority      The priority of this callback.
	 * @param int    $accepted_args The number of args to accept.
	 */
	public function add_filter( $filter, $priority = 10, $accepted_args = 1 ) {
		add_filter( $filter, array( $this, 'filter' ), $priority, $accepted_args );
	}

	/**
	 * Stop listening to an action.
	 *
	 * @since 2.2.0
	 *
	 * @param string $action   The name of the action to stop listening to.
	 * @param int    $priority The priority of this callback.
	 */
	public function remove_action( $action, $priority = 10 ) {
		remove_action( $action, array( $this, 'action' ), $priority );
	}

	/**
	 * Stop listening to a filter.
	 *
	 * @since 2.2.0
	 *
	 * @param string $filter   The name of the filter to stop listening to.
	 * @param int    $priority The priority of this callback.
	 */
	public function remove_filter( $filter, $priority = 10 ) {
		remove_filter( $filter, array( $this, 'filter' ), $priority );
	}

	/**
	 * Listen for the ID of the current user when a filter runs.
	 *
	 * @since 2.1.0 As part of WordPoints_Mock_Filter.
	 * @since 2.2.0
	 *
	 * @param string $filter   The name of the filter to listen to.
	 * @param int    $priority The priority of this callback.
	 */
	public function listen_for_current_user( $filter, $priority = 10 ) {
		add_filter( $filter, array( $this, 'record_current_user' ), $priority );
	}

	/**
	 * Record the ID of the current user.
	 *
	 * @since 2.1.0 As part of WordPoints_Mock_Filter.
	 * @since 2.2.0
	 */
	public function record_current_user( $value ) {

		$this->current_user[] = get_current_user_id();

		return $value;
	}
}

// EOF
