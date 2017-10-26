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
	 * @since 2.3.0
	 *
	 * @param string $slug The slug for this extension. This should always be passed,
	 *                     and is only optional for backward compatibility.
	 */
	public function __construct( $slug = null ) {

		if ( isset( $slug ) ) {
			$this->slug = $slug;
		}
	}

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

		$this->validator  = $validator;
		$this->event_args = $event_args;

		$this->validator->push_field( $this->slug );

		$validated_settings = $this->validate_extension_settings(
			$settings[ $this->slug ]
		);

		$this->validator->pop_field();

		if ( null !== $validated_settings ) {
			$settings[ $this->slug ] = $validated_settings;
		} else {
			unset( $settings[ $this->slug ] );
		}

		return $settings;
	}

	/**
	 * Validate the settings of this particular extension.
	 *
	 * Only the settings stored in the meta value corresponding to the key matching
	 * this extension's slug is passed to this function. The validated version of the
	 * settings should be returned, or, if the settings are irreconcilably bad, null
	 * should be returned.
	 *
	 * @since 2.3.0
	 *
	 * @param mixed $settings The raw settings.
	 *
	 * @return mixed The validated settings, or null if invalid.
	 */
	protected function validate_extension_settings( $settings ) {

		if ( ! is_array( $settings ) ) {

			$this->validator->add_error( __( 'Invalid settings format.', 'wordpoints' ) );

			return null;
		}

		foreach ( $settings as $action_type => $action_type_settings ) {

			$this->validator->push_field( $action_type );

			$validated_settings = $this->validate_action_type_settings(
				$action_type_settings
			);

			if ( null !== $validated_settings ) {
				$settings[ $action_type ] = $validated_settings;
			} else {
				unset( $settings[ $action_type ] );
			}

			$this->validator->pop_field();
		}

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
	 * If the settings are irrecoverably bad, just return null.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $settings The settings for a particular action type.
	 *
	 * @return mixed The validated settings, or null if invalid.
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
