<?php

/**
 * Legacy points reversals hook extension class.
 *
 * @package WordPoints\Points
 * @since 2.1.0
 */

/**
 * Makes actions of one type behave as reversals of actions of another type.
 *
 * @since 2.1.0
 */
class WordPoints_Points_Hook_Extension_Legacy_Reversals
	extends WordPoints_Hook_Extension_Reversals {

	/**
	 * @since 2.1.0
	 */
	protected $slug = 'points_legacy_reversals';

	/**
	 * @since 2.1.0
	 */
	public function should_hit( WordPoints_Hook_Fire $fire ) {

		// Normally we wouldn't let the hit occur if there is nothing to reverse.
		if ( ! parent::should_hit( $fire ) ) {

			// But in this case we want to check the points logs as well.
			$logs = $this->get_points_logs_to_be_reversed( $fire );

			// If there are logs to reverse, we'll let the hit happen.
			return count( $logs ) > 0;
		}

		return true;
	}

	/**
	 * @since 2.1.0
	 */
	public function after_miss( WordPoints_Hook_Fire $fire ) {

		parent::after_miss( $fire );

		if ( ! $this->get_settings_from_fire( $fire ) ) {
			return;
		}

		foreach ( $this->get_points_logs_to_be_reversed( $fire ) as $log ) {
			wordpoints_add_points_log_meta( $log->id, 'auto_reversed', 0 );
		}
	}

	/**
	 * Get a list of points logs to be reversed by a fire.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_Fire $fire The fire object.
	 *
	 * @return array The points logs to be reversed.
	 */
	protected function get_points_logs_to_be_reversed( WordPoints_Hook_Fire $fire ) {

		if ( isset( $fire->data[ $this->slug ]['points_logs'] ) ) {
			return $fire->data[ $this->slug ]['points_logs'];
		}

		$meta_queries = array(
			array(
				// This is needed for back-compat with the way the points hooks
				// reversed transactions, so we don't re-reverse them.
				'key'     => 'auto_reversed',
				'compare' => 'NOT EXISTS',
			),
		);

		$meta_key = $fire->reaction->get_meta( 'legacy_meta_key' );

		if ( $meta_key ) {

			$entities = $fire->event_args->get_signature_args();

			if ( ! $entities ) {
				$fire->data[ $this->slug ]['points_logs'] = array();
				return array();
			}

			// Legacy hooks only ever related to a single entity.
			$entity = reset( $entities );

			$meta_queries[] = array(
				'key'   => $meta_key,
				'value' => $entity->get_the_id(),
			);
		}

		$log_type = $fire->reaction->get_meta( 'legacy_log_type' );

		if ( ! $log_type ) {
			$log_type = $fire->reaction->get_event_slug();
		}

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => $log_type,
				'meta_query' => $meta_queries,
			)
		);

		$logs = $query->get();

		if ( ! $logs ) {
			$logs = array();
		}

		$fire->data[ $this->slug ]['points_logs'] = $logs;

		return $logs;
	}
}

// EOF
