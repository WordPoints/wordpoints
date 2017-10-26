<?php

/**
 * Legacy periods hook extension.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Limits the number of times that targets can be hit in a given time period.
 *
 * Provides legacy support for imported points hooks by falling back on the points
 * logs.
 *
 * @since 2.1.0
 */
class WordPoints_Points_Hook_Extension_Legacy_Periods
	extends WordPoints_Hook_Extension_Periods {

	/**
	 * @since 2.1.0
	 */
	protected $slug = 'points_legacy_periods';

	/**
	 * @since 2.1.0
	 */
	protected function get_period_by_reaction(
		array $settings,
		WordPoints_Hook_ReactionI $reaction
	) {

		$period = parent::get_period_by_reaction( $settings, $reaction );

		if ( $period ) {
			return $period;
		}

		$expected_settings = array( array( 'current:user' ) );

		if ( ! isset( $settings['args'] ) || $settings['args'] !== $expected_settings ) {
			return false;
		}

		// Get the user ID.
		$user = $this->event_args->get_from_hierarchy( array( 'current:user' ) );

		if ( ! $user instanceof WordPoints_Entity ) {
			return false;
		}

		$user_id       = $user->get_the_id();
		$reaction_guid = $reaction->get_guid();
		$points_type   = $reaction->get_meta( 'points_type' );
		$log_type      = $reaction->get_meta( 'legacy_log_type' );

		$cache_key = wp_json_encode( $reaction_guid ) . "-{$user_id}-{$points_type}-{$log_type}";

		// Before we run the query, we try to lookup the period in the cache.
		$period = wp_cache_get( $cache_key, 'wordpoints_points_legacy_hook_periods' );

		if ( $period ) {
			return $period;
		}

		$query = new WordPoints_Points_Logs_Query(
			array(
				'fields'      => 'date',
				'user_id'     => $user_id,
				'points_type' => $points_type,
				'log_type'    => $log_type,
				'limit'       => 1,
			)
		);

		$date = $query->get( 'var' );

		if ( ! $date ) {
			return false;
		}

		$period = (object) array( 'date' => $date );

		wp_cache_set( $cache_key, $period, 'wordpoints_points_legacy_hook_periods' );

		return $period;
	}
}

// EOF
