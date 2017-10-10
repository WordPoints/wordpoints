<?php

/**
 * Legacy points hook to reaction importer class.
 *
 * @package WordPoints\Points
 * @since 2.1.0
 */

/**
 * Imports legacy points hooks to reactions in the new hooks API.
 *
 * @since 2.1.0
 */
class WordPoints_Points_Legacy_Hook_To_Reaction_Importer {

	/**
	 * The legacy points hook handler for the type of hook being imported.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Points_Hook
	 */
	protected $legacy_handler;

	/**
	 * The legacy slug of the type of points hook being imported.
	 *
	 * This is the name of the class, minus the leading "WordPoints_" and the
	 * trailing "_Points_Hook", and all lowercase.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $legacy_id_base;

	/**
	 * The slug of the event to use when converting the hook instances to reactions.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $event_slug;

	/**
	 * The target to use when converting the hook instances to reactions.
	 *
	 * @since 2.1.0
	 *
	 * @var string[]
	 */
	protected $target;

	/**
	 * The settings the legacy points hooks of this type are expected to have.
	 *
	 * The settings should be keys, the values are currently unused and should be a
	 * simple boolean true.
	 *
	 * @since 2.1.0
	 *
	 * @var array
	 */
	protected $expected_settings;

	/**
	 * The legacy log type for the points logs created by points hooks of this type.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $legacy_log_type;

	/**
	 * The legacy points logs meta key used to store the ID of the primary entity.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $legacy_meta_key;

	/**
	 * Whether or not to skip importing of points hooks that don't auto-reverse.
	 *
	 * If true, hooks that have the auto_reverse setting set to a falsey value will
	 * not be imported. We currently always skip these hooks.
	 *
	 * @since 2.1.0
	 *
	 * @var bool
	 */
	protected $skip_non_reversing_hooks = true;

	/**
	 * The type of hook currently being imported, "network" or "standard".
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $hook_mode;

	/**
	 * Data for each of the hooks that have been imported.
	 *
	 * In case we want to save this information for later.
	 *
	 * @since 2.1.0
	 *
	 * @var array[]
	 */
	protected $imported_hooks;

	/**
	 * The list of legacy hook IDs, indexed by points type.
	 *
	 * @since 2.1.0
	 *
	 * @var string[][]
	 */
	protected $points_types_hooks;

	/**
	 * The reaction store that we are storing the reactions in upon import.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_Reaction_StoreI
	 */
	protected $reaction_store;

	/**
	 * The legacy points hook instances being imported.
	 *
	 * We remove instances from this list as we import them. So any that are left at
	 * the end must not have been imported for various reasons.
	 *
	 * @since 2.1.0
	 *
	 * @var array[]
	 */
	protected $legacy_instances;

	/**
	 * The legacy points hook instance currently being imported.
	 *
	 * @since 2.1.0
	 *
	 * @var array
	 */
	protected $legacy_instance;

	/**
	 * Constructs the importer.
	 *
	 * @since 2.1.0
	 *
	 * @param string $legacy_id_base    The id base of the type of hook to import.
	 * @param string $event_slug        The slug of the event to use when converting
	 *                                  an instance to a reaction.
	 * @param array  $expected_settings The expected settings for this hook. Only the
	 *                                  keys are used, the values should just be
	 *                                  boolean true. If an instance includes any
	 *                                  settings which are not in this list, it will
	 *                                  not be imported.
	 * @param string $legacy_log_type   The legacy log type used by the type of hook
	 *                                  being imported.
	 * @param array  $target            The target to use when converting an instance
	 *                                  to a reaction.
	 * @param string $legacy_meta_key   The legacy points logs meta key used to store
	 *                                  the ID of the primary entity for the type of
	 *                                  hook being imported. Defaults to none.
	 */
	public function __construct(
		$legacy_id_base,
		$event_slug,
		$expected_settings,
		$legacy_log_type,
		$target,
		$legacy_meta_key = null
	) {

		$this->event_slug        = $event_slug;
		$this->target            = $target;
		$this->expected_settings = $expected_settings;
		$this->legacy_log_type   = $legacy_log_type;
		$this->legacy_id_base    = $legacy_id_base;
		$this->legacy_meta_key   = $legacy_meta_key;
	}

	/**
	 * Import points hooks based on the settings this importer was constructed with.
	 *
	 * @since 2.1.0
	 */
	public function import() {

		$this->legacy_handler = WordPoints_Points_Hooks::get_handler_by_id_base(
			$this->legacy_id_base
		);

		if ( ! $this->legacy_handler ) {
			return;
		}

		if ( WordPoints_Points_Hooks::get_network_mode() ) {
			$this->hook_mode = 'network';
		} else {
			$this->hook_mode = 'standard';
		}

		$imported_option      = 'wordpoints_imported_points_hooks';
		$this->imported_hooks = $this->get_array_option( $imported_option );

		$this->points_types_hooks = WordPoints_Points_Hooks::get_points_types_hooks();

		$this->reaction_store = wordpoints_hooks()->get_reaction_store( 'points' );

		if ( ! $this->reaction_store ) {
			return;
		}

		$this->legacy_instances = $this->legacy_handler->get_instances(
			$this->hook_mode
		);

		foreach ( $this->legacy_instances as $number => $legacy_instance ) {
			$this->import_instance( $legacy_instance, $number );
		}

		// This means that all of the instances were imported successfully.
		if ( empty( $this->legacy_instances ) ) {

			$option = 'wordpoints_legacy_points_hooks_disabled';

			$disabled = $this->get_array_option( $option );
			$disabled[ $this->legacy_id_base ] = true;

			$this->update_option( $option, $disabled );
		}

		WordPoints_Points_Hooks::save_points_types_hooks( $this->points_types_hooks );

		$this->update_option( $imported_option, $this->imported_hooks );
	}

	/**
	 * Attempt to import a legacy points hook instance as a hook reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param array $legacy_instance The legacy points hook instance being imported.
	 * @param int   $number          The number ID for this instance.
	 *
	 * @return bool Whether the instance was successfully converted to a reaction.
	 */
	protected function import_instance( $legacy_instance, $number ) {

		$this->legacy_instance = $legacy_instance;

		if ( ! $this->should_import_instance() ) {
			return false;
		}

		$this->legacy_handler->set_number( $number );

		$points_type = $this->legacy_handler->points_type(
			'network' === $this->hook_mode ? "network_{$number}" : $number
		);

		$reaction_settings = array(
			'points'          => $legacy_instance['points'],
			'target'          => $this->target,
			'reactor'         => 'points_legacy',
			'event'           => $this->event_slug,
			'points_type'     => $points_type,
			'description'     => $this->legacy_handler->get_description(),
			'legacy_log_type' => $this->legacy_log_type,
			'legacy_meta_key' => $this->legacy_meta_key,
		);

		$order = array_search(
			$this->legacy_handler->get_id()
			, $this->points_types_hooks[ $points_type ]
			, true
		);

		$reaction_settings = $this->filter_reaction_settings(
			$reaction_settings,
			$order
		);

		if ( ! isset( $reaction_settings['log_text'] ) ) {
			$reaction_settings['log_text'] = $this->get_log_text_for_instance(
				$points_type
			);
		}

		if ( ! $this->create_reaction( $reaction_settings, $order ) ) {
			return false;
		}

		$this->legacy_handler->delete_callback( $number );

		unset(
			$this->points_types_hooks[ $points_type ][ $order ]
			, $this->legacy_instances[ $number ]
		);

		return true;
	}

	/**
	 * Checks if the current legacy points hook instance should be imported.
	 *
	 * @since 2.1.0
	 *
	 * @return bool Whether to import the instance or not.
	 */
	protected function should_import_instance() {

		// We ignore certain settings...
		$ignored = array( '_description' => true );

		// But if there are any other unexpected ones, we really can't safely import.
		if ( array_diff_key( $this->legacy_instance, $this->expected_settings, $ignored ) ) {
			return false;
		}

		if (
			$this->skip_non_reversing_hooks
			&& isset( $this->legacy_instance['auto_reverse'] )
			&& ! $this->legacy_instance['auto_reverse']
		) {
			return false;
		}

		return true;
	}

	/**
	 * Get log text setting to use for the reaction created from this instance.
	 *
	 * @since 2.1.0
	 *
	 * @param string $points_type The points type that this instance is for.
	 * @param array  $meta        The "transaction metadata" to use.
	 *
	 * @return string The log text derived from this instance.
	 */
	protected function get_log_text_for_instance( $points_type, $meta = array() ) {

		if ( ! method_exists( $this->legacy_handler, 'logs' ) ) {
			return $this->legacy_handler->get_option( 'description' );
		}

		if (
			! isset( $meta['post_type'] )
			&& isset( $this->legacy_instance['post_type'] )
		) {
			$meta['post_type'] = $this->legacy_instance['post_type'];
		}

		if (
			! isset( $meta['period'] )
			&& isset( $this->legacy_instance['period'] )
		) {
			$meta['period'] = $this->legacy_instance['period'];
		}

		return $this->legacy_handler->logs(
			''
			, 0
			, $points_type
			, 0
			, $this->legacy_id_base
			, $meta
		);
	}

	/**
	 * Modifies the reaction settings before they are saved.
	 *
	 * Note that it is expected that you will NOT modify the 'points_type' setting.
	 *
	 * @since 2.1.0
	 *
	 * @param array $settings The reaction settings.
	 * @param int   $order    The order number of the legacy hook instance.
	 *
	 * @return array The modified settings.
	 */
	protected function filter_reaction_settings( $settings, $order ) {

		if ( 'wordpoints_periodic_points_hook' === $this->legacy_id_base ) {

			$settings['points_legacy_periods'] = array(
				'fire' => array(
					array(
						'length' => $this->legacy_instance['period'],
						'args'   => array( array( 'current:user' ) ),
					),
				),
			);

		} else {

			// We do this even if reversals will be blocked, in case the blocking
			// is ever removed for this reaction.
			$settings['points_legacy_reversals'] = array(
				'toggle_off' => 'toggle_on',
			);
		}

		if (
			isset( $this->legacy_instance['auto_reverse'] )
			&& ! $this->legacy_instance['auto_reverse']
		) {
			$settings['blocker']['toggle_off'] = true;
		}

		if ( isset( $this->legacy_instance['post_type'] ) ) {

			$post_type = $this->legacy_instance['post_type'];

			if ( 'ALL' === $post_type ) {

				$post_type_slugs = get_post_types( array( 'public' => true ) );

				$post_type = array_pop( $post_type_slugs );

				foreach ( $post_type_slugs as $post_type_slug ) {

					$this->create_reaction(
						$this->format_settings_for_post_type(
							$post_type_slug
							, $settings
						)
						, $order
					);
				}
			}

			$settings = $this->format_settings_for_post_type(
				$post_type
				, $settings
			);
		}

		return $settings;
	}

	/**
	 * Format the settings for a reaction for a particular post type.
	 *
	 * @since 2.1.0
	 *
	 * @param string $post_type The slug of the post type to format the settings for.
	 * @param array  $settings  The reaction settings.
	 *
	 * @return array The settings modified for this particular post type.
	 */
	protected function format_settings_for_post_type( $post_type, $settings ) {

		if (
			'post_publish\post' === $settings['event']
			|| 'points_legacy_post_publish\post' === $settings['event']
		) {

			if ( 'attachment' === $post_type ) {

				$settings['event'] = 'media_upload';

			} else {

				$settings['points_legacy_repeat_blocker'] = array(
					'toggle_on' => true,
				);
			}
		}

		$settings['event'] = str_replace(
			'\post'
			, '\\' . $post_type
			, $settings['event']
		);

		$settings['target'] = str_replace(
			'\post'
			, '\\' . $post_type
			, $settings['target']
		);

		$settings['log_text'] = $this->get_log_text_for_instance(
			$settings['points_type']
			, array( 'post_type' => $post_type )
		);

		return $settings;
	}

	/**
	 * Create a reaction and mark this instance as imported.
	 *
	 * @since 2.1.0
	 *
	 * @param array $settings The settings for this reaction.
	 * @param int   $order    The order number of the legacy hook instance.
	 *
	 * @return WordPoints_Hook_ReactionI|false The reaction, or false.
	 */
	protected function create_reaction( $settings, $order ) {

		$reaction = $this->reaction_store->create_reaction( $settings );

		if ( ! $reaction instanceof WordPoints_Hook_ReactionI ) {
			return false;
		}

		$this->imported_hooks[] = array(
			'order'       => $order,
			'id_base'     => $this->legacy_id_base,
			'instance'    => $this->legacy_instance,
			'points_type' => $settings['points_type'],
			'reaction_id' => $reaction->get_id(),
		);

		return $reaction;
	}

	/**
	 * Update a network or regular option based on the current hook mode.
	 *
	 * @since 2.1.0
	 *
	 * @param string $option The option name.
	 * @param mixed  $value  The option value.
	 *
	 * @return bool Whether the option was updated successfully.
	 */
	protected function update_option( $option, $value ) {

		if ( 'network' === $this->hook_mode ) {
			return update_site_option( $option, $value );
		} else {
			return update_option( $option, $value );
		}
	}

	/**
	 * Get a network or regular option based on the current hook mode.
	 *
	 * @since 2.1.0
	 *
	 * @param string $option The option name.
	 *
	 * @return array The option value.
	 */
	protected function get_array_option( $option ) {

		return wordpoints_get_maybe_network_array_option(
			$option
			, 'network' === $this->hook_mode
		);
	}
}

// EOF
