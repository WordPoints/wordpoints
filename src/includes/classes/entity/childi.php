<?php

/**
 * Interface for an entity child.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Defines the API for an entity child.
 *
 * @since 2.1.0
 */
interface WordPoints_Entity_ChildI {

	/**
	 * Set the value of the child from an entity.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Entity $entity The entity.
	 *
	 * @return bool Whether the value was set successfully.
	 */
	public function set_the_value_from_entity( WordPoints_Entity $entity );
}

// EOF
