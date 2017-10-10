<?php

/**
 * Hooks 1.4.0 updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Updates the points hooks to 1.4.0.
 *
 * @since 2.4.0
 */
class WordPoints_Points_Updater_1_4_0_Hooks implements WordPoints_RoutineI {

	/**
	 * The hooks to split.
	 *
	 * @since 2.4.0
	 *
	 * @var array
	 */
	protected $hooks;

	/**
	 * Whether to split network or regular hooks.
	 *
	 * @since 2.4.0
	 *
	 * @var bool
	 */
	protected $network;

	/**
	 * @since 2.4.0
	 *
	 * @param string[][] $hooks   An array of data for each of the hooks to update:
	 *                            the slug of the `hook` to split, the slug of the
	 *                            `new_hook` that it is being split into, the
	 *                            settings `key` that holds the number of points,
	 *                            and the name of the `split_key` for the points
	 *                            settings in the new hook.
	 * @param bool       $network Whether to split network hooks or regular hooks.
	 */
	public function __construct( $hooks, $network = false ) {

		$this->hooks   = $hooks;
		$this->network = $network;
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {

		add_filter( 'wordpoints_points_hook_update_callback', array( $this, 'clean_hook_settings' ), 10, 4 );

		$network_mode = WordPoints_Points_Hooks::get_network_mode();
		WordPoints_Points_Hooks::set_network_mode( $this->network );

		foreach ( $this->hooks as $data ) {
			$this->split_hooks(
				$data['hook'],
				$data['new_hook'],
				$data['key'],
				$data['split_key']
			);
		}

		WordPoints_Points_Hooks::set_network_mode( $network_mode );

		remove_filter( 'wordpoints_points_hook_update_callback', array( $this, 'clean_hook_settings' ) );
	}

	/**
	 * Split a set of points hooks.
	 *
	 * @since 2.4.0
	 *
	 * @param string $hook      The slug of the hook type to split.
	 * @param string $new_hook  The slug of the new hook that this one is being split into.
	 * @param string $key       The settings key for the hook that holds the points.
	 * @param string $split_key The settings key for points that is being split.
	 */
	protected function split_hooks( $hook, $new_hook, $key, $split_key ) {

		if ( WordPoints_Points_Hooks::get_network_mode() ) {
			$hook_type = 'network';
			$network_  = 'network_';
		} else {
			$hook_type = 'standard';
			$network_  = '';
		}

		$new_hook = WordPoints_Points_Hooks::get_handler_by_id_base( $new_hook );
		$hook     = WordPoints_Points_Hooks::get_handler_by_id_base( $hook );

		$points_types_hooks = WordPoints_Points_Hooks::get_points_types_hooks();
		$instances          = $hook->get_instances( $hook_type );

		// Loop through all of the post hook instances.
		foreach ( $instances as $number => $settings ) {

			// Don't split the hook if it is just a placeholder, or it's already split.
			if ( 0 === (int) $number || ! isset( $settings[ $key ], $settings[ $split_key ] ) ) {
				continue;
			}

			if ( ! isset( $settings['post_type'] ) ) {
				$settings['post_type'] = 'ALL';
			}

			// If the trash points are set, create a post delete points hook instead.
			if ( isset( $settings[ $split_key ] ) && wordpoints_posint( $settings[ $split_key ] ) ) {

				$new_hook->update_callback(
					array(
						'points'    => $settings[ $split_key ],
						'post_type' => $settings['post_type'],
					)
					, $new_hook->next_hook_id_number()
				);

				// Make sure the correct points type is retrieved for network hooks.
				$points_type = $hook->points_type( $network_ . $number );

				// Add this instance to the points-types-hooks list.
				$points_types_hooks[ $points_type ][] = $new_hook->get_id( $number );
			}

			// If the publish points are set, update the settings of the hook.
			if ( isset( $settings[ $key ] ) && wordpoints_posint( $settings[ $key ] ) ) {

				$settings['points'] = $settings[ $key ];

				$hook->update_callback( $settings, $number );

			} else {

				// If not, delete this instance.
				$hook->delete_callback( $hook->get_id( $number ) );
			}

		} // End foreach ( $instances ).

		WordPoints_Points_Hooks::save_points_types_hooks( $points_types_hooks );
	}

	/**
	 * Clean the settings for the post and comment points hooks.
	 *
	 * Removes old and no longer used settings from the comment and post points hooks.
	 *
	 * @since 2.4.0
	 *
	 * @WordPress\filter wordpoints_points_hook_update_callback Added by self::run().
	 *
	 * @param array                  $instance     The settings for the instance.
	 * @param array                  $new_instance The new settings for the instance.
	 * @param array                  $old_instance The old settings for the instance.
	 * @param WordPoints_Points_Hook $hook         The hook object.
	 *
	 * @return array The filtered instance settings.
	 */
	public function clean_hook_settings( $instance, $new_instance, $old_instance, $hook ) {

		if ( $hook instanceof WordPoints_Post_Points_Hook ) {
			unset( $instance['trash'], $instance['publish'] );
		} elseif ( $hook instanceof WordPoints_Comment_Points_Hook ) {
			unset( $instance['approve'], $instance['disapprove'] );
		}

		return $instance;
	}
}

// EOF
