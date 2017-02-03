<?php

/**
 * Meta table stored entity attribute class.
 *
 * @package WordPoints\Entities
 * @since 2.3.0
 */

/**
 * Represents an entity attribute that is stored in a meta table in the database.
 *
 * @since 2.3.0
 */
abstract class WordPoints_Entity_Attr_Stored_DB_Table_Meta
	extends WordPoints_Entity_Attr
	implements WordPoints_Entityish_StoredI {

	/**
	 * The type of meta (post, user, comment, etc.).
	 *
	 * You must either define this or override the get_attr_value_from_entity()
	 * method.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	protected $meta_type;

	/**
	 * The name of the $wpdb property that holds the name of the table.
	 *
	 * This will be defined automatically if you define the $meta_key property and
	 * this value is left empty.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	protected $wpdb_table_name;

	/**
	 * The field where the ID of the entity is stored in the table.
	 *
	 * This will be defined automatically if you define the $meta_key property and
	 * this value is left empty.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	protected $entity_id_field;

	/**
	 * The value of the meta key field in a row where this attribute is stored.
	 *
	 * You must either define this or override the get_attr_value_from_entity()
	 * method.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	protected $meta_key;

	/**
	 * The field where the name of the attribute is stored in the table.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	protected $meta_key_field = 'meta_key';

	/**
	 * The field where the value of the attribute is stored in the table.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	protected $meta_value_field = 'meta_value';

	/**
	 * @since 2.3.0
	 */
	public function __construct( $slug ) {

		if ( $this->meta_type ) {

			if ( ! $this->wpdb_table_name ) {
				$this->wpdb_table_name = $this->meta_type . 'meta';
			}

			if ( ! $this->entity_id_field ) {
				$this->entity_id_field = $this->meta_type . '_id';
			}
		}

		parent::__construct( $slug );
	}

	/**
	 * @since 2.3.0
	 */
	protected function get_attr_value_from_entity( WordPoints_Entity $entity ) {

		$entity_id = $entity->get_the_id();

		if ( ! $entity_id ) {
			return null;
		}

		return get_metadata(
			$this->meta_type
			, $entity_id
			, $this->meta_key
			, true
		);
	}

	/**
	 * @since 2.3.0
	 */
	public function get_storage_info() {

		return array(
			'type' => 'db',
			'info' => array(
				'type'             => 'meta_table',
				'table_name'       => $GLOBALS['wpdb']->{$this->wpdb_table_name},
				'meta_key'         => $this->meta_key,
				'meta_key_field'   => $this->meta_key_field,
				'meta_value_field' => $this->meta_value_field,
				'entity_id_field'  => $this->entity_id_field,
			),
		);
	}
}

// EOF
