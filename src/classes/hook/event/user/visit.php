<?php

/**
 * User visit hook event class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Represents a hook event that occurs when a user visits the site.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Event_User_Visit extends WordPoints_Hook_Event {

	/**
	 * @since 2.1.0
	 */
	public function get_title() {
		return __( 'Visit', 'wordpoints' );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_description() {
		return __( 'When a logged-in user or guest visits the site.', 'wordpoints' );
	}
}

// EOF
