<?php

/**
 * Action and filter hooks for the points component.
 *
 * @package WordPoints\Points
 * @since 2.1.0
 */

add_action( 'wordpoints_init_app_registry-hooks-reactors', 'wordpoints_points_hook_reactors_init' );
add_action( 'wordpoints_init_app_registry-hooks-reaction_stores', 'wordpoints_points_hook_reaction_stores_init' );
add_action( 'wordpoints_init_app_registry-hooks-extensions', 'wordpoints_points_hook_extensions_init' );

add_action( 'wp_enqueue_scripts', 'wordpoints_points_register_scripts', 5 );
add_action( 'admin_enqueue_scripts', 'wordpoints_points_register_scripts', 5 );

add_filter( 'wordpoints_format_points', 'wordpoints_format_points_filter', 5, 3 );

add_action( 'deleted_user', 'wordpoints_delete_points_logs_for_user' );
add_action( 'delete_blog', 'wordpoints_delete_points_logs_for_blog' );

add_action( 'wordpoints_points_type_form_top', 'wordpoints_points_settings_custom_meta_key_message' );
add_action( 'wordpoints_admin_points_logs_tab', 'wordpoints_points_logs_custom_meta_key_message' );

add_action( 'init', 'wordpoints_points_add_global_cache_groups', 5 );

add_action( 'wordpoints_register_points_logs_queries', 'wordpoints_register_default_points_logs_queries' );

add_action( 'wordpoints_points_log-profile_edit', 'wordpoints_points_logs_profile_edit', 10, 6 );
add_action( 'wordpoints_points_log-comment_disapprove', 'wordpoints_points_logs_comment_disapprove', 10, 6 );
add_action( 'wordpoints_points_log-post_delete', 'wordpoints_points_logs_post_delete', 10, 6 );

add_action( 'wordpoints_points_altered', 'wordpoints_clean_points_logs_cache', 10, 3 );
add_action( 'wordpoints_points_altered', 'wordpoints_clean_points_top_users_cache', 10, 3 );

add_action( 'user_register', 'wordpoints_clean_points_top_users_cache_user_register' );

add_action( 'wordpoints_modules_loaded', 'WordPoints_Points_Hooks::initialize_hooks' );

add_action( 'widgets_init', 'wordpoints_register_points_widgets' );

add_action( 'wordpoints_points_hooks_register', 'wordpoints_register_points_hooks' );

if ( ! is_multisite() || is_wordpoints_network_active() ) {
	add_action( 'deleted_user', 'wordpoints_clean_points_top_users_cache_user_deleted' );
} else {
	add_action( 'remove_user_from_blog', 'wordpoints_clean_points_top_users_cache_user_deleted' );
}

add_filter( 'wordpoints_user_can_view_points_log', 'wordpoints_hooks_user_can_view_points_log' );

// EOF
