<?php

/**
 * Enumerable entity interface.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Implemented by entities that are enumerable.
 *
 * Indicates that only a finite number of entities of this type will exist, and
 * that it is therefore appropriate to display a list of the entities in a select
 * box in the UI.
 *
 * @since 2.1.0
 */
interface WordPoints_Entity_EnumerableI {

	/**
	 * Get a list of the entities of this type.
	 *
	 * @since 2.1.0
	 *
	 * @return array The entity objects/IDs.
	 */
	public function get_enumerated_values();
}

// EOF
