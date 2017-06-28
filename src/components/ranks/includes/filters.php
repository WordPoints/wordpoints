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

if ( is_multisite() ) {
	add_action( 'remove_user_from_blog', 'wordpoints_delete_user_ranks' );
} else {
	add_action( 'deleted_user', 'wordpoints_delete_user_ranks' );
}

WordPoints_Shortcodes::register( 'wordpoints_user_rank', 'WordPoints_Rank_Shortcode_User_Rank' );
WordPoints_Shortcodes::register( 'wordpoints_rank_list', 'WordPoints_Rank_Shortcode_Rank_List' );

// EOF
