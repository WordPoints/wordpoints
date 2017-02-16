<?php

/**
 * Post content entity attribute class.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Represents a Post's content attribute.
 *
 * @since 2.1.0
 */
class WordPoints_Entity_Post_Content extends WordPoints_Entity_Attr_Field {

	/**
	 * @since 2.1.0
	 */
	protected $storage_type = 'db';

	/**
	 * @since 2.1.0
	 */
	protected $data_type = 'text';

	/**
	 * @since 2.1.0
	 */
	protected $field = 'post_content';

	/**
	 * @since 2.1.0
	 */
	public function get_title() {
		return _x( 'Content', 'post entity', 'wordpoints' );
	}
}

// EOF
