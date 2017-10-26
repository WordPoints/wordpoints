<?php

/**
 * Term name entity attribute class.
 *
 * @package WordPoints\Entities
 * @since 2.4.0
 */

/**
 * Represents a Term's name attribute.
 *
 * @since 2.4.0
 */
class WordPoints_Entity_Term_Name extends WordPoints_Entity_Attr_Field {

	/**
	 * @since 2.4.0
	 */
	protected $storage_type = 'db';

	/**
	 * @since 2.4.0
	 */
	protected $data_type = 'text';

	/**
	 * @since 2.4.0
	 */
	protected $field = 'name';

	/**
	 * @since 2.4.0
	 */
	public function get_title() {
		return _x( 'Name', 'term entity', 'wordpoints' );
	}
}

// EOF
