<?php

/**
 * Orphaned reactions 2.4.0 updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Deletes the reactions and points hooks that are for a nonexistent points type.
 *
 * There was a bug in prior versions of the code that deleted a points type that left
 * the reactions and hooks for the points type for individual sites when network
 * active on multisite.
 *
 * @since 2.4.0
 */
class WordPoints_Points_Updater_2_4_0_Reactions_Orphaned
	implements WordPoints_RoutineI {

	/**
	 * @since 2.4.0
	 */
	public function run() {

		$points_types = wordpoints_get_points_types();

		// Clean up reactions.
		$reaction_stores = wordpoints_hooks()->get_reaction_stores( 'points' );

		foreach ( $reaction_stores as $reaction_store ) {

			$reactions = $reaction_store->get_reactions();

			foreach ( $reactions as $reaction ) {

				$points_type = $reaction->get_meta( 'points_type' );

				if ( empty( $points_type ) ) {
					continue;
				}

				if ( ! isset( $points_types[ $points_type ] ) ) {
					$reaction_store->delete_reaction( $reaction->get_id() );
				}
			}
		}

		// Clean up legacy points hooks.
		$points_types_hooks = WordPoints_Points_Hooks::get_points_types_hooks();
		$points_types_hooks = array_intersect_key(
			$points_types_hooks
			, $points_types
		);

		WordPoints_Points_Hooks::save_points_types_hooks( $points_types_hooks );
	}
}

// EOF
