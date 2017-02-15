<?php

/**
 * Points widget class.
 *
 * @package WordPoints|Points
 * @since   2.3.0
 */

/**
 * WordPoints points widget template class.
 *
 * @since 1.0.0
 * @since 1.9.0 Now extends WordPoints_Widget instead of WP_Widget directly.
 * @since 2.0.0 Now abstract.
 */
abstract class WordPoints_Points_Widget extends WordPoints_Widget {

	/**
	 * @since 1.9.0
	 */
	protected function verify_settings( $instance ) {

		if ( isset( $this->defaults['points_type'] ) ) {

			// This happens when the widget is first created in the Customizer; the
			// settings are completely empty.
			if ( empty( $instance['points_type'] ) ) {
				$default = $this->defaults['points_type'];

				if ( ! $default ) {
					$this->make_a_points_type( $default );
				}

				$instance['points_type'] = $default;
			}

			if ( ! wordpoints_is_points_type( $instance['points_type'] ) ) {
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

	/**
	 * @since 2.0.0
	 */
	public function update( $new_instance, $old_instance ) {

		parent::update( $new_instance, $old_instance );

		$this->make_a_points_type( $this->instance['points_type'] );

		return $this->instance;
	}

	/**
	 * @since 2.0.0
	 */
	public function form( $instance ) {

		parent::form( $instance );

		$this->form_points_type_field();

		return true;
	}

	/**
	 * Display the points type field for a widget form.
	 *
	 * @since 2.0.0
	 */
	public function form_points_type_field() {

		$dropdown_args = array(
			'selected' => $this->instance['points_type'],
			'id'       => $this->get_field_id( 'points_type' ),
			'name'     => $this->get_field_name( 'points_type' ),
			'class'    => 'widefat',
		);

		?>

		<p>
			<label for="<?php echo esc_attr( $dropdown_args['id'] ); ?>"><?php echo esc_html_x( 'Points type', 'form label', 'wordpoints' ); ?></label>
			<?php wordpoints_points_types_dropdown( $dropdown_args ); ?>
		</p>

		<?php
	}
}

// EOF
