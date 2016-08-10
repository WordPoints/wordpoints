<?php

/**
 * Hook arg interface.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Defines API for a hook arg.
 *
 * When an action is fired, each event that is triggered by it needs to retrieve one
 * or more values related to that event. These are called the event args. The values
 * may come from the action itself, or from elsewhere. Here we provides a common
 * interface for retrieving those values and converting them into entities.
 *
 * @since 2.1.0
 */
interface WordPoints_Hook_ArgI {

	/**
	 * Get the slug of this arg.
	 *
	 * @since 2.1.0
	 *
	 * @return string The arg slug.
	 */
	public function get_slug();

	/**
	 * Get the slug of the type of entity this arg is.
	 *
	 * @since 2.1.0
	 *
	 * @return string The entity slug.
	 */
	public function get_entity_slug();

	/**
	 * Get the entity object for this arg's value.
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_Entity|false The entity, or false if not registered.
	 */
	public function get_entity();

	/**
	 * Retrieves the value for this arg.
	 *
	 * @since 2.1.0
	 *
	 * @return mixed The arg value.
	 */
	public function get_value();

	/**
	 * Retrieves the human-readable title of this arg.
	 *
	 * @since 2.1.0
	 *
	 * @return string The arg title.
	 */
	public function get_title();

	/**
	 * Check whether the arg is stateful.
	 *
	 * @since 2.1.0
	 *
	 * @return bool Whether this arg is stateful.
	 */
	public function is_stateful();
}

// EOF
