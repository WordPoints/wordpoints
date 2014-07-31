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
 */
class WordPoints_Comment_Points_Hook extends WordPoints_Post_Type_Points_Hook_Base {

	/**
	 * The default values.
	 *
	 * @since 1.0.0
	 *
	 * @type array $defaults
	 */
	protected $defaults = array( 'points' => 10, 'post_type' => 'ALL' );

	/**
	 * Initialize the hook.
	 *
	 * @since 1.0.0
	 *
	 * @uses add_action() To hook up the hook() and new_comment_hook() methods.
	 * @uses add_filter() To add the approve_logs() method.
	 */
	public function __construct() {

		parent::init(
			_x( 'Comment', 'points hook name', 'wordpoints' )
			, array(
				'description' => __( 'Leaving a new comment.', 'wordpoints' ),
				/* translators: the post type name. */
				'post_type_description' => __( 'Leaving a new comment on a %s.', 'wordpoints' ),
				'post_type_filter' => array( $this, 'post_type_supports_comments' ),
			)
		);

		add_action( 'transition_comment_status', array( $this, 'hook' ), 10, 3 );
		add_action( 'wp_insert_comment', array( $this, 'new_comment_hook' ), 10, 2 );

		add_filter( 'wordpoints_points_log-comment_approve', array( $this, 'approve_logs' ), 10, 6 );

		add_action( 'delete_comment', array( $this, 'clean_logs_on_comment_deletion' ) );
		add_action( 'delete_post', array( $this, 'clean_logs_on_post_deletion' ) );

		add_filter( 'wordpoints_user_can_view_points_log-comment_approve', array( $this, 'user_can_view' ), 10, 2 );
	}

	/**
	 * Award points when a comment is approved.
	 *
	 * If the comment's status has been transitioned to approved, we award points.
	 *
	 * @since 1.0.0
	 * @since 1.4.0  No longer removes points if the comment's status is being
	 *               trasitioned from approved.
	 *
	 * @action transition_comment_status Added by the constructor.
	 *
	 * @param string $new_status The new status of the comment.
	 * @param string $old_status The old status of the comment.
	 * @param object $comment    The comment object.
	 *
	 * @return void
	 */
	public function hook( $new_status, $old_status, $comment ) {

		if ( ! $comment->user_id || $old_status === $new_status ) {
			return;
		}

		$post = get_post( $comment->comment_post_ID );

		if ( 'approved' === $new_status ) {

			foreach ( $this->get_instances() as $number => $instance ) {

				$instance = array_merge( $this->defaults, $instance );

				if ( isset( $instance['approve'] ) ) {
					_deprecated_argument( __METHOD__, '1.4.0', 'The "approve" hook setting is no longer used to hold the value for the points. Use "points" instead.' );
					$instance['points'] = $instance['approve'];
				}

				$points_type = $this->points_type( $number );

				if (
					$this->is_matching_post_type( $post->post_type, $instance['post_type'] )
					&& ! $this->awarded_points_already( $comment->comment_ID, $points_type )
				) {
					wordpoints_add_points(
						$comment->user_id
						, $instance['points']
						, $points_type
						, 'comment_approve'
						, array( 'comment_id' => $comment->comment_ID )
					);
				}
			}
		}
	}

	/**
	 * New comment hook.
	 *
	 * This function runs whenever a new comment is posted. This is required in
	 * addition to the hook above, because 'transition_comment_status' isn't
	 * called when a new comment is created. (This is inconsistent with
	 * 'transition_post_status'.)
	 *
	 * @link http://core.trac.wordpress.org/ticket/16365 Ticket to fix inconsistency.
	 *
	 * @since 1.0.0
	 *
	 * @action wp_insert_comment Added by the constructor.
	 *
	 * @uses WordPoints_Comment_Points_Hook::hook()
	 *
	 * @param int      $comment_id The comment's ID.
	 * @param stdClass $comment    The comment object.
	 *
	 * @return void
	 */
	public function new_comment_hook( $comment_id, $comment ) {

		if ( 0 === (int) $comment->user_id ) {
			return;
		}

		switch ( $comment->comment_approved ) {

			// Comment hasn't been approved yet.
			case 0: return;

			// Comment is approved.
			case 1:
				$new_status = 'approved';
				$old_status = 'new';
			break;

			// Comment is 'spam' (or 'trash').
			default:
				$new_status = $comment->comment_approved;
				$old_status = 'approved';
		}

		$this->hook( $new_status, $old_status, $comment );
	}

	/**
	 * Generate the log entry for an approve comment transaction.
	 *
	 * @since 1.0.0
	 *
	 * @action wordpoints_render_log-comment_approve Added by the constructor.
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

		$comment = false;

		if ( isset( $meta['comment_id'] ) ) {
			$comment = get_comment( $meta['comment_id'] );
		}

		if ( ! $comment ) {

			$post = false;

			if ( isset( $meta['post_id'] ) ) {
				$post = get_post( $meta['post_id'] );
			}

			if ( $post ) {

				$post_title = get_the_title( $post->ID );

				$link = '<a href="' . get_permalink( $post->ID ) . '">'
					. ( $post_title ? $post_title : _x( '(no title)', 'post title', 'wordpoints' ) )
					. '</a>';

				$text = sprintf( _x( 'Comment on %s.', 'points log description', 'wordpoints' ), $link );

			} else {

				$text = _x( 'Comment', 'points log description', 'wordpoints' );
			}

		} else {

			$post_title = get_the_title( $comment->comment_post_ID );
			$link       = '<a href="' . get_comment_link( $comment ) . '">'
				. ( $post_title ? $post_title : _x( '(no title)', 'post title', 'wordpoints' ) )
				. '</a>';

			/* translators: %s will be the post's title. */
			$text = sprintf( _x( 'Comment on %s.', 'points log description', 'wordpoints' ), $link );
		}

		return $text;
	}

	/**
	 * Generate the log entry for a disapprove comment transaction.
	 *
	 * @since 1.0.0
	 * @deprecated 1.4.0
	 * @deprecated Use WordPoints_Comment_Removed_Points_Hook::logs() instead.
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

		_deprecated_function( __METHOD__, '1.4.0', 'WordPoints_Comment_Removed_Points_Hook::logs()' );

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_comment_removed_points_hook' );

		if ( $hook ) {
			$text = $hook->logs( $text, $points, $points_type, $user_id, $log_type, $meta );
		}

		return $text;
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
	 * @since 1.0.0
	 *
	 * @param int    $comment_id  The ID of a comment.
	 * @param string $points_type The points type to check.
	 *
	 * @return bool Whether points have been awarded.
	 */
	public function awarded_points_already( $comment_id, $points_type ) {

		$last_status = get_comment_meta( $comment_id, "wordpoints_last_status-{$points_type}", true );

		if ( 'approved' !== $last_status ) {

			update_comment_meta( $comment_id, "wordpoints_last_status-{$points_type}", 'approved', $last_status );

			return false;
		}

		return true;
	}

	/**
	 * Clean the logs on comment deletion.
	 *
	 * Cleans the metadata for any logs related to this comment. The comment ID meta
	 * field is updated in the database, to instead store the post ID of the post the
	 * comment was on. If the post ID isn't available, we just delete those rows.
	 *
	 * Once the metadata is cleaned up, the logs for this comment are regenerated.
	 *
	 * @since 1.2.0
	 *
	 * @action delete_comment Added by the constructor.
	 *
	 * @param int $comment_id The ID of the comment being deleted.
	 *
	 * @return void
	 */
	public function clean_logs_on_comment_deletion( $comment_id ) {

		global $wpdb;

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'comment_approve',
				'meta_query' => array(
					array(
						'key'   => 'comment_id',
						'value' => $comment_id,
					),
				),
			)
		);

		$logs = $query->get();

		if ( ! $logs ) {
			return;
		}

		$comment = get_comment( $comment_id );

		if ( ! $comment ) {

			foreach ( $logs as $log ) {

				$wpdb->delete(
					$wpdb->wordpoints_points_log_meta
					, array(
						'meta_key'   => 'comment_id',
						'meta_value' => $comment_id,
						'log_id'     => $log->id,
					)
					, array( '%s', '%d', '%d' )
				);
			}

		} else {

			foreach ( $logs as $log ) {

				$wpdb->update(
					$wpdb->wordpoints_points_log_meta
					, array(
						'meta_key'   => 'post_id',
						'meta_value' => $comment->comment_post_ID,
					)
					, array(
						'meta_key'   => 'comment_id',
						'meta_value' => $comment_id,
						'log_id'     => $log->id,
					)
					, array( '%s', '%d' )
					, array( '%s', '%d', '%d' )
				);
			}
		}

		wordpoints_regenerate_points_logs( $logs );
	}

	/**
	 * Clean the logs when a post is deleted.
	 *
	 * Cleans the metadata for any logs related to the post being deleted. The post
	 * ID meta field is deleted from the database. Once the metadata is cleaned up,
	 * the logs are regenerated.
	 *
	 * @since 1.4.0
	 *
	 * @action delete_post Added by the constructor.
	 *
	 * @param int $post_id The ID of the post being deleted.
	 *
	 * return void
	 */
	public function clean_logs_on_post_deletion( $post_id ) {

		global $wpdb;

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'comment_approve',
				'meta_query' => array(
					array(
						'key'   => 'post_id',
						'value' => $post_id,
					),
				),
			)
		);

		$logs = $query->get();

		if ( ! $logs ) {
			return;
		}

		foreach ( $logs as $log ) {

			$wpdb->delete(
				$wpdb->wordpoints_points_log_meta
				, array(
					'meta_key'   => 'post_id',
					'meta_value' => $post_id,
					'log_id'     => $log->id,
				)
				, array( '%s', '%d', '%d' )
			);
		}

		wordpoints_regenerate_points_logs( $logs );
	}

	/**
	 * Check if a user can view a particular log entry.
	 *
	 * @since 1.3.0
	 *
	 * @filter wordpoints_user_can_view_points_log-comment_approve Added by the constructor.
	 *
	 * @param bool   $can_view Whether the user can view this log entry.
	 * @param object $log      The log object.
	 *
	 * @return bool Whether the user can view this log.
	 */
	public function user_can_view( $can_view, $log ) {

		if ( $can_view ) {
			$comment_id = wordpoints_get_points_log_meta( $log->id, 'comment_id', true );

			if ( $comment_id && ( $comment = get_comment( $comment_id ) ) ) {
				$can_view = current_user_can( 'read_post', $comment->comment_post_ID );
			}
		}

		return $can_view;
	}
}
