<?php

/**
 * Periods hook extension.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Limits the number of times that targets can be hit in a given time period.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Extension_Periods
	extends WordPoints_Hook_Extension
	implements WordPoints_Hook_Extension_Hit_ListenerI,
		WordPoints_Hook_UI_Script_Data_ProviderI {

	/**
	 * @since 2.1.0
	 */
	protected $slug = 'periods';

	/**
	 * The type of action that is being fired.
	 *
	 * Set in fire() and after_fire().
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $action_type;

	/**
	 * @since 2.1.0
	 */
	public function get_ui_script_data() {

		$period_units = array(
			1                   => __( 'Seconds', 'wordpoints' ),
			MINUTE_IN_SECONDS   => __( 'Minutes', 'wordpoints' ),
			HOUR_IN_SECONDS     => __( 'Hours'  , 'wordpoints' ),
			DAY_IN_SECONDS      => __( 'Days'   , 'wordpoints' ),
			WEEK_IN_SECONDS     => __( 'Weeks'  , 'wordpoints' ),
			30 * DAY_IN_SECONDS => __( 'Months' , 'wordpoints' ),
		);

		/**
		 * Filter the list of period units displayed in the hooks UI.
		 *
		 * @since 2.2.0
		 *
		 * @param string[] $units The unit titles, indexed by unit length in seconds.
		 */
		$period_units = apply_filters(
			'wordpoints_hooks_ui_data_period_units'
			, $period_units
		);

		return array(
			'period_units' => $period_units,
			'l10n'         => array(
				'label' => __( 'Trigger reaction no more than once in:', 'wordpoints' ),
			),
		);
	}

	/**
	 * Validate the periods.
	 *
	 * @since 2.1.0
	 *
	 * @param array $periods The periods.
	 *
	 * @return array The validated periods.
	 */
	protected function validate_action_type_settings( $periods ) {

		if ( ! is_array( $periods ) ) {

			$this->validator->add_error(
				__( 'Periods do not match expected format.', 'wordpoints' )
			);

			return array();
		}

		foreach ( $periods as $index => $period ) {

			$this->validator->push_field( $index );

			$period = $this->validate_period( $period );

			if ( $period ) {
				$periods[ $index ] = $period;
			}

			$this->validator->pop_field();
		}

		return $periods;
	}

	/**
	 * Validate the settings for a period.
	 *
	 * @since 2.1.0
	 *
	 * @param array $period The period.
	 *
	 * @return array|false The validated period, or false if invalid.
	 */
	protected function validate_period( $period ) {

		if ( ! is_array( $period ) ) {
			$this->validator->add_error(
				__( 'Period does not match expected format.', 'wordpoints' )
			);

			return false;
		}

		if ( isset( $period['args'] ) ) {
			$this->validate_period_args( $period['args'] );
		}

		if ( ! isset( $period['length'] ) ) {

			$this->validator->add_error(
				__( 'Period length setting is missing.', 'wordpoints' )
			);

		} elseif ( false === wordpoints_posint( $period['length'] ) ) {

			$this->validator->add_error(
				__( 'Period length must be a positive integer.', 'wordpoints' )
				, 'length'
			);

			return false;
		}

		return $period;
	}

	/**
	 * Validate the period args.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $args The args the period is related to.
	 */
	protected function validate_period_args( $args ) {

		if ( ! is_array( $args ) ) {

			$this->validator->add_error(
				__( 'Period does not match expected format.', 'wordpoints' )
				, 'args'
			);

			return;
		}

		$this->validator->push_field( 'args' );

		foreach ( $args as $index => $hierarchy ) {

			$this->validator->push_field( $index );

			if ( ! is_array( $hierarchy ) ) {

				$this->validator->add_error(
					__( 'Period does not match expected format.', 'wordpoints' )
				);

			} elseif ( ! $this->event_args->get_from_hierarchy( $hierarchy ) ) {

				$this->validator->add_error(
					__( 'Invalid arg hierarchy for period.', 'wordpoints' )
				);
			}

			$this->validator->pop_field();
		}

		$this->validator->pop_field();
	}

	/**
	 * @since 2.1.0
	 */
	public function should_hit( WordPoints_Hook_Fire $fire ) {

		$periods = $this->get_settings_from_fire( $fire );

		if ( empty( $periods ) ) {
			return true;
		}

		$this->event_args  = $fire->event_args;
		$this->action_type = $fire->action_type;

		foreach ( $periods as $period ) {
			if ( ! $this->has_period_ended( $period, $fire->reaction ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check whether a period has ended.
	 *
	 * @since 2.1.0
	 *
	 * @param array                     $settings The period's settings.
	 * @param WordPoints_Hook_ReactionI $reaction The reaction object.
	 *
	 * @return bool Whether the period has ended.
	 */
	protected function has_period_ended(
		array $settings,
		WordPoints_Hook_ReactionI $reaction
	) {

		$period = $this->get_period_by_reaction( $settings, $reaction );

		// If the period isn't found, we know that we can still fire.
		if ( ! $period ) {
			return true;
		}

		$now      = current_time( 'timestamp', true );
		$hit_time = strtotime( $period->date, $now );

		if ( ! empty( $settings['relative'] ) ) {

			return ( $now > $hit_time + $settings['length'] );

		} else {

			$offset = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;

			return (
				(int) ( ( $hit_time + $offset ) / $settings['length'] )
				< (int) ( ( $now + $offset ) / $settings['length'] )
			);
		}
	}

	/**
	 * Get the values of the args that a period relates to.
	 *
	 * @since 2.1.0
	 *
	 * @param array $period_args The args this period relates to.
	 *
	 * @return array The arg values.
	 */
	protected function get_arg_values( array $period_args ) {

		$values = array();

		foreach ( $period_args as $arg_hierarchy ) {

			$arg = $this->event_args->get_from_hierarchy(
				$arg_hierarchy
			);

			if ( ! $arg instanceof WordPoints_EntityishI ) {
				continue;
			}

			$values[ implode( '.', $arg_hierarchy ) ] = $arg->get_the_value();
		}

		ksort( $values );

		return $values;
	}

	/**
	 * Get a period from the database by ID.
	 *
	 * @since 2.1.0
	 *
	 * @param int $period_id The ID of a period.
	 *
	 * @return object|false The period data, or false if not found.
	 */
	protected function get_period( $period_id ) {

		$period = wp_cache_get( $period_id, 'wordpoints_hook_periods' );

		if ( ! $period ) {

			global $wpdb;

			$period = $wpdb->get_row(
				$wpdb->prepare(
					"
						SELECT *, `period`.`id` AS `id`
						FROM `{$wpdb->wordpoints_hook_periods}` AS `period`
						INNER JOIN `{$wpdb->wordpoints_hook_hits}` AS `hit`
							ON `hit`.`id` = `period`.`hit_id`
						WHERE `period`.`id` = %d
					"
					, $period_id
				)
			);

			if ( ! $period ) {
				return false;
			}

			wp_cache_set( $period->id, $period, 'wordpoints_hook_periods' );
		}

		return $period;
	}

	/**
	 * Get a period from the database by reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param array                     $settings The period's settings.
	 * @param WordPoints_Hook_ReactionI $reaction The reaction object.
	 *
	 * @return object|false The period data, or false if not found.
	 */
	protected function get_period_by_reaction(
		array $settings,
		WordPoints_Hook_ReactionI $reaction
	) {

		$signature     = $this->get_period_signature( $settings, $reaction );
		$reaction_guid = $reaction->get_guid();

		$cache_key = wp_json_encode( $reaction_guid ) . "-{$signature}-{$this->action_type}";

		// Before we run the query, we try to lookup the ID in the cache.
		$period_id = wp_cache_get( $cache_key, 'wordpoints_hook_period_ids_by_reaction', false, $found );

		// If we found it, we can retrieve the period by ID instead.
		if ( $period_id ) {
			return $this->get_period( $period_id );
		} elseif ( $found ) {
			// If the cache was set to false, then we have already checked if there
			// are any hits for this period, and haven't found any.
			return false;
		}

		global $wpdb;

		// Otherwise, we have to run this query.
		$period = $wpdb->get_row(
			$wpdb->prepare(
				"
					SELECT `period`.`id`, `hit`.`date`, `hit`.`id` AS `hit_id`
					FROM `{$wpdb->wordpoints_hook_periods}` AS `period`
					INNER JOIN `{$wpdb->wordpoints_hook_hits}` AS `hit`
						ON `hit`.`id` = period.`hit_id`
					WHERE `period`.`signature` = %s
						AND `hit`.`reaction_mode` = %s
						AND `hit`.`reaction_store` = %s
						AND `hit`.`reaction_context_id` = %s
						AND `hit`.`reaction_id` = %d
						AND `hit`.`action_type` = %s
					ORDER BY `hit`.`date` DESC
					LIMIT 1
				"
				, $signature
				, $reaction_guid['mode']
				, $reaction_guid['store']
				, wp_json_encode( $reaction_guid['context_id'] )
				, $reaction_guid['id']
				, $this->action_type
			)
		);

		if ( ! $period ) {

			// Cache the result anyway, so we know that no periods have been created
			// matching this yet, and can avoid re-running this query.
			wp_cache_set( $cache_key, false, 'wordpoints_hook_period_ids_by_reaction' );

			return false;
		}

		$period->signature           = $signature;
		$period->reaction_mode       = $reaction_guid['mode'];
		$period->reaction_store      = $reaction_guid['store'];
		$period->reaction_context_id = wp_json_encode( $reaction_guid['context_id'] );
		$period->reaction_id         = $reaction_guid['id'];
		$period->action_type         = $this->action_type;

		wp_cache_set( $cache_key, $period->id, 'wordpoints_hook_period_ids_by_reaction' );
		wp_cache_set( $period->id, $period, 'wordpoints_hook_periods' );

		return $period;
	}

	/**
	 * @since 2.1.0
	 */
	public function after_hit( WordPoints_Hook_Fire $fire ) {

		$periods = $this->get_settings_from_fire( $fire );

		if ( empty( $periods ) ) {
			return;
		}

		$this->event_args  = $fire->event_args;
		$this->action_type = $fire->action_type;

		foreach ( $periods as $settings ) {

			$this->add_period(
				$this->get_period_signature( $settings, $fire->reaction )
				, $fire
			);
		}
	}

	/**
	 * Get the signature for a period.
	 *
	 * The period signature is a hash value calculated based on the values of the
	 * event args to which that period is related. This is calculated as a hash so
	 * that it can be easily stored and queried at a fixed length.
	 *
	 * @since 2.1.0
	 *
	 * @param array                     $settings The period settings.
	 * @param WordPoints_Hook_ReactionI $reaction The reaction.
	 *
	 * @return string The period signature.
	 */
	protected function get_period_signature(
		array $settings,
		WordPoints_Hook_ReactionI $reaction
	) {

		if ( isset( $settings['args'] ) ) {
			$period_args = $settings['args'];
		} else {
			$period_args = array( $reaction->get_meta( 'target' ) );
		}

		return wordpoints_hash(
			wp_json_encode( $this->get_arg_values( $period_args ) )
		);
	}

	/**
	 * Add a period to the database.
	 *
	 * @since 2.1.0
	 *
	 * @param string               $signature The period signature.
	 * @param WordPoints_Hook_Fire $fire      The fire object.
	 *
	 * @return false|object The period data, or false if not found.
	 */
	protected function add_period( $signature, WordPoints_Hook_Fire $fire ) {

		global $wpdb;

		$inserted = $wpdb->insert(
			$wpdb->wordpoints_hook_periods
			, array(
				'hit_id'    => $fire->hit_id,
				'signature' => $signature,
			)
			, array( '%d', '%s' )
		);

		if ( ! $inserted ) {
			return false;
		}

		$period_id = $wpdb->insert_id;

		wp_cache_set(
			wp_json_encode( $fire->reaction->get_guid() ) . "-{$signature}-{$this->action_type}"
			, $period_id
			, 'wordpoints_hook_period_ids_by_reaction'
		);

		return $period_id;
	}
}

// EOF
