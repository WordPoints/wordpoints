<?php

/**
 * Action and filter hooks.
 *
 * @package WordPoints
 * @since 2.1.0
 */

register_activation_hook( WORDPOINTS_DIR . 'wordpoints.php', 'wordpoints_activate' );

add_action( 'plugins_loaded', 'wordpoints_register_installer' );
add_action( 'plugins_loaded', 'wordpoints_breaking_update' );
add_action( 'plugins_loaded', 'wordpoints_load_textdomain' );
add_action( 'plugins_loaded', 'wordpoints_load_modules', 15 );

add_filter( 'map_meta_cap', 'wordpoints_map_custom_meta_caps', 10, 3 );

add_action( 'wordpoints_components_register', 'wordpoints_points_component_register' );
add_action( 'wordpoints_components_register', 'wordpoints_ranks_component_register' );

add_action( 'init', 'wordpoints_init_cache_groups', 5 );

add_action( 'wp_enqueue_scripts', 'wordpoints_register_scripts', 5 );
add_action( 'admin_enqueue_scripts', 'wordpoints_register_scripts', 5 );

add_action( 'wordpoints_modules_loaded', 'WordPoints_Installables::maybe_do_updates', 5 );
add_action( 'admin_notices', 'WordPoints_Installables::admin_notices' );
add_action( 'wpmu_new_blog', 'WordPoints_Installables::wpmu_new_blog' );

if ( isset( $_GET['wordpoints_module_check'], $_GET['check_module'] ) ) { // WPCS: CSRF OK.

	add_action( 'shutdown', 'wordpoints_maintenance_shutdown_print_rand_str' );

	if ( is_network_admin() ) {
		$filter = 'pre_site_option_wordpoints_sitewide_active_modules';
	} else {
		$filter = 'pre_option_wordpoints_active_modules';
	}

	add_filter( $filter, 'wordpoints_maintenance_filter_modules' );
}

add_action( 'wordpoints_init_app-apps', 'wordpoints_apps_init' );
add_action( 'wordpoints_init_app-entities', 'wordpoints_entities_app_init' );
add_action( 'wordpoints_init_app-entities-restrictions', 'wordpoints_entities_restrictions_app_init' );

add_action( 'wordpoints_init_app_registry-apps-entities', 'wordpoints_entities_init' );
add_action( 'wordpoints_init_app_registry-entities-contexts', 'wordpoints_entity_contexts_init' );
add_action( 'wordpoints_init_app_registry-entities-restrictions-know', 'wordpoints_entity_restrictions_know_init' );
add_action( 'wordpoints_init_app_registry-entities-restrictions-view', 'wordpoints_entity_restrictions_view_init' );

add_action( 'wordpoints_init_app_registry-apps-data_types', 'wordpoints_data_types_init' );

add_action( 'wordpoints_init_app_registry-hooks-extensions', 'wordpoints_hook_extensions_init' );
add_action( 'wordpoints_init_app_registry-hooks-events', 'wordpoints_hook_events_init' );
add_action( 'wordpoints_init_app_registry-hooks-actions', 'wordpoints_hook_actions_init' );
add_action( 'wordpoints_init_app_registry-hooks-conditions', 'wordpoints_hook_conditions_init' );

add_action( 'wordpoints_modules_loaded', 'wordpoints_init_hooks' );

// EOF
