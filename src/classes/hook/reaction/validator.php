<?php

/**
 * Hook reaction validator class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Validator for hook reaction settings.
 *
 * @since 2.1.0
 */
final class WordPoints_Hook_Reaction_Validator {

	/**
	 * The object for the hook reaction being validated, or false if none is set.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_ReactionI|false
	 */
	protected $reaction = false;

	/**
	 * The settings of the hook reaction being validated.
	 *
	 * @since 2.1.0
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * The slug of the event the reaction is to.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $event_slug;

	/**
	 * Whether to stop validating after encountering the first error.
	 *
	 * @since 2.1.0
	 *
	 * @var bool
	 */
	protected $fail_fast = false;

	/**
	 * A stack of fields for tracking the hierarchy of the field being validated.
	 *
	 * @since 2.1.0
	 *
	 * @var array
	 */
	protected $field_stack = array();

	/**
	 * A list of error messages and the fields they are associated with.
	 *
	 * @since 2.1.0
	 *
	 * @var array[]
	 */
	protected $errors = array();

	/**
	 * The event args object for the event this reaction is for.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_Event_Args
	 */
	protected $event_args;

	/**
	 * Shortcut to the hooks app.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hooks
	 */
	protected $hooks;

	/**
	 * The slug of the reactor the reaction is for.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $reactor_slug;

	/**
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_ReactionI|array $settings  The settings or reaction to
	 *                                                   validate.
	 * @param bool                            $fail_fast Whether to fail as soon as
	 *                                                   the first error is found.
	 */
	public function __construct( $settings, $fail_fast = false ) {

		$this->fail_fast = $fail_fast;
		$this->hooks     = wordpoints_hooks();

		if ( $settings instanceof WordPoints_Hook_ReactionI ) {

			$this->reaction     = $settings;
			$this->settings     = $this->reaction->get_all_meta();
			$this->event_slug   = $this->reaction->get_event_slug();
			$this->reactor_slug = $this->reaction->get_reactor_slug();

		} else {

			$this->settings = $settings;

			if ( isset( $this->settings['event'] ) ) {
				$this->event_slug = $this->settings['event'];
			}

			if ( isset( $this->settings['reactor'] ) ) {
				$this->reactor_slug = $this->settings['reactor'];
			}
		}
	}

	/**
	 * Validates the settings for the reaction.
	 *
	 * @since 2.1.0
	 *
	 * @return array The validated settings.
	 */
	public function validate() {

		$this->field_stack = array();
		$this->errors      = array();

		try {

			// We have to bail early if we don't have a valid event.
			$fail_fast       = $this->fail_fast;
			$this->fail_fast = true;

			$events = $this->hooks->get_sub_app( 'events' );

			if ( ! isset( $this->event_slug ) ) {
				$this->add_error( __( 'Event is missing.', 'wordpoints' ), 'event' );
			} elseif ( ! $events->is_registered( $this->event_slug ) ) {
				$this->add_error( __( 'Event is invalid.', 'wordpoints' ), 'event' );
			}

			$reactors = $this->hooks->get_sub_app( 'reactors' );

			if ( ! isset( $this->reactor_slug ) ) {
				$this->add_error( __( 'Reactor is missing.', 'wordpoints' ), 'reactor' );
			} elseif ( ! $reactors->is_registered( $this->reactor_slug ) ) {
				$this->add_error( __( 'Reactor is invalid.', 'wordpoints' ), 'reactor' );
			}

			// From here on out we can collect errors as they come (unless we are
			// supposed to fail fast).
			$this->fail_fast = $fail_fast;

			$event_args = $events->get_sub_app( 'args' )->get_children(
				$this->event_slug
			);

			$this->event_args = new WordPoints_Hook_Event_Args( $event_args );
			$this->event_args->set_validator( $this );

			/** @var WordPoints_Hook_ReactorI $reactor */
			$reactor = $reactors->get( $this->reactor_slug );

			$this->settings = $reactor->validate_settings( $this->settings, $this, $this->event_args );

			/** @var WordPoints_Hook_ExtensionI $extension */
			foreach ( $this->hooks->get_sub_app( 'extensions' )->get_all() as $extension ) {
				$this->settings = $extension->validate_settings( $this->settings, $this, $this->event_args );
			}

			/**
			 * A hook reaction's settings are being validated.
			 *
			 * @param array                              $settings  The settings.
			 * @param WordPoints_Hook_Reaction_Validator $validator The validator object.
			 * @param WordPoints_Hook_Event_Args         $args      The event args object.
			 */
			$this->settings = apply_filters( 'wordpoints_hook_reaction_validate', $this->settings, $this, $this->event_args );

		} catch ( WordPoints_Hook_Validator_Exception $e ) {

			// Do nothing.
			unset( $e );

		} // End try { validating settings } catch ( validator exception ).

		return $this->settings;
	}

	/**
	 * Get the args for the event this reaction is for.
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_Hook_Event_Args The event args object.
	 */
	public function get_event_args() {
		return $this->event_args;
	}

	/**
	 * Adds an error to the stack.
	 *
	 * @since 2.1.0
	 *
	 * @param string $message The message to add.
	 * @param string $field   The field the message is for.
	 *
	 * @throws WordPoints_Hook_Validator_Exception If the validator is configured to
	 *                                             fail as soon as an error is found.
	 */
	public function add_error( $message, $field = null ) {

		$field_stack = $this->field_stack;

		if ( null !== $field ) {
			$field_stack[] = $field;
		}

		$this->errors[] = array( 'message' => $message, 'field' => $field_stack );

		if ( $this->fail_fast ) {
			throw new WordPoints_Hook_Validator_Exception();
		}
	}

	/**
	 * Checks if any settings were invalid, giving errors.
	 *
	 * @since 2.1.0
	 *
	 * @return bool Whether the validator found any errors.
	 */
	public function had_errors() {
		return ! empty( $this->errors );
	}

	/**
	 * Get the errors.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] The messages and the fields they relate to.
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Push a field onto the field stack.
	 *
	 * @since 2.1.0
	 *
	 * @param string $field The field.
	 */
	public function push_field( $field ) {
		$this->field_stack[] = $field;
	}

	/**
	 * Pop a field off of the field stack.
	 *
	 * @since 2.1.0
	 */
	public function pop_field() {
		array_pop( $this->field_stack );
	}

	/**
	 * Get the current field hierarchy.
	 *
	 * @since 2.1.0
	 *
	 * @return string[] The field stack.
	 */
	public function get_field_stack() {
		return $this->field_stack;
	}

	/**
	 * Get the reaction settings.
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Get the reaction object.
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_Hook_ReactionI|false The reaction, or false if not set.
	 */
	public function get_reaction() {
		return $this->reaction;
	}

	/**
	 * Get the reaction ID.
	 *
	 * @since 2.1.0
	 *
	 * @return int|false The reaction ID, or false if no reaction is set.
	 */
	public function get_id() {

		if ( ! $this->reaction ) {
			return false;
		}

		return $this->reaction->get_id();
	}

	/**
	 * Get the slug of the event this reaction is for.
	 *
	 * @since 2.1.0
	 *
	 * @return string The event slug.
	 */
	public function get_event_slug() {
		return $this->event_slug;
	}

	/**
	 * Get the slug of the reactor this reaction is for.
	 *
	 * @since 2.1.0
	 *
	 * @return string The reactor slug.
	 */
	public function get_reactor_slug() {
		return $this->reactor_slug;
	}

	/**
	 * Get a piece of metadata for this reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param string $key The meta key.
	 *
	 * @return mixed The meta value.
	 */
	public function get_meta( $key ) {

		if ( ! isset( $this->settings[ $key ] ) ) {
			return null;
		}

		return $this->settings[ $key ];
	}

	/**
	 * Get all metadata for this reaction.
	 *
	 * @since 2.1.0
	 *
	 * @return array The reaction metadata.
	 */
	public function get_all_meta() {
		return $this->settings;
	}
}

// EOF
