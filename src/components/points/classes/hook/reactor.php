<?php

/**
 * Points hook reactor class.
 *
 * @package WordPoints\Points
 * @since 2.1.0
 */

/**
 * Hook reactor to award user points.
 *
 * @since 2.1.0
 */
class WordPoints_Points_Hook_Reactor
	extends WordPoints_Hook_Reactor
	implements WordPoints_Hook_Reactor_Target_ValidatorI {

	/**
	 * @since 2.1.0
	 */
	protected $slug = 'points';

	/**
	 * @since 2.1.0
	 */
	protected $arg_types = 'user';

	/**
	 * @since 2.1.0
	 */
	protected $action_types = array( 'fire', 'toggle_on', 'toggle_off' );

	/**
	 * @since 2.1.0
	 */
	protected $settings_fields = array(
		'description' => array(
			'type'     => 'text',
			'required' => true,
		),
		'log_text'    => array(
			'type'     => 'text',
			'required' => true,
		),
		'points'      => array(
			'default'  => 0,
			'type'     => 'number',
			'required' => true,
		),
		'points_type' => array(
			'default'  => '',
			'type'     => 'hidden',
			'required' => true,
		),
	);

	/**
	 * @since 2.1.0
	 */
	public function get_settings_fields() {

		$this->settings_fields['points']['label']      = _x( 'Points', 'form label', 'wordpoints' );
		$this->settings_fields['log_text']['label']    = _x( 'Log Text', 'form label', 'wordpoints' );
		$this->settings_fields['description']['label'] = _x( 'Description', 'form label', 'wordpoints' );

		return parent::get_settings_fields();
	}

	/**
	 * @since 2.1.0
	 */
	public function get_ui_script_data() {

		$data = parent::get_ui_script_data();

		$data['target_label']  = __( 'Award To', 'wordpoints' );
		$data['periods_label'] = __( 'Award each user no more than once in:', 'wordpoints' );

		return $data;
	}

	/**
	 * @since 2.1.0
	 */
	public function validate_settings(
		array $settings,
		WordPoints_Hook_Reaction_Validator $validator,
		WordPoints_Hook_Event_Args $event_args
	) {

		if ( ! isset( $settings['points'] ) || false === wordpoints_int( $settings['points'] ) ) {
			$validator->add_error( __( 'Points must be an integer.', 'wordpoints' ), 'points' );
		}

		if ( ! isset( $settings['points_type'] ) || ! wordpoints_is_points_type( $settings['points_type'] ) ) {
			$validator->add_error( __( 'Invalid points type.', 'wordpoints' ), 'points_type' );
		}

		if ( ! isset( $settings['description'] ) ) {
			$validator->add_error( __( 'Description is required.', 'wordpoints' ), 'description' );
		}

		if ( ! isset( $settings['log_text'] ) ) {
			$validator->add_error( __( 'Log Text is required.', 'wordpoints' ), 'log_text' );
		}

		return parent::validate_settings( $settings, $validator, $event_args );
	}

	/**
	 * @since 2.1.0
	 */
	public function update_settings(
		WordPoints_Hook_ReactionI $reaction,
		array $settings
	) {

		parent::update_settings( $reaction, $settings );

		$reaction->update_meta( 'points', $settings['points'] );
		$reaction->update_meta( 'points_type', $settings['points_type'] );
		$reaction->update_meta( 'description', $settings['description'] );
		$reaction->update_meta( 'log_text', $settings['log_text'] );
	}

	/**
	 * @since 2.4.2
	 */
	public function can_hit(
		WordPoints_EntityishI $target,
		WordPoints_Hook_Fire $fire
	) {

		if ( 'toggle_off' === $fire->action_type ) {
			return true;
		}

		if ( ! $target instanceof WordPoints_Entity || ! $target->get_the_id() ) {
			return false;
		}

		return true;
	}

	/**
	 * @since 2.1.0
	 */
	public function hit( WordPoints_Hook_Fire $fire ) {

		if ( 'toggle_off' === $fire->action_type ) {
			$this->reverse_hit( $fire );
			return;
		}

		$reaction = $fire->reaction;

		$target = $fire->event_args->get_from_hierarchy(
			$reaction->get_meta( 'target' )
		);

		if ( ! $target instanceof WordPoints_Entity ) {
			return;
		}

		$meta = array( 'hook_hit_id' => $fire->hit_id );

		foreach ( $fire->event_args->get_entities() as $entity ) {
			$meta[ $entity->get_slug() ]           = $entity->get_the_id();
			$meta[ $entity->get_slug() . '_guid' ] = $entity->get_the_guid();
		}

		wordpoints_alter_points(
			$target->get_the_id()
			, $this->get_points_to_award( $fire )
			, $reaction->get_meta( 'points_type' )
			, $reaction->get_event_slug()
			, $meta
			, $reaction->get_meta( 'log_text' )
		);
	}

	/**
	 * @since 2.1.0
	 */
	public function reverse_hit( WordPoints_Hook_Fire $fire ) {

		$hit_ids = $this->get_hit_ids_to_be_reversed( $fire );

		if ( empty( $hit_ids ) ) {
			return;
		}

		$query = new WordPoints_Points_Logs_Query(
			array(
				'meta_query' => array(
					array(
						'key'     => 'hook_hit_id',
						'value'   => $hit_ids,
						'compare' => 'IN',
					),
				),
			)
		);

		$logs = $query->get();

		if ( ! $logs ) {
			return;
		}

		$this->reverse_logs( $logs, $fire );
	}

	/**
	 * Get the IDs of the hits to be reversed for a fire.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_Fire $fire The fire object.
	 *
	 * @return array The IDs of the hits to be reversed.
	 */
	protected function get_hit_ids_to_be_reversed( WordPoints_Hook_Fire $fire ) {

		// We closely integrate with the reversals extension to get the hit IDs.
		if ( ! isset( $fire->data['reversals']['hit_ids'] ) ) {
			return array();
		}

		return $fire->data['reversals']['hit_ids'];
	}

	/**
	 * Reverse some points logs.
	 *
	 * @since 2.1.0
	 *
	 * @param object[]             $logs The logs to reverse.
	 * @param WordPoints_Hook_Fire $fire The fire object.
	 */
	protected function reverse_logs( $logs, WordPoints_Hook_Fire $fire ) {

		$event = wordpoints_hooks()->get_sub_app( 'events' )->get(
			$fire->reaction->get_event_slug()
		);

		if ( $event instanceof WordPoints_Hook_Event_ReversingI ) {

			// translators: 1. Log text for reversed transaction; 2. Reason for reversal.
			$template = __( 'Reversed &#8220;%1$s&#8221; (%2$s)', 'wordpoints' );

			$event_description = $event->get_reversal_text();

		} else {

			// translators: 1. Log text for reversed transaction.
			$template = __( 'Reversed &#8220;%1$s&#8221;', 'wordpoints' );

			$event_description = '';
		}

		foreach ( $logs as $log ) {

			$log_id = wordpoints_alter_points(
				$log->user_id
				, -$log->points
				, $log->points_type
				, "reverse-{$log->log_type}"
				, array(
					'original_log_id' => $log->id,
					'hook_hit_id'     => $fire->hit_id,
				)
				, sprintf( $template, $log->text, $event_description )
			);

			// Mark the old log as reversed by this one.
			wordpoints_update_points_log_meta( $log->id, 'auto_reversed', $log_id );
		}
	}

	/**
	 * Get the number of points to award for a hook fire.
	 *
	 * @since 2.3.0
	 *
	 * @param WordPoints_Hook_Fire $fire The fire object.
	 *
	 * @return int The number of points to award.
	 */
	protected function get_points_to_award( WordPoints_Hook_Fire $fire ) {

		$points = $fire->reaction->get_meta( 'points' );

		/**
		 * Filters the number of points to award in the Points hook reactor.
		 *
		 * @since 2.3.0
		 *
		 * @param int                  $points The number of points.
		 * @param WordPoints_Hook_Fire $fire   The hook fire object.
		 */
		return (int) apply_filters(
			'wordpoints_points_hook_reactor_points_to_award'
			, $points
			, $fire
		);
	}
}

// EOF
