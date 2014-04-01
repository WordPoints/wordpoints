<?php

/**
 * The registration points hook class.
 *
 * @package WordPoints\Points\Hooks
 * @since 1.4.0
 */

// Register the registration hook.
WordPoints_Points_Hooks::register( 'WordPoints_Registration_Points_Hook' );

/**
 * Registration hook.
 *
 * Award a user points on registration.
 *
 * @since 1.0.0
 */
class WordPoints_Registration_Points_Hook extends WordPoints_Points_Hook {

	/**
	 * The default values.
	 *
	 * @since 1.0.0
	 *
	 * @type array $defaults
	 */
	private $defaults = array( 'points' => 100 );

	/**
	 * Construct the class.
	 *
	 * @since 1.0.0
	 *
	 * @uses add_action() To add hook() to the 'user_register' action.
	 * @uses add_filter() To add logs() to the log generation hook.
	 */
	public function __construct() {

		parent::init(
			_x( 'Registration', 'points hook name', 'wordpoints' )
			, array( 'description' => _x( 'Registering with the site.', 'points hook description', 'wordpoints' ) )
		);

		add_action( 'user_register', array( $this, 'hook' ) );
		add_filter( 'wordpoints_points_log-register', array( $this, 'logs' ) );
	}

	/**
	 * Award points when the hook is fired.
	 *
	 * @since 1.0.0
	 *
	 * @action user_register Added by the constructor.
	 *
	 * @param int $user_id The ID of the newly registered user.
	 *
	 * @return void
	 */
	public function hook( $user_id ) {

		foreach ( $this->get_instances() as $number => $instance ) {

			if ( isset( $instance['points'] ) ) {
				wordpoints_add_points( $user_id, $instance['points'], $this->points_type( $number ), 'register' );
			}
		}
	}

	/**
	 * Generate the log entry for a transaction.
	 *
	 * @since 1.0.0
	 *
	 * @action wordpoints_render_log-register Added by the constructor.
	 *
	 * @return string The log entry.
	 */
	public function logs() {

		return _x( 'Registration.', 'points log description', 'wordpoints' );
	}

	/**
	 * Update a particular instance of this hook.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance New settings for this instance.
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array Settings to save.
	 */
	protected function update( $new_instance, $old_instance ) {

		$new_instance = array_merge( $this->defaults, $old_instance, $new_instance );

		wordpoints_posint( $new_instance['points'] );

		return $new_instance;
	}

	/**
	 * Echo the settings update form.
	 *
	 * @param array $instance Current settings.
	 *
	 * @return bool True.
	 */
	protected function form( $instance ) {

		$instance = array_merge( $this->defaults, $instance );

		?>

		<p>
			<label for="<?php $this->the_field_id( 'points' ); ?>"><?php _ex( 'Points:', 'form label', 'wordpoints' ); ?></label>
			<input class="widefat" name="<?php $this->the_field_name( 'points' ); ?>"  id="<?php $this->the_field_id( 'points' ); ?>" type="text" value="<?php echo wordpoints_posint( $instance['points'] ); ?>" />
		</p>

		<?php

		return true;
	}
}
