<?php

/**
 * Hook hit logger class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Logs hook hits.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Hit_Logger {

	/**
	 * The fire for which a hit might occur.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_Fire
	 */
	protected $fire;

	/**
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_Fire $fire The fire that might be logged as a hit.
	 */
	public function __construct( WordPoints_Hook_Fire $fire ) {

		$this->fire = $fire;
	}

	/**
	 * Logs a hit for this fire.
	 *
	 * @since 2.1.0
	 *
	 * @return int|false The hit ID, or false if logging the hit failed.
	 */
	public function log_hit() {

		global $wpdb;

		$signature = wordpoints_hooks_get_event_primary_arg_guid_json( $this->fire->event_args );

		$inserted = $wpdb->insert(
			$wpdb->wordpoints_hook_hits
			, array(
				'action_type' => $this->fire->action_type,
				'primary_arg_guid' => $signature,
				'event' => $this->fire->reaction->get_event_slug(),
				'reactor' => $this->fire->reaction->get_reactor_slug(),
				'reaction_mode' => $this->fire->reaction->get_mode_slug(),
				'reaction_store' => $this->fire->reaction->get_store_slug(),
				'reaction_context_id' => wp_json_encode( $this->fire->reaction->get_context_id() ),
				'reaction_id' => $this->fire->reaction->get_id(),
				'date' => current_time( 'mysql' ),
			)
		);

		if ( ! $inserted ) {
			return false;
		}

		$hit_id = $wpdb->insert_id;

		return $hit_id;
	}
}

// EOF
