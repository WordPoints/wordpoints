<?php

/**
 * Action and filter hooks for the points component.
 *
 * @package WordPoints\Points
 * @since 2.1.0
 */

add_action( 'wordpoints_init_app-components', 'wordpoints_points_components_app_init' );

add_action( 'wordpoints_init_app-components-points', 'wordpoints_points_apps_init' );
add_action( 'wordpoints_init_app-components-points-logs', 'wordpoints_points_logs_apps_init' );

add_action( 'wordpoints_init_app_registry-components-points-logs-views', 'wordpoints_points_logs_views_init' );
add_action( 'wordpoints_init_app_registry-components-points-logs-viewing_restrictions', 'wordpoints_points_logs_viewing_restrictions_init' );

add_action( 'wordpoints_init_app_registry-hooks-reactors', 'wordpoints_points_hook_reactors_init' );
add_action( 'wordpoints_init_app_registry-hooks-reaction_stores', 'wordpoints_points_hook_reaction_stores_init' );
add_action( 'wordpoints_init_app_registry-hooks-extensions', 'wordpoints_points_hook_extensions_init' );

if ( get_option( 'wordpoints_points_register_legacy_post_publish_event' ) ) {
	add_action( 'wordpoints_register_post_type_hook_events', 'wordpoints_points_register_legacy_post_publish_events' );
}

add_filter( 'wordpoints_htgp_shortcode_reaction_points', 'wordpoints_points_htgp_shortcode_hide_disabled_reactions', 10, 2 );

add_action( 'wp_enqueue_scripts', 'wordpoints_points_register_scripts', 5 );
add_action( 'admin_enqueue_scripts', 'wordpoints_points_register_scripts', 5 );

add_filter( 'wordpoints_format_points', 'wordpoints_format_points_filter', 5, 3 );

add_action( 'deleted_user', 'wordpoints_delete_points_logs_for_user' );
add_action( 'delete_blog', 'wordpoints_delete_points_logs_for_blog' );

add_action( 'wordpoints_points_type_form_top', 'wordpoints_points_settings_custom_meta_key_message' );
add_action( 'wordpoints_admin_points_logs_tab', 'wordpoints_points_logs_custom_meta_key_message' );

add_action( 'init', 'wordpoints_points_add_global_cache_groups', 5 );

add_action( 'wordpoints_register_points_logs_queries', 'wordpoints_register_default_points_logs_queries' );

add_filter( 'wordpoints_points_log-profile_edit', 'wordpoints_points_logs_profile_edit', 10, 6 );
add_filter( 'wordpoints_points_log-comment_disapprove', 'wordpoints_points_logs_comment_disapprove', 10, 6 );
add_filter( 'wordpoints_points_log-post_delete', 'wordpoints_points_logs_post_delete', 10, 6 );

add_action( 'wordpoints_points_altered', 'wordpoints_clean_points_logs_cache', 10, 3 );
add_action( 'wordpoints_points_altered', 'wordpoints_clean_points_top_users_cache', 10, 3 );

add_action( 'user_register', 'wordpoints_clean_points_top_users_cache_user_register' );

add_action( 'wordpoints_extensions_loaded', 'WordPoints_Points_Hooks::initialize_hooks' );

add_action( 'widgets_init', 'wordpoints_register_points_widgets' );

add_action( 'wordpoints_points_hooks_register', 'wordpoints_register_points_hooks' );

if ( ! is_multisite() || is_wordpoints_network_active() ) {
	add_action( 'deleted_user', 'wordpoints_clean_points_top_users_cache_user_deleted' );
} else {
	add_action( 'remove_user_from_blog', 'wordpoints_clean_points_top_users_cache_user_deleted' );
}

WordPoints_Shortcodes::register( 'wordpoints_points_top', 'WordPoints_Points_Shortcode_Top_Users' );
WordPoints_Shortcodes::register( 'wordpoints_points_logs', 'WordPoints_Points_Shortcode_Logs' );
WordPoints_Shortcodes::register( 'wordpoints_points', 'WordPoints_Points_Shortcode_User_Points' );
WordPoints_Shortcodes::register( 'wordpoints_how_to_get_points', 'WordPoints_Points_Shortcode_HTGP' );

// EOF
