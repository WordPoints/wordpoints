<?php

/**
 * The comment points hook class.
 *
 * @package WordPoints\Points\Hooks
 * @since 1.4.0
 */

// Register the comment hook.
WordPoints_Points_Hooks::register( 'WordPoints_Comment_Points_Hook' );

/**
 * Comment points hook.
 *
 * This hook will award points when a user leaves a comment.
 *
 * @since 1.0.0
 * @since 1.4.0 No longer subtracts points for comment removal.
 * @since 1.8.0 Now extends WordPoints_Comment_Approved_Points_Hook_Base.
 */
class WordPoints_Comment_Points_Hook extends WordPoints_Comment_Approved_Points_Hook_Base {

	/**
	 * @since 1.8.0
	 */
	protected $log_type = 'comment_approve';

	/**
	 * Initialize the hook.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		parent::__construct(
			_x( 'Comment', 'points hook name', 'wordpoints' )
			, array(
				'description' => __( 'Leaving a new comment.', 'wordpoints' ),
				/* translators: the post type name. */
				'post_type_description' => __( 'Leaving a new comment on a %s.', 'wordpoints' ),
				/* translators: %s will be the post's title. */
				'log_text_post_title' => _x( 'Comment on %s.', 'points log description', 'wordpoints' ),
				'log_text_no_post_title' => _x( 'Comment', 'points log description', 'wordpoints' ),
				/* translators: %s is the name of a post type. */
				'log_text_post_type' => _x( 'Comment on a %s.', 'points log description', 'wordpoints' ),
				/* translators: %s will be the post's title. */
				'log_text_post_title_reverse' => _x( 'Comment on %s removed.', 'points log description', 'wordpoints' ),
				'log_text_no_post_title_reverse' => _x( 'Comment removed.', 'points log description', 'wordpoints' ),
				/* translators: %s is the name of a post type. */
				'log_text_post_type_reverse' => _x( 'Comment on a %s removed.', 'points log description', 'wordpoints' ),
				'last_status_meta_key' => 'wordpoints_last_status',
			)
		);

		if ( get_site_option( 'wordpoints_comment_hook_legacy' ) ) {
			$this->set_option(
				'disable_auto_reverse_label'
				, __( 'Revoke the points if the comment is removed.', 'wordpoints' )
			);
		}
	}

	/**
	 * Generate the log entry for an approve comment transaction.
	 *
	 * @since 1.0.0
	 * @deprecated 1.8.0 Use self::logs() instead.
	 *
	 * @param string $text        The text for the log entry.
	 * @param int    $points      The number of points.
	 * @param string $points_type The type of points for the transaction.
	 * @param int    $user_id     The affected user's ID.
	 * @param string $log_type    The type of transaction.
	 * @param array  $meta        Transaction meta data.
	 *
	 * @return string
	 */
	public function approve_logs( $text, $points, $points_type, $user_id, $log_type, $meta ) {

		_deprecated_function( __METHOD__, '1.8.0', __CLASS__ . '::logs()' );

		return $this->logs( $text, $points, $points_type, $user_id, $log_type, $meta );
	}

	/**
	 * Generate the log entry for a disapprove comment transaction.
	 *
	 * @since 1.0.0
	 * @deprecated 1.4.0
	 * @deprecated Use wordpoints_points_logs_comment_disapprove() instead.
	 *
	 * @param string $text        The text for the log entry.
	 * @param int    $points      The number of points.
	 * @param string $points_type The type of points for the transaction.
	 * @param int    $user_id     The affected user's ID.
	 * @param string $log_type    The type of transaction.
	 * @param array  $meta        Transaction meta data.
	 *
	 * @return string
	 */
	public function disapprove_logs( $text, $points, $points_type, $user_id, $log_type, $meta ) {

		_deprecated_function( __METHOD__, '1.4.0', 'wordpoints_points_logs_comment_disapprove' );

		return wordpoints_points_logs_comment_disapprove(
			$text
			, $points
			, $points_type
			, $user_id
			, $log_type
			, $meta
		);
	}

	/**
	 * Check if points have already been awarded for a comment.
	 *
	 * The behavior of this hook is to award points when a comment has been approved,
	 * given the following conditions:
	 *
	 * * No points have been awarded for the comment yet, or
	 * * The last status of the comment where points were affected was not 'approved'
	 *
	 * This function was deprecated in 1.8.0 because of confusion surrounding its
	 * name. It seems that it should only check a value in the database, but it goes
	 * beyond that and updates the value in anticipation of points being awarded.
	 * Because of this, strange things will happen if it is used more than once
	 * and points haven't been awarded, because after the first time it is used in
	 * such a case it will always return true.
	 *
	 * It is possible that this function would be resurected in a later version, with
	 * this behavior corrected, but until then it should be avoided.
	 *
	 * @since 1.0.0
	 * @deprecated 1.8.0
	 *
	 * @param int    $comment_id  The ID of a comment.
	 * @param string $points_type The points type to check.
	 *
	 * @return bool Whether points have been awarded.
	 */
	public function awarded_points_already( $comment_id, $points_type ) {

		_deprecated_function( __METHOD__, '1.8.0' );

		$meta_key = $this->get_last_status_comment_meta_key( $points_type );

		$last_status = get_comment_meta( $comment_id, $meta_key, true );

		if ( 'approved' !== $last_status ) {

			update_comment_meta( $comment_id, $meta_key, 'approved', $last_status );

			return false;
		}

		return true;
	}

} // class WordPoints_Comment_Points_Hook

// EOF
