<?php

/**
 * The comment received points hook class.
 *
 * @package WordPoints\Points\Hooks
 * @since 1.8.0
 */

WordPoints_Points_Hooks::register( 'WordPoints_Comment_Received_Points_Hook' );

/**
 * Comment received points hook.
 *
 * This hook will award points when an author when they receive a comment on one of
 * their posts.
 *
 * @since 1.8.0
 */
class WordPoints_Comment_Received_Points_Hook extends WordPoints_Comment_Approved_Points_Hook_Base {

	/**
	 * @since 1.8.0
	 */
	protected $log_type = 'comment_received';

	/**
	 * @since 1.8.0
	 */
	public function __construct() {

		parent::__construct(
			_x( 'Comment Received', 'points hook name', 'wordpoints' )
			, array(
				'description' => __( 'Receiving a comment.', 'wordpoints' ),
				/* translators: the post type name. */
				'post_type_description' => __( 'Receiving a comment on a %s.', 'wordpoints' ),
				/* translators: %s will be the post's title. */
				'log_text_post_title' => _x( 'Received a comment on %s.', 'points log description', 'wordpoints' ),
				'log_text_no_post_title' => _x( 'Received a comment.', 'points log description', 'wordpoints' ),
				/* translators: %s will be the post's title. */
				'log_text_post_title_reverse' => _x( 'Comment received on %s removed.', 'points log description', 'wordpoints' ),
				'log_text_no_post_title_reverse' => _x( 'Comment received removed.', 'points log description', 'wordpoints' ),
				/* translators: %s is the name of a post type. */
				'log_text_post_type' => _x( 'Received a comment on a %s.', 'points log description', 'wordpoints' ),
				/* translators: %s is the name of a post type. */
				'log_text_post_type_reverse' => _x( 'Comment received on a %s removed.', 'points log description', 'wordpoints' ),
			)
		);
	}

	/**
	 * @since 1.8.0
	 */
	protected function shortcircuit_hook( $new_status, $old_status, $comment ) {

		if ( parent::shortcircuit_hook( $new_status, $old_status, $comment ) ) {
			return true;
		}

		$post = get_post( $comment->comment_post_ID );

		if ( (int) $post->post_author === (int) $comment->user_id ) {
			return true;
		}

		return false;
	}

	/**
	 * @since 1.8.0
	 */
	protected function select_user_to_award( $comment, $post ) {
		return $post->post_author;
	}

} // class WordPoints_Comment_Received_Points_Hook

// EOF
