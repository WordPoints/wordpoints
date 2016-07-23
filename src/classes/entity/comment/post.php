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
 */
class WordPoints_Entity_Comment_Post
	extends WordPoints_Entity_Relationship_Dynamic
	implements WordPoints_Entityish_StoredI {

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

	/**
	 * @since 2.1.0
	 */
	protected function get_related_entity_ids( WordPoints_Entity $entity ) {
		return $entity->get_the_attr_value( $this->related_ids_field );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_storage_info() {
		return array(
			'type' => 'db',
			'info' => array(
				'type' => 'field',
				'field' => $this->related_ids_field,
			),
		);
	}
}

// EOF
