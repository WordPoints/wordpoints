<?php

/**
 * Hook reactor interface.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Defines the API for a hook reactor.
 *
 * When a hook event fires, it is the job of the reactor to perform the action
 * specified for each reaction object. For most reactors this means that they must
 * "hit" a "target". For example, it might award points to a particular user.
 *
 * @since 2.1.0
 */
interface WordPoints_Hook_ReactorI extends WordPoints_Hook_SettingsI {

	/**
	 * Get the slug of this reactor.
	 *
	 * @since 2.1.0
	 *
	 * @return string The reactor's slug.
	 */
	public function get_slug();

	/**
	 * Get a list of the slugs of each type of arg that this reactor supports.
	 *
	 * @since 2.1.0
	 *
	 * @return string[] The slugs of the arg types this reactor supports.
	 */
	public function get_arg_types();

	/**
	 * Get a list of the slugs of the action types that this reactor listens for.
	 *
	 * @since 2.1.0
	 *
	 * @return string[] The slugs of the action types this reactor listens for.
	 */
	public function get_action_types();

	/**
	 * Get the settings fields used by the reactor.
	 *
	 * @since 2.1.0
	 *
	 * @return string[] The meta keys used to store this reactor's settings.
	 */
	public function get_settings_fields();

	/**
	 * Check what context this reactor exists in.
	 *
	 * When a reactor is not network-wide, network reactions are not supported. For
	 * example, the points reactor is not network-wide when WordPoints isn't network-
	 * active, because the points types are created per-site. We default all reactors
	 * to being network wide only when WordPoints is network-active, but some may
	 * need to override this.
	 *
	 * @since 2.1.0
	 *
	 * @return string The slug of the context in which this reactor exists.
	 */
	public function get_context();

	/**
	 * Perform an action when the reactor is hit by an event being fired.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_Fire $fire The hook fire object.
	 */
	public function hit( WordPoints_Hook_Fire $fire );
}

// EOF
