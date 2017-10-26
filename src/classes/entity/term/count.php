<?php

/**
 * Term count entity attribute class.
 *
 * @package WordPoints\Entities
 * @since 2.4.0
 */

/**
 * Represents a Term's count attribute.
 *
 * @since 2.4.0
 */
class WordPoints_Entity_Term_Count
	extends WordPoints_Entity_Attr_Stored_DB_Table {

	/**
	 * @since 2.4.0
	 */
	protected $data_type = 'integer';

	/**
	 * @since 2.4.0
	 */
	protected $wpdb_table_name = 'term_taxonomy';

	/**
	 * @since 2.4.0
	 */
	protected $entity_id_field = 'term_id';

	/**
	 * @since 2.4.0
	 */
	protected $attr_field = 'count';

	/**
	 * @since 2.4.0
	 */
	public function get_title() {
		return _x( 'Count', 'term entity', 'wordpoints' );
	}

	/**
	 * @since 2.4.0
	 */
	protected function get_attr_value_from_entity( WordPoints_Entity $entity ) {
		return $entity->get_the_attr_value( 'count' );
	}
}

// EOF
