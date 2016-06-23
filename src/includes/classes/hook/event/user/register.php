<?php

/**
 * User Register hook event class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * An event that fires when a user is registered.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Event_User_Register
	extends WordPoints_Hook_Event
	implements WordPoints_Hook_Event_ReversingI {

	/**
	 * @since 2.1.0
	 */
	public function get_title() {
		return __( 'Register', 'wordpoints' );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_description() {
		return __( 'Registering.', 'wordpoints' );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_reversal_text() {
		return __( 'User removed.', 'wordpoints' );
	}
}

// EOF
