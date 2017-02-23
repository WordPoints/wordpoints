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

add_filter( 'script_loader_tag', 'wordpoints_script_templates_filter', 10, 2 );

add_action( 'admin_menu', 'wordpoints_admin_menu' );
add_action( 'network_admin_menu', 'wordpoints_admin_menu' );

add_action( 'load-wordpoints_page_wordpoints_modules', 'wordpoints_admin_screen_modules_load' );
add_action( 'load-toplevel_page_wordpoints_modules', 'wordpoints_admin_screen_modules_load' );

add_action( 'load-toplevel_page_wordpoints_configure', 'wordpoints_admin_screen_configure_load' );

add_action( 'load-toplevel_page_wordpoints_configure', 'wordpoints_admin_activate_components' );

add_action( 'wordpoints_install_modules-upload', 'wordpoints_install_modules_upload' );

add_action( 'update-custom_upload-wordpoints-module', 'wordpoints_upload_module_zip' );

add_action( 'upgrader_source_selection', 'wordpoints_plugin_upload_error_filter', 5 );
add_action( 'upgrader_source_selection', 'wordpoints_plugin_upload_error_filter', 20 );

add_action( 'wordpoints_admin_configure_foot', 'wordpoints_admin_settings_screen_sidebar', 5 );

add_action( 'admin_notices', 'wordpoints_admin_notices' );

add_action( 'set-screen-option', 'wordpoints_admin_set_screen_option', 10, 3 );

add_action( 'wp_ajax_nopriv_wordpoints_breaking_module_check', 'wordpoints_admin_ajax_breaking_module_check' );
add_action( 'wp_ajax_wordpoints-delete-admin-notice-option', 'wordpoints_delete_admin_notice_option' );

add_action( 'load-plugins.php', 'wordpoints_admin_maybe_disable_update_row_for_php_version_requirement', 100 );
add_action( 'load-update-core.php', 'wordpoints_admin_maybe_remove_from_updates_screen' );
add_action( 'install_plugins_pre_plugin-information', 'wordpoints_admin_maybe_remove_from_updates_screen', 9 );

// EOF
