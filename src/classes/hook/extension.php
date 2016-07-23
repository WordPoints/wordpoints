<?php

/**
 * Hook extension class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Represents a hook extension.
 *
 * @since 2.1.0
 */
abstract class WordPoints_Hook_Extension implements WordPoints_Hook_ExtensionI {

	/**
	 * The unique slug for identifying this extension.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The validator for the current reaction.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_Reaction_Validator
	 */
	protected $validator;

	/**
	 * The args for the current event.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_Event_Args
	 */
	protected $event_args;

	/**
	 * @since 2.1.0
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * @since 2.1.0
	 */
	public function validate_settings(
		array $settings,
		WordPoints_Hook_Reaction_Validator $validator,
		WordPoints_Hook_Event_Args $event_args
	) {

		if ( ! isset( $settings[ $this->slug ] ) ) {
			return $settings;
		}

		if ( ! is_array( $settings[ $this->slug ] ) ) {

			$validator->add_error(
				__( 'Invalid settings format.', 'wordpoints' )
				, $this->slug
			);

			return $settings;
		}

		$this->validator = $validator;
		$this->event_args = $event_args;

		$this->validator->push_field( $this->slug );

		foreach ( $settings[ $this->slug ] as $action_type => $action_type_settings ) {

			$this->validator->push_field( $action_type );

			$settings[ $this->slug ][ $action_type ] = $this->validate_action_type_settings(
				$action_type_settings
			);

			$this->validator->pop_field();
		}

		$this->validator->pop_field();

		return $settings;
	}

	/**
	 * @since 2.1.0
	 */
	public function update_settings( WordPoints_Hook_ReactionI $reaction, array $settings ) {

		if ( isset( $settings[ $this->slug ] ) ) {
			$reaction->update_meta( $this->slug, $settings[ $this->slug ] );
		} else {
			$reaction->delete_meta( $this->slug );
		}
	}

	/**
	 * Validate the settings for this extension for a particular action type.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $settings The settings for a particular action type.
	 *
	 * @return mixed The validated settings.
	 */
	protected function validate_action_type_settings( $settings ) {
		return $settings;
	}

	/**
	 * Get the extension settings from the fire object.
	 *
	 * By default the settings are stored per action type, so we offer this helper
	 * method to get the settings that should be used based on the action type from
	 * the fire object.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_Fire $fire The hook fire object.
	 *
	 * @return mixed The settings for the extension, or false if none.
	 */
	protected function get_settings_from_fire( WordPoints_Hook_Fire $fire ) {

		$settings = $fire->reaction->get_meta( $this->slug );

		if ( ! is_array( $settings ) ) {
			return $settings;
		}

		if ( isset( $settings[ $fire->action_type ] ) ) {
			return $settings[ $fire->action_type ];
		} else {
			return false;
		}
	}
}

// EOF
