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

/**
 * Register the included rank types.
 *
 * @since 1.7.0
 *
 * @WordPress\action wordpoints_ranks_register
 */
function wordpoints_register_core_ranks() {}
add_action( 'wordpoints_ranks_register', 'wordpoints_register_core_ranks' );

// EOF
