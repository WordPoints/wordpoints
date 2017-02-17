<?php

/**
 * Comment parent entity relationship class.
 *
 * @package WordPoints\Entities
 * @since 2.3.0
 */

/**
 * Represents the relationship between a Comment and its parent Comment.
 *
 * @since 2.3.0
 */
class WordPoints_Entity_Comment_Parent
	extends WordPoints_Entity_Relationship_Dynamic_Stored_Field {

	/**
	 * @since 2.3.0
	 */
	protected $storage_type = 'db';

	/**
	 * @since 2.3.0
	 */
	protected $primary_entity_slug = 'comment';

	/**
	 * @since 2.3.0
	 */
	protected $related_entity_slug = 'comment';

	/**
	 * @since 2.3.0
	 */
	protected $related_ids_field = 'comment_parent';

	/**
	 * @since 2.3.0
	 */
	public function get_title() {
		return _x( 'Parent Comment', 'comment entity', 'wordpoints' );
	}
}

// EOF
