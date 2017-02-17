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
	extends WordPoints_Entity_Relationship_Dynamic
	implements WordPoints_Entityish_StoredI {

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
	protected function get_related_entity_ids( WordPoints_Entity $entity ) {
		return $entity->get_the_attr_value( $this->related_ids_field );
	}

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

	/**
	 * @since 2.3.0
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
