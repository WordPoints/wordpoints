<?php

/**
 * Action and filter hooks.
 *
 * @package WordPoints
 * @since 2.1.0
 */

register_activation_hook( WORDPOINTS_DIR . 'wordpoints.php', 'wordpoints_activate' );
register_deactivation_hook( WORDPOINTS_DIR . 'wordpoints.php', 'wordpoints_deactivate' );

add_action( 'plugins_loaded', 'wordpoints_register_installer' );
add_action( 'plugins_loaded', 'wordpoints_breaking_update' );
add_action( 'plugins_loaded', 'wordpoints_load_textdomain' );
add_action( 'plugins_loaded', 'wordpoints_load_modules', 15 );

add_filter( 'map_meta_cap', 'wordpoints_map_custom_meta_caps', 10, 3 );

add_action( 'wordpoints_components_register', 'wordpoints_points_component_register' );
add_action( 'wordpoints_components_register', 'wordpoints_ranks_component_register' );

add_action( 'init', 'wordpoints_init_cache_groups', 5 );

if (
	! wp_doing_ajax()
	&& ( is_main_site() || is_network_admin() || ! is_wordpoints_network_active() )
) {

	add_action( 'init', 'wordpoints_schedule_extension_updates_check' );
	add_action( 'wordpoints_extension_update_check_completed', 'wordpoints_reschedule_extension_updates_check' );

	add_action( 'wordpoints_check_for_extension_updates', 'wordpoints_check_for_extension_updates' );
}

add_action( 'wp_enqueue_scripts', 'wordpoints_register_scripts', 5 );
add_action( 'admin_enqueue_scripts', 'wordpoints_register_scripts', 5 );

add_action( 'wordpoints_extensions_loaded', 'wordpoints_installables_maybe_update', 5 );
add_action( 'wpmu_new_blog', 'wordpoints_installables_install_on_new_site' );

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
add_action( 'wordpoints_init_app_registry-apps-extension_server_apis', 'wordpoints_extension_server_apis_init' );

add_action( 'wordpoints_init_app_registry-hooks-extensions', 'wordpoints_hook_extensions_init' );
add_action( 'wordpoints_init_app_registry-hooks-events', 'wordpoints_hook_events_init' );
add_action( 'wordpoints_init_app_registry-hooks-actions', 'wordpoints_hook_actions_init' );
add_action( 'wordpoints_init_app_registry-hooks-conditions', 'wordpoints_hook_conditions_init' );

add_action( 'wordpoints_register_post_type_entities', 'wordpoints_register_post_type_taxonomy_entities' );

add_action( 'wordpoints_extensions_loaded', 'wordpoints_init_hooks' );

add_filter( 'wp_get_update_data', 'wordpoints_extension_update_counts' );

add_filter( 'wordpoints_extensions_dir', 'wordpoints_legacy_modules_path', 5 );
add_filter( 'wordpoints_extensions_url', 'wordpoints_legacy_modules_path', 5 );

add_filter( 'wordpoints_extension_data', 'wordpoints_extension_data_missing_server_headers_filter' );

// EOF
