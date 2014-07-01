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
 * @since 1.0.0
 */
class WordPoints_Points_Widget extends WP_Widget {

	/**
	 * Default settings for the widget.
	 *
	 * @since 1.0.0
	 *
	 * @type array $defaults
	 */
	protected $defaults;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct( $id_base, $name, $widget_options = array(), $control_options = array() ) {

		parent::__construct( $id_base, $name, $widget_options, $control_options );
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

		parent::__construct( 'WordPoints_Points_Widget', __( 'WordPoints', 'wordpoints' ), array( 'description' => __( 'Display the points of the current logged in user.', 'wordpoints' ) ) );

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
	 * Display the widget.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args     Arguments for widget display.
	 * @param array $instance The settings for this widget instance.
	 */
	public function widget( $args, $instance ) {

		if ( ! is_user_logged_in() && empty( $instance['text_alt'] ) ) {
			return;
		}

		$this->make_a_points_type( $instance['points_type'] );

		if ( ! $instance['points_type'] ) {
			return;
		}

		if ( ! wordpoints_posint( $instance['number_logs'] ) ) {
			$instance['number_logs'] = 0;
		}

		echo $args['before_widget'];

		/**
		 * The widget's title.
		 *
		 * @since 1.0.0
		 *
		 * @param string $title The widget title.
		 */
		$title = apply_filters( 'widget_title', $instance['title'] );

		if ( ! empty( $title ) ) {

			echo $args['before_title'] . $title . $args['after_title'];
		}

		if ( is_user_logged_in() ) {

			if ( empty( $instance['text'] ) ) {

				$instance['text'] = wordpoints_get_points_type_setting( 'name', $instance['points_type'] ) .': %points%';
			}

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

			$text = $instance['text_alt'];
		}

		/**
		 * Before the WordPoints points widget.
		 *
		 * @since 1.0.0
		 *
		 * @param array $instance The settings for this widget instance.
		 */
		do_action( 'wordpoints_points_widget_before', $instance );

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

		echo '<div class="wordpoints-points-widget-text">', $text, '</div><br />';

		if ( $instance['number_logs'] != 0 ) {

			$query_args = wordpoints_get_points_logs_query_args( $instance['points_type'], 'current_user' );

			$query_args['limit'] = $instance['number_logs'];

			$logs_query = new WordPoints_Points_Logs_Query( $query_args );
			$logs_query->prime_cache( 'current_user:%points_type%:%user_id%' );

			wordpoints_show_points_logs( $logs_query, array( 'datatable' => false, 'show_users' => false ) );
		}

		/**
		 * After the WordPoints points widget.
		 *
		 * @since 1.0.0
		 *
		 * @param array $instance The settings for this widget instance.
		 */
		do_action( 'wordpoints_points_widget_after', $instance );

		echo $args['after_widget'];
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
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _ex( 'Title', 'form label', 'wordpoints' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $dropdown_args['id']; ?>"><?php _e( 'Points type to display', 'wordpoints' ); ?></label>
			<br />
			<?php echo wordpoints_points_types_dropdown( $dropdown_args ); ?>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'text' ); ?>"><?php _e( 'Widget text', 'wordpoints' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>" value="<?php echo esc_attr( $instance['text'] ); ?>" />
			<small><i><?php _e( '%points% will be replaced with the points of the logged in user', 'wordpoints' ); ?></i></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'alt_text' ); ?>"><?php _e( 'Text if the user is not logged in', 'wordpoints' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'alt_text' ); ?>" name="<?php echo $this->get_field_name( 'alt_text' ); ?>" value="<?php echo esc_attr( $instance['alt_text'] ); ?>" />
			<small><i><?php _e( 'Leave this field blank to hide the widget if the user is not logged in', 'wordpoints' ); ?></i></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number_logs' ); ?>"><?php _e( 'Number of latest log entries for this user to display', 'wordpoints' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'number_logs' ); ?>" name="<?php echo $this->get_field_name( 'number_logs' ); ?>" value="<?php echo esc_attr( $instance['number_logs'] ); ?>" />
			<small><i><?php _e( 'Set this to 0 to keep from showing any logs', 'wordpoints' ); ?></i></small>
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

		parent::__construct( 'WordPoints_Top_Users_Widget', _x( 'WordPoints Top Users', 'widget name', 'wordpoints' ), array( 'description' => __( 'Showcase the users with the most points.', 'wordpoints' ) ) );

		$this->defaults = array(
			'title'       => _x( 'Top Users', 'widget title', 'wordpoints' ),
			'points_type' => wordpoints_get_default_points_type(),
			'num_users'   => 3,
		);
	}

	/**
	 * Display the widget.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args     Arguments for widget display.
	 * @param array $instance The settings for this widget instance.
	 */
	public function widget( $args, $instance ) {

		$this->make_a_points_type( $instance['points_type'] );

		if ( ! $instance['points_type'] ) {
			return;
		}

		echo $args['before_widget'];

		/**
		 * @see WordPoints_My_Points_Widget::widget()
		 */
		$title = apply_filters( 'widget_title', $instance['title'] );

		if ( ! empty( $title ) ) {

			echo $args['before_title'] . $title . $args['after_title'];
		}

		if ( empty( $instance['num_users'] ) ) {
			$instance['num_users'] = $this->defaults['num_users'];
		}

		$top_users = wordpoints_points_get_top_users( $instance['num_users'], $instance['points_type'] );

		if ( ! $top_users ) {
			return;
		}

		wp_enqueue_style( 'wordpoints-top-users' );

		/**
		 * Before the top users widget.
		 *
		 * @since 1.0.0
		 *
		 * @param array $instance The settings for this widget instance.
		 */
		do_action( 'wordpoints_top_users_widget_before', $instance );

		echo '<table class="wordpoints-points-top-users">';

		$position = 1;

		foreach ( $top_users as $user_id ) {

			$user = get_userdata( $user_id );

			?>

			<tr class="top-<?php echo $position; ?>">
				<td><?php echo number_format_i18n( $position ); ?></td>
				<td><?php echo get_avatar( $user_id, 32 ); ?></td>
				<td><?php echo sanitize_user_field( 'display_name', $user->display_name, $user_id, 'display' ); ?></td>
				<td><?php wordpoints_display_points( $user_id, $instance['points_type'], 'top_users_widget' ); ?></td>
			</tr>

			<?php

			$position++;
		}

		echo '</table>';

		/**
		 * After the top users widget.
		 *
		 * @since 1.0.0
		 *
		 * @param array $instance The settings for this widget instance.
		 */
		do_action( 'wordpoints_top_users_widget_after', $instance );

		echo $args['after_widget'];
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
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _ex( 'Title', 'form label', 'wordpoints' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $dropdown_args['id']; ?>"><?php _ex( 'Points type', 'form label', 'wordpoints' ); ?></label>
			<?php wordpoints_points_types_dropdown( $dropdown_args ); ?>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'num_users' ); ?>"><?php _e( 'Number of top users to show', 'wordpoints' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'num_users' ); ?>" name="<?php echo $this->get_field_name( 'num_users' ); ?>" value="<?php echo absint( $instance['num_users'] ); ?>" />
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

		parent::__construct( 'WordPoints_Points_Logs_Widget', _x( 'Points Logs', 'widget name', 'wordpoints' ), array( 'description' => __( 'Display the latest points activity.', 'wordpoints' ) ) );

		$this->defaults = array(
			'title'       => _x( 'Points Logs', 'widget title', 'wordpoints' ),
			'number_logs' => 10,
			'points_type' => wordpoints_get_default_points_type(),
		);
	}

	/**
	 * Display the widget.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args     Arguments for widget display.
	 * @param array $instance The settings for this widget instance.
	 */
	public function widget( $args, $instance ) {

		if ( ! wordpoints_is_points_type( $instance['points_type'] ) ) {
			return;
		}

		if ( ! wordpoints_posint( $instance['number_logs'] ) ) {
			$instance['number_logs'] = $this->defaults['number_logs'];
		}

		echo $args['before_widget'];

		/**
		 * @see WordPoints_My_Points_Widget::widget()
		 */
		$title = apply_filters( 'widget_title', $instance['title'] );

		if ( ! empty( $title ) ) {

			echo $args['before_title'] . $title . $args['after_title'];
		}

		/**
		 * Before the points logs widget.
		 *
		 * @since 1.0.0
		 *
		 * @param array $instance The settings for this widget instance.
		 */
		do_action( 'wordpoints_top_users_widget_before', $instance );

		$query_args = wordpoints_get_points_logs_query_args( $instance['points_type'] );

		$query_args['limit'] = $instance['number_logs'];

		$logs_query = new WordPoints_Points_Logs_Query( $query_args );
		$logs_query->prime_cache();

		wordpoints_show_points_logs( $logs_query, array( 'datatable' => false ) );

		/**
		 * After the top users widget.
		 *
		 * @since 1.0.0
		 *
		 * @param array $instance The settings for this widget instance.
		 */
		do_action( 'wordpoints_top_users_widget_after', $instance );

		echo $args['after_widget'];
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
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _ex( 'Title', 'form label', 'wordpoints' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $dropdown_args['id']; ?>"><?php _ex( 'Points type', 'form label', 'wordpoints' ); ?></label>
			<?php wordpoints_points_types_dropdown( $dropdown_args ); ?>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number_logs' ); ?>"><?php _e( 'Number of log entries to display', 'wordpoints' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'number_logs' ); ?>" name="<?php echo $this->get_field_name( 'number_logs' ); ?>" value="<?php echo absint( $instance['number_logs'] ); ?>" />
		</p>

		<?php

		return true;
	}
}

// end of file /components/points/includes/widgets.php
