<?php

/**
 * Table stored entity attribute class.
 *
 * @package WordPoints\Entities
 * @since 2.4.0
 */

/**
 * Represents an entity attribute that is stored in a table in the database.
 *
 * @since 2.4.0
 */
abstract class WordPoints_Entity_Attr_Stored_DB_Table
	extends WordPoints_Entity_Attr
	implements WordPoints_Entityish_StoredI {

	/**
	 * The name of the $wpdb property that holds the name of the table.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $wpdb_table_name;

	/**
	 * The field where the ID of the entity is stored in the table.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $entity_id_field;

	/**
	 * The field where the value of the attribute is stored in the table.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $attr_field;

	/**
	 * @since 2.4.0
	 */
	public function get_storage_info() {

		return array(
			'type' => 'db',
			'info' => array(
				'type'            => 'table',
				'table_name'      => $GLOBALS['wpdb']->{$this->wpdb_table_name},
				'attr_field'      => $this->attr_field,
				'entity_id_field' => $this->entity_id_field,
			),
		);
	}
}

// EOF
