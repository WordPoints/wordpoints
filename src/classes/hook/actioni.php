<?php

/**
 * Hook action interface.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Describes the API for an action object.
 *
 * Action objects represent a higher level representation of a WordPress action.
 * While WordPress actions are very generic, an action object converts it into a
 * more specific form. It allows the args to be retrieved by slugs, making it
 * easy to retrieve a certain type of value across different actions.
 *
 * Action objects also have the power to optionally abort propagating the action,
 * allowing for generic WordPress actions to be represented by more specific action
 * objects.
 */
interface WordPoints_Hook_ActionI {

	/**
	 * Get the slug of this action.
	 *
	 * Note that the slug is not necessarily the name of the WordPress action.
	 *
	 * @since 2.1.0
	 *
	 * @return string The slug of this action.
	 */
	public function get_slug();

	/**
	 * Checks if the action should fire.
	 *
	 * @since 2.1.0
	 *
	 * @return bool Whether the action should fire.
	 */
	public function should_fire();

	/**
	 * Get the value of one of the args.
	 *
	 * @since 2.1.0
	 *
	 * @param string $arg_slug The slug of the arg whose value to retrieve.
	 *
	 * @return mixed The arg value.
	 */
	public function get_arg_value( $arg_slug );
}

// EOF
