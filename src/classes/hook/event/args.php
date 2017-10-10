<?php

/**
 * Hook event args class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Represents the args associated with a hook event.
 *
 * This is similar to the regular entity hierarchy, except that it handles errors
 * with the reaction validators, and accepts an array of hook args instead of
 * requiring entity objects to be passed directly.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Event_Args extends WordPoints_Entity_Hierarchy {

	/**
	 * Whether the event is repeatable.
	 *
	 * @since 2.1.0
	 *
	 * @var bool
	 */
	protected $is_repeatable = true;

	/**
	 * The signature args for this event.
	 *
	 * @since 2.3.0
	 *
	 * @var WordPoints_Entity[]
	 */
	protected $signature_args = array();

	/**
	 * The slug of the primary arg for this event, if it has one.
	 *
	 * @since 2.1.0
	 * @deprecated 2.3.0 Use self::$signature_args instead.
	 *
	 * @var string|false
	 */
	protected $primary_arg_slug = false;

	/**
	 * The validator associated with the current hook reaction.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_Reaction_Validator
	 */
	protected $validator;

	/**
	 * Whether to push fields onto the validator when we descend into the hierarchy.
	 *
	 * This will usually be true, however, when we are getting a value from the
	 * hierarchy, we aren't actually descending into a sub-field, but descending
	 * down the arg hierarchy stored within a field.
	 *
	 * @since 2.1.0
	 *
	 * @var bool
	 */
	protected $push_on_descend = true;

	/**
	 * Construct the object with the arg objects.
	 *
	 * @param WordPoints_Hook_ArgI[] $args The hook args.
	 */
	public function __construct( array $args ) {

		parent::__construct();

		foreach ( $args as $arg ) {

			$entity = $arg->get_entity();
			$slug   = $arg->get_slug();

			if ( ! $entity instanceof WordPoints_Entity ) {
				continue;
			}

			$this->entities[ $slug ] = $entity;

			if ( ! $arg->is_stateful() ) {

				// If any of the args aren't stateful the event isn't repeatable.
				$this->is_repeatable = false;

				$this->signature_args[ $slug ] = $entity;

				// Back-compat. Some events have multiple "primary" args.
				if ( ! $this->primary_arg_slug ) {
					$this->primary_arg_slug = $slug;
				}
			}
		}
	}

	/**
	 * Whether the event is repeatable.
	 *
	 * An arg that has its status modified is called the primary arg of the event,
	 * because each event can have no more than one of these. An arg whose status is
	 * not toggled is called a stateful arg. An event is repeatable if it has no
	 * primary arg, only stateful ones: in that case, it is possible for the event to
	 * occur with the same args multiple times in a row without being reversed in
	 * between.
	 *
	 * @since 2.1.0
	 *
	 * @return bool Whether the event is repeatable.
	 */
	public function is_event_repeatable() {
		return $this->is_repeatable;
	}

	/**
	 * Get the signature args for this event.
	 *
	 * @since 2.3.0
	 *
	 * @return WordPoints_Entity[]|false The entity objects for the signature
	 *                                   args indexed by arg slug, or false if this
	 *                                   event has none.
	 */
	public function get_signature_args() {

		if ( ! $this->signature_args ) {
			return false;
		}

		return $this->signature_args;
	}

	/**
	 * Get the primary arg for this event.
	 *
	 * @since 2.1.0
	 * @deprecated 2.3.0 Use self::get_signature_args() instead.
	 *
	 * @return WordPoints_Entity|false The entity objects for the primary arg, or
	 *                                 false if this entity has none.
	 */
	public function get_primary_arg() {

		_deprecated_function(
			__METHOD__
			, '2.3.0'
			, __CLASS__ . '::get_signature_args()'
		);

		if ( ! $this->primary_arg_slug ) {
			return false;
		}

		return $this->entities[ $this->primary_arg_slug ];
	}

	/**
	 * Get the stateful args for this event.
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_Entity[] The entity objects for the stateful args.
	 */
	public function get_stateful_args() {

		return array_diff_key( $this->entities, $this->signature_args );
	}

	/**
	 * Get the validator object.
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_Hook_Reaction_Validator|null The validator or null if none.
	 */
	public function get_validator() {
		return $this->validator;
	}

	/**
	 * Set the validator for the current reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_Reaction_Validator $validator The validator.
	 */
	public function set_validator( WordPoints_Hook_Reaction_Validator $validator ) {
		$this->validator = $validator;
	}

	/**
	 * @since 2.1.0
	 */
	public function descend( $child_slug ) {

		$result = parent::descend( $child_slug );

		// Just in case no validator has been set.
		if ( ! $this->validator ) {
			return $result;
		}

		if ( ! $result ) {

			if ( ! isset( $this->current ) ) {

				$this->validator->add_error(
					sprintf(
						// translators: Arg slug.
						__( 'The &#8220;%s&#8221; arg is not registered for this event.', 'wordpoints' )
						, $child_slug
					)
				);

			} elseif ( ! ( $this->current instanceof WordPoints_Entity_ParentI ) ) {

				$this->validator->add_error(
					sprintf(
						// translators: Arg name.
						__( 'Cannot get descendant of %s: not a parent.', 'wordpoints' )
						, $this->current->get_title()
					)
				);

			} else {

				$child_arg = $this->current->get_child( $child_slug );

				if ( ! $child_arg ) {
					$this->validator->add_error(
						sprintf(
							// translators: 1. Arg slug; 2. Other arg slug.
							__( '%1$s does not have a child &#8220;%2$s&#8221;.', 'wordpoints' )
							, $this->current->get_slug()
							, $child_slug
						)
						, $this->push_on_descend ? $child_slug : null
					);
				}

			} // End if ( no current ) elseif ( current not parent ) else {}.

		} elseif ( $this->push_on_descend ) {

			$this->validator->push_field( $child_slug );

		} // End if ( ! $result ) elseif ( $this->push_on_descend ).

		return $result;
	}

	/**
	 * @since 2.1.0
	 */
	public function ascend() {

		$ascended = parent::ascend();

		if ( $ascended && $this->validator ) {
			$this->validator->pop_field();
		}

		return $ascended;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_from_hierarchy( array $hierarchy ) {

		$this->push_on_descend = false;
		$entityish             = parent::get_from_hierarchy( $hierarchy );
		$this->push_on_descend = true;

		return $entityish;
	}
}

// EOF
