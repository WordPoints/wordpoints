<?php

/**
 * Field stored entity relationship class.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Represents a relationship that is stored in a field of the primary entity.
 *
 * @since 2.1.0
 */
abstract class WordPoints_Entity_Relationship_Stored_Field
	extends WordPoints_Entity_Relationship
	implements WordPoints_Entityish_StoredI {

	/**
	 * The storage type for this relationship.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $storage_type;

	/**
	 * @since 2.1.0
	 */
	public function get_storage_info() {
		return array(
			'type' => $this->storage_type,
			'info' => array(
				'type'  => 'field',
				'field' => $this->related_ids_field,
			),
		);
	}
}

// EOF
