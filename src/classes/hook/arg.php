<?php

/**
 * Hook arg class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Represents a hook arg.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Arg implements WordPoints_Hook_ArgI {

	/**
	 * The slug of this arg.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The slug of the type of entity that this arg's value is.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $entity_slug;

	/**
	 * The action object.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_ActionI
	 */
	protected $action;

	/**
	 * Whether this arg is stateful or not.
	 *
	 * @since 2.1.0
	 *
	 * @var bool
	 */
	protected $is_stateful = false;

	/**
	 * Construct the arg with a slug.
	 *
	 * Slugs are typically the slug of the entity itself, but this isn't always the
	 * case. Sometimes more than one value associated with an event will be of the
	 * same type of entity. To work around this, the arg slug can also be an entity
	 * alias. Entity aliases are just entity slugs that are prefixed with an
	 * arbitrary string ending in a semicolon. For example, 'current:user' is an
	 * alias of the User entity.
	 *
	 * @since 2.1.0
	 *
	 * @param string                  $slug        The arg slug.
	 * @param string                  $action_slug The action slug.
	 * @param WordPoints_Hook_ActionI $action      The calling action's object.
	 */
	public function __construct(
		$slug,
		$action_slug = null,
		WordPoints_Hook_ActionI $action = null
	) {

		$this->slug   = $slug;
		$this->action = $action;

		$parts = explode( ':', $slug, 2 );

		if ( isset( $parts[1] ) ) {
			$this->entity_slug = $parts[1];
		} else {
			$this->entity_slug = $slug;
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
	public function get_entity_slug() {
		return $this->entity_slug;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_entity() {

		$entity = wordpoints_entities()->get(
			$this->get_entity_slug()
		);

		if ( $entity instanceof WordPoints_Entity ) {
			$value = $this->get_value();

			if ( $value ) {
				$entity->set_the_value( $value );
			}
		}

		return $entity;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_value() {

		if ( $this->action instanceof WordPoints_Hook_ActionI ) {
			return $this->action->get_arg_value( $this->slug );
		} else {
			return null;
		}
	}

	/**
	 * @since 2.1.0
	 */
	public function get_title() {

		$entity = $this->get_entity();

		if ( ! $entity ) {
			return $this->slug;
		}

		return $entity->get_title();
	}

	/**
	 * @since 2.1.0
	 */
	public function is_stateful() {
		return $this->is_stateful;
	}
}

// EOF
