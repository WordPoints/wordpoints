<?php

/**
 * Current User hook arg class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Represents the current User as a hook arg.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Arg_Current_User extends WordPoints_Hook_Arg {

	/**
	 * @since 2.1.0
	 */
	protected $is_stateful = true;

	/**
	 * @since 2.1.0
	 */
	public function get_value() {
		return wp_get_current_user();
	}

	/**
	 * @since 2.1.0
	 */
	public function get_title() {
		return __( 'Visitor', 'wordpoints' );
	}
}

// EOF
