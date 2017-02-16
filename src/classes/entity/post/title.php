<?php

/**
 * Post title entity attribute class.
 *
 * @package WordPoints\Entities
 * @since 2.3.0
 */

/**
 * Represents a Post's title attribute.
 *
 * @since 2.3.0
 */
class WordPoints_Entity_Post_Title extends WordPoints_Entity_Attr_Field {

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
	protected $field = 'post_title';

	/**
	 * @since 2.3.0
	 */
	public function get_title() {
		return _x( 'Title', 'post entity', 'wordpoints' );
	}
}

// EOF
