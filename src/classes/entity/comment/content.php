<?php

/**
 * Comment content entity attribute class.
 *
 * @package WordPoints\Entities
 * @since 2.3.0
 */

/**
 * Represents a Comment's content attribute.
 *
 * @since 2.3.0
 */
class WordPoints_Entity_Comment_Content extends WordPoints_Entity_Attr_Field {

	/**
	 * @since 2.3.0
	 */
	protected $storage_type = 'db';

	/**
	 * @since 2.3.0
	 */
	protected $data_type = 'text';

	/**
	 * @since 2.3.0
	 */
	protected $field = 'comment_content';

	/**
	 * @since 2.3.0
	 */
	public function get_title() {
		return _x( 'Content', 'comment entity', 'wordpoints' );
	}
}

// EOF
