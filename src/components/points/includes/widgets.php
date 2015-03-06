<?php

/**
 * WordPoints widgets.
 *
 * @package WordPoints\Points
 * @since 1.0.0
 */

/**
 * Register the widgets.
 *
 * @since 1.0.0
 *
 * @action widgets_init
 */
function wordpoints_register_points_widgets() {

	// My points widget.
	register_widget( 'WordPoints_My_Points_Widget' );

	// Top users widget.
	register_widget( 'WordPoints_Top_Users_Points_Widget' );

	// Points logs widget.
	register_widget( 'WordPoints_Points_Logs_Widget' );
}
add_action( 'widgets_init', 'wordpoints_register_points_widgets' );

/**
 * WordPoints points widget template class.
 *
 * This class is not intended to be instantiated, and should be declared abstract.
 * However, it was not at first, and so it is presently left as non-abstract just in
 * case, for backward compatibiility. It will likely be made abstract in 2.0.0.
 *
 * @since 1.0.0
 * @since 1.9.0 Now extends WordPoints_Widget instead of WP_Widget directly.
 */
/* abstract */ class WordPoints_Points_Widget extends WordPoints_Widget {

	/**
	 * Default settings for the widget.
	 *
	 * @since 1.0.0
	 *
	 * @type array $defaults
	 */
	protected $defaults;

	/**
	 * @since 1.9.0
	 */
	protected function verify_settings( $instance ) {

		if ( isset( $this->defaults['points_type'] ) ) {

			if (
				empty( $instance['points_type'] )
				|| ! wordpoints_is_points_type( $instance['points_type'] )
			) {
				return new WP_Error(
					'wordpoints_widget_invalid_points_type'
					, esc_html__( 'Please select a valid points type.', 'wordpoints' )
				);
			}
		}

		return parent::verify_settings( $instance );
	}

	/**
	 * Ensure that a variable contains the slug of a points type.
	 *
	 * The variable passed will be null if no points types exist.
	 *
	 * (This function *does not* create a new points type.)
	 *
	 * @since 1.0.0
	 *
	 * @param string $maybe_points_type The variable to insure is a points type.
	 */
	protected function make_a_points_type( &$maybe_points_type ) {

		if ( ! wordpoints_is_points_type( $maybe_points_type ) ) {

			$maybe_points_type = wordpoints_get_default_points_type();

			if ( ! $maybe_points_type ) {

				$points_types = wordpoints_get_points_types();

				$maybe_points_type = key( $points_types );
			}
		}
	}
}

/**
 * My Points widget.
 *
 * Note that while the class name is WordPoints_My_Points_Widget, the widget ID base
 * is just WordPoints_Points_Widget.
 *
 * @since 1.0.0
 *
 * @see WordPoints_Points_Widget Parent class.
 */
class WordPoints_My_Points_Widget extends WordPoints_Points_Widget {

	/**
	 * Initialize the widget.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		parent::__construct(
			'WordPoints_Points_Widget'
			, __( 'WordPoints', 'wordpoints' )
			, array(
				'description' => __( 'Display the points of the current logged in user.', 'wordpoints' ),
				'wordpoints_hook_slug' => 'points',
			)
		);

		$this->defaults = array(
			'title'       => _x( 'My Points', 'widget title', 'wordpoints' ),
			'points_type' => wordpoints_get_default_points_type(),
			'text'        => sprintf( __( 'Points: %s', 'wordpoints' ), '%points%' ),
			'alt_text'    => __( 'You must be logged in to view your points.', 'wordpoints' ),
			'number_logs' => 5,
		);

		add_filter( 'wordpoints_points_widget_text', 'esc_html', 20 );
	}

	/**
	 * @since 1.9.0
	 */
	protected function verify_settings( $instance ) {

		if ( ! is_user_logged_in() && empty( $instance['alt_text'] ) ) {
			return new WP_Error;
		}

		if (
			! isset( $instance['number_logs'] )
			|| ! wordpoints_posint( $instance['number_logs'] )
		) {
			$instance['number_logs'] = 0;
		}

		// In case the points type isn't set, we do this first.
		$instance = parent::verify_settings( $instance );

		if ( ! is_wp_error( $instance ) && is_user_logged_in() && empty( $instance['text'] ) ) {
			$instance['text'] = wordpoints_get_points_type_setting( $instance['points_type'], 'name' ) . ': %points%';
		}

		return $instance;
	}

	/**
	 * @since 1.9.0
	 */
	protected function widget_body( $instance ) {

		if ( is_user_logged_in() ) {

			$text = str_replace(
				'%points%',
				wordpoints_format_points(
					wordpoints_get_points( get_current_user_id(), $instance['points_type'] ),
					$instance['points_type'],
					'my_points_widget'
				),
				$instance['text']
			);

		} else {

			$text = $instance['alt_text'];
		}

		/**
		 * The my points widget text.
		 *
		 * By default, esc_html() is hooked to this filter at the priority 20. To
		 * allow HTML in the widget, you can use this code:
		 *
		 * remove_filter( 'wordpoints_points_widget_text', 'esc_html', 20 );
		 *
		 * @since 1.0.0
		 * @since 1.5.0 esc_html() is now hooked, and at priority 20 by default.
		 *
		 * @param string $text The text for the widget set by the user.
		 * @param array  $instance The settings for this instance of the widget.
		 */
		$text = apply_filters( 'wordpoints_points_widget_text', $text, $instance );

		echo '<div class="wordpoints-points-widget-text">', $text, '</div><br />'; // XSS OK, WPCS

		if ( is_user_logged_in() && 0 !== $instance['number_logs'] ) {

			$query_args = wordpoints_get_points_logs_query_args( $instance['points_type'], 'current_user' );

			$query_args['limit'] = $instance['number_logs'];

			$logs_query = new WordPoints_Points_Logs_Query( $query_args );
			$logs_query->prime_cache( 'current_user:%points_type%:%user_id%' );

			wordpoints_show_points_logs( $logs_query, array( 'paginate' => false, 'searchable' => false, 'show_users' => false ) );
		}
	}

	/**
	 * Update widget settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance The new settings for this instance.
	 * @param array $old_instance The old settings for this instance.
	 *
	 * @return array The updated settings for the widget instance.
	 */
	public function update( $new_instance, $old_instance ) {

		$new_instance = array_merge( $this->defaults, $old_instance, $new_instance );

		$new_instance['title']    = strip_tags( $new_instance['title'] );
		$new_instance['text']     = trim( $new_instance['text'] );
		$new_instance['alt_text'] = trim( $new_instance['alt_text'] );

		if ( ! wordpoints_posint( $new_instance['number_logs'] ) ) {
			$new_instance['number_logs'] = 0;
		}

		$this->make_a_points_type( $new_instance['points_type'] );

		return $new_instance;
	}

	/**
	 * Display widget settings form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance The settings for this widget instance.
	 */
	public function form( $instance ) {

		$instance = array_merge( $this->defaults, $instance );

		$dropdown_args = array(
			'selected' => $instance['points_type'],
			'id'       => $this->get_field_id( 'points_type' ),
			'name'     => $this->get_field_name( 'points_type' ),
			'class'    => 'widefat',
		);

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_html_x( 'Title', 'form label', 'wordpoints' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $dropdown_args['id'] ); ?>"><?php esc_html_e( 'Points type to display', 'wordpoints' ); ?></label>
			<br />
			<?php wordpoints_points_types_dropdown( $dropdown_args ); ?>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"><?php esc_html_e( 'Widget text', 'wordpoints' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" value="<?php echo esc_attr( $instance['text'] ); ?>" />
			<small><i><?php echo esc_html( sprintf( __( '%s will be replaced with the points of the logged in user', 'wordpoints' ), '%points%' ) ); ?></i></small>
			<?php

			/**
			 * Display content below the text field of the My Points widget.
			 *
			 * @since 1.7.0
			 *
			 * @param array $instance The settings of the current widget instance.
			 */
			do_action( 'wordpoints_my_points_widget_below_text_field', $instance );

			?>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'alt_text' ) ); ?>"><?php esc_html_e( 'Text if the user is not logged in', 'wordpoints' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'alt_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'alt_text' ) ); ?>" value="<?php echo esc_attr( $instance['alt_text'] ); ?>" />
			<small><i><?php esc_html_e( 'Leave this field blank to hide the widget if the user is not logged in', 'wordpoints' ); ?></i></small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number_logs' ) ); ?>"><?php esc_html_e( 'Number of latest log entries for this user to display', 'wordpoints' ); ?></label>
			<input type="number" min="0" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'number_logs' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number_logs' ) ); ?>" value="<?php echo esc_attr( $instance['number_logs'] ); ?>" />
			<small><i><?php esc_html_e( 'Set this to 0 to keep from showing any logs', 'wordpoints' ); ?></i></small>
		</p>

		<?php

		return true;
	}
}

/**
 * WordPoints Top Users Widget.
 *
 * Note that the class name is WordPoints_Top_Users_Points_Widget, but the ID base
 * for instances of this widget is WordPoints_Top_Users_Widget. This wasn't intended
 * but that's how it is staying for now.
 *
 * @since 1.0.0
 *
 * @see WordPoints_Points_Widget Parent class.
 */
class WordPoints_Top_Users_Points_Widget extends WordPoints_Points_Widget {

	/**
	 * Initialize the widget.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		parent::__construct(
			'WordPoints_Top_Users_Widget'
			, _x( 'WordPoints Top Users', 'widget name', 'wordpoints' )
			, array(
				'description' => __( 'Showcase the users with the most points.', 'wordpoints' ),
				'wordpoints_hook_slug' => 'top_users',
			)
		);

		$this->defaults = array(
			'title'       => _x( 'Top Users', 'widget title', 'wordpoints' ),
			'points_type' => wordpoints_get_default_points_type(),
			'num_users'   => 3,
		);
	}

	/**
	 * @since 1.9.0
	 */
	protected function verify_settings( $instance ) {

		if ( empty( $instance['num_users'] ) ) {
			$instance['num_users'] = $this->defaults['num_users'];
		}

		return parent::verify_settings( $instance );
	}

	/**
	 * @since 1.9.0
	 */
	protected function widget_body( $instance ) {

		wordpoints_points_show_top_users(
			$instance['num_users']
			, $instance['points_type']
			, 'widget'
		);
	}

	/**
	 * Update widget settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance The new settings for this instance.
	 * @param array $old_instance The old settings for this instance.
	 *
	 * @return array The updated settings for the widget instance.
	 */
	public function update( $new_instance, $old_instance ) {

		$new_instance = array_merge( $this->defaults, $old_instance, $new_instance );

		$new_instance['title'] = strip_tags( $new_instance['title'] );

		if ( ! wordpoints_posint( $new_instance['num_users'] ) ) {
			$new_instance['num_users'] = $this->defaults['num_users'];
		}

		$this->make_a_points_type( $new_instance['points_type'] );

		return $new_instance;
	}

	/**
	 * Display widget settings form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance The settings for this widget instance.
	 */
	public function form( $instance ) {

		$instance = array_merge( $this->defaults, $instance );

		$dropdown_args = array(
			'selected' => $instance['points_type'],
			'id'       => $this->get_field_id( 'points_type' ),
			'name'     => $this->get_field_name( 'points_type' ),
			'class'    => 'widefat',
		);

		if ( ! wordpoints_posint( $instance['num_users'] ) ) {
			$instance['num_users'] = $this->defaults['num_users'];
		}

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_html_x( 'Title', 'form label', 'wordpoints' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $dropdown_args['id'] ); ?>"><?php echo esc_html_x( 'Points type', 'form label', 'wordpoints' ); ?></label>
			<?php wordpoints_points_types_dropdown( $dropdown_args ); ?>
		</p>
		<p>
			<label for="<?php echo esc_html( $this->get_field_id( 'num_users' ) ); ?>"><?php esc_html_e( 'Number of top users to show', 'wordpoints' ); ?></label>
			<input type="number" min="1" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'num_users' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'num_users' ) ); ?>" value="<?php echo absint( $instance['num_users'] ); ?>" />
		</p>

		<?php

		return true;
	}
}

/**
 * Recent points logs widget.
 *
 * @since 1.0.0
 */
class WordPoints_Points_Logs_Widget extends WordPoints_Points_Widget {

	/**
	 * Initialize the widget.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		parent::__construct(
			'WordPoints_Points_Logs_Widget'
			, _x( 'Points Logs', 'widget name', 'wordpoints' )
			, array(
				'description' => __( 'Display the latest points activity.', 'wordpoints' ),
				'wordpoints_hook_slug' => 'points_logs',
			)
		);

		$this->defaults = array(
			'title'       => _x( 'Points Logs', 'widget title', 'wordpoints' ),
			'number_logs' => 10,
			'points_type' => wordpoints_get_default_points_type(),
		);
	}

	/**
	 * @since 1.9.0
	 */
	protected function verify_settings( $instance ) {

		if ( ! wordpoints_posint( $instance['number_logs'] ) ) {
			$instance['number_logs'] = $this->defaults['number_logs'];
		}

		return parent::verify_settings( $instance );
	}

	/**
	 * @since 1.9.0
	 */
	public function widget_body( $instance ) {

		$query_args = wordpoints_get_points_logs_query_args( $instance['points_type'] );

		$query_args['limit'] = $instance['number_logs'];

		$logs_query = new WordPoints_Points_Logs_Query( $query_args );
		$logs_query->prime_cache();

		wordpoints_show_points_logs( $logs_query, array( 'paginate' => false, 'searchable' => false ) );
	}

	/**
	 * Update widget settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance The new settings for this instance.
	 * @param array $old_instance The old settings for this instance.
	 *
	 * @return array The updated settings for the widget instance.
	 */
	public function update( $new_instance, $old_instance ) {

		$new_instance = array_merge( $this->defaults, $old_instance, $new_instance );

		$new_instance['title'] = strip_tags( $new_instance['title'] );

		if ( ! wordpoints_posint( $new_instance['number_logs'] ) ) {
			$new_instance['number_logs'] = $this->defaults['number_logs'];
		}

		$this->make_a_points_type( $new_instance['points_type'] );

		return $new_instance;
	}

	/**
	 * Display widget settings form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance The settings for this widget instance.
	 */
	public function form( $instance ) {

		$instance = array_merge( $this->defaults, $instance );

		$dropdown_args = array(
			'selected' => $instance['points_type'],
			'id'       => $this->get_field_id( 'points_type' ),
			'name'     => $this->get_field_name( 'points_type' ),
			'class'    => 'widefat',
		);

		if ( ! wordpoints_posint( $instance['number_logs'] ) ) {
			$instance['number_logs'] = $this->defaults['number_logs'];
		}

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_html_x( 'Title', 'form label', 'wordpoints' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $dropdown_args['id'] ); ?>"><?php echo esc_html_x( 'Points type', 'form label', 'wordpoints' ); ?></label>
			<?php wordpoints_points_types_dropdown( $dropdown_args ); ?>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number_logs' ) ); ?>"><?php esc_html_e( 'Number of log entries to display', 'wordpoints' ); ?></label>
			<input type="number" min="1" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'number_logs' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number_logs' ) ); ?>" value="<?php echo absint( $instance['number_logs'] ); ?>" />
		</p>

		<?php

		return true;
	}
}

// EOF
