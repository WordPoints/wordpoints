<?php

/**
 * Nonpublic post status comment entity restriction class.
 *
 * @package WordPoints
 * @since   2.2.0
 */

/**
 * Restriction rule for comments on a post with a nonpublic status.
 *
 * @since 2.2.0
 */
class WordPoints_Entity_Restriction_Comment_Post_Status_Nonpublic
	extends WordPoints_Entity_Restriction_Post_Status_Nonpublic {

	/**
	 * @since 2.2.0
	 */
	protected function get_post_id() {

		$comment = get_comment( $this->entity_id );

		if ( $comment ) {
			return $comment->comment_post_ID;
		}

		return false;
	}
}

// EOF
