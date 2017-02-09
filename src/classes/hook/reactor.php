<?php

/**
 * Hook reactor class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Bootstrap for performing pre-scripted reactions when an event is fired.
 *
 * @since 2.1.0
 */
abstract class WordPoints_Hook_Reactor
	implements WordPoints_Hook_ReactorI,
		WordPoints_Hook_UI_Script_Data_ProviderI {

	/**
	 * The unique slug identifying this hook reactor.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The types of args that this reactor can target.
	 *
	 * @since 2.1.0
	 *
	 * @var string|string[]
	 */
	protected $arg_types;

	/**
	 * The slugs of the action types that this reactor listens for.
	 *
	 * @since 2.1.0
	 *
	 * @var string|string[]
	 */
	protected $action_types;

	/**
	 * The settings fields used by this reactor.
	 *
	 * @since 2.1.0
	 *
	 * @var array
	 */
	protected $settings_fields;

	/**
	 * @since 2.1.0
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_arg_types() {
		return (array) $this->arg_types;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_action_types() {
		return (array) $this->action_types;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_settings_fields() {
		return $this->settings_fields;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_ui_script_data() {

		return array(
			'slug'         => $this->get_slug(),
			'fields'       => $this->get_settings_fields(),
			'arg_types'    => $this->get_arg_types(),
			'action_types' => $this->get_action_types(),
		);
	}

	/**
	 * @since 2.1.0
	 */
	public function get_context() {

		return is_wordpoints_network_active() ? 'network' : 'site';
	}

	/**
	 * @since 2.1.0
	 */
	public function validate_settings(
		array $settings,
		WordPoints_Hook_Reaction_Validator $validator,
		WordPoints_Hook_Event_Args $event_args
	) {

		if (
			empty( $settings['target'] )
			|| ! is_array( $settings['target'] )
		) {

			$validator->add_error( __( 'Invalid target.', 'wordpoints' ), 'target' );

		} else {

			$target = $event_args->get_from_hierarchy( $settings['target'] );

			if (
				! $target instanceof WordPoints_Entity
				|| ! in_array( $target->get_slug(), (array) $this->arg_types, true )
			) {
				$validator->add_error( __( 'Invalid target.', 'wordpoints' ), 'target' );
			}
		}

		return $settings;
	}

	/**
	 * @since 2.1.0
	 */
	public function update_settings( WordPoints_Hook_ReactionI $reaction, array $settings ) {
		$reaction->update_meta( 'target', $settings['target'] );
	}
}

// EOF
