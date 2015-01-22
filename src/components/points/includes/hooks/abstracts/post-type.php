<?php

/**
 * Abstract class to extend for points hooks that implement a post type setting.
 *
 * @package WordPoints\Points\Hooks
 * @since 1.5.0
 */

/**
 * A base class for points hooks that have a post type settings.
 *
 * Intended to reduce code (and bug) duplication by providing helper functions.
 *
 * @since 1.5.0
 */
abstract class WordPoints_Post_Type_Points_Hook_Base extends WordPoints_Points_Hook {

	/**
	 * The log type of the logs of this hook.
	 *
	 * @since 1.9.0
	 *
	 * @type string $log_type
	 */
	protected $log_type;

	/**
	 * Initialize the hook.
	 *
	 * @since 1.9.0
	 *
	 * @see WordPoints_Points_Hook::init()
	 */
	public function __construct( $title, $args ) {

		parent::init( $title, $args );

		add_filter( "wordpoints_points_log-{$this->log_type}", array( $this, 'logs' ), 10, 6 );
		add_filter( "wordpoints_points_log-reverse_{$this->log_type}", array( $this, 'logs' ), 10, 6 );

		add_action( 'delete_post', array( $this, 'clean_logs_on_post_deletion' ) );

		add_filter( "wordpoints_user_can_view_points_log-{$this->log_type}", array( $this, 'user_can_view' ), 10, 2 );
	}

	/**
	 * Check if the post type setting matches a certian post type.
	 *
	 * @since 1.5.0
	 *
	 * @param string $post_type          The post type to check against the setting.
	 * @param string $instance_post_type The post type setting from an instance.
	 *
	 * @return bool Whether this post type matches and points should be awarded.
	 */
	public function is_matching_post_type( $post_type, $instance_post_type ) {

		return (
			$instance_post_type === $post_type
			|| (
				$instance_post_type === 'ALL'
				&& post_type_exists( $post_type )
				&& get_post_type_object( $post_type )->public
			)
		);
	}

	/**
	 * Check if automatic reversals should be performed for a particular post type.
	 *
	 * @since 1.9.0
	 *
	 * @param int $post_type The slug of a post type.
	 *
	 * @return bool True if auto-reversals should be done; false otherwise.
	 */
	protected function should_do_auto_reversals_for_post_type( $post_type ) {

		// Check if this hook type allows auto-reversals to be disabled.
		if ( ! $this->get_option( 'disable_auto_reverse_label' ) ) {
			return true;
		}

		$instances = $this->get_instances();

		foreach ( $instances as $instance ) {

			$instance = array_merge( $this->defaults, $instance );

			if ( $post_type === $instance['post_type'] ) {
				return ! empty( $instance['auto_reverse'] );
			} elseif ( 'ALL' === $instance['post_type'] ) {
				$all_posts_instance = $instance;
			}
		}

		return ! empty( $all_posts_instance['auto_reverse'] );
	}

	/**
	 * Generate a description for an instance of this hook.
	 *
	 * @since 1.5.0
	 *
	 * @param array $instance The settings for the instance the description is for.
	 *
	 * @return string A description for the hook instance.
	 */
	protected function generate_description( $instance = array() ) {

		if ( ! empty( $instance['post_type'] ) && 'ALL' !== $instance['post_type'] ) {
			$post_type = get_post_type_object( $instance['post_type'] );

			if ( $post_type ) {
				return sprintf( $this->get_option( 'post_type_description' ), $post_type->labels->singular_name );
			}
		}

		return parent::generate_description( $instance );
	}

	/**
	 * Display the settings update form.
	 *
	 * @since 1.5.0
	 *
	 * @param array $instance Current settings.
	 *
	 * @return bool True.
	 */
	protected function form( $instance ) {

		$instance = array_merge( $this->defaults, $instance );

		?>

		<p>
			<label for="<?php $this->the_field_id( 'post_type' ); ?>"><?php esc_html_e( 'Select post type:', 'wordpoints' ); ?></label>
			<?php

			wordpoints_list_post_types(
				array(
					'selected' => $instance['post_type'],
					'id'       => $this->get_field_id( 'post_type' ),
					'name'     => $this->get_field_name( 'post_type' ),
					'class'    => 'widefat wordpoints-append-to-hook-title',
					'filter'   => $this->get_option( 'post_type_filter' ),
				)
				, array( 'public' => true )
			);

			?>
		</p>

		<?php parent::form( $instance ); ?>

		<?php

		return true;
	}

	/**
	 * Filter post types based on whether they support comments.
	 *
	 * @since 1.5.1
	 *
	 * @param object $post_type A post type object.
	 *
	 * @return bool True if the post type supports comments, false otherwise.
	 */
	public function post_type_supports_comments( $post_type ) {

		return post_type_supports( $post_type->name, 'comments' );
	}

	/**
	 * Check if a user can view a particular log entry.
	 *
	 * @since 1.9.0
	 *
	 * @WordPress/filter wordpoints_user_can_view_points_log-{$this->log_type}
	 *                   Added by the constructor.
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

// EOF
