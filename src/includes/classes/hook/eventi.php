<?php

/**
 * Hook event interface.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Defines the API for a hook event.
 *
 * @since 2.1.0
 */
interface WordPoints_Hook_EventI {

	/**
	 * Get the event slug.
	 *
	 * @since 2.1.0
	 *
	 * @return string The event slug.
	 */
	public function get_slug();

	/**
	 * Get the human-readable title of this event.
	 *
	 * @since 2.1.0
	 *
	 * @return string The event title.
	 */
	public function get_title();

	/**
	 * Get the event description.
	 *
	 * @since 2.1.0
	 *
	 * @return string The event description.
	 */
	public function get_description();
}

// EOF
