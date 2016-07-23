<?php

/**
 * Entity relationship class.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Represents the relationship between one type of entity and another.
 *
 * Relationships are intended to be unidirectional. For example, a relationship that
 * has a Post as the primary entity and a User as the secondary entity, and thus
 * represents a Post author, does not also represent the relationship between that
 * User and all of the other Posts that they have authored. You can get the author
 * from the post using such a relationship object, but not the post(s) from the user.
 *
 * @since 2.1.0
 */
abstract class WordPoints_Entity_Relationship
	extends WordPoints_Entityish
	implements WordPoints_Entity_ParentI, WordPoints_Entity_ChildI {

	//
	// Protected.
	//

	/**
	 * The slug of the primary entity type.
	 *
	 * You must either define this or override get_primary_entity_slug().
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $primary_entity_slug;

	/**
	 * The slug of the related entity type.
	 *
	 * You must either define this or override get_related_entity_slug().
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $related_entity_slug;

	/**
	 * The field on the primary entity where the related entity IDs are stored.
	 *
	 * You must either define this or override get_related_entity_ids().
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $related_ids_field;

	/**
	 * Parse an entity slug.
	 *
	 * This makes possible support for one-to-many relationships via use of an array
	 * syntax: entity{}.
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug The slug to parse.
	 *
	 * @return array The parsed slug in the 'slug' key and whether it is an array in
	 *               the 'is_array' key.
	 */
	protected function parse_slug( $slug ) {

		$is_array = false;

		if ( '{}' === substr( $slug, -2 ) ) {
			$is_array = true;
			$slug = substr( $slug, 0, -2 );
		}

		return array( 'slug' => $slug, 'is_array' => $is_array );
	}

	/**
	 * Get the ID(s) of the related entity(ies).
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Entity $entity The entity.
	 *
	 * @return mixed The ID (array of or IDs) of the related entity (or entities).
	 */
	protected function get_related_entity_ids( WordPoints_Entity $entity ) {
		return $entity->get_the_attr_value( $this->related_ids_field );
	}

	//
	// Public.
	//

	/**
	 * Get the slug of the primary entity.
	 *
	 * @since 2.1.0
	 *
	 * @return string The slug of the primary entity.
	 */
	public function get_primary_entity_slug() {
		return $this->primary_entity_slug;
	}

	/**
	 * Get the slug of the related entity.
	 *
	 * @since 2.1.0
	 *
	 * @return string the slug of the related entity.
	 */
	public function get_related_entity_slug() {
		return $this->related_entity_slug;
	}

	/**
	 * Get the related entity, or array of entities if a one-to-many relationship.
	 *
	 * @since 2.1.0
	 *
	 * @param string $child_slug The slug of the related entity.
	 *
	 * @return WordPoints_Entity_Array|WordPoints_Entity|false An entity or array of
	 *                                                         entities, or false.
	 */
	public function get_child( $child_slug ) {

		if ( $child_slug !== $this->get_related_entity_slug() ) {
			return false;
		}

		$parsed_slug = $this->parse_slug( $child_slug );

		if ( $parsed_slug['is_array'] ) {
			$child = new WordPoints_Entity_Array( $parsed_slug['slug'] );
		} else {
			$child = wordpoints_entities()->get( $parsed_slug['slug'] );
		}

		if ( isset( $this->the_value ) ) {
			$child->set_the_value( $this->the_value );
		}

		return $child;
	}

	/**
	 * @since 2.1.0
	 */
	public function set_the_value_from_entity( WordPoints_Entity $entity ) {

		$this->the_value = null;

		$related_ids = $this->get_related_entity_ids( $entity );

		if ( ! $related_ids ) {
			return false;
		}

		$this->the_value = $related_ids;

		return true;
	}
}

// EOF
