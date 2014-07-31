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
 * @since 1.4.0 No longer subtracts points when a hook is deleted.
 *
 * @see WordPoints_Post_Delete_Points_Hook
 */
class WordPoints_Post_Points_Hook extends WordPoints_Post_Type_Points_Hook_Base {

	/**
	 * The default values.
	 *
	 * @since 1.0.0
	 *
	 * @type array $defaults
	 */
	protected $defaults = array( 'points' => 20, 'post_type' => 'ALL' );

	/**
	 * Set up the hook.
	 *
	 * @since 1.0.0
	 *
	 * @uses add_action() To set up the hook() method.
	 * @uses add_filter() To hook up the logs() method.
	 */
	public function __construct() {

		parent::init(
			_x( 'Post Publish', 'points hook name', 'wordpoints' )
			, array(
				'description' => __( 'New post published.', 'wordpoints' ),
				/* translators: the post type name. */
				'post_type_description' => __( 'New %s published.', 'wordpoints' ),
			)
		);

		add_action( 'transition_post_status', array( $this, 'publish_hook' ), 10, 3 );
		add_filter( 'wordpoints_points_log-post_publish', array( $this, 'publish_logs' ), 10, 6 );
		add_action( 'delete_post', array( $this, 'clean_logs_on_post_deletion' ) );
		add_filter( 'wordpoints_user_can_view_points_log-post_publish', array( $this, 'user_can_view' ), 10, 2 );
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

		if ( $new_status !== 'publish' ) {
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
	 * Remove points when a post is deleted.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 The post_type is now passed as metadata when points are awarded.
	 * @since 1.1.2 Points are only removed if the post type is public.
	 * @deprecated 1.4.0
	 * @deprecated Use the WordPoints_Post_Delete_Points_Hook instead.
	 *
	 * @param int $post_id The post's ID.
	 */
	public function delete_hook( $post_id ) {

		_deprecated_function( __METHOD__, '1.4.0', 'WordPoints_Post_Delete_Points_Hook::hook()' );
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

		$post = null;

		if ( isset( $meta['post_id'] ) ) {
			$post = get_post( $meta['post_id'], OBJECT, 'display' );
		}

		if ( ! $post ) {

			$post_type = null;

			if ( isset( $meta['post_type'] ) && post_type_exists( $meta['post_type'] ) ) {
				$post_type = get_post_type_object( $meta['post_type'] );
			}

			if ( $post_type ) {
				/* translators: %s is the name of a post type. */
				return sprintf( _x( '%s published.', 'points log description', 'wordpoints' ), $post_type->labels->singular_name );
			} else {
				return _x( 'Post published.', 'points log description', 'wordpoints' );
			}

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
	 * Generate the log entry for a transaction.
	 *
	 * The data isn't sanitized here becuase we do that before saving it.
	 *
	 * @since 1.0.0
	 * @deprecated 1.4.0
	 * @deprecated Use WordPoints_Post_Delete_Points_Hook::logs() instead.
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

		_deprecated_function( __METHOD__, '1.4.0', 'WordPoints_Post_Delete_Points_Hook::logs()' );

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_post_delete_points_hook' );

		if ( $hook ) {
			$text = $hook->logs( $text, $points, $points_type, $user_id, $log_type, $meta );
		}

		return $text;
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

	/**
	 * Clean the logs when a post is deleted.
	 *
	 * Cleans the metadata for any logs related to this post. The post ID meta field
	 * is updated in the database, to instead store the post type. If the post type
	 * isn't available, we just delete those rows.
	 *
	 * After the metadata is cleaned up, the affected logs are regenerated.
	 *
	 * @since 1.2.0
	 *
	 * @action delete_post Added by the constructor.
	 *
	 * @param int $post_id The ID of the post being deleted.
	 *
	 * @return void
	 */
	public function clean_logs_on_post_deletion( $post_id ) {

		global $wpdb;

		$logs_query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'post_publish',
				'meta_query' => array(
					array(
						'key'   => 'post_id',
						'value' => $post_id,
					),
				),
			)
		);

		$logs = $logs_query->get();

		if ( ! $logs ) {
			return;
		}

		$post = get_post( $post_id );

		if ( ! $post ) {

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

		} else {

			foreach ( $logs as $log ) {

				$wpdb->update(
					$wpdb->wordpoints_points_log_meta
					, array(
						'meta_key'   => 'post_type',
						'meta_value' => $post->post_type,
					)
					, array(
						'meta_key'   => 'post_id',
						'meta_value' => $post_id,
						'log_id'     => $log->id,
					)
					, array( '%s', '%s' )
					, array( '%s', '%d', '%d' )
				);
			}
		}

		wordpoints_regenerate_points_logs( $logs );
	}

	/**
	 * Check if a user can view a particular log entry.
	 *
	 * @since 1.3.0
	 *
	 * @filter wordpoints_user_can_view_points_log-post_publish Added by the constructor.
	 *
	 * @param bool   $can_view Whether the user can view this log entry.
	 * @param object $log      The log object.
	 *
	 * @return bool Whether the user can view this log.
	 */
	public function user_can_view( $can_view, $log ) {

		if ( $can_view ) {
			$post_id = wordpoints_get_points_log_meta( $log->id, 'post_id', true );

			if ( $post_id ) {
				$can_view = current_user_can( 'read_post', $post_id );
			}
		}

		return $can_view;
	}
}
