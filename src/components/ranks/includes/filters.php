<?php

/**
 * Action and filter hooks of the ranks component.
 *
 * @package WordPoints\Ranks
 * @since 2.1.0
 */

add_action( 'wordpoints_extensions_loaded', 'WordPoints_Rank_Types::init' );

add_action( 'wordpoints_ranks_register', 'wordpoints_register_core_ranks' );

add_action( 'user_register', 'wordpoints_set_new_user_ranks' );
add_action( 'add_user_to_blog', 'wordpoints_set_new_user_ranks' );

WordPoints_Shortcodes::register( 'wordpoints_user_rank', 'WordPoints_Rank_Shortcode_User_Rank' );

// EOF
