<?php

/**
 * Base class for comment approved points hooks.
 *
 * @package WordPoints\Points\Hooks
 * @since 1.8.0
 */

/**
 * Base comment approved points hook.
 *
 * @since 1.8.0
 */
abstract class WordPoints_Comment_Approved_Points_Hook_Base extends WordPoints_Post_Type_Points_Hook_Base {

	/**
	 * The default values.
	 *
	 * @since 1.8.0
	 *
	 * @type array $defaults
	 */
	protected $defaults = array(
		'points' => 10,
		'post_type' => 'ALL',
		'auto_reverse' => 1,
	);

	/**
	 * Initialize the hook.
	 *
	 * @since 1.8.0
	 */
	public function __construct( $title, $args ) {

		$defaults = array(
			'post_type_filter' => array( $this, 'post_type_supports_comments' ),
		);

		$args = array_merge( $defaults, $args );

		parent::__construct( $title, $args );

		add_action( 'transition_comment_status', array( $this, 'hook' ), 10, 3 );
		add_action( 'transition_comment_status', array( $this, 'reverse_hook' ), 10, 3 );
		add_action( 'wp_insert_comment', array( $this, 'new_comment_hook' ), 10, 2 );

		add_action( 'delete_comment', array( $this, 'clean_logs_on_comment_deletion' ) );
	}

	/**
	 * Award points when a comment is approved.
	 *
	 * If the comment's status has been transitioned to approved, we award points.
	 *
	 * @since 1.8.0
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

		if ( $this->shortcircuit_hook( $new_status, $old_status, $comment ) ) {
			return;
		}

		$post = get_post( $comment->comment_post_ID );

		foreach ( $this->get_instances() as $number => $instance ) {

			$instance = array_merge( $this->defaults, $instance );

			$points_type = $this->points_type( $number );

			$meta_key = $this->get_last_status_comment_meta_key( $points_type );

			$last_status = get_comment_meta( $comment->comment_ID, $meta_key, true );

			if (
				'approved' === $last_status
				|| ! $this->is_matching_post_type( $post->post_type, $instance['post_type'] )
			) {
				continue;
			}

			wordpoints_add_points(
				$this->select_user_to_award( $comment, $post )
				, $instance['points']
				, $points_type
				, $this->log_type
				, array( 'comment_id' => $comment->comment_ID )
			);

			update_comment_meta( $comment->comment_ID, $meta_key, 'approved', $last_status );
		}
	}

	/**
	 * Called at the start of running the hook to check if it should skip running.
	 *
	 * @since 1.8.0
	 *
	 * @param string $new_status The new status of the comment.
	 * @param string $old_status The old status of the comment.
	 * @param object $comment    The comment object.
	 *
	 * @return bool Whether the hook should shortcircuit and not award any points.
	 */
	protected function shortcircuit_hook( $new_status, $old_status, $comment ) {

		if ( 'approved' !== $new_status || $old_status === $new_status ) {
			return true;
		}

		return false;
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
	 * @since 1.8.0
	 *
	 * @action wp_insert_comment Added by the constructor.
	 *
	 * @param int      $comment_id The comment's ID.
	 * @param stdClass $comment    The comment object.
	 *
	 * @return void
	 */
	public function new_comment_hook( $comment_id, $comment ) {

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
	 * Reverse a transaction when a comment is deapproved.
	 *
	 * @since 1.8.0
	 *
	 * @action transition_comment_status Added by the constructor.
	 *
	 * @param string $new_status The new status of the comment.
	 * @param string $old_status The old status of the comment.
	 * @param object $comment    The comment object.
	 *
	 * @return void
	 */
	public function reverse_hook( $new_status, $old_status, $comment ) {

		if ( 'approved' !== $old_status || $old_status === $new_status ) {
			return;
		}

		$post_type = get_post_field( 'post_type', $comment->comment_post_ID );
		if ( ! $this->should_do_auto_reversals_for_post_type( $post_type ) ) {
			return;
		}

		$logs = $this->get_logs_to_auto_reverse(
			array(
				'key'   => 'comment_id',
				'value' => $comment->comment_ID,
			)
		);

		foreach ( $logs as $log ) {

			$meta_key = $this->get_last_status_comment_meta_key( $log->points_type );

			if ( 'approved' !== get_comment_meta( $comment->comment_ID, $meta_key, true ) ) {
				continue;
			}

			$this->auto_reverse_log( $log );

			delete_comment_meta( $comment->comment_ID, $meta_key );
		}
	}

	/**
	 * Select which user to award points to.
	 *
	 * Overriding this function gives you the ability to choose whether to award the
	 * points to the comment author (the default), the post author, or even someone
	 * else.
	 *
	 * @since 1.8.0
	 *
	 * @param stdClass $comment The object for the comment that triggerd the hook.
	 * @param WP_Post  $post    The object for the post this comment was on.
	 *
	 * @return int The ID of the user the points should be awarded to.
	 */
	protected function select_user_to_award( $comment, $post ) {
		return $comment->user_id;
	}

	/**
	 * Generate the log entry for a comment received transaction.
	 *
	 * @since 1.8.0
	 *
	 * @action wordpoints_render_log-comment_received Added by the constructor.
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
	public function logs( $text, $points, $points_type, $user_id, $log_type, $meta ) {

		$reverse = '';

		if ( "reverse_{$this->log_type}" === $log_type ) {
			$reverse = '_reverse';
		}

		$comment = false;

		if ( isset( $meta['comment_id'] ) ) {
			$comment = get_comment( $meta['comment_id'] );
		}

		if ( ! $comment ) {

			$text = parent::logs( $text, $points, $points_type, $user_id, $log_type, $meta );

		} else {

			$text = $this->log_with_post_title_link(
				$comment->comment_post_ID
				, $reverse
				, get_comment_link( $comment )
			);
		}

		return $text;
	}

	/**
	 * Get the meta key for the comment metadata where the last status is stored.
	 *
	 * @since 1.8.0
	 *
	 * @param string $points_type The slug of the points type to get the key for.
	 *
	 * @return string The meta key where the last status is stored.
	 */
	protected function get_last_status_comment_meta_key( $points_type ) {

		$meta_key = $this->get_option( 'last_status_meta_key' );

		if ( empty( $meta_key ) ) {
			$meta_key = "wordpoints_last_status-{$this->log_type}";
		}

		return "{$meta_key}-{$points_type}";
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
	 * @since 1.8.0
	 *
	 * @action delete_comment Added by the constructor.
	 *
	 * @param int $comment_id The ID of the comment being deleted.
	 *
	 * @return void
	 */
	public function clean_logs_on_comment_deletion( $comment_id ) {

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => $this->log_type,
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

		foreach ( $logs as $log ) {

			wordpoints_delete_points_log_meta( $log->id, 'comment_id' );

			if ( $comment ) {

				wordpoints_add_points_log_meta(
					$log->id
					, 'post_id'
					, $comment->comment_post_ID
				);
			}
		}

		wordpoints_regenerate_points_logs( $logs );
	}

	/**
	 * Check if a user can view a particular log entry.
	 *
	 * @since 1.8.0
	 *
	 * @filter wordpoints_user_can_view_points_log-{$this->log_type} Added by the constructor.
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

} // class WordPoints_Comment_Approved_Points_Hook_Base

// EOF
