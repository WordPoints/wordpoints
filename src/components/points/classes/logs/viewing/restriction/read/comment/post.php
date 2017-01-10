<?php

/**
 * Read comment post points logs viewing restriction.
 *
 * @package WordPoints
 * @since   2.2.0
 */

/**
 * Restriction based on whether the user can view the post the comment is on.
 *
 * @since 2.2.0
 */
class WordPoints_Points_Logs_Viewing_Restriction_Read_Comment_Post
	extends WordPoints_Points_Logs_Viewing_Restriction_Read_Post {

	/**
	 * @since 2.2.0
	 */
	public function get_description() {
		return __( 'This log entry is only visible to users who can view the post that the comment is on.', 'wordpoints' );
	}

	/**
	 * @since 2.2.0
	 */
	protected function get_post_id() {

		$comment_id = wordpoints_get_points_log_meta(
			$this->log->id
			, 'comment_id'
			, true
		);

		if ( $comment_id ) {
			$comment = get_comment( $comment_id );

			if ( $comment ) {
				return $comment->comment_post_ID;
			}
		}

		return false;
	}
}

// EOF
