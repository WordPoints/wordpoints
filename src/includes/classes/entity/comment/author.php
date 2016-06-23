<?php

/**
 * Comment author entity relationship class.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Represents the relationship between a Comment and its author.
 *
 * @since 2.1.0
 */
class WordPoints_Entity_Comment_Author extends WordPoints_Entity_Relationship_Stored_Field {

	/**
	 * @since 2.1.0
	 */
	protected $storage_type = 'db';

	/**
	 * @since 2.1.0
	 */
	protected $primary_entity_slug = 'comment';

	/**
	 * @since 2.1.0
	 */
	protected $related_entity_slug = 'user';

	/**
	 * @since 2.1.0
	 */
	protected $related_ids_field = 'user_id';

	/**
	 * @since 2.1.0
	 */
	public function get_title() {
		return __( 'Author', 'wordpoints' );
	}
}

// EOF
