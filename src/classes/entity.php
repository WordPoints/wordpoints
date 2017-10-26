<?php

/**
 * Class for representing an entity.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Represents an entity.
 *
 * An entity can be just about anything, like a Post, a User, a Comment, a Site, etc.
 * This class defines a single common interface for interacting with entities. This
 * is useful when some code needs to be able to work with several different kinds of
 * entities and can't know beforehand what they are.
 *
 * Each different type of entity is defined by a child of this class.
 *
 * @since 2.1.0
 */
abstract class WordPoints_Entity
	extends WordPoints_Entityish
	implements WordPoints_Entity_ParentI {

	//
	// Protected Methods.
	//

	/**
	 * The context in which this type of entity exists.
	 *
	 * Most entities exist only in the context of a specific site on the network (in
	 * multisite—when not on multisite they are just global to the install). Entities
	 * with other contexts need to specify that by overriding this property.
	 *
	 * You must either define this or override get_context() in your subclass.
	 *
	 * @since 2.1.0
	 *
	 * @see wordpoints_entities_get_current_context_id()
	 *
	 * @var string
	 */
	protected $context = 'site';

	/**
	 * The field the entity is identified by.
	 *
	 * You must either define this or override get_id_field() in your subclass.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $id_field;

	/**
	 * Whether the IDs of the entities are integers.
	 *
	 * If true, the ID will always be casted to an integer before it is returned.
	 *
	 * @since 2.4.0
	 *
	 * @var bool
	 */
	protected $id_is_int = false;

	/**
	 * The field the entity can be identified by humans by.
	 *
	 * You must either define this or override get_entity_human_id().
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $human_id_field;

	/**
	 * A function to call with an entity ID to retrieve that entity.
	 *
	 * You must either define this or override get_entity().
	 *
	 * @since 2.1.0
	 *
	 * @var callable
	 */
	protected $getter;

	/**
	 * The entity itself.
	 *
	 * This will probably always be an array or object.
	 *
	 * @since 2.1.0
	 *
	 * @var mixed
	 */
	protected $the_entity;

	/**
	 * The GUID of the context in which the entity exists.
	 *
	 * @since 2.1.0
	 *
	 * @see wordpoints_entities_get_current_context_id()
	 *
	 * @var array|false|null
	 */
	protected $the_context;

	/**
	 * Get an entity by its ID.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $id The unique ID of the entity.
	 *
	 * @return mixed The entity, or false if not found.
	 */
	protected function get_entity( $id ) {

		$entity = call_user_func( $this->getter, $id );

		if ( ! $this->is_entity( $entity ) ) {
			return false;
		}

		return $entity;
	}

	/**
	 * Checks if a value is an entity of this type.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $entity A value that might be an entity.
	 *
	 * @return bool Whether the passed value is an entity.
	 */
	protected function is_entity( $entity ) {

		if ( ! is_object( $entity ) && ! is_array( $entity ) ) {
			return false;
		}

		return (bool) $this->get_entity_id( $entity );
	}

	/**
	 * Get an entity from a (possibly) foreign context.
	 *
	 * Normally we only get entities from the current context. This method will grab
	 * an entity from a particular context, regardless of whether it is the current
	 * context or not.
	 *
	 * @since 2.2.0
	 *
	 * @param int|string $id      The unique ID of the entity.
	 * @param array      $context The context to get the entity from.
	 *
	 * @return mixed The entity, or false if not found.
	 */
	protected function get_entity_from_context( $id, $context ) {

		/** @var WordPoints_Entity_Contexts $contexts */
		$contexts = wordpoints_entities()->get_sub_app( 'contexts' );

		if ( ! $contexts->switch_to( $context ) ) {
			return false;
		}

		$entity = $this->get_entity( $id );

		$contexts->switch_back();

		return $entity;
	}

	/**
	 * Checks if a value is a GUID for an entity of this type.
	 *
	 * @since 2.2.0
	 *
	 * @param array $guid A value that might be an entity GUID.
	 *
	 * @return bool Whether the passed value is a GUID for an entity of this type.
	 */
	protected function is_guid( $guid ) {

		if ( ! is_array( $guid ) ) {
			return false;
		}

		$context = $this->get_context();

		return (
			isset( $guid[ $this->get_slug() ] )
			&& ( '' === $context || isset( $guid[ $context ] ) )
		);
	}

	/**
	 * Splits an entity GUID into the ID and context.
	 *
	 * @since 2.2.0
	 *
	 * @param array $guid An entity GUID.
	 *
	 * @return array {
	 *         @type string|int $id      The entity ID.
	 *         @type array      $context The entity context.
	 * }
	 */
	protected function split_guid( $guid ) {

		$slug    = $this->get_slug();
		$id      = $guid[ $slug ];
		$context = $guid;

		unset( $context[ $slug ] );

		return array( 'id' => $id, 'context' => $context );
	}

	/**
	 * Gets the value of one of an entity's attributes.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed  $entity An entity of this type.
	 * @param string $attr   The attribute whose value to get.
	 *
	 * @return mixed The value of the attribute of the entity.
	 */
	protected function get_attr_value( $entity, $attr ) {

		if ( is_array( $entity ) ) {
			if ( isset( $entity[ $attr ] ) ) {
				return $entity[ $attr ];
			}
		} else {
			if ( isset( $entity->{$attr} ) ) {
				return $entity->{$attr};
			}
		}

		return null;
	}

	/**
	 * Get the ID from an entity.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $entity The entity (usually object or array).
	 *
	 * @return mixed The ID of the entity.
	 */
	protected function get_entity_id( $entity ) {

		$id = $this->get_attr_value( $entity, $this->get_id_field() );

		if ( $this->id_is_int && null !== $id ) {
			$id = wordpoints_posint( $id );
		}

		return $id;
	}

	/**
	 * Get the human ID from an entity.
	 *
	 * The human ID is a human readable identifier for the entity, and may be
	 * different than the regular ID. It is also possible that the human ID will not
	 * always be unique.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $entity The entity (usually object or array).
	 *
	 * @return mixed The human ID of the entity.
	 */
	protected function get_entity_human_id( $entity ) {
		return $this->get_attr_value( $entity, $this->human_id_field );
	}

	//
	// Public Methods.
	//

	/**
	 * Get the slug of the context in which this type of entity exists.
	 *
	 * @since 2.1.0
	 *
	 * @see wordpoints_entities_get_current_context_id()
	 *
	 * @return string The slug of the context in which this type of entity exists.
	 */
	public function get_context() {
		return $this->context;
	}

	/**
	 * Get the attribute that holds the entity's unique ID.
	 *
	 * @since 2.1.0
	 *
	 * @return string The attribute that holds the entity's unique ID.
	 */
	public function get_id_field() {
		return $this->id_field;
	}

	/**
	 * Get the human ID for an entity.
	 *
	 * @since 2.1.0
	 *
	 * @see self::get_entity_human_id()
	 *
	 * @param mixed $id The ID of an entity.
	 *
	 * @return string|int|float|false The human identifier for the entity, or false.
	 */
	public function get_human_id( $id ) {

		$entity = $this->get_entity( $id );

		if ( ! $entity ) {
			return false;
		}

		return $this->get_entity_human_id( $entity );
	}

	/**
	 * Check if an entity exists, by ID.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $id The entity ID.
	 *
	 * @return bool Whether or not an entity with that ID exists.
	 */
	public function exists( $id ) {
		return (bool) $this->get_entity( $id );
	}

	/**
	 * Get a child of this entity.
	 *
	 * Entities can have children, which currently fall into two types: attributes
	 * and relationships.
	 *
	 * @since 2.1.0
	 *
	 * @param string $child_slug The slug of the child.
	 *
	 * @return WordPoints_Entityish|false The child's object, or false if not found.
	 */
	public function get_child( $child_slug ) {

		$children = wordpoints_entities()->get_sub_app( 'children' );

		$child = $children->get( $this->slug, $child_slug );

		if (
			isset( $this->the_value )
			&& $child instanceof WordPoints_Entity_ChildI
		) {
			$child->set_the_value_from_entity( $this );
		}

		return $child;
	}

	/**
	 * Set the value of this entity.
	 *
	 * This class can represent a type of entity generically (e.g., Post), or a
	 * specific entity of that type (the Post with ID 3). This function allows you to
	 * make this object instance represent a specific entity.
	 *
	 * If the value passed is not an entity, and is not a valid ID, it will be
	 * ignored and the value will not be set.
	 *
	 * @since 2.1.0
	 * @since 2.2.0 The $value parameter now accepts an entity GUID.
	 *
	 * @param mixed $value An entity or entity ID or GUID.
	 *
	 * @return bool Whether the value was set.
	 */
	public function set_the_value( $value ) {

		$this->the_value   = null;
		$this->the_entity  = null;
		$this->the_context = null;

		if ( $this->is_entity( $value ) ) {

			$entity = $value;
			$value  = $this->get_entity_id( $value );

		} elseif ( $this->is_guid( $value ) ) {

			$guid    = $this->split_guid( $value );
			$context = $guid['context'];
			$value   = $guid['id'];

			$entity = $this->get_entity_from_context( $guid['id'], $context );

		} else {

			$entity = $this->get_entity( $value );
		}

		if ( ! $entity ) {
			return false;
		}

		if ( ! isset( $context ) ) {
			$context = wordpoints_entities_get_current_context_id(
				$this->get_context()
			);
		}

		$this->the_value   = $value;
		$this->the_entity  = $entity;
		$this->the_context = $context;

		return true;
	}

	/**
	 * Get the value of one of this entity's attributes.
	 *
	 * @since 2.1.0
	 *
	 * @param string $attr The attribute to get the value of.
	 *
	 * @return mixed The value of the attribute.
	 */
	public function get_the_attr_value( $attr ) {
		return $this->get_attr_value( $this->the_entity, $attr );
	}

	/**
	 * Get the ID of the entity.
	 *
	 * @since 2.1.0
	 *
	 * @return mixed The ID of the entity.
	 */
	public function get_the_id() {

		$the_id = $this->get_the_value();

		if ( $this->id_is_int && null !== $the_id ) {
			$the_id = wordpoints_posint( $the_id );
		}

		return $the_id;
	}

	/**
	 * Get the human ID of the entity.
	 *
	 * @since 2.1.0
	 *
	 * @see self::get_entity_human_id()
	 *
	 * @return string|int|float|null The human identifier for the entity, or null.
	 */
	public function get_the_human_id() {
		return $this->get_entity_human_id( $this->the_entity );
	}

	/**
	 * Get the context in which the current entity exists.
	 *
	 * @since 2.1.0
	 *
	 * @see wordpoints_entities_get_current_context_id()
	 *
	 * @return array|null The context values indexed by context slugs.
	 */
	public function get_the_context() {
		return $this->the_context;
	}

	/**
	 * Get the Globally Unique ID of the entity.
	 *
	 * The GUID is an array of values that includes the GUID of the entity context
	 * in addition to the ID of the entity itself.
	 *
	 * The entity ID is first in the array, then the context IDs in ascending
	 * hierarchical order.
	 *
	 * @since 2.1.0
	 *
	 * @return array|false|null The GUID, false if it could not be determined, or
	 *                          null if the value isn't set.
	 */
	public function get_the_guid() {

		$guid = $this->get_the_context();

		if ( ! is_array( $guid ) ) {
			return $guid;
		}

		$guid = array( $this->slug => $this->get_the_id() ) + $guid;

		return $guid;
	}
}

// EOF
