<?php

/**
 * Action and filter hooks of the ranks component.
 *
 * @package WordPoints\Ranks
 * @since 2.1.0
 */

add_action( 'wordpoints_modules_loaded', 'WordPoints_Rank_Types::init' );

add_action( 'wordpoints_ranks_register', 'wordpoints_register_core_ranks' );

// EOF
