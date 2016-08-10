<?php

/**
 * Entity post class.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Represents a Post.
 *
 * @since 2.1.0
 */
class WordPoints_Entity_Post
	extends WordPoints_Entity_Stored_DB_Table
	implements WordPoints_Entity_Restricted_VisibilityI {

	/**
	 * @since 2.1.0
	 */
	protected $wpdb_table_name = 'posts';

	/**
	 * @since 2.1.0
	 */
	protected $id_field = 'ID';

	/**
	 * @since 2.1.0
	 */
	protected $getter = 'get_post';

	/**
	 * @since 2.1.0
	 */
	protected $human_id_field = 'post_title';

	/**
	 * @since 2.1.0
	 */
	public function get_title() {

		$post_type = get_post_type_object( substr( $this->slug, 5 /* post\ */ ) );

		if ( $post_type ) {
			return $post_type->labels->singular_name;
		} else {
			return $this->slug;
		}
	}

	/**
	 * @since 2.1.0
	 */
	public function user_can_view( $user_id, $id ) {
		return user_can( $user_id, 'read_post', $id );
	}
}

// EOF
