<?php

/**
 * Admin-side action and filter hooks of the points component.
 *
 * @package WordPoints\Points
 * @since 2.1.0
 */

add_action( 'init', 'wordpoints_points_admin_register_scripts' );

add_action( 'admin_menu', 'wordpoints_points_admin_menu' );

if ( is_wordpoints_network_active() ) {
	add_action( 'network_admin_menu', 'wordpoints_points_admin_menu' );
}

add_action( 'load-wordpoints_page_wordpoints_points_hooks', 'wordpoints_admin_points_hooks_help' );

add_action( 'load-wordpoints_page_wordpoints_points_hooks', 'wordpoints_no_js_points_hooks_save' );

add_action( 'screen_settings', 'wordpoints_admin_points_hooks_screen_options', 10, 2 );

add_action( 'wordpoints_in_points_hook_form', 'wordpoints_points_hook_description_form', 10, 3 );

add_action( 'personal_options', 'wordpoints_points_profile_options', 20 );

add_action( 'personal_options_update', 'wordpoints_points_profile_options_update' );
add_action( 'edit_user_profile_update', 'wordpoints_points_profile_options_update' );

add_action( 'wordpoints_admin_settings_top', 'wordpoints_points_admin_settings' );

add_action( 'wordpoints_admin_settings_update', 'wordpoints_points_admin_settings_save' );

add_action( 'admin_notices', 'wordpoints_points_admin_notices' );

add_action( 'wp_ajax_wordpoints-points-hooks-order', 'wordpoints_ajax_points_hooks_order' );
add_action( 'wp_ajax_save-wordpoints-points-hook', 'wordpoints_ajax_save_points_hook' );

// EOF
