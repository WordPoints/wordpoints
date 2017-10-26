<?php

/**
 * Entity array class.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Represents an array of entities.
 *
 * All of the entities an array must be of the same type. For example, you can
 * have an array of Posts, or an array of Users, but you cannot have an array of
 * Posts and Users both together.
 *
 * @since 2.1.0
 */
class WordPoints_Entity_Array extends WordPoints_Entityish {

	/**
	 * The slug of the type of the entities in this array.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $entity_slug;

	/**
	 * The object for the type of entity in this array.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Entity|false
	 */
	protected $entity_object;

	/**
	 * The objects for the entities in this array.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Entity[]
	 */
	protected $the_entities = array();

	/**
	 * Construct the array with the slug of the type of the entities it will contain.
	 *
	 * @since 2.1.0
	 *
	 * @param string $entity_slug The slug of the entity type.
	 */
	public function __construct( $entity_slug ) {

		$this->entity_slug   = $entity_slug;
		$this->entity_object = wordpoints_entities()->get( $this->entity_slug );

		parent::__construct( $this->entity_slug . '{}' );
	}

	/**
	 * Get the slug of the type of entities in this array.
	 *
	 * @since 2.1.0
	 *
	 * @return string The entity slug.
	 */
	public function get_entity_slug() {
		return $this->entity_slug;
	}

	/**
	 * Populate this array with some entities.
	 *
	 * @since 2.1.0
	 *
	 * @param array $values The entities or their IDs to populate the array with.
	 *
	 * @return bool Whether the value was set successfully.
	 */
	public function set_the_value( $values ) {

		$this->the_value    = array();
		$this->the_entities = array();

		if ( ! ( $this->entity_object instanceof WordPoints_Entity ) ) {
			return false;
		}

		foreach ( $values as $value ) {

			$entity = clone $this->entity_object;

			if ( $entity->set_the_value( $value ) ) {
				$this->the_entities[] = $entity;
				$this->the_value[]    = $entity->get_the_id();
			}
		}

		return true;
	}

	/**
	 * Get the entities in this array.
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_Entity[] The objects for the entities in this array.
	 */
	public function get_the_entities() {
		return $this->the_entities;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_title() {

		if ( $this->entity_object ) {

			return sprintf(
				// translators: Singular name of an item.
				__( '%s Collection', 'wordpoints' )
				, $this->entity_object->get_title()
			);

		} else {
			return __( 'Item Collection', 'wordpoints' );
		}
	}
}

// EOF
