<?php

/**
 * Legacy repeat blocker hook extension class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Blocks identical fires from hitting twice for a single reaction.
 *
 * Extends the base repeat blocker to provide legacy support for reactions that are
 * imported from the old points hooks, and so need to also block based on the points
 * logs and not just the hook logs.
 *
 * @since 2.1.0
 */
class WordPoints_Points_Hook_Extension_Legacy_Repeat_Blocker
	extends WordPoints_Hook_Extension_Repeat_Blocker {

	/**
	 * @since 2.1.0
	 */
	protected $slug = 'points_legacy_repeat_blocker';

	/**
	 * @since 2.1.0
	 */
	protected function validate_action_type_settings( $settings ) {
		return (bool) $settings;
	}

	/**
	 * @since 2.1.0
	 */
	public function should_hit( WordPoints_Hook_Fire $fire ) {

		$block_repeats = (bool) $this->get_settings_from_fire( $fire );

		if ( ! $block_repeats ) {
			return true;
		}

		if ( ! parent::should_hit( $fire ) ) {
			return false;
		}

		$meta_queries = array();

		$meta_key = $fire->reaction->get_meta( 'legacy_meta_key' );

		if ( $meta_key ) {

			$entities = $fire->event_args->get_signature_args();

			if ( ! $entities ) {
				return true;
			}

			// Legacy hooks only ever related to a single entity.
			$entity = reset( $entities );

			$meta_queries = array(
				array(
					'key'   => $meta_key,
					'value' => $entity->get_the_id(),
				),
			);
		}

		$user = $fire->event_args->get_from_hierarchy(
			$fire->reaction->get_meta( 'target' )
		);

		if ( ! $user instanceof WordPoints_Entity ) {
			return true;
		}

		$log_type = $fire->reaction->get_meta( 'legacy_log_type' );

		if ( ! $log_type ) {
			$log_type = $fire->reaction->get_event_slug();
		}

		$query = new WordPoints_Points_Logs_Query(
			array(
				'user_id'     => $user->get_the_id(),
				'log_type'    => $log_type,
				'points_type' => $fire->reaction->get_meta( 'points_type' ),
				'meta_query'  => $meta_queries,
			)
		);

		return $query->count() === 0;
	}
}

// EOF
