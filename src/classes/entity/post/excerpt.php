<?php

/**
 * Post excerpt entity attribute class.
 *
 * @package WordPoints\Entities
 * @since 2.3.0
 */

/**
 * Represents a Post's excerpt attribute.
 *
 * This will only be set when the user has entered a hand-crafted excerpt in the meta
 * box on the post edit screen, even though the theme may auto-generate excerpts for
 * all posts.
 *
 * @since 2.3.0
 */
class WordPoints_Entity_Post_Excerpt extends WordPoints_Entity_Attr_Field {

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
	protected $field = 'post_excerpt';

	/**
	 * @since 2.3.0
	 */
	public function get_title() {
		return _x( 'Excerpt', 'post entity', 'wordpoints' );
	}
}

// EOF
