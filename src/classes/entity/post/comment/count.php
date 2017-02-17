<?php

/**
 * Post comment count entity attribute class.
 *
 * @package WordPoints\Entities
 * @since 2.3.0
 */

/**
 * Represents a Post's comment count attribute.
 *
 * @since 2.3.0
 */
class WordPoints_Entity_Post_Comment_Count extends WordPoints_Entity_Attr_Field {

	/**
	 * @since 2.3.0
	 */
	protected $storage_type = 'db';

	/**
	 * @since 2.3.0
	 */
	protected $data_type = 'integer';

	/**
	 * @since 2.3.0
	 */
	protected $field = 'comment_count';

	/**
	 * @since 2.3.0
	 */
	public function get_title() {
		return _x( 'Comment Count', 'post entity', 'wordpoints' );
	}
}

// EOF
