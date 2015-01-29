<?php

/**
 * The post points hook class.
 *
 * @package WordPoints\Points\Hooks
 * @since 1.4.0
 */

// Register the post hook.
WordPoints_Points_Hooks::register( 'WordPoints_Post_Points_Hook' );

/**
 * Post points hook.
 *
 * Awards points when a post is published.
 *
 * @since 1.0.0
 * @since 1.4.0 No longer subtracts points when a post is deleted.
 * @since 1.9.0 Automatically subtracts points when a post is deleted.
 */
class WordPoints_Post_Points_Hook extends WordPoints_Post_Type_Points_Hook_Base {

	/**
	 * @since 1.9.0
	 */
	protected $log_type = 'post_publish';

	/**
	 * The default values.
	 *
	 * @since 1.0.0
	 *
	 * @type array $defaults
	 */
	protected $defaults = array(
		'points' => 20,
		'post_type' => 'ALL',
		'auto_reverse' => 1,
	);

	/**
	 * Set up the hook.
	 *
	 * @since 1.0.0
	 *
	 * @uses add_action() To set up the hook() method.
	 * @uses add_filter() To hook up the logs() method.
	 */
	public function __construct() {

		parent::__construct(
			_x( 'Post Publish', 'points hook name', 'wordpoints' )
			, array(
				'description' => __( 'New post published.', 'wordpoints' ),
				/* translators: the post type name. */
				'post_type_description' => __( 'New %s published.', 'wordpoints' ),
				/* translators: %s will be a link to the post. */
				'log_text_post_title' => _x( 'Post %s published.', 'points log description', 'wordpoints' ),
				/* translators: 1 is the post type name, 2 is a link to the post. */
				'log_text_post_title_and_type' => _x( '%2$s %1$s published.', 'points log description', 'wordpoints' ),
				/* translators: %s is the name of a post type. */
				'log_text_post_type' => _x( '%s published.', 'points log description', 'wordpoints' ),
				'log_text_no_post_title' => _x( 'Post published.', 'points log description', 'wordpoints' ),
				/* translators: %s is the name of a post type. */
				'log_text_post_type_reverse' => _x( '%s deleted.', 'points log description', 'wordpoints' ),
				/* translators: 1 is the post type name, 2 is the post title. */
				'log_text_post_title_and_type_reverse' => _x( '%1$s &#8220;%2$s&#8221; deleted.', 'points log description', 'wordpoints' ),
				/* translators: %s will be the post title. */
				'log_text_post_title_reverse' => _x( 'Post &#8220;%s&#8221; deleted.', 'points log description', 'wordpoints' ),
				'log_text_no_post_title_reverse' => _x( 'Post deleted.', 'points log description', 'wordpoints' ),
			)
		);

		if ( get_site_option( 'wordpoints_post_hook_legacy' ) ) {
			$this->set_option(
				'disable_auto_reverse_label'
				, __( 'Revoke the points if the post is permanently deleted.', 'wordpoints' )
			);
		}

		add_action( 'transition_post_status', array( $this, 'publish_hook' ), 10, 3 );
		add_action( 'delete_post', array( $this, 'reverse_hook' ) );

		// Back-compat.
		remove_filter( "wordpoints_points_log-{$this->log_type}", array( $this, 'logs' ), 10, 6 );
		add_filter( 'wordpoints_points_log-post_publish', array( $this, 'publish_logs' ), 10, 6 );
	}

	/**
	 * Award points when a post is published.
	 *
	 * @since 1.0.0
	 *
	 * @action transition_post_status Added by the constructor.
	 *
	 * @param string $old_status The old status of the post.
	 * @param string $new_status The new status of the post.
	 * @param object $post       The post object.
	 *
	 * @return void
	 */
	public function publish_hook( $new_status, $old_status, $post ) {

		if ( 'publish' !== $new_status ) {
			return;
		}

		foreach ( $this->get_instances() as $number => $instance ) {

			$instance = array_merge( $this->defaults, $instance );

			$points_type = $this->points_type( $number );

			if (
				$this->is_matching_post_type( $post->post_type, $instance['post_type'] )
				&& ! $this->awarded_points_already( $post->ID, $points_type )
			) {

				if ( isset( $instance['publish'] ) ) {
					_deprecated_argument( __METHOD__, '1.4.0', 'The "publish" hook setting is no longer used to hold the value for the points. Use "points" instead.' );
					$instance['points'] = $instance['publish'];
				}

				if ( ! isset( $instance['points'] ) ) {
					continue;
				}

				wordpoints_alter_points(
					$post->post_author
					, $instance['points']
					, $points_type
					, 'post_publish'
					, array( 'post_id' => $post->ID )
				);
			}
		}
	}

	/**
	 * Automatically reverse any transactions for a post when it is deleted.
	 *
	 * @since 1.9.0
	 *
	 * @WordPress\action delete_post Added by the constructor.
	 */
	public function reverse_hook( $post_id ) {

		$post_type = get_post_field( 'post_type', $post_id );
		if ( ! $this->should_do_auto_reversals_for_post_type( $post_type ) ) {
			return;
		}

		$logs = $this->get_logs_to_auto_reverse(
			array(
				'key'   => 'post_id',
				'value' => $post_id,
			)
		);

		foreach ( $logs as $log ) {
			$this->auto_reverse_log( $log );
		}
	}

	/**
	 * Remove points when a post is deleted.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 The post_type is now passed as metadata when points are awarded.
	 * @since 1.1.2 Points are only removed if the post type is public.
	 * @deprecated 1.4.0
	 * @deprecated Use WordPoints_Post_Points_Hook::reverse_hook() instead.
	 *
	 * @param int $post_id The post's ID.
	 */
	public function delete_hook( $post_id ) {

		_deprecated_function( __METHOD__, '1.4.0', 'WordPoints_Post_Points_Hook::reverse_hook()' );
	}

	/**
	 * Generate the log entry for a publish post transaction.
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

		return parent::logs( $text, $points, $points_type, $user_id, $log_type, $meta );
	}

	/**
	 * Generate the log entry for a transaction.
	 *
	 * @since 1.0.0
	 * @deprecated 1.4.0
	 * @deprecated Use wordpoints_points_logs_post_delete() instead.
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

		_deprecated_function( __METHOD__, '1.4.0', 'wordpoints_points_logs_post_delete' );

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
	 * Check if points have already been awarded for this post.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $post_id     The ID of the post.
	 * @param string $points_type The points type to check.
	 *
	 * @return bool Whether points have been awarded for publishing this post before.
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

} // class WordPoints_Post_Points_Hook

// EOF
