<?php

/**
 * Admin-side filter and action hooks.
 *
 * @package WordPoints\Admin
 * @since 2.1.0
 */

add_action( 'wordpoints_init_app-apps', 'wordpoints_hooks_register_admin_apps' );

add_action( 'admin_init', 'wordpoints_hooks_admin_ajax' );
add_action( 'admin_init', 'wordpoints_register_admin_scripts' );

if ( ! is_multisite() || is_network_admin() ) {
	add_action( 'admin_init', 'wordpoints_extension_update_rows' );
}

add_filter( 'script_loader_tag', 'wordpoints_script_templates_filter', 10, 2 );

add_action( 'admin_menu', 'wordpoints_admin_menu' );
add_action( 'network_admin_menu', 'wordpoints_admin_menu' );

add_action( 'load-wordpoints_page_wordpoints_extensions', 'wordpoints_admin_screen_modules_load' );
add_action( 'load-toplevel_page_wordpoints_extensions', 'wordpoints_admin_screen_modules_load' );

add_action( 'load-wordpoints_page_wordpoints_modules', 'wordpoints_admin_screen_modules_load' );
add_action( 'load-toplevel_page_wordpoints_modules', 'wordpoints_admin_screen_modules_load' );

add_action( 'load-toplevel_page_wordpoints_configure', 'wordpoints_admin_screen_configure_load' );

add_action( 'load-toplevel_page_wordpoints_configure', 'wordpoints_admin_activate_components' );

add_action( 'wordpoints_install_extensions-upload', 'wordpoints_install_modules_upload' );

add_action( 'update-custom_upload-wordpoints-module', 'wordpoints_upload_module_zip' );
add_action( 'update-custom_wordpoints-upgrade-extension', 'wordpoints_admin_screen_upgrade_extension' );
add_action( 'update-custom_wordpoints-reactivate-extension', 'wordpoints_admin_iframe_reactivate_extension' );
add_action( 'update-custom_wordpoints-iframe-extension-changelog', 'wordpoints_iframe_extension_changelog' );
add_action( 'update-custom_update-selected-wordpoints-extensions', 'wordpoints_iframe_update_extensions' );

add_action( 'update-core-custom_do-wordpoints-extension-upgrade', 'wordpoints_admin_screen_update_selected_extensions' );
add_action( 'wordpoints_extensions_screen-update-selected', 'wordpoints_admin_screen_update_selected_extensions' );

add_action( 'core_upgrade_preamble', 'wordpoints_list_extension_updates' );

add_action( 'upgrader_source_selection', 'wordpoints_plugin_upload_error_filter', 5 );
add_action( 'upgrader_source_selection', 'wordpoints_plugin_upload_error_filter', 20 );

add_action( 'wordpoints_admin_configure_foot', 'wordpoints_admin_settings_screen_sidebar', 5 );

add_action( 'admin_notices', 'wordpoints_admin_notices' );
add_action( 'admin_notices', 'wordpoints_admin_show_extension_license_notices' );

add_action( 'set-screen-option', 'wordpoints_admin_set_screen_option', 10, 3 );

add_action( 'wp_ajax_nopriv_wordpoints_breaking_module_check', 'wordpoints_admin_ajax_breaking_module_check' );
add_action( 'wp_ajax_wordpoints-delete-admin-notice-option', 'wordpoints_delete_admin_notice_option' );

add_action( 'load-plugins.php', 'wordpoints_admin_maybe_disable_update_row_for_php_version_requirement', 100 );
add_action( 'load-update-core.php', 'wordpoints_admin_maybe_remove_from_updates_screen' );
add_action( 'install_plugins_pre_plugin-information', 'wordpoints_admin_maybe_remove_from_updates_screen', 9 );

add_filter( 'wp_kses_allowed_html', 'wordpoints_extension_changelog_allowed_html', 10, 2 );

add_filter( 'wordpoints_modules_list_table_items', 'wordpoints_admin_save_extension_licenses' );
add_filter( 'wordpoints_module_list_row_class', 'wordpoints_extension_list_row_license_classes', 10, 3 );
add_action( 'wordpoints_after_module_row', 'wordpoints_extension_license_row', 10, 2 );

if ( ! wp_doing_ajax() && ( is_main_site() || is_network_admin() ) ) {

	add_action( 'load-wordpoints_page_wordpoints_extensions', 'wordpoints_check_for_extension_updates_hourly' );
	add_action( 'load-toplevel_page_wordpoints_extensions', 'wordpoints_check_for_extension_updates_hourly' );
	add_action( 'load-update.php', 'wordpoints_check_for_extension_updates_hourly' );
	add_action( 'load-update-core.php', 'wordpoints_check_for_extension_updates_now' );
	add_action( 'upgrader_process_complete', 'wordpoints_recheck_for_extension_updates_after_upgrade', 10, 2 );
	add_action( 'admin_init', 'wordpoints_check_for_extension_updates', 10, 0 );
}

// EOF
