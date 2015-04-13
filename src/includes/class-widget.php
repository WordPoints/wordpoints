<?php

/**
 * Base class providing a bootstrap for widgets.
 *
 * @package WordPoints
 * @since 1.9.0
 */

/**
 * WordPoints widget template.
 *
 * This class was introduced to provide a bootstrap for the plugin's widgets. It
 * implements a widget() method that takes care of displaying the widget title and
 * other common widget code, so that the extending classes just need to implement a
 * widget_body() method to display the main contents of the widget. It also provides
 * an API for verifying an instance of the widget's settings for display, and showing
 * errors to appropriate users when there is a problem.
 *
 * @since 1.9.0
 */
abstract class WordPoints_Widget extends WP_Widget {

	/**
	 * Default settings for an instance of this widget.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	protected $defaults;

	/**
	 * The settings of the widget instance currently being processed.
	 *
	 * This is currently only set when form() or update() is called.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	protected $instance;

	/**
	 * Display an error to the user if they have sufficient capabilities.
	 *
	 * If the user doesn't have the capabilities to edit the widget, we don't
	 * do anything.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Error|string $message The error message to display.
	 * @param array           $args    Arguments for widget display.
	 */
	public function wordpoints_widget_error( $message, $args ) {

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		if ( is_wp_error( $message ) ) {
			$message = $message->get_error_message();
		}

		echo $args['before_widget']; // XSS OK here, WPCS.

		?>

		<div class="wordpoints-widget-error" style="background-color: #fe7777; color: #fff; padding: 5px; border: 2px solid #f00;">
			<p>
				<?php

				echo wp_kses(
					sprintf(
						esc_html__( 'The &#8220;%1$s&#8221; widget could not be displayed because of an error: %2$s', 'wordpoints' )
						, esc_html( $this->name )
						,  $message
					)
					, 'wordpoints_widget_error'
				);

				?>
			</p>
		</div>

		<?php

		echo $args['after_widget']; // XSs OK here too, WPCS.
	}

	/**
	 * Verify an instance's settings.
	 *
	 * This function is called by the widget() method to verify the settings of an
	 * instance before it is displayed.
	 *
	 * You can override this in your child class, but it's recommended that you
	 * return parent::verify_settings().
	 *
	 * @since 1.9.0
	 *
	 * @param array|WP_Error $instance The settings for an instance.
	 *
	 * @return array|WP_Error The verified settings.
	 */
	protected function verify_settings( $instance ) {

		if ( ! isset( $instance['title'] ) ) {
			$instance['title'] = '';
		}

		return $instance;
	}

	/**
	 * Display the widget.
	 *
	 * @since 1.9.0
	 *
	 * @param array $args     Arguments for widget display.
	 * @param array $instance The settings for this widget instance.
	 */
	public function widget( $args, $instance ) {

		$instance = $this->verify_settings( $instance );

		if ( is_wp_error( $instance ) ) {
			$this->wordpoints_widget_error( $instance, $args );
			return;
		}

		echo $args['before_widget']; // XSS OK here, WPCS.

		/**
		 * The widget's title.
		 *
		 * @since 1.0.0
		 *
		 * @param string $title The widget title.
		 */
		$title = apply_filters( 'widget_title', $instance['title'] );

		if ( ! empty( $title ) ) {

			echo $args['before_title'] . $title . $args['after_title']; // XSS OK, WPCS.
		}

		$widget_slug = $this->widget_options['wordpoints_hook_slug'];

		/**
		 * Before a WordPoints widget.
		 *
		 * @since 1.9.0
		 *
		 * @param array $instance The settings for this widget instance.
		 */
		do_action( "wordpoints_{$widget_slug}_widget_before", $instance );

		$this->widget_body( $instance );

		/**
		 * After a WordPoints widget.
		 *
		 * @since 1.9.0
		 *
		 * @param array $instance The settings for this widget instance.
		 */
		do_action( "wordpoints_{$widget_slug}_widget_after", $instance );

		echo $args['after_widget']; // XSS OK here too, WPCS.
	}

	/**
	 * Display the main body of the widget.
	 *
	 * This function will be called in the widget() method to display the main body
	 * of the widget. That method displays the widget title and before and after
	 * content, this function just needs to display the widget's main contents.
	 *
	 * When implementing this method, you need to define the wordpoints_hook_slug
	 * widget option. (The widget options are an array, including the 'description',
	 * that are passed as the thrid argument when calling parent::__construct()).
	 *
	 * @since 1.9.0
	 *
	 * @param array $instance The settings of the widget instance to display.
	 */
	protected function widget_body( $instance ) {}

	/**
	 * @since 2.0.0
	 */
	public function update( $new_instance, $old_instance ) {

		$this->instance = array_merge( $this->defaults, $old_instance, $new_instance );

		$this->update_title();

		return $this->instance;
	}

	/**
	 * Update the title field for a widget instance.
	 *
	 * @since 2.0.0
	 */
	public function update_title() {
		$this->instance['title'] = strip_tags( $this->instance['title'] );
	}

	/**
	 * @since 2.0.0
	 */
	public function form( $instance ) {

		$this->instance = array_merge( $this->defaults, $instance );

		$this->form_title_field();

		return true;
	}

	/**
	 * Display the title field for a widget form.
	 *
	 * @since 2.0.0
	 */
	public function form_title_field() {

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_html_x( 'Title', 'form label', 'wordpoints' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $this->instance['title'] ); ?>" />
		</p>

		<?php
	}

}

// EOF
