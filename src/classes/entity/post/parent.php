<?php

/**
 * Post parent entity relationship class.
 *
 * @package WordPoints\Entities
 * @since 2.3.0
 */

/**
 * Represents the relationship between a Post and its parent Post.
 *
 * @since 2.3.0
 */
class WordPoints_Entity_Post_Parent
	extends WordPoints_Entity_Relationship_Dynamic_Stored_Field {

	/**
	 * @since 2.3.0
	 */
	protected $storage_type = 'db';

	/**
	 * @since 2.3.0
	 */
	protected $primary_entity_slug = 'post';

	/**
	 * @since 2.3.0
	 */
	protected $related_entity_slug = 'post';

	/**
	 * @since 2.3.0
	 */
	protected $related_ids_field = 'post_parent';

	/**
	 * @since 2.3.0
	 */
	public function get_title() {

		return sprintf(
			// translators: Singular post type name.
			_x( 'Parent %s', 'post entity', 'wordpoints' )
			, parent::get_title()
		);
	}
}

// EOF
