<?php

/**
 * User points widget class.
 *
 * @package WordPoints\Points
 * @since   2.3.0
 */

/**
 * My Points widget.
 *
 * Note that while the class name is WordPoints_Points_Widget_User_Points, the widget
 * ID base is just WordPoints_Points_Widget.
 *
 * @since 1.0.0 As WordPoints_My_Points_Widget.
 * @since 2.3.0
 * @since 2.3.0 Now extends WordPoints_Points_Widget_Logs.
 *
 * @see WordPoints_Points_Widget Parent class.
 */
class WordPoints_Points_Widget_User_Points extends WordPoints_Points_Widget_Logs {

	/**
	 * @since 2.3.0
	 */
	protected $query_slug = 'current_user';

	/**
	 * @since 2.3.0
	 */
	protected $cache_key = 'current_user:%points_type%:%user_id%';

	/**
	 * Initialize the widget.
	 *
	 * @since 1.0.0 As part of WordPoints_My_Points_Widget.
	 * @since 2.3.0
	 */
	public function __construct() {

		WP_Widget::__construct(
			'WordPoints_Points_Widget'
			, __( 'WordPoints', 'wordpoints' )
			, array(
				'description'          => __( 'Display the points of the current logged in user.', 'wordpoints' ),
				'wordpoints_hook_slug' => 'points',
			)
		);

		$this->defaults = array(
			'title'       => _x( 'My Points', 'widget title', 'wordpoints' ),
			'points_type' => wordpoints_get_default_points_type(),
			// translators: Number of points.
			'text'        => sprintf( __( 'Points: %s', 'wordpoints' ), '%points%' ),
			'alt_text'    => __( 'You must be logged in to view your points.', 'wordpoints' ),
			'number_logs' => 5,
			'columns'     => array(
				'user'        => 0,
				'points'      => 1,
				'description' => 1,
				'time'        => 1,
			),
		);

		add_filter( 'wordpoints_points_widget_text', 'esc_html', 20 );
	}

	/**
	 * @since 1.9.0 As part of WordPoints_My_Points_Widget.
	 * @since 2.3.0
	 */
	protected function verify_settings( $instance ) {

		if ( ! is_user_logged_in() && empty( $instance['alt_text'] ) ) {
			return new WP_Error();
		}

		if (
			! isset( $instance['number_logs'] )
			|| ! wordpoints_posint( $instance['number_logs'] )
		) {
			$instance['number_logs'] = 0;
		}

		// In case the points type isn't set, we do this first.
		$instance = WordPoints_Points_Widget::verify_settings( $instance );

		if ( ! is_wp_error( $instance ) && is_user_logged_in() && empty( $instance['text'] ) ) {
			$instance['text'] = wordpoints_get_points_type_setting( $instance['points_type'], 'name' ) . ': %points%';
		}

		return $instance;
	}

	/**
	 * @since 1.9.0 As part of WordPoints_My_Points_Widget.
	 * @since 2.3.0
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
			parent::widget_body( $instance );
		}
	}

	/**
	 * Update widget settings.
	 *
	 * @since 1.0.0 As part of WordPoints_My_Points_Widget.
	 * @since 2.3.0
	 *
	 * @param array $new_instance The new settings for this instance.
	 * @param array $old_instance The old settings for this instance.
	 *
	 * @return array The updated settings for the widget instance.
	 */
	public function update( $new_instance, $old_instance ) {

		$this->defaults['number_logs'] = 0;

		parent::update( $new_instance, $old_instance );

		$this->instance['text']     = trim( $this->instance['text'] );
		$this->instance['alt_text'] = trim( $this->instance['alt_text'] );

		return $this->instance;
	}

	/**
	 * @since 1.0.0 As part of WordPoints_My_Points_Widget.
	 * @since 2.3.0
	 */
	public function form( $instance ) {

		WordPoints_Points_Widget::form( $instance );

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"><?php esc_html_e( 'Widget text', 'wordpoints' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" value="<?php echo esc_attr( $this->instance['text'] ); ?>" />
			<small>
				<i>
					<?php

					// translators: Placeholder name.
					echo esc_html( sprintf( __( '%s will be replaced with the points of the logged in user', 'wordpoints' ), '%points%' ) );

					?>
				</i>
			</small>
			<?php

			/**
			 * Display content below the text field of the My Points widget.
			 *
			 * @since 1.7.0
			 *
			 * @param array $instance The settings of the current widget instance.
			 */
			do_action( 'wordpoints_my_points_widget_below_text_field', $this->instance );

			?>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'alt_text' ) ); ?>"><?php esc_html_e( 'Text if the user is not logged in', 'wordpoints' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'alt_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'alt_text' ) ); ?>" value="<?php echo esc_attr( $this->instance['alt_text'] ); ?>" />
			<small><i><?php esc_html_e( 'Leave this field blank to hide the widget if the user is not logged in', 'wordpoints' ); ?></i></small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number_logs' ) ); ?>"><?php esc_html_e( 'Number of latest log entries for this user to display', 'wordpoints' ); ?></label>
			<input type="number" min="0" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'number_logs' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number_logs' ) ); ?>" value="<?php echo esc_attr( $this->instance['number_logs'] ); ?>" />
			<small><i><?php esc_html_e( 'Set this to 0 to keep from showing any logs', 'wordpoints' ); ?></i></small>
		</p>

		<?php

		$this->display_column_fields();
		$this->display_hide_user_names_field();
		$this->display_horizontal_scrolling_field();

		return true;
	}
}

// EOF
