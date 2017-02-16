<?php

/**
 * Post modified date entity attribute class.
 *
 * @package WordPoints\Entities
 * @since 2.3.0
 */

/**
 * Represents a Post's modified date attribute.
 *
 * @since 2.3.0
 */
class WordPoints_Entity_Post_Date_Modified extends WordPoints_Entity_Attr_Field {

	/**
	 * @since 2.3.0
	 */
	protected $storage_type = 'db';

	/**
	 * @since 2.3.0
	 */
	protected $data_type = 'mysql_datetime';

	/**
	 * @since 2.3.0
	 */
	protected $field = 'post_modified';

	/**
	 * @since 2.3.0
	 */
	public function get_title() {
		return _x( 'Date Modified', 'post entity', 'wordpoints' );
	}
}

// EOF
