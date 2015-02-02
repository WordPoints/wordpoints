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

		add_action( 'delete_post', array( $this, 'clean_logs_on_post_deletion' ), 15 );

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
	 * Get the logs to auto-reverse.
	 *
	 * @since 1.9.0
	 *
	 * @param array $meta_query Meta query arguments to use in the search.
	 *
	 * @return array The logs to reverse.
	 */
	protected function get_logs_to_auto_reverse( array $meta_query ) {

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => $this->log_type,
				'meta_query' => array(
					$meta_query,
					array(
						'key'     => 'auto_reversed',
						'compare' => 'NOT EXISTS',
						'value'   => 'see bug #23268 (fixed in 3.9)',
					),
				),
			)
		);

		$logs = $query->get();

		if ( ! $logs ) {
			return array();
		}

		return $logs;
	}

	/**
	 * Automatically reverse a transaction.
	 *
	 * @since 1.9.0
	 *
	 * @param object $log The log to reverse.
	 */
	protected function auto_reverse_log( $log ) {

		wordpoints_alter_points(
			$log->user_id
			, -$log->points
			, $log->points_type
			, "reverse_{$this->log_type}"
			, array( 'original_log_id' => $log->id )
		);

		wordpoints_add_points_log_meta( $log->id, 'auto_reversed', true );
	}

	/**
	 * Generate the log entry for a transaction.
	 *
	 * @since 1.9.0
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

		$post = false;

		if ( '' === $reverse && isset( $meta['post_id'] ) ) {
			$post = get_post( $meta['post_id'] );
		}

		if ( $post ) {

			// This post exists, so we should include the permalink in the log text.
			$text = $this->log_with_post_title_link( $post->ID, $reverse );

		} else {

			// This post doesn't exist; we probably can't use the title.
			$text = $this->get_option( 'log_text_no_post_title' . $reverse );

			if (
				$this->get_option( 'log_text_post_type' . $reverse )
				&& isset( $meta['post_type'] )
				&& post_type_exists( $meta['post_type'] )
			) {

				// We do know the type of post though, so include that in the log.
				$text = sprintf(
					$this->get_option( 'log_text_post_type' . $reverse )
					, get_post_type_object( $meta['post_type'] )->labels->singular_name
				);

			} elseif ( isset( $meta['post_title'] ) ) {

				// If the title is saved as metadata, then we can use it.
				$text = sprintf(
					$this->get_option( 'log_text_post_title' . $reverse )
					, $meta['post_title']
				);
			}
		}

		return $text;
	}

	/**
	 * Generate the text for a log that contains a link with the post title as text.
	 *
	 * @since 1.9.0
	 *
	 * @param int    $post_id The ID of the post being linked to.
	 * @param string $reverse Whether this is a reversal ('_reverse') or not ('').
	 * @param string $url     The URL to link to. Default to the post permalink.
	 *
	 * @return string The text for the log entry.
	 */
	protected function log_with_post_title_link( $post_id, $reverse = '', $url = null ) {

		if ( ! isset( $url ) ) {
			$url = get_permalink( $post_id );
		}

		$post_title = get_the_title( $post_id );

		$args = array();

		$args[] = '<a href="' . esc_attr( $url ) . '">'
			. ( $post_title ? $post_title : _x( '(no title)', 'post title', 'wordpoints' ) )
			. '</a>';

		$text = $this->get_option( 'log_text_post_title_and_type' . $reverse );

		if (
			$text
			&& ( $post_type = get_post_field( 'post_type', $post_id ) )
			&& post_type_exists( $post_type )
		) {
			$args[] = get_post_type_object( $post_type )->labels->singular_name;
		} else {
			$text = $this->get_option( 'log_text_post_title' . $reverse );
		}

		return vsprintf( $text, $args );
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

		$disable_label = $this->get_option( 'disable_auto_reverse_label' );

		if ( $disable_label ) {

			?>

			<p>
				<input class="widefat" name="<?php $this->the_field_name( 'auto_reverse' ); ?>" id="<?php $this->the_field_id( 'auto_reverse' ); ?>" type="checkbox" value="1" <?php checked( '1', $instance['auto_reverse'] ); ?> />
				<label for="<?php $this->the_field_id( 'auto_reverse' ); ?>"><?php echo esc_html( $disable_label ); ?></label>
			</p>

		<?php
		}

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

	/**
	 * Clean the logs when a post is deleted.
	 *
	 * Cleans the metadata for any logs related to this post. The post ID meta field
	 * is updated in the database, to instead store the post type. If the post type
	 * isn't available, we just delete those rows.
	 *
	 * After the metadata is cleaned up, the affected logs are regenerated.
	 *
	 * @since 1.9.0
	 *
	 * @WordPress\action delete_post Added by the constructor.
	 *
	 * @param int $post_id The ID of the post being deleted.
	 *
	 * @return void
	 */
	public function clean_logs_on_post_deletion( $post_id ) {

		$logs_query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => $this->log_type,
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

		foreach ( $logs as $log ) {

			wordpoints_delete_points_log_meta( $log->id, 'post_id' );

			if ( $post ) {

				wordpoints_add_points_log_meta(
					$log->id
					, 'post_type'
					, $post->post_type
				);
			}
		}

		wordpoints_regenerate_points_logs( $logs );
	}
}

// EOF
