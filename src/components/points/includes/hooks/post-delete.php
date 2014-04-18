<?php

/**
 * The post delete points hook class.
 *
 * @package WordPoints\Points\Hooks
 * @since 1.4.0
 */

// Register the post delete hook.
WordPoints_Points_Hooks::register( 'WordPoints_Post_Delete_Points_Hook' );

/**
 * Post delete points hook.
 *
 * Subtracts points when a post is permanently deleted.
 *
 * @since 1.4.0
 */
class WordPoints_Post_Delete_Points_Hook extends WordPoints_Points_Hook {

	/**
	 * The default values.
	 *
	 * @since 1.4.0
	 *
	 * @type array $defaults
	 */
	private $defaults = array( 'points' => 20, 'post_type' => 'ALL' );

	//
	// Public Methods.
	//

	/**
	 * Set up the hook.
	 *
	 * @since 1.4.0
	 */
	public function __construct() {

		parent::init(
			_x( 'Post Delete', 'points hook name', 'wordpoints' )
			,array(
				'description' => __( 'A post is permanently deleted.', 'wordpoints' ),
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
	 * @action before_delete_post Added by the constructor.
	 *
	 * @param int $post_id The post's ID.
	 */
	public function hook( $post_id ) {

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
	public function logs( $text, $points, $points_type, $user_id, $log_type, $meta ) {

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

	//
	// Protected Methods.
	//

	/**
	 * Generate a description for an instance of this hook.
	 *
	 * @since 1.4.0
	 *
	 * @param array $instance The settings for the instance the description is for.
	 *
	 * @return string A description for the hook instance.
	 */
	protected function generate_description( $instance = array() ) {

		if ( ! empty( $instance['post_type'] ) && $instance['post_type'] !== 'ALL' ) {
			$post_type = get_post_type_object( $instance['post_type'] );

			if ( $post_type ) {
				/* translators: the post type name. */
				return sprintf( __( '%s permanently deleted.', 'wordpoints' ), $post_type->labels->singular_name );
			}
		}

		return parent::generate_description( $instance );
	}

	/**
	 * Update a particular instance of this hook.
	 *
	 * @since 1.4.0
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
			<label for="<?php $this->the_field_id( 'points' ); ?>"><?php _e( 'Points removed when deleted:', 'wordpoints' ); ?></label>
			<input class="widefat" name="<?php $this->the_field_name( 'points' ); ?>"  id="<?php $this->the_field_id( 'points' ); ?>" type="text" value="<?php echo wordpoints_posint( $instance['points'] ); ?>" />
		</p>

		<?php

		return true;
	}
}
