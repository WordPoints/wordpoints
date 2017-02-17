<?php

/**
 * Comment date entity attribute class.
 *
 * @package WordPoints\Entities
 * @since 2.3.0
 */

/**
 * Represents a Comment's date attribute.
 *
 * @since 2.3.0
 */
class WordPoints_Entity_Comment_Date extends WordPoints_Entity_Attr_Field {

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
	protected $field = 'comment_date';

	/**
	 * @since 2.3.0
	 */
	public function get_title() {
		return _x( 'Date', 'comment entity', 'wordpoints' );
	}
}

// EOF
