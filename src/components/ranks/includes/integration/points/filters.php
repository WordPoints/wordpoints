<?php

/**
 * Ranks component actions and filters to integrate with the points component.
 *
 * @package WordPoints\Ranks
 * @since 2.1.0
 */

add_filter( 'wordpoints_points_widget_text', 'wordpoints_ranks_points_widget_text_filter', 30, 2 );
add_action( 'wordpoints_my_points_widget_below_text_field', 'wordpoints_ranks_my_points_widget_below_text_field' );

add_filter( 'wordpoints_points_top_users_username', 'wordpoints_ranks_points_top_users_username_filter', 10, 4 );

add_filter( 'shortcode_atts_wordpoints_user_rank', 'wordpoints_user_rank_shortcode_points_type_attr', 10, 3 );
add_filter( 'shortcode_atts_wordpoints_rank_list', 'wordpoints_user_rank_shortcode_points_type_attr', 10, 3 );

add_action( 'wordpoints_ranks_register', 'wordpoints_register_points_ranks' );

add_action( 'add_meta_boxes', 'wordpoints_ranks_add_points_types_meta_box_ranks', 20 );

// EOF
