<?php

/**
 * Reversals hook extension class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Makes actions of one type behave as reversals of actions of another type.
 *
 * Use this if your reactor has multiple responses, and the one tied to one action
 * type should only fire if the last fire of the action type tied to another response
 * produced a hit. In other words, you only want the last fire reversed if it was a
 * hit; you only want the last hit reversed if it was from the last fire. This will
 * prevent you from reversing a hit if reversal of it has already reverse-fired and
 * the fire was blocked for some reason.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Extension_Reversals
	extends WordPoints_Hook_Extension
	implements WordPoints_Hook_Extension_Hit_ListenerI,
		WordPoints_Hook_Extension_Miss_ListenerI {

	/**
	 * @since 2.1.0
	 */
	protected $slug = 'reversals';

	/**
	 * @since 2.1.0
	 */
	public function should_hit( WordPoints_Hook_Fire $fire ) {

		if ( ! $this->get_settings_from_fire( $fire ) ) {
			return true;
		}

		$ids = $this->get_hits_to_be_reversed( $fire );

		return count( $ids ) > 0;
	}

	/**
	 * @since 2.1.0
	 */
	public function after_hit( WordPoints_Hook_Fire $fire ) {

		if ( ! $this->get_settings_from_fire( $fire ) ) {
			return;
		}

		foreach ( $this->get_hits_to_be_reversed( $fire ) as $id ) {
			add_metadata( 'wordpoints_hook_hit', $id, 'reverse_fired', true );
		}
	}

	/**
	 * @since 2.1.0
	 */
	public function after_miss( WordPoints_Hook_Fire $fire ) {
		$this->after_hit( $fire );
	}

	/**
	 * Get the IDs of the hits that should be reversed by this fire.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_Fire $fire The fire object.
	 *
	 * @return array The IDs the hits to be reversed.
	 */
	protected function get_hits_to_be_reversed( WordPoints_Hook_Fire $fire ) {

		// We cache these so that we don't run the query both before and after the
		// fire.
		if ( isset( $fire->data[ $this->slug ]['hit_ids'] ) ) {
			return $fire->data[ $this->slug ]['hit_ids'];
		}

		$query = $fire->get_matching_hits_query();

		$query->set_args(
			array(
				'fields'       => 'id',
				'action_type'  => $this->get_settings_from_fire( $fire ),
				'meta_key'     => 'reverse_fired',
				'meta_compare' => 'NOT EXISTS',
			)
		);

		$ids = $query->get( 'col' );

		if ( ! $ids ) {
			$ids = array();
		}

		$fire->data[ $this->slug ]['hit_ids'] = $ids;

		return $ids;
	}
}

// EOF
