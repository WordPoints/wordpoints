<?php

/**
 * Points logs widget class.
 *
 * @package WordPoints\Points
 * @since   2.3.0
 */

/**
 * Recent points logs widget.
 *
 * @since 1.0.0 As WordPoints_Points_Logs_Widget.
 * @since 2.3.0
 */
class WordPoints_Points_Widget_Logs extends WordPoints_Points_Widget {

	/**
	 * The slug of the points logs query to display.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	protected $query_slug = 'default';

	/**
	 * The cache key to use for the logs query.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	protected $cache_key = 'default:%points_type%';

	/**
	 * The args related to the display of the logs.
	 *
	 * @since 2.3.0
	 *
	 * @var array
	 */
	protected $show_logs_args = array( 'paginate' => false, 'searchable' => false );

	/**
	 * Initialize the widget.
	 *
	 * @since 1.0.0 As part of WordPoints_Points_Logs_Widget.
	 * @since 2.3.0
	 */
	public function __construct() {

		parent::__construct(
			'WordPoints_Points_Logs_Widget'
			, _x( 'Points Logs', 'widget name', 'wordpoints' )
			, array(
				'description'          => __( 'Display the latest points activity.', 'wordpoints' ),
				'wordpoints_hook_slug' => 'points_logs',
			)
		);

		$this->defaults = array(
			'title'       => _x( 'Points Logs', 'widget title', 'wordpoints' ),
			'number_logs' => 10,
			'points_type' => wordpoints_get_default_points_type(),
			'columns'     => array(
				'user'        => 1,
				'points'      => 1,
				'description' => 1,
				'time'        => 1,
			),
		);
	}

	/**
	 * @since 1.9.0 As part of WordPoints_Points_Logs_Widget.
	 * @since 2.3.0
	 */
	protected function verify_settings( $instance ) {

		if ( ! wordpoints_posint( $instance['number_logs'] ) ) {
			$instance['number_logs'] = $this->defaults['number_logs'];
		}

		return parent::verify_settings( $instance );
	}

	/**
	 * @since 1.9.0 As part of WordPoints_Points_Logs_Widget.
	 * @since 2.3.0
	 */
	protected function widget_body( $instance ) {

		$query_args = wordpoints_get_points_logs_query_args(
			$instance['points_type']
			, $this->query_slug
		);

		$query_args['limit'] = $instance['number_logs'];

		$logs_query = new WordPoints_Points_Logs_Query( $query_args );
		$logs_query->prime_cache( $this->cache_key );

		$this->instance = $instance;

		add_filter(
			'wordpoints_points_logs_table_extra_classes'
			, array( $this, 'add_points_logs_table_extra_classes' )
		);

		wordpoints_show_points_logs( $logs_query, $this->show_logs_args );

		remove_filter(
			'wordpoints_points_logs_table_extra_classes'
			, array( $this, 'add_points_logs_table_extra_classes' )
		);
	}

	/**
	 * Filter the points log table classes based on the widget's settings.
	 *
	 * @since 2.3.0
	 *
	 * @param string[] $classes The extra classes for the table.
	 *
	 * @return string[] The extra classes for the table.
	 */
	public function add_points_logs_table_extra_classes( $classes ) {

		if ( $this->instance && isset( $this->instance['columns'] ) ) {
			foreach ( $this->defaults['columns'] as $column => $unused ) {
				if ( empty( $this->instance['columns'][ $column ] ) ) {
					$classes[] = "wordpoints-hide-{$column}-column";
				}
			}
		}

		if ( ! empty( $this->instance['hide_user_names'] ) ) {
			$classes[] = 'wordpoints-hide-user-names';
		}

		if ( ! empty( $this->instance['horizontal_scrolling'] ) ) {
			$classes[] = 'wordpoints-force-horizontal-scrolling';
		}

		return $classes;
	}

	/**
	 * Update widget settings.
	 *
	 * @since 1.0.0 As part of WordPoints_Points_Logs_Widget.
	 * @since 2.3.0
	 *
	 * @param array $new_instance The new settings for this instance.
	 * @param array $old_instance The old settings for this instance.
	 *
	 * @return array The updated settings for the widget instance.
	 */
	public function update( $new_instance, $old_instance ) {

		parent::update( $new_instance, $old_instance );

		if ( ! wordpoints_posint( $this->instance['number_logs'] ) ) {
			$this->instance['number_logs'] = $this->defaults['number_logs'];
		}

		foreach ( $this->instance['columns'] as $column => $value ) {

			if ( ! isset( $this->defaults['columns'][ $column ] ) ) {
				unset( $this->instance['columns'][ $column ] );
			} elseif ( $value ) {
				$this->instance['columns'][ $column ] = '1';
			}
		}

		if ( ! isset( $new_instance['hide_user_names'] ) ) {
			unset( $this->instance['hide_user_names'] );
		} elseif ( ! empty( $this->instance['hide_user_names'] ) ) {
			$this->instance['hide_user_names'] = '1';
		}

		if ( ! isset( $new_instance['horizontal_scrolling'] ) ) {
			unset( $this->instance['horizontal_scrolling'] );
		} elseif ( ! empty( $this->instance['horizontal_scrolling'] ) ) {
			$this->instance['horizontal_scrolling'] = '1';
		}

		return $this->instance;
	}

	/**
	 * @since 1.0.0 As part of WordPoints_Points_Logs_Widget.
	 * @since 2.3.0
	 */
	public function form( $instance ) {

		parent::form( $instance );

		if ( ! wordpoints_posint( $this->instance['number_logs'] ) ) {
			$this->instance['number_logs'] = $this->defaults['number_logs'];
		}

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number_logs' ) ); ?>"><?php esc_html_e( 'Number of log entries to display', 'wordpoints' ); ?></label>
			<input type="number" min="1" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'number_logs' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number_logs' ) ); ?>" value="<?php echo absint( $this->instance['number_logs'] ); ?>" />
		</p>

		<?php

		$this->display_column_fields();
		$this->display_hide_user_names_field();
		$this->display_horizontal_scrolling_field();

		return true;
	}

	/**
	 * Displays fields to decide which columns should be displayed.
	 *
	 * @since 2.3.0
	 */
	protected function display_column_fields() {

		?>

		<fieldset>
			<legend>
				<?php esc_html_e( 'Which columns should be displayed?', 'wordpoints' ); ?>
			</legend>

			<label for="<?php echo esc_attr( $this->get_field_id( 'columns[user]' ) ); ?>">
				<input
					type="checkbox"
					class="widefat"
					id="<?php echo esc_attr( $this->get_field_id( 'columns[user]' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'columns[user]' ) ); ?>"
					value="1"
					<?php checked( ! empty( $this->instance['columns']['user'] ) ); ?>
				/>
				<?php esc_html_e( 'User', 'wordpoints' ); ?>
			</label>
			<br />
			<label for="<?php echo esc_attr( $this->get_field_id( 'columns[points]' ) ); ?>">
				<input
					type="checkbox"
					class="widefat"
					id="<?php echo esc_attr( $this->get_field_id( 'columns[points]' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'columns[points]' ) ); ?>"
					value="1"
					<?php checked( ! empty( $this->instance['columns']['points'] ) ); ?>
				/>
				<?php esc_html_e( 'Points', 'wordpoints' ); ?>
			</label>
			<br />
			<label for="<?php echo esc_attr( $this->get_field_id( 'columns[description]' ) ); ?>">
				<input
					type="checkbox"
					class="widefat"
					id="<?php echo esc_attr( $this->get_field_id( 'columns[description]' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'columns[description]' ) ); ?>"
					value="1"
					<?php checked( ! empty( $this->instance['columns']['description'] ) ); ?>
				/>
				<?php esc_html_e( 'Description', 'wordpoints' ); ?>
			</label>
			<br />
			<label for="<?php echo esc_attr( $this->get_field_id( 'columns[time]' ) ); ?>">
				<input
					type="checkbox"
					class="widefat"
					id="<?php echo esc_attr( $this->get_field_id( 'columns[time]' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'columns[time]' ) ); ?>"
					value="1"
					<?php checked( ! empty( $this->instance['columns']['time'] ) ); ?>
				/>
				<?php esc_html_e( 'Time', 'wordpoints' ); ?>
			</label>
			<br />
		</fieldset>

		<?php
	}

	/**
	 * Displays the hide user names field.
	 *
	 * @since 2.3.0
	 */
	protected function display_hide_user_names_field() {

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'hide_user_names' ) ); ?>">
				<input
					type="checkbox"
					class="widefat"
					id="<?php echo esc_attr( $this->get_field_id( 'hide_user_names' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'hide_user_names' ) ); ?>"
					value="1"
					<?php checked( ! empty( $this->instance['hide_user_names'] ) ); ?>
				/>
				<?php esc_html_e( 'Hide user names (but not avatars)', 'wordpoints' ); ?>
			</label>
		</p>

		<?php
	}

	/**
	 * Displays the horizontal scrolling field.
	 *
	 * @since 2.3.0
	 */
	protected function display_horizontal_scrolling_field() {

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'horizontal_scrolling' ) ); ?>">
				<input
					type="checkbox"
					class="widefat"
					id="<?php echo esc_attr( $this->get_field_id( 'horizontal_scrolling' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'horizontal_scrolling' ) ); ?>"
					value="1"
					<?php checked( ! empty( $this->instance['horizontal_scrolling'] ) ); ?>
				/>
				<?php esc_html_e( 'Enable horizontal scrolling', 'wordpoints' ); ?>
			</label>
		</p>

		<?php
	}
}

// EOF
