<?php

/**
 * Stored entityish interface.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Implemented by entity-like objects that are stored somewhere.
 *
 * Both entities and entity children can be stored somewhere. This interface defines
 * a method that returns information about where an entity/entity child is stored.
 *
 * @since 2.1.0
 */
interface WordPoints_Entityish_StoredI {

	/**
	 * Get information about where this entity-like object is stored.
	 *
	 * @since 2.1.0
	 *
	 * @return array {
	 *         Storage info for the entity.
	 *
	 *         @type string $type The type of storage medium used. Examples: 'db',
	 *                            'array'.
	 *         @type array  $info More specific information about how this is stored.
	 * }
	 */
	public function get_storage_info();
}

// EOF
