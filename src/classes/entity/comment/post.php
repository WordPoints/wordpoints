<?php

/**
 * Comment post entity relationship class.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Represents the relationship between a Comment and its Post.
 *
 * @since 2.1.0
 * @since 2.3.0 Now extends WordPoints_Entity_Relationship_Dynamic_Stored_Field.
 */
class WordPoints_Entity_Comment_Post
	extends WordPoints_Entity_Relationship_Dynamic_Stored_Field {

	/**
	 * @since 2.3.0
	 */
	protected $storage_type = 'db';

	/**
	 * @since 2.1.0
	 */
	protected $primary_entity_slug = 'comment';

	/**
	 * @since 2.1.0
	 */
	protected $related_entity_slug = 'post';

	/**
	 * @since 2.1.0
	 */
	protected $related_ids_field = 'comment_post_ID';
}

// EOF
