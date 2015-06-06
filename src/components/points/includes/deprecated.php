<?php

/**
 * Deprecated code for the points component.
 *
 * @package WordPoints\Points
 * @since 1.2.0
 */

/**
 * Display top users.
 *
 * @since 1.0.0
 * @deprecated 1.8.0 Use WordPoints_Shortcodes::do_shortcode() instead.
 *
 * @param array $atts {
 *        The shortcode attributes.
 *
 *        @type int    $users       The number of users to display.
 *        @type string $points_type The type of points.
 * }
 *
 * @return string
 */
function wordpoints_points_top_shortcode( $atts ) {

	_deprecated_function( __FUNCTION__, '1.8.0', 'WordPoints_Shortcodes::do_shortcode()' );

	return WordPoints_Shortcodes::do_shortcode( $atts, null, 'wordpoints_points_top' );
}

/**
 * Points logs shortcode.
 *
 * @since 1.0.0
 * @since 1.6.0 The datatables attribute is deprecated in favor of paginate.
 * @since 1.6.0 The searchable attribute is added.
 * @deprecated 1.8.0 Use WordPoints_Shortcodes::do_shortcode() instead.
 *
 * @param array $atts {
 *        The shortcode attributes.
 *
 *        @type string $points_type The type of points to display. Required.
 *        @type string $query       The logs query to display.
 *        @type int    $paginate    Whether to paginate the table. 1 or 0.
 *        @type int    $searchable  Whether to display a search form. 1 or 0.
 *        @type int    $datatables  Whether the table should be a datatable. 1 or 0.
 *                                  Deprecated in favor of paginate.
 *        @type int    $show_users  Whether to show the 'Users' column in the table.
 * }
 *
 * @return string
 */
function wordpoints_points_logs_shortcode( $atts ) {

	_deprecated_function( __FUNCTION__, '1.8.0', 'WordPoints_Shortcodes::do_shortcode()' );

	return WordPoints_Shortcodes::do_shortcode( $atts, null, 'wordpoints_points_logs' );
}

/**
 * Display the points of a user.
 *
 * @since 1.3.0
 * @since 1.8.0 Added support for the post_author value of the user_id attribute.
 * @deprecated 1.8.0 Use WordPoints_Shortcodes::do_shortcode() instead.
 *
 * @param array $atts {
 *        The shortcode attributes.
 *
 *        @type string $points_type The type of points to display.
 *        @type mixed  $user_id     The ID of the user whose points should be
 *                                  displayed. Defaults to the current user. If set
 *                                  to post_author, the author of the current post.
 * }
 *
 * @return string The points for the user.
 */
function wordpoints_points_shortcode( $atts ) {

	_deprecated_function( __FUNCTION__, '1.8.0', 'WordPoints_Shortcodes::do_shortcode()' );

	return WordPoints_Shortcodes::do_shortcode( $atts, null, 'wordpoints_points' );
}

/**
 * Display a list of ways users can earch points.
 *
 * @since 1.4.0
 * @deprecated 1.8.0 Use WordPoints_Shortcodes::do_shortcode() instead.
 *
 * @param array $atts {
 *        The shortcode attributes.
 *
 *        @type string $points_type The type of points to display the list for.
 * }
 *
 * @return string A list of points hooks describing how the user can earn points.
 */
function wordpoints_how_to_get_points_shortcode( $atts ) {

	_deprecated_function( __FUNCTION__, '1.8.0', 'WordPoints_Shortcodes::do_shortcode()' );

	return WordPoints_Shortcodes::do_shortcode( $atts, null, 'wordpoints_how_to_get_points' );
}

/**
 * Comment removed points hook.
 *
 * This hook will remove points if a comment is marked as spam or moved to the trash.
 *
 * Prior to version 1.4.0, this functionality was part of the comment points hook.
 *
 * @since 1.4.0
 * @deprecated 1.9.0
 */
class WordPoints_Comment_Removed_Points_Hook extends WordPoints_Post_Type_Points_Hook_Base {

	/**
	 * The default values.
	 *
	 * @since 1.4.0
	 *
	 * @type array $defaults
	 */
	protected $defaults = array( 'points' => 10, 'post_type' => 'ALL' );

	/**
	 * Initialize the hook.
	 *
	 * @since 1.4.0
	 */
	public function __construct() {

		_deprecated_function( __METHOD__, '1.9.0' );

		$this->init(
			_x( 'Comment Removed', 'points hook name', 'wordpoints' )
			, array(
				'description' => __( 'Comment removed from the site.', 'wordpoints' ),
				/* translators: the post type name. */
				'post_type_description' => __( 'Comment on a %s removed from the site.', 'wordpoints' ),
				'post_type_filter' => array( $this, 'post_type_supports_comments' ),
				'points_label' => __( 'Points subtracted if comment removed:', 'wordpoints' ),
			)
		);

		add_action( 'transition_comment_status', array( $this, 'hook' ), 10, 3 );
		add_filter( 'wordpoints_points_log-comment_disapprove', array( $this, 'logs' ), 10, 6 );
	}

	/**
	 * Remove points when a comment is removed.
	 *
	 * If the comment's status is being transitioned from approved, we remove points.
	 *
	 * @since 1.4.0
	 *
	 * @WordPress\action transition_comment_status Added by the constructor.
	 *
	 * @param string $new_status The new status of the comment.
	 * @param string $old_status The old status of the comment.
	 * @param object $comment    The comment object.
	 *
	 * @return void
	 */
	public function hook( $new_status, $old_status, $comment ) {

		_deprecated_function( __METHOD__, '1.9.0' );

		if ( ! $comment->user_id || $old_status === $new_status ) {
			return;
		}

		$post = get_post( $comment->comment_post_ID );

		if ( 'approved' === $old_status ) {

			foreach ( $this->get_instances() as $number => $instance ) {

				$instance = array_merge( $this->defaults, $instance );

				if ( ! $this->is_matching_post_type( $post->post_type, $instance['post_type'] ) ) {
					continue;
				}

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

		_deprecated_function( __METHOD__, '1.9.0' );

		if ( 0 === (int) $comment->user_id ) {
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

		_deprecated_function( __METHOD__, '1.9.0', 'wordpoints_points_logs_comment_disapprove' );

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
	 * Get the number of points for an instance of this hook.
	 *
	 * @since 1.4.0
	 *
	 * @param int $number The ID number of the instance.
	 *
	 * @return int|false The number of points, or false.
	 */
	public function get_points( $number = null ) {

		_deprecated_function( __METHOD__, '1.9.0' );

		$points = parent::get_points( $number );

		if ( $points ) {
			$points = -$points;
		}

		return $points;
	}

} // class WordPoints_Comment_Removed_Points_Hook

/**
 * Post delete points hook.
 *
 * Subtracts points when a post is permanently deleted.
 *
 * @since 1.4.0
 * @deprecated 1.9.0
 */
class WordPoints_Post_Delete_Points_Hook extends WordPoints_Post_Type_Points_Hook_Base {

	/**
	 * The default values.
	 *
	 * @since 1.4.0
	 *
	 * @type array $defaults
	 */
	protected $defaults = array( 'points' => 20, 'post_type' => 'ALL' );

	//
	// Public Methods.
	//

	/**
	 * Set up the hook.
	 *
	 * @since 1.4.0
	 */
	public function __construct() {

		_deprecated_function( __METHOD__, '1.9.0' );

		$this->init(
			_x( 'Post Delete', 'points hook name', 'wordpoints' )
			,array(
				'description'  => __( 'A post is permanently deleted.', 'wordpoints' ),
				'points_label' => __( 'Points removed when deleted:', 'wordpoints' ),
				/* translators: the post type name. */
				'post_type_description' => __( '%s permanently deleted.', 'wordpoints' ),
			)
		);

		add_action( 'delete_post', array( $this, 'hook' ) );
		add_filter( 'wordpoints_points_log-post_delete', array( $this, 'logs' ), 10, 6 );
	}

	/**
	 * Remove points when a post is deleted.
	 *
	 * @since 1.4.0
	 *
	 * @WordPress\action before_delete_post Added by the constructor.
	 *
	 * @param int $post_id The post's ID.
	 */
	public function hook( $post_id ) {

		_deprecated_function( __METHOD__, '1.9.0' );

		$post = get_post( $post_id, OBJECT, 'display' );

		foreach ( $this->get_instances() as $number => $instance ) {

			$instance = array_merge( $this->defaults, $instance );

			if (
				$this->is_matching_post_type( $post->post_type, $instance['post_type'] )
				&& 'auto-draft' !== $post->post_status
				&& __( 'Auto Draft', 'default' ) !== $post->post_title
			) {

				wordpoints_alter_points(
					$post->post_author
					, -$instance['points']
					, $this->points_type( $number )
					, 'post_delete'
					, array( 'post_title' => $post->post_title, 'post_type' => $post->post_type )
				);
			}
		}
	}

	/**
	 * Generate the log entry for a transaction.
	 *
	 * The data isn't sanitized here becuase we do that before saving it.
	 *
	 * @since 1.4.0
	 *
	 * @WordPress\action wordpoints_render_log-post_delete Added by the constructor.
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

		_deprecated_function( __METHOD__, '1.9.0', 'wordpoints_points_logs_post_delete' );

		return wordpoints_points_logs_post_delete(
			$text
			, $points
			, $points_type
			, $user_id
			, $log_type
			, $meta
		);
	}

	/**
	 * Get the number of points for an instance of this hook.
	 *
	 * @since 1.4.0
	 *
	 * @param int $number The ID number of the instance.
	 *
	 * @return int|false The number of points, or false.
	 */
	public function get_points( $number = null ) {

		_deprecated_function( __METHOD__, '1.9.0' );

		$points = parent::get_points( $number );

		if ( $points ) {
			$points = -$points;
		}

		return $points;
	}

} // class WordPoints_Post_Delete_Points_Hook

/**
 * Get the database schema for the points component.
 *
 * @since 1.5.1
 * @deprecated 2.0.0
 *
 * @return string CREATE TABLE queries that can be passed to dbDelta().
 */
function wordpoints_points_get_db_schema() {

	_deprecated_function( __FUNCTION__, '2.0.0' );

	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();

	return "CREATE TABLE {$wpdb->wordpoints_points_logs} (
			id BIGINT(20) NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) NOT NULL,
			log_type VARCHAR(255) NOT NULL,
			points BIGINT(20) NOT NULL,
			points_type VARCHAR(255) NOT NULL,
			text LONGTEXT,
			blog_id SMALLINT(5) UNSIGNED NOT NULL,
			site_id SMALLINT(5) UNSIGNED NOT NULL,
			date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY points_type (points_type(191)),
			KEY log_type (log_type(191))
		) {$charset_collate};
		CREATE TABLE {$wpdb->wordpoints_points_log_meta} (
			meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			log_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			meta_key VARCHAR(255) DEFAULT NULL,
			meta_value LONGTEXT,
			PRIMARY KEY  (meta_id),
			KEY log_id (log_id),
			KEY meta_key (meta_key(191))
		) {$charset_collate};";
}

/**
 * Add custom capabilities to new sites on creation when in network mode.
 *
 * @since 1.5.0
 *
 * @param int $blog_id The ID of the new site.
 */
function wordpoints_points_add_custom_caps_to_new_sites( $blog_id ) {

	_deprecated_function( __FUNCTION__, '2.0.0' );

	if ( ! is_wordpoints_network_active() ) {
		return;
	}

	switch_to_blog( $blog_id );
	wordpoints_add_custom_caps( wordpoints_points_get_custom_caps() );
	restore_current_blog();
}

// EOF
