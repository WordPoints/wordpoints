<?php

/**
 * Comment entity class.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Represents a Comment as an entity.
 *
 * @since 2.1.0
 * @since 2.2.0 No longer implements WordPoints_Entity_Restricted_VisibilityI.
 */
class WordPoints_Entity_Comment
	extends WordPoints_Entity_Stored_DB_Table {

	/**
	 * @since 2.1.0
	 */
	protected $wpdb_table_name = 'comments';

	/**
	 * @since 2.1.0
	 */
	protected $id_field = 'comment_ID';

	/**
	 * @since 2.1.0
	 */
	protected function get_entity( $id ) {

		// We must do this because the $id parameter is expected by reference.
		$comment = get_comment( $id );

		if ( ! $comment ) {
			return false;
		}

		return $comment;
	}

	/**
	 * @since 2.1.0
	 */
	protected function get_entity_human_id( $entity ) {
		return get_comment_excerpt( $entity->comment_ID );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_title() {
		return __( 'Comment', 'wordpoints' );
	}

	/**
	 * @since 2.1.0
	 * @deprecated 2.2.0 Use entity restrictions API instead.
	 */
	public function user_can_view( $user_id, $id ) {

		_deprecated_function( __METHOD__, '2.2.0' );

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );
		$restriction = $restrictions->get( $id, $this->get_slug(), 'view' );

		return $restriction->user_can( $user_id );
	}
}

// EOF
