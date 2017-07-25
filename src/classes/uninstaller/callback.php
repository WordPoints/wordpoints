<?php

/**
 * Callback uninstaller class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Uninstaller that runs a callback over each of a list of items.
 *
 * @since 2.4.0
 */
class WordPoints_Uninstaller_Callback implements WordPoints_RoutineI {

	/**
	 * The callback to call for each item.
	 *
	 * @since 2.4.0
	 *
	 * @var callable
	 */
	protected $callback;

	/**
	 * The list of items to pass to the callback.
	 *
	 * @since 2.4.0
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * @since 2.4.0
	 *
	 * @param callable $callback The callback function to call with each item.
	 * @param array    $items    The items to pass to the callback function.
	 */
	public function __construct( $callback, $items ) {

		$this->items    = $items;
		$this->callback = $callback;
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {
		array_map( $this->callback, $this->items );
	}
}

// EOF
