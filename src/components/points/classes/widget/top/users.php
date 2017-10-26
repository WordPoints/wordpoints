<?php

/**
 * Top users points widget class.
 *
 * @package WordPoints\Points
 * @since   2.3.0
 */

/**
 * WordPoints Top Users Widget.
 *
 * Note that the class name is WordPoints_Points_Widget_Top_Users, but the ID base
 * for instances of this widget is WordPoints_Top_Users_Widget.
 *
 * @since 1.0.0 As WordPoints_Top_Users_Points_Widget.
 * @since 2.3.0
 *
 * @see WordPoints_Points_Widget Parent class.
 */
class WordPoints_Points_Widget_Top_Users extends WordPoints_Points_Widget {

	/**
	 * Initialize the widget.
	 *
	 * @since 1.0.0 As part of WordPoints_Top_Users_Points_Widget.
	 * @since 2.3.0
	 */
	public function __construct() {

		parent::__construct(
			'WordPoints_Top_Users_Widget'
			, _x( 'WordPoints Top Users', 'widget name', 'wordpoints' )
			, array(
				'description'          => __( 'Showcase the users with the most points.', 'wordpoints' ),
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
	 * @since 1.9.0 As part of WordPoints_Top_Users_Points_Widget.
	 * @since 2.3.0
	 */
	protected function verify_settings( $instance ) {

		if ( empty( $instance['num_users'] ) ) {
			$instance['num_users'] = $this->defaults['num_users'];
		}

		return parent::verify_settings( $instance );
	}

	/**
	 * @since 1.9.0 As part of WordPoints_Top_Users_Points_Widget.
	 * @since 2.3.0
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
	 * @since 1.0.0 As part of WordPoints_Top_Users_Points_Widget.
	 * @since 2.3.0
	 *
	 * @param array $new_instance The new settings for this instance.
	 * @param array $old_instance The old settings for this instance.
	 *
	 * @return array The updated settings for the widget instance.
	 */
	public function update( $new_instance, $old_instance ) {

		parent::update( $new_instance, $old_instance );

		if ( ! wordpoints_posint( $this->instance['num_users'] ) ) {
			$this->instance['num_users'] = $this->defaults['num_users'];
		}

		return $this->instance;
	}

	/**
	 * @since 1.0.0 As part of WordPoints_Top_Users_Points_Widget.
	 * @since 2.3.0
	 */
	public function form( $instance ) {

		parent::form( $instance );

		if ( ! wordpoints_posint( $this->instance['num_users'] ) ) {
			$this->instance['num_users'] = $this->defaults['num_users'];
		}

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'num_users' ) ); ?>"><?php esc_html_e( 'Number of top users to show', 'wordpoints' ); ?></label>
			<input type="number" min="1" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'num_users' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'num_users' ) ); ?>" value="<?php echo absint( $this->instance['num_users'] ); ?>" />
		</p>

		<?php

		return true;
	}
}

// EOF
