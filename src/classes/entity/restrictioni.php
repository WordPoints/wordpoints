<?php

/**
 * Entity restriction interface.
 *
 * @package WordPoints
 * @since   2.2.0
 */

/**
 * Interface for objects that define entity restriction rules.
 *
 * @since 2.2.0
 */
interface WordPoints_Entity_RestrictionI {

	/**
	 * @since 2.2.0
	 *
	 * @param int|string $entity_id The ID of the entity the being checked.
	 * @param string[]   $hierarchy The hierarchy of this entity, consisting of the
	 *                              entity slug followed by the slugs leading to the
	 *                              descendant in question, if not the entity itself.
	 */
	public function __construct( $entity_id, array $hierarchy );

	/**
	 * Check whether the rule applies to this particular entity.
	 *
	 * @since 2.2.0
	 *
	 * @return bool Whether this rule applies to the entity.
	 */
	public function applies();

	/**
	 * Check if a user is restricted or not.
	 *
	 * @since 2.2.0
	 *
	 * @param int $user_id The ID of a user to check the restriction against.
	 *
	 * @return bool Whether this particular user is restricted.
	 */
	public function user_can( $user_id );
}

// EOF
