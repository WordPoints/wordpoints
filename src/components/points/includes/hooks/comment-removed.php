<?php

/**
 * The comment removed points hook class.
 *
 * @package WordPoints\Points\Hooks
 * @since 1.4.0
 */

// Register the comment removed hook.
WordPoints_Points_Hooks::register( 'WordPoints_Comment_Removed_Points_Hook' );

/**
 * Comment removed points hook.
 *
 * This hook will remove points if a comment is marked as spam or moved to the trash.
 *
 * Prior to version 1.4.0, this functionality was part of the comment points hook.
 *
 * @since 1.4.0
 */
class WordPoints_Comment_Removed_Points_Hook extends WordPoints_Points_Hook {

	/**
	 * The default values.
	 *
	 * @since 1.4.0
	 *
	 * @type array $defaults
	 */
	private $defaults = array( 'points' => 10 );

	/**
	 * Initialize the hook.
	 *
	 * @since 1.4.0
	 */
	public function __construct() {

		parent::init(
			_x( 'Comment Removed', 'points hook name', 'wordpoints' )
			, array( 'description' => __( 'Comment removed from the site.', 'wordpoints' ) )
		);

		add_action( 'transition_comment_status', array( $this, 'hook' ), 10, 3 );
		add_filter( 'wordpoints_points_log-comment_disapprove', array( $this, 'logs' ), 10, 6 );
	}

	/**
	 * Remove points when a comment is removed.
	 *
	 * If the comment's status is being trasitioned from approved, we remove points.
	 *
	 * @since 1.4.0
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

		if ( ! $comment->user_id || $old_status == $new_status ) {
			return;
		}

		if ( 'approved' == $old_status ) {

			foreach ( $this->get_instances() as $number => $instance ) {

				$instance = array_merge( $this->defaults, $instance );

				$points_type = $this->points_type( $number );

				wordpoints_subtract_points( $comment->user_id, $instance['points'], $points_type, 'comment_disapprove', array( 'status' => $new_status ) );

				update_comment_meta( $comment->comment_ID, "wordpoints_last_status-{$points_type}", $new_status );
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
	 * @since 1.4.0
	 *
	 * @action wp_insert_comment Added by the constructor.
	 *
	 * @param int      $comment_id The comment's ID.
	 * @param stdClass $comment    The comment object.
	 *
	 * @return void
	 */
	public function new_comment_hook( $comment_id, $comment ) {

		if ( 0 == $comment->user_id ) {
			return;
		}

		switch ( $comment->comment_approved ) {

			// Comment hasn't been approved yet.
			case 0: return;

			// Comment is approved.
			case 1: return;

			// Comment is 'spam' (or 'trash').
			default:
				$new_status = $comment->comment_approved;
				$old_status = 'approved';
		}

		$this->hook( $new_status, $old_status, $comment );
	}

	/**
	 * Generate the log entry for a transaction.
	 *
	 * @since 1.4.0
	 *
	 * @action wordpoints_render_log-comment_disapprove Added by the constructor.
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

		switch ( $meta['status'] ) {

			case 'spam':
				$message = _x( 'Comment marked as spam.', 'points log description', 'wordpoints' );
			break;

			case 'trash':
				$message = _x( 'Comment moved to trash.', 'points log description', 'wordpoints' );
			break;

			default:
				$message = _x( 'Comment unapproved.', 'points log description', 'wordpoints' );
		}

		return $message;
	}

	/**
	 * Get the number of points for an instance of this hook.
	 *
	 * @since 1.4.0
	 *
	 * @param int $number The ID number of the instance.
	 *
	 * @return int|bool The number of points, or false.
	 */
	public function get_points( $number = null ) {

		$points = parent::get_points( $number );

		if ( $points ) {
			$points = -$points;
		}

		return $points;
	}

	/**
	 * Update a particular instance of this hook.
	 *
	 * @since 1.4.0
	 *
	 * @param array $new_instance New settings for this instance as input by user.
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array Settings to save.
	 */
	protected function update( $new_instance, $old_instance ) {

		$new_instance = array_merge( $this->defaults, $old_instance, $new_instance );

		wordpoints_posint( $new_instance['points'] );

		return $new_instance;
	}

	/**
	 * Echo the settings update form.
	 *
	 * @since 1.4.0
	 *
	 * @param array $instance Current settings.
	 *
	 * @return bool True.
	 */
	protected function form( $instance ) {

		$instance = array_merge( $this->defaults, $instance );

		?>

		<p>
			<label for="<?php $this->the_field_id( 'points' ); ?>"><?php _e( 'Points subtracted if comment removed:', 'wordpoints' ); ?></label>
			<input class="widefat" name="<?php $this->the_field_name( 'points' ); ?>"  id="<?php $this->the_field_id( 'points' ); ?>" type="text" value="<?php echo wordpoints_posint( $instance['points'] ); ?>" />
		</p>

		<?php

		return true;
	}
}
