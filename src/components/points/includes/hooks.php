<?php

/**
 * Points Hooks.
 *
 * These are the Points Hooks included with the plugin. They are each an extension of
 * the base class WordPoints_Points_Hook.
 *
 * @package WordPoints\Points
 * @since 1.0.0
 *
 * @see WordPoints_Points_Hook
 */

// Register the registration hook.
WordPoints_Points_Hooks::register( 'WordPoints_Registration_Points_Hook' );

/**
 * Registration hook.
 *
 * Award a user points on registration.
 *
 * @since 1.0.0
 */
class WordPoints_Registration_Points_Hook extends WordPoints_Points_Hook {

	/**
	 * The default values.
	 *
	 * @since 1.0.0
	 *
	 * @type array $defaults
	 */
	private $defaults = array( 'points' => 100 );

	/**
	 * Construct the class.
	 *
	 * @since 1.0.0
	 *
	 * @uses add_action() To add hook() to the 'user_register' action.
	 * @uses add_filter() To add logs() to the log generation hook.
	 */
	public function __construct() {

		parent::init( _x( 'Registration', 'points hook name', 'wordpoints' ), array( 'description' => _x( 'User registration', 'points hook description', 'wordpoints' ) ) );

		add_action( 'user_register', array( $this, 'hook' ) );
		add_filter( 'wordpoints_points_log-register', array( $this, 'logs' ) );
	}

	/**
	 * Award points when the hook is fired.
	 *
	 * @since 1.0.0
	 *
	 * @action user_register Added by the constructor.
	 *
	 * @param int $user_id The ID of the newly registered user.
	 *
	 * @return void
	 */
	public function hook( $user_id ) {

		foreach ( $this->get_instances() as $number => $instance ) {

			if ( isset( $instance['points'] ) )
				wordpoints_add_points( $user_id, $instance['points'], $this->points_type( $number ), 'register' );
		}
	}

	/**
	 * Display the log entry for a transaction.
	 *
	 * @since 1.0.0
	 *
	 * @action wordpoints_render_log-register Added by the constructor.
	 *
	 * @return string The log entry.
	 */
	function logs() {

		return _x( 'Registration.', 'points log description', 'wordpoints' );
	}

	/**
	 * Update a particular instance of this hook.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance New settings for this instance.
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
	 * @param array $instance Current settings.
	 *
	 * @return bool True.
	 */
	protected function form( $instance ) {

		$instance = array_merge( $this->defaults, $instance );

		$points = wordpoints_posint( $instance['points'] );

		?>

		<p>
			<label for="<?php $this->the_field_id( 'points' ); ?>"><?php _ex( 'Points:', 'form label', 'wordpoints' ); ?></label>
			<input class="widefat" name="<?php $this->the_field_name( 'points' ); ?>"  id="<?php $this->the_field_id( 'points' ); ?>" type="text" value="<?php echo $points; ?>" />
		</p>

		<?php

		return true;
	}
}

// Register the post hook.
WordPoints_Points_Hooks::register( 'WordPoints_Post_Points_Hook' );

/**
 * Post hook.
 *
 * Awards points when a post is published, and/or subtracts them when one is
 * permanently deleted.
 *
 * @since 1.0.0
 */
class WordPoints_Post_Points_Hook extends WordPoints_Points_Hook {

	/**
	 * The default values.
	 *
	 * @since 1.0.0
	 *
	 * @type array $defaults
	 */
	private $defaults = array( 'publish' => 20, 'trash' => 20, 'post_type' => 'ALL' );

	/**
	 * Set up the hook.
	 *
	 * @since 1.0.0
	 *
	 * @uses add_action() To set up the publish_hook() and delete_hook() methods.
	 * @uses add_filter() To hook up the publish_logs() and delete_logs() methods.
	 */
	public function __construct() {

		parent::init(
			_x( 'Post', 'points hook name', 'wordpoints' )
			,array(
				'description' => __( 'Add points when a post is published, and/or subtract points when one is permanently deleted.', 'wordpoints' ),
			)
		);

		add_action( 'transition_post_status', array( $this, 'publish_hook' ), 10, 3 );
		add_action( 'delete_post', array( $this, 'delete_hook' ) );

		add_filter( 'wordpoints_points_log-post_publish', array( $this, 'publish_logs' ), 10, 6 );
		add_filter( 'wordpoints_points_log-post_delete', array( $this, 'delete_logs' ), 10, 6 );
	}

	/**
	 * Award points when a post is published.
	 *
	 * @since 1.0.0
	 *
	 * @action user_register Added by the constructor.
	 *
	 * @param string $old_status The old status of the post.
	 * @param string $new_status The new status of the post.
	 * @param object $post       The post object.
	 *
	 * @return void
	 */
	public function publish_hook( $new_status, $old_status, $post ) {

		if ( $new_status != 'publish' )
			return;

		foreach ( $this->get_instances() as $number => $instance ) {

			$instance = array_merge( $this->defaults, $instance );

			$points_type = $this->points_type( $number );

			if (
				(
					$instance['post_type'] == 'ALL'
					|| $instance['post_type'] == $post->post_type
				)
				&& ! $this->awarded_points_already( $post->ID, $points_type )
			) {

				wordpoints_alter_points( $post->post_author, $instance['publish'], $points_type, 'post_publish', array( 'post_id' => $post->ID ) );
			}
		}
	}

	/**
	 * Remove points when a post is deleted.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 The post_type is now passed as metadata when points are awarded.
	 *
	 * @action before_delete_post Added by the constructor.
	 *
	 * @param int $post_id The post's ID.
	 */
	public function delete_hook( $post_id ) {

		$post = get_post( $post_id, OBJECT, 'display' );

		foreach ( $this->get_instances() as $number => $instance ) {

			$instance = array_merge( $this->defaults, $instance );

			if (
				! empty( $instance['post_type'] )
				&& (
					$instance['post_type'] == 'ALL'
					|| $instance['post_type'] == $post->post_type
				)
			) {

				wordpoints_alter_points(
					$post->post_author
					, -$instance['trash']
					, $this->points_type( $number )
					, 'post_delete'
					, array( 'post_title' => $post->post_title, 'post_type' => $post->post_type )
				);
			}
		}
	}

	/**
	 * Display the log entry for a publish post transaction.
	 *
	 * @since 1.0.0
	 *
	 * @action wordpoints_points_log-post_publish Added by the constructor.
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
	public function publish_logs( $text, $points, $points_type, $user_id, $log_type, $meta ) {

		$post = get_post( $meta['post_id'], OBJECT, 'display' );

		if ( null == $post ) {

			return _x( 'Post published.', 'points log description', 'wordpoints' );

		} else {

			$link = '<a href="' . get_permalink( $post ) . '">' . ( $post->post_title ? $post->post_title : _x( '(no title)', 'post title', 'wordpoints' ) ) . '</a>';

			$post_type = get_post_type_object( $post->post_type );

			if ( is_null( $post_type ) ) {

				/* translators: %s will be a link to the post. */
				return sprintf( _x( 'Post %s published.', 'points log description', 'wordpoints' ), $link );
			}

			/* translators: 1 is the post type name, 2 is a link to the post. */
			return sprintf( _x( '%1$s %2$s published.', 'points log description', 'wordpoints' ), $post_type->labels->singular_name, $link );
		}
	}

	/**
	 * Display the log entry for a transaction.
	 *
	 * The data isn't sanitized here becuase we do that before saving it.
	 *
	 * @since 1.0.0
	 *
	 * @action wordpoints_render_log-post_delete Added by the constructor.
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
	public function delete_logs( $text, $points, $points_type, $user_id, $log_type, $meta ) {

		if ( isset( $meta['post_type'] ) ) {

			$post_type = get_post_type_object( $meta['post_type'] );

			if ( ! is_null( $post_type ) ) {

				/* translators: 1 is the post type name, 2 is the post title. */
				return sprintf( _x( '%1$s "%2$s" deleted.', 'points log description', 'wordpoints' ), $post_type->labels->singular_name, $meta['post_title'] );
			}
		}

		/* translators: %s will be the post title. */
		return sprintf( _x( 'Post "%s" deleted.', 'points log description', 'wordpoints' ), $meta['post_title'] );
	}

	/**
	 * Check if points have already been awarded for this post.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $post_id     The ID of the post.
	 * @param int    $user_id     The ID of the user.
	 * @param string $points_type The points type to check.
	 *
	 * @return bool Whether the user
	 */
	public function awarded_points_already( $post_id, $points_type ) {

		$query = new WordPoints_Points_Logs_Query(
			array(
				'fields'      => 'id',
				'points_type' => $points_type,
				'log_type'    => 'post_publish',
				'meta_key'    => 'post_id',
				'meta_value'  => $post_id,
			)
		);

		return ( $query->count() > 0 );
	}

	/**
	 * Update a particular instance of this hook.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance New settings for this instance.
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array Settings to save.
	 */
	protected function update( $new_instance, $old_instance ) {

		$new_instance = array_merge( $this->defaults, $old_instance, $new_instance );

		wordpoints_posint( $new_instance['publish'] );
		wordpoints_posint( $new_instance['trash'] );

		return $new_instance;
	}

	/**
	 * Echo the settings update form.
	 *
	 * @param array $instance Current settings.
	 *
	 * @return bool True.
	 */
	protected function form( $instance ) {

		$instance = array_merge( $this->defaults, $instance );

		$publish = wordpoints_posint( $instance['publish'] );
		$trash   = wordpoints_posint( $instance['trash'] );

		?>

		<p>
			<label for="<?php $this->the_field_id( 'post_type' ); ?>"><?php _e( 'Select post type:', 'wordpoints' ); ?></label>
			<?php wordpoints_list_post_types( array( 'selected' => $instance['post_type'], 'id' => $this->get_field_id( 'post_type' ), 'name' => $this->get_field_name( 'post_type' ), 'class' => 'widefat' ), array( 'public' => true ) ); ?>
		</p>
		<p>
			<label for="<?php $this->the_field_id( 'publish' ); ?>"><?php _e( 'Points added when published:', 'wordpoints' ); ?></label>
			<input class="widefat" name="<?php $this->the_field_name( 'publish' ); ?>"  id="<?php $this->the_field_id( 'publish' ); ?>" type="text" value="<?php echo $publish; ?>" />
		</p>
		<p>
			<label for="<?php $this->the_field_id( 'trash' ); ?>"><?php _e( 'Points removed when deleted:', 'wordpoints' ); ?></label>
			<input class="widefat" name="<?php $this->the_field_name( 'trash' ); ?>"  id="<?php $this->the_field_id( 'trash' ); ?>" type="text" value="<?php echo $trash; ?>" />
		</p>

		<?php

		return true;
	}
}

// Register the comment hook.
WordPoints_Points_Hooks::register( 'WordPoints_Comment_Points_Hook' );

/**
 * Comment hook.
 *
 * This hook will award points when a user leaves a comment. It will remove points if
 * the comment is marked as spam or moved to the trash. If this is done, then points
 * will be awarded again if the action is reversed.
 *
 * @since 1.0.0
 */
class WordPoints_Comment_Points_Hook extends WordPoints_Points_Hook {

	/**
	 * The default values.
	 *
	 * @since 1.0.0
	 *
	 * @type array $defaults
	 */
	private $defaults = array( 'approve' => 10, 'disapprove' => 10 );

	/**
	 * Initialize the hook.
	 *
	 * @since 1.0.0
	 *
	 * @uses add_action() To hook up the hook() and new_comment_hook() methods.
	 * @uses add_filter() To add the approve_logs() and disapprove_logs() methods.
	 */
	public function __construct() {

		parent::init( _x( 'Comment', 'points hook name', 'wordpoints' ), array( 'description' => __( 'Add points when a comment is approved, and/or subtract points when one is removed.', 'wordpoints' ) ) );

		add_action( 'transition_comment_status', array( $this, 'hook' ), 10, 3 );
		add_action( 'wp_insert_comment', array( $this, 'new_comment_hook' ), 10, 2 );

		add_filter( 'wordpoints_points_log-comment_approve', array( $this, 'approve_logs' ), 10, 6 );
		add_filter( 'wordpoints_points_log-comment_disapprove', array( $this, 'disapprove_logs' ), 10, 6 );
	}

	/**
	 * Award points when a comment is approved.
	 *
	 * If the comment's status has been transitioned to approved, we award points. If
	 * the comment's status is being trasitioned from approved, we remove points.
	 *
	 * @since 1.0.0
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

		if ( ! $comment->user_id || $old_status == $new_status )
			return;

		if ( 'approved' == $new_status ) {

			foreach ( $this->get_instances() as $number => $instance ) {

				$instance = array_merge( $this->defaults, $instance );

				$points_type = $this->points_type( $number );

				if ( ! $this->awarded_points_already( $comment->comment_ID, $points_type ) )
					wordpoints_add_points( $comment->user_id, $instance['approve'], $points_type, 'comment_approve', array( 'comment_id' => $comment->comment_ID ) );
			}

		} elseif ( 'approved' == $old_status ) {

			foreach ( $this->get_instances() as $number => $instance ) {

				$instance = array_merge( $this->defaults, $instance );

				$points_type = $this->points_type( $number );

				wordpoints_subtract_points( $comment->user_id, $instance['disapprove'], $points_type, 'comment_disapprove', array( 'status' => $new_status ) );

				update_comment_meta( $comment->comment_ID, "wordpoints_last_status-{$points_type}", $new_status );
			}
		}
	}

	/**
	 * New comment hook
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

		if ( 0 == $comment->user_id )
			return;

		switch( $comment->comment_approved ) {

			// Comment hasn't been approved yet.
			case 0: return;

			// Comment is approved.
			case 1:
				$new_status = 'approved';
				$old_status = 'new';
			break;

			// Comment is 'spam' (or 'trash').
			default:
				$new_status = $status;
				$old_status = 'approved';
		}

		$this->hook( $new_status, $old_status, $comment );
	}

	/**
	 * Display the log entry for an approve comment transaction.
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

		$comment = get_comment( $meta['comment_id'] );

		if ( ! $comment ) {

			return '<span title="' . __( 'Comment removed...', 'wordpoints' ) . '">' . _x( 'Comment', 'points log description', 'wordpoints' ) . '</span>';

		} else {

			$detail = wp_trim_words( strip_tags( $comment->comment_content ) );
			$post_title = get_the_title( $comment->comment_post_ID );
			$link = '<a href="' . get_comment_link( $comment ) . '">' . ( $post_title ? $post_title : _x( '(no title)', 'post title', 'wordpoints' ) ) . '</a>';

			/* translators: %s will be the post's title. */
			return '<span title="' . esc_attr( $detail ) . '">' . sprintf( _x( 'Comment on %s.', 'points log description', 'wordpoints' ), $link ) . '</span>';
		}
	}

	/**
	 * Display the log entry for a transaction.
	 *
	 * @since 1.0.0
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
	public function disapprove_logs( $text, $points, $points_type, $user_id, $log_type, $meta ) {

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
	 * @param int    $post_id     The ID of the post.
	 * @param int    $user_id     The ID of the user.
	 * @param string $points_type The points type to check.
	 *
	 * @return bool Whether points have been awarded.
	 */
	public function awarded_points_already( $comment_id, $points_type ) {

		$last_status = get_comment_meta( $comment_id, "wordpoints_last_status-{$points_type}", true );

		if ( 'approved' != $last_status ) {

			update_comment_meta( $comment_id, "wordpoints_last_status-{$points_type}", 'approved', $last_status );

			return false;
		}

		return true;
	}

	/**
	 * Update a particular instance of this hook.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance New settings for this instance as input by user.
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array Settings to save.
	 */
	protected function update( $new_instance, $old_instance ) {

		$new_instance = array_merge( $this->defaults, $old_instance, $new_instance );

		wordpoints_posint( $new_instance['approve'] );
		wordpoints_posint( $new_instance['disapprove'] );

		return $new_instance;
	}

	/**
	 * Echo the settings update form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance Current settings.
	 *
	 * @return bool True.
	 */
	protected function form( $instance ) {

		$instance = array_merge( $this->defaults, $instance );

		$approve    = wordpoints_posint( $instance['approve'] );
		$disapprove = wordpoints_posint( $instance['disapprove'] );

		?>

		<p>
			<label for="<?php $this->the_field_id( 'approve' ); ?>"><?php _e( 'Points for comment:', 'wordpoints' ); ?></label>
			<input class="widefat" name="<?php $this->the_field_name( 'approve' ); ?>"  id="<?php $this->the_field_id( 'approve' ); ?>" type="text" value="<?php echo $approve; ?>" />
		</p>
		<p>
			<label for="<?php $this->the_field_id( 'disapprove' ); ?>"><?php _e( 'Points subtracted if comment removed:', 'wordpoints' ); ?></label>
			<input class="widefat" name="<?php $this->the_field_name( 'disapprove' ); ?>"  id="<?php $this->the_field_id( 'disapprove' ); ?>" type="text" value="<?php echo $disapprove; ?>" />
		</p>

		<?php

		return true;
	}
}
// Register the periodic hook.
WordPoints_Points_Hooks::register( 'WordPoints_Periodic_Points_Hook' );

/**
 * Periodic hook.
 *
 * This hook will award points to a user once every period, if the visit the site
 * during that period. For example, one every day that they visit the site.
 *
 * @since 1.0.0
 */
class WordPoints_Periodic_Points_Hook extends WordPoints_Points_Hook {

	/**
	 * The default values.
	 *
	 * @since 1.0.0
	 *
	 * @type array $defaults
	 */
	private $defaults = array( 'period' => 'daily', 'points' => 10 );

	/**
	 * Initialize the hook.
	 *
	 * @since 1.0.0
	 *
	 * @uses add_action() To hook the hook() method to 'set_current_user'.
	 * @uses add_filter() To add the logs() method to the logs generation filter.
	 */
	public function __construct() {

		parent::init( _x( 'Periodic Points', 'points hook name', 'wordpoints' ), array( 'description' => __( 'Award a user points when they visit your site at least once in a given time period.', 'wordpoints' ) ) );

		add_action( 'set_current_user', array( $this, 'hook' ) );

		add_filter( 'wordpoints_points_log-periodic', array( $this, 'logs' ), 10, 6 );
	}

	/**
	 * Award points when a user visits the site.
	 *
	 * @since 1.0.0
	 *
	 * @action set_current_user Added by the constructor.
	 *
	 * @return void
	 */
	public function hook() {

		$user_id = get_current_user_id();

		if ( ! $user_id )
			return;

		$last_visit = get_user_meta( $user_id, 'wordpoints_points_period_start', true );

		if ( ! is_array( $last_visit ) )
			$last_visit = array();

		$now = current_time( 'timestamp' );

		$awarded_points = false;

		foreach ( $this->get_instances() as $number => $instance ) {

			$instance = array_merge( $this->defaults, $instance );

			$points_type = $this->points_type( $number );

			if ( ! isset( $last_visit[ $points_type ] ) || $last_visit[ $points_type ] + $instance['period'] <= $now ) {

				wordpoints_add_points( $user_id, $instance['points'], $points_type, 'periodic', array( 'period' => $instance['period'] ) );

				$last_visit[ $points_type ] = $now;

				$awarded_points = true;
			}
		}

		if ( $awarded_points ) {

			update_user_meta( $user_id, 'wordpoints_points_period_start', $last_visit );
		}
	}

	/**
	 * Display the log entry for a transaction.
	 *
	 * @since 1.0.0
	 *
	 * @action wordpoints_render_log-periodic Added by the constructor.
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

		switch ( $meta['period'] ) {

			case HOUR_IN_SECONDS:
				$message = _x( 'Hourly points.', 'points log description', 'wordpoints' );
			break;

			case DAY_IN_SECONDS:
				$message = _x( 'Daily points.', 'points log description', 'wordpoints' );
			break;

			case WEEK_IN_SECONDS:
				$message = _x( 'Weekly points.', 'points log description', 'wordpoints' );
			break;

			case 30 * DAY_IN_SECONDS:
				$message = _x( 'Monthly points.', 'points log description', 'wordpoints' );
			break;

			default:
				$message = _x( 'Periodic points.', 'points log description', 'wordpoints' );
		}

		return $message;
	}

	/**
	 * Update a particular instance of this hook.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance New settings for this instance as input by user.
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array Settings to save.
	 */
	protected function update( $new_instance, $old_instance ) {

		$new_instance = array_merge( $this->defaults, $old_instance, $new_instance );

		wordpoints_posint( $new_instance['points'] );
		wordpoints_posint( $new_instance['disapprove'] );

		return $new_instance;
	}

	/**
	 * Echo the settings update form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance Current settings.
	 *
	 * @return bool True.
	 */
	protected function form( $instance ) {

		$instance = array_merge( $this->defaults, $instance );

		$points = wordpoints_posint( $instance['points'] );

		$dropdown_args = array(
			'selected' => $instance['period'],
			'id'       => $this->get_field_id( 'period' ),
			'name'     => $this->get_field_name( 'period' ),
			'class'    => 'widefat',
		);

		$dropdown = new WordPoints_Dropdown_Builder( $this->get_periods(), $dropdown_args );

		?>

		<p>
			<label><?php _ex( 'Points:', 'form label', 'wordpoints' ); ?></label>
			<input class="widefat" name="<?php $this->the_field_name( 'points' ); ?>"  id="<?php $this->the_field_id( 'points' ); ?>" type="text" value="<?php echo $points; ?>" />
		</p>
		<p>
			<label for="<?php $this->the_field_id( 'period' ); ?>"><?php _ex( 'Period:', 'length of time', 'wordpoints' ); ?></label>
			<?php $dropdown->display(); ?>
		</p>

		<?php

		return true;
	}

	/**
	 * Get the array of options for the periods dropdown.
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'wordpoints_points_periods' with the defaults.
	 *
	 * @return array
	 */
	protected function get_periods() {

		$periods = array(
			HOUR_IN_SECONDS     => __( 'hourly',  'wordpoints' ),
			DAY_IN_SECONDS      => __( 'daily',   'wordpoints' ),
			WEEK_IN_SECONDS     => __( 'weekly',  'wordpoints' ),
			30 * DAY_IN_SECONDS => __( 'monthly', 'wordpoints' ),
		);

		/**
		 * The array of options for the points periods dropdown.
		 *
		 * @since 1.0.0
		 *
		 * @param array $periods The default periods. Values are period names, keys
		 *        length of periods in seconds.
		 */
		return apply_filters( 'wordpoints_points_periods', $periods );
	}
}

// end of file /components/points/includes/hooks.php
