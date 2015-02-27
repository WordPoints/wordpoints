<?php

/**
 * The periodic points hook class.
 *
 * @package WordPoints\Points\Hooks
 * @since 1.4.0
 */

// Register the periodic hook.
WordPoints_Points_Hooks::register( 'WordPoints_Periodic_Points_Hook' );

/**
 * Periodic points hook.
 *
 * This hook will award points to a user once every period, if the visit the site
 * during that period. For example, one every day that they visit the site.
 *
 * @since 1.0.0
 */
class WordPoints_Periodic_Points_Hook extends WordPoints_Points_Hook {

	/**
	 * The default values.
	 *
	 * @since 1.0.0
	 *
	 * @type array $defaults
	 */
	protected $defaults = array( 'period' => 'daily', 'points' => 10 );

	/**
	 * Initialize the hook.
	 *
	 * @since 1.0.0
	 *
	 * @uses add_action() To hook the hook() method to 'set_current_user'.
	 * @uses add_filter() To add the logs() method to the logs generation filter.
	 */
	public function __construct() {

		$this->init(
			_x( 'Periodic Points', 'points hook name', 'wordpoints' )
			, array( 'description' => __( 'Visiting the site at least once in a given time period.', 'wordpoints' ) )
		);

		add_action( 'init', array( $this, 'hook' ) );

		add_filter( 'wordpoints_points_log-periodic', array( $this, 'logs' ), 10, 6 );
	}

	/**
	 * Award points when a user visits the site.
	 *
	 * @since 1.0.0
	 *
	 * @action set_current_user Added by the constructor.
	 *
	 * @return void
	 */
	public function hook() {

		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return;
		}

		$last_visit = get_user_option( 'wordpoints_points_period_start', $user_id );

		if ( ! is_array( $last_visit ) ) {
			$last_visit = array();
		}

		$now = current_time( 'timestamp' );

		$awarded_points = false;

		foreach ( $this->get_instances() as $number => $instance ) {

			$instance = array_merge( $this->defaults, $instance );

			$points_type = $this->points_type( $number );

			if ( ! $points_type ) {
				continue;
			}

			if (
				! isset( $last_visit[ $points_type ] )
				|| (int) ( $last_visit[ $points_type ] / $instance['period'] ) < (int) ( $now / $instance['period'] )
			) {

				wordpoints_add_points(
					$user_id
					, $instance['points']
					, $points_type
					, 'periodic'
					, array( 'period' => $instance['period'] )
				);

				$last_visit[ $points_type ] = $now;

				$awarded_points = true;
			}
		}

		if ( $awarded_points ) {

			$global = ( ! is_multisite() || is_wordpoints_network_active() );

			update_user_option( $user_id, 'wordpoints_points_period_start', $last_visit, $global );
		}
	}

	/**
	 * Generate the log entry for a transaction.
	 *
	 * @since 1.0.0
	 *
	 * @action wordpoints_render_log-periodic Added by the constructor.
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

		switch ( $meta['period'] ) {

			case HOUR_IN_SECONDS:
				$message = _x( 'Hourly points.', 'points log description', 'wordpoints' );
			break;

			case DAY_IN_SECONDS:
				$message = _x( 'Daily points.', 'points log description', 'wordpoints' );
			break;

			case WEEK_IN_SECONDS:
				$message = _x( 'Weekly points.', 'points log description', 'wordpoints' );
			break;

			case 30 * DAY_IN_SECONDS:
				$message = _x( 'Monthly points.', 'points log description', 'wordpoints' );
			break;

			default:
				$message = _x( 'Periodic points.', 'points log description', 'wordpoints' );
		}

		return $message;
	}

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

		if ( ! empty( $instance['period'] ) ) {

			switch ( $instance['period'] ) {

				case HOUR_IN_SECONDS:
					return __( 'Visiting the site at least once in an hour.', 'wordpoints' );

				case DAY_IN_SECONDS:
					return __( 'Visiting the site at least once in a day.', 'wordpoints' );

				case WEEK_IN_SECONDS:
					return __( 'Visiting the site at least once in a week.', 'wordpoints' );

				case 30 * DAY_IN_SECONDS:
					return __( 'Visiting the site at least once in a month.', 'wordpoints' );

				default:
					return __( 'Visiting the site periodically.', 'wordpoints' );
			}
		}

		return parent::generate_description( $instance );
	}

	/**
	 * Update a particular instance of this hook.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance New settings for this instance as input by user.
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array Settings to save.
	 */
	protected function update( $new_instance, $old_instance ) {

		$new_instance = array_merge( $this->defaults, $old_instance, $new_instance );

		wordpoints_posint( $new_instance['points'] );
		wordpoints_posint( $new_instance['period'] );

		return $new_instance;
	}

	/**
	 * Display the settings update form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance Current settings.
	 *
	 * @return bool True.
	 */
	protected function form( $instance ) {

		$instance = array_merge( $this->defaults, $instance );

		$dropdown_args = array(
			'selected' => $instance['period'],
			'id'       => $this->get_field_id( 'period' ),
			'name'     => $this->get_field_name( 'period' ),
			'class'    => 'widefat wordpoints-append-to-hook-title',
		);

		$dropdown = new WordPoints_Dropdown_Builder( $this->get_periods(), $dropdown_args );

		parent::form( $instance );

		?>

		<p>
			<label for="<?php $this->the_field_id( 'period' ); ?>"><?php echo esc_html_x( 'Period:', 'length of time', 'wordpoints' ); ?></label>
			<?php $dropdown->display(); ?>
		</p>

		<?php

		return true;
	}

	/**
	 * Get the array of options for the periods dropdown.
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'wordpoints_points_periods' with the defaults.
	 *
	 * @return array
	 */
	protected function get_periods() {

		$periods = array(
			HOUR_IN_SECONDS     => __( 'hourly',  'wordpoints' ),
			DAY_IN_SECONDS      => __( 'daily',   'wordpoints' ),
			WEEK_IN_SECONDS     => __( 'weekly',  'wordpoints' ),
			30 * DAY_IN_SECONDS => __( 'monthly', 'wordpoints' ),
		);

		/**
		 * The array of options for the points periods dropdown.
		 *
		 * @since 1.0.0
		 *
		 * @param array $periods The default periods. Values are period names, keys
		 *        length of periods in seconds.
		 */
		return apply_filters( 'wordpoints_points_periods', $periods );
	}

} // class WordPoints_Periodic_Points_Hook

// EOF
