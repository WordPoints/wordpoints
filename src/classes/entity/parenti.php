<?php

/**
 * Interface for things that are parents of entities.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Defines the API for a thing that is a parent of other entities.
 *
 * @since 2.1.0
 */
interface WordPoints_Entity_ParentI {

	/**
	 * Get a child of this entityish thing.
	 *
	 * If the value of the parent is set, the value of the child will be set also.
	 *
	 * @since 2.1.0
	 *
	 * @param string $child_slug The slug of the child.
	 *
	 * @return WordPoints_EntityishI|false The child, or false if not found.
	 */
	public function get_child( $child_slug );
}

// EOF
