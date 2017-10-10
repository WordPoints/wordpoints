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
	 * There is no need to define this in your child class, it will automatically be
	 * set to the parent entity slug that is passed in when the object is
	 * constructed.
	 *
	 * @since 2.1.0
	 * @since 2.4.0 Now automatically set to the parent entity slug passed to the
	 *              constructor.
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
	 * The GUID(s) of the related entity(ies).
	 *
	 * @since 2.4.0
	 *
	 * @var array
	 */
	protected $the_guids;

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
			$slug     = substr( $slug, 0, -2 );
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
	 * @return mixed The ID (or array of IDs) of the related entity (or entities).
	 */
	protected function get_related_entity_ids( WordPoints_Entity $entity ) {
		return $entity->get_the_attr_value( $this->related_ids_field );
	}

	/**
	 * Gets the GUID(s) of the related entity(ies).
	 *
	 * By default, it gets the related ID(s) with {@see self::get_related_entity_ids()}
	 * and then generates the GUID(s) from them. This is possible because the related
	 * entity usually comes from a context that is either the same as the context of
	 * the primary entity, or the context is a just higher level in the same context
	 * hierarchy. For relationships where this is not the case, this method would
	 * need to be overridden instead.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Entity $entity The primary entity object.
	 *
	 * @return mixed The GUID (or array of GUIDs) of the related entity(ies), or
	 *               false on failure.
	 */
	protected function get_related_entity_guids( WordPoints_Entity $entity ) {

		$ids = $this->get_related_entity_ids( $entity );

		if ( ! $ids ) {
			return false;
		}

		return $this->get_related_guids_from_ids( $ids );
	}

	/**
	 * Gets the GUID(s) of the related entity(ies) from the ID(s)
	 *
	 * @since 2.4.0
	 *
	 * @param string|int|array $related_ids The related entity ID(s).
	 *
	 * @return array|bool The related entity GUID(s), or false on failure.
	 */
	protected function get_related_guids_from_ids( $related_ids ) {

		$parsed_slug = $this->parse_slug( $this->get_related_entity_slug() );
		$slug        = $parsed_slug['slug'];

		/** @var WordPoints_Entity $related_entity */
		$related_entity = wordpoints_entities()->get( $slug );

		if ( ! $related_entity ) {
			return false;
		}

		$context = wordpoints_entities_get_current_context_id(
			$related_entity->get_context()
		);

		if ( is_array( $related_ids ) ) {

			$related_guids = array();

			foreach ( $related_ids as $id ) {
				$related_guids[] = array( $slug => $id ) + $context;
			}

		} else {
			$related_guids = array( $slug => $related_ids ) + $context;
		}

		return $related_guids;
	}

	/**
	 * Extracts just the ID(s) from one or more GUIDs.
	 *
	 * @since 2.4.0
	 *
	 * @param array $guids The GUID or array of GUIDs.
	 *
	 * @return string|int|array The ID or array of IDs.
	 */
	protected function get_ids_from_guids( $guids ) {

		if ( is_array( reset( $guids ) ) ) {
			$ids = array_map( 'array_shift', $guids );
		} else {
			$ids = array_shift( $guids );
		}

		return $ids;
	}

	//
	// Public.
	//

	/**
	 * @since 2.4.0
	 */
	public function __construct( $slug, $parent_slug = null ) {

		// Back-compat for pre-2.3.0, just in case someone is manually constructing.
		if ( isset( $parent_slug ) ) {
			$this->primary_entity_slug = $parent_slug;
		}

		parent::__construct( $slug );
	}

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
			$child->set_the_value( $this->the_guids );
		}

		return $child;
	}

	/**
	 * @since 2.4.0
	 */
	public function set_the_value( $value ) {

		$this->the_value = null;
		$this->the_guids = null;

		// If this is a GUID or array of GUIDs for the related entity(ies).
		if (
			is_array( $value )
			&& ( is_array( reset( $value ) ) || is_string( key( $value ) ) )
		) {

			$this->the_guids = $value;
			$ids             = $this->get_ids_from_guids( $value );

		} else {

			// Otherwise, this is an ID or array of IDs for the related entity(ies).
			$ids = $value;

			/** @var WordPoints_Entity $entity */
			$entity = wordpoints_entities()->get( $this->get_primary_entity_slug() );

			if ( ! $entity ) {
				return false;
			}

			$context = wordpoints_entities_get_current_context_id(
				$entity->get_context()
			);

			if ( false === $context ) {
				return false;
			}

			/** @var WordPoints_Entity_Contexts $contexts */
			$contexts = wordpoints_entities()->get_sub_app( 'contexts' );

			if ( ! $contexts->switch_to( $context ) ) {
				return false;
			}

			$guids = $this->get_related_guids_from_ids( $ids );

			$contexts->switch_back();

			if ( ! $guids ) {
				return false;
			}

			$this->the_guids = $guids;
		}

		return parent::set_the_value( $ids );
	}

	/**
	 * @since 2.1.0
	 */
	public function set_the_value_from_entity( WordPoints_Entity $entity ) {

		$this->the_value = null;
		$this->the_guids = null;

		// Ensure that we are in the correct context when getting the IDs.
		$the_context = $entity->get_the_context();

		if ( null === $the_context ) {
			return false;
		}

		/** @var WordPoints_Entity_Contexts $contexts */
		$contexts = wordpoints_entities()->get_sub_app( 'contexts' );

		if ( ! $contexts->switch_to( $the_context ) ) {
			return false;
		}

		$related_guids = $this->get_related_entity_guids( $entity );

		$contexts->switch_back();

		if ( ! $related_guids ) {
			return false;
		}

		// For legacy reasons, the value is just the ID(s).
		$this->the_value = $this->get_ids_from_guids( $related_guids );
		$this->the_guids = $related_guids;

		return true;
	}
}

// EOF
