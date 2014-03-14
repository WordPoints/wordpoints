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

		add_action( 'delete_post', array( $this, 'clean_logs_on_post_deletion' ) );

		add_filter( 'wordpoints_user_can_view_points_log-post_publish', array( $this, 'user_can_view' ), 10, 2 );
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

		if ( $new_status != 'publish' ) {
			return;
		}

		foreach ( $this->get_instances() as $number => $instance ) {

			$instance = array_merge( $this->defaults, $instance );

			$points_type = $this->points_type( $number );

			if (
				(
					$instance['post_type'] == $post->post_type
					|| (
						$instance['post_type'] == 'ALL'
						&& post_type_exists( $post->post_type )
						&& get_post_type_object( $post->post_type )->public
					)
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
	 * @since 1.1.2 Points are only removed if the post type is public.
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
					$instance['post_type'] == $post->post_type
					|| (
						$instance['post_type'] == 'ALL'
						&& post_type_exists( $post->post_type )
						&& get_post_type_object( $post->post_type )->public
					)
				)
				&& $post->post_status !== 'auto-draft'
				&& $post->post_title !== __( 'Auto Draft', 'default' )
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

		if ( null == $post ) {

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
				return sprintf( _x( '%1$s &#8220;%2$s&#8221; deleted.', 'points log description', 'wordpoints' ), $post_type->labels->singular_name, $meta['post_title'] );
			}
		}

		/* translators: %s will be the post title. */
		return sprintf( _x( 'Post &#8220;%s&#8221; deleted.', 'points log description', 'wordpoints' ), $meta['post_title'] );
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
				'fields'     => 'id',
				'log_type'   => 'post_publish',
				'meta_query' => array(
					array(
						'key'   => 'post_id',
						'value' => $post_id,
					)
				)
			)
		);

		$log_ids = $logs_query->get( 'col' );

		if ( ! $log_ids ) {
			return;
		}

		$post = get_post( $post_id );

		if ( ! $post ) {

			$wpdb->delete(
				$wpdb->wordpoints_points_log_meta
				, array( 'meta_key' => 'post_id', 'meta_value' => $post_id )
				, array( '%s', '%d' )
			);

		} else {

			$wpdb->update(
				$wpdb->wordpoints_points_log_meta
				, array( 'meta_key' => 'post_type', 'meta_value' => $post->post_type )
				, array( 'meta_key' => 'post_id', 'meta_value' => $post_id )
				, array( '%s', '%s' )
				, array( '%s', '%d' )
			);
		}

		wordpoints_regenerate_points_logs( $log_ids );
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

		?>

		<p>
			<label for="<?php $this->the_field_id( 'post_type' ); ?>"><?php _e( 'Select post type:', 'wordpoints' ); ?></label>
			<?php

			wordpoints_list_post_types(
				array(
					'selected' => $instance['post_type'],
					'id'       => $this->get_field_id( 'post_type' ),
					'name'     => $this->get_field_name( 'post_type' ),
					'class'    => 'widefat',
				)
				, array( 'public' => true )
			);

			?>
		</p>
		<p>
			<label for="<?php $this->the_field_id( 'publish' ); ?>"><?php _e( 'Points added when published:', 'wordpoints' ); ?></label>
			<input class="widefat" name="<?php $this->the_field_name( 'publish' ); ?>"  id="<?php $this->the_field_id( 'publish' ); ?>" type="text" value="<?php echo wordpoints_posint( $instance['publish'] ); ?>" />
		</p>
		<p>
			<label for="<?php $this->the_field_id( 'trash' ); ?>"><?php _e( 'Points removed when deleted:', 'wordpoints' ); ?></label>
			<input class="widefat" name="<?php $this->the_field_name( 'trash' ); ?>"  id="<?php $this->the_field_id( 'trash' ); ?>" type="text" value="<?php echo wordpoints_posint( $instance['trash'] ); ?>" />
		</p>

		<?php

		return true;
	}
}
