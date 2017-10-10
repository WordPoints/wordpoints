<?php

/**
 * Hook fire class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Holds the data for a hook fire.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Fire {

	/**
	 * The type of action being fired.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	public $action_type;

	/**
	 * The args for the event that is being fired.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_Event_Args
	 */
	public $event_args;

	/**
	 * The reaction that is being fired at.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_ReactionI
	 */
	public $reaction;

	/**
	 * The ID of the hit (if this fire has hit).
	 *
	 * @since 2.1.0
	 *
	 * @var int|false
	 */
	public $hit_id = false;

	/**
	 * Other data for this fire.
	 *
	 * This is unused by default, but it may be used by extensions as needed. It is
	 * highly recommended that extensions store their data in a sub-array under a key
	 * matching the extension slug.
	 *
	 * @since 2.1.0
	 *
	 * @var array
	 */
	public $data = array();

	/**
	 * @param WordPoints_Hook_Event_Args $event_args  The event args.
	 * @param WordPoints_Hook_ReactionI  $reaction    The reaction.
	 * @param string                     $action_type The type of action.
	 */
	public function __construct(
		WordPoints_Hook_Event_Args $event_args,
		WordPoints_Hook_ReactionI $reaction,
		$action_type
	) {

		$this->action_type = $action_type;
		$this->event_args  = $event_args;
		$this->reaction    = $reaction;
	}

	/**
	 * Make this fire a hit.
	 *
	 * @since 2.1.0
	 *
	 * @return int|false The ID of the hit, or false if it failed to be logged.
	 */
	public function hit() {

		if ( ! $this->hit_id ) {

			$this->hit_id = $this->log_hit();

			if ( ! $this->hit_id ) {
				return false;
			}
		}

		return $this->hit_id;
	}

	/**
	 * Logs a hit for this fire.
	 *
	 * @since 2.1.0
	 *
	 * @return int|false The hit ID, or false if logging the hit failed.
	 */
	protected function log_hit() {

		global $wpdb;

		$signature = wordpoints_hooks_get_event_signature_arg_guids_json(
			$this->event_args
		);

		$inserted = $wpdb->insert(
			$wpdb->wordpoints_hook_hits
			, array(
				'action_type'         => $this->action_type,
				'signature_arg_guids' => $signature,
				'event'               => $this->reaction->get_event_slug(),
				'reactor'             => $this->reaction->get_reactor_slug(),
				'reaction_mode'       => $this->reaction->get_mode_slug(),
				'reaction_store'      => $this->reaction->get_store_slug(),
				'reaction_context_id' => wp_json_encode(
					$this->reaction->get_context_id()
				),
				'reaction_id'         => $this->reaction->get_id(),
				'date'                => current_time( 'mysql', true ),
			)
		);

		if ( ! $inserted ) {
			return false;
		}

		$hit_id = $wpdb->insert_id;

		return $hit_id;
	}

	/**
	 * Get a query for hits in the database matching the current fire.
	 *
	 * If you don't need to query based on all of the args, you can use {@see
	 * WordPoints_Hook_Hit_Query::set_args()} to override them.
	 *
	 * Example:
	 *
	 * ```php
	 * $query->set_args( array( 'reaction_id' => null ) );
	 * ```
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_Hook_Hit_Query A query pre-populated with args matching the
	 *                                   current fire.
	 */
	public function get_matching_hits_query() {

		return new WordPoints_Hook_Hit_Query(
			array(
				'action_type'         => $this->action_type,
				'signature_arg_guids' => wordpoints_hooks_get_event_signature_arg_guids_json(
					$this->event_args
				),
				'event'               => $this->reaction->get_event_slug(),
				'reactor'             => $this->reaction->get_reactor_slug(),
				'reaction_store'      => $this->reaction->get_store_slug(),
				'reaction_context_id' => wp_json_encode(
					$this->reaction->get_context_id()
				),
				'reaction_id'         => $this->reaction->get_id(),
			)
		);
	}
}

// EOF
