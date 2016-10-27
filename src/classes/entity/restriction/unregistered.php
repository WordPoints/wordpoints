<?php

/**
 * Unregistered entity restriction class.
 *
 * @package WordPoints
 * @since   2.2.0
 */

/**
 * Restriction rule for entities that are no longer registered.
 *
 * If an entity type is not found, we have no way of determining whether it is safe
 * for the user to view it.
 *
 * @since 2.2.0
 */
class WordPoints_Entity_Restriction_Unregistered
	implements WordPoints_Entity_RestrictionI {

	/**
	 * Whether the entity type is registered.
	 *
	 * @since 2.2.0
	 *
	 * @var bool
	 */
	protected $is_registered;

	/**
	 * @since 2.2.0
	 */
	public function __construct( $entity_id, array $hierarchy ) {
		$this->is_registered = wordpoints_entities()->is_registered( $hierarchy[0] );
	}

	/**
	 * @since 2.2.0
	 */
	public function user_can( $user_id ) {
		return $this->is_registered;
	}

	/**
	 * @since 2.2.0
	 */
	public function applies() {
		return ! $this->is_registered;
	}
}

// EOF
