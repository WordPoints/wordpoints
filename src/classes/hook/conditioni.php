<?php

/**
 * Hook condition interface.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Defines the API for a hook condition.
 *
 * Conditions help determine whether a particular hook firing should hit the target,
 * by specifying certain conditions which the hook's args must meet. Each child of
 * this class handles a certain type of condition.
 *
 * @since 2.1.0
 */
interface WordPoints_Hook_ConditionI {

	/**
	 * Get the human-readable title of this condition.
	 *
	 * @since 2.1.0
	 *
	 * @return string The condition title.
	 */
	public function get_title();

	/**
	 * Get a list of settings fields for this condition.
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	public function get_settings_fields();

	/**
	 * Validate the settings for an instance of this condition.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_EntityishI              $arg       The hook arg this condition is on.
	 * @param array                              $settings  The settings to validate.
	 * @param WordPoints_Hook_Reaction_Validator $validator The validator for the hook
	 *                                                      reaction this condition is for.
	 *
	 * @return array The validated settings (any errors will be added to $validator).
	 */
	public function validate_settings(
		$arg,
		array $settings,
		WordPoints_Hook_Reaction_Validator $validator
	);

	/**
	 * Check whether the hook args meet the requirements placed by this coniditon.
	 *
	 * @since 2.1.0
	 *
	 * @param array                      $settings The condition's settings.
	 * @param WordPoints_Hook_Event_Args $args     The hook args.
	 *
	 * @return bool Whether this condition is met.
	 */
	public function is_met( array $settings, WordPoints_Hook_Event_Args $args );
}

// EOF
