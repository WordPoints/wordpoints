<?php

/**
 * Register the rank types.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

/**
 * The base rank type class.
 *
 * @since 1.7.0
 */
include_once( WORDPOINTS_DIR . '/components/ranks/includes/rank-types/base.php' );

if ( wordpoints_component_is_active( 'points' ) ) {
	/**
	 * The points rank type.
	 *
	 * @since 1.7.0
	 */
	include_once( WORDPOINTS_DIR . '/components/ranks/includes/rank-types/points.php' );
}

/**
 * Register the included rank types.
 *
 * @since 1.7.0
 *
 * @WordPress\action wordpoints_ranks_register
 */
function wordpoints_register_core_ranks() {

	if ( wordpoints_component_is_active( 'points' ) ) {

		foreach ( wordpoints_get_points_types() as $slug => $points_type ) {

			WordPoints_Rank_Groups::register_group(
				"points_type-{$slug}"
				, array(
					'name' => $points_type['name'],
					'description' => sprintf(
						__(
							'This rank group is associated with the &#8220;%s&#8221; points type.'
							, 'wordpoints'
						)
						, $points_type['name']
					)
				)
			);

			WordPoints_Rank_Types::register_type(
				"points-{$slug}"
				, 'WordPoints_Points_Rank_Type'
				, array( 'points_type' => $slug )
			);

			WordPoints_Rank_Groups::register_type_for_group(
				"points-{$slug}",
				"points_type-{$slug}"
			);
		}
	}
}
add_action( 'wordpoints_ranks_register', 'wordpoints_register_core_ranks' );

// EOF
