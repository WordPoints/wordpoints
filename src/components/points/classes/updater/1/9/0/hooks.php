<?php

/**
 * Hooks 1.9.0 updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Updates the points hooks to 1.9.0.
 *
 * @since 2.4.0
 */
class WordPoints_Points_Updater_1_9_0_Hooks implements WordPoints_RoutineI {

	/**
	 * The hook type => reverse hook type pairs to update.
	 *
	 * @since 2.4.0
	 *
	 * @var string[]
	 */
	protected $hooks;

	/**
	 * Whether to update network or regular hooks.
	 *
	 * @since 2.4.0
	 *
	 * @var bool
	 */
	protected $network;

	/**
	 * @since 2.4.0
	 *
	 * @param string[] $hooks   The hook types (keys) and reverse hook types
	 *                          (values).
	 * @param bool     $network Whether to update network or regular hooks.
	 */
	public function __construct( $hooks, $network = false ) {

		$this->hooks   = $hooks;
		$this->network = $network;
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {

		$network_mode = WordPoints_Points_Hooks::get_network_mode();
		WordPoints_Points_Hooks::set_network_mode( $this->network );

		foreach ( $this->hooks as $type => $reverse_type ) {
			$this->combine_hooks( $type, $reverse_type );
		}

		WordPoints_Points_Hooks::set_network_mode( $network_mode );
	}

	/**
	 * Combine any Comment/Comment Removed or Post/Post Delete hook instance pairs.
	 *
	 * @since 2.4.0
	 *
	 * @param string $type         The primary hook type that awards the points.
	 * @param string $reverse_type The counterpart hook type that reverses points.
	 */
	protected function combine_hooks( $type, $reverse_type ) {

		$hook         = WordPoints_Points_Hooks::get_handler_by_id_base(
			"wordpoints_{$type}_points_hook"
		);
		$reverse_hook = WordPoints_Points_Hooks::get_handler_by_id_base(
			"wordpoints_{$reverse_type}_points_hook"
		);

		if ( WordPoints_Points_Hooks::get_network_mode() ) {
			$hook_type = 'network';
			$network_  = 'network_';
		} else {
			$hook_type = 'standard';
			$network_  = '';
		}

		$hook_instances         = $hook->get_instances( $hook_type );
		$hook_reverse_instances = $reverse_hook->get_instances( $hook_type );

		$default_points = ( 'post' === $hook_type ) ? 20 : 10;
		$defaults       = array( 'points' => $default_points, 'post_type' => 'ALL' );

		// Get the hooks into an array that is indexed by post type and the
		// number of points. This allows us to easily check for any counterparts when
		// we loop through the reverse type hooks below. It is even safe if a user
		// is doing something crazy like multiple hooks for the same post type.
		$hook_instances_indexed = array();

		foreach ( $hook_instances as $number => $instance ) {

			$instance = array_merge( $defaults, $instance );

			$hook_instances_indexed
			[ $hook->points_type( $network_ . $number ) ]
			[ $instance['post_type'] ]
			[ $instance['points'] ]
			[] = $number;
		}

		foreach ( $hook_reverse_instances as $number => $instance ) {

			$instance = array_merge( $defaults, $instance );

			$points_type = $reverse_hook->points_type( $network_ . $number );

			// We use empty() instead of isset() because array_pop() below may leave
			// us with an empty array as the value.
			if ( empty( $hook_instances_indexed[ $points_type ][ $instance['post_type'] ][ $instance['points'] ] ) ) {
				continue;
			}

			$comment_instance_number = array_pop(
				$hook_instances_indexed[ $points_type ][ $instance['post_type'] ][ $instance['points'] ]
			);

			// We need to unset this instance from the list of hook instances. It
			// is expected for it to be automatically reversed, and that is the
			// default setting. If we don't unset it here it will get auto-reversal
			// turned off below, which isn't what we want.
			unset( $hook_instances[ $comment_instance_number ] );

			// Now we can just delete this reverse hook instance.
			$reverse_hook->delete_callback(
				$reverse_hook->get_id( $number )
			);
		}

		// Any hooks left in the array are not paired with a reverse type hook, and
		// aren't expected to auto-reverse, so we need to turn their auto-reversal
		// setting off.
		if ( ! empty( $hook_instances ) ) {

			foreach ( $hook_instances as $number => $instance ) {
				$instance['auto_reverse'] = 0;
				$hook->update_callback( $instance, $number );
			}

			// We add a flag to the database so we'll know to enable legacy features.
			update_site_option(
				"wordpoints_{$type}_hook_legacy"
				, true
			);
		}

		// Now we check if there are any unpaired reverse type hooks. If there are
		// we'll set this flag in the database that will keep some legacy features
		// enabled.
		if ( $reverse_hook->get_instances( $hook_type ) ) {
			update_site_option(
				"wordpoints_{$reverse_type}_hook_legacy"
				, true
			);
		}
	}
}

// EOF
