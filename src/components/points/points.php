<?php

/**
 * WordPoints Points component
 *
 * @package WordPoints\Points
 * @since 1.0.0
 */

global $wpdb;

$wpdb->wordpoints_points_logs = "{$wpdb->base_prefix}wordpoints_points_logs";
$wpdb->wordpoints_points_log_meta = "{$wpdb->base_prefix}wordpoints_points_log_meta";

/**
 * The points logs database table name.
 *
 * This table is network-wide on multisite installs.
 *
 * @since 1.0.0
 * @deprecated 1.1.0
 * @deprecated Use $wpdb->wordpoints_points_logs instead.
 *
 * @type string
 */
define( 'WORDPOINTS_POINTS_LOGS_DB', $wpdb->wordpoints_points_logs );

/**
 * The points logs meta database table name.
 *
 * This table is network-wide on multisite installs.
 *
 * @since 1.0.0
 * @deprecated 1.1.0
 * @deprecated Use $wpdb->wordpoints_points_log_meta instead.
 *
 * @type string
 */
define( 'WORDPOINTS_POINTS_LOG_META_DB', $wpdb->wordpoints_points_log_meta );

/**
 * Register the points component.
 *
 * @since 1.0.0
 *
 * @action wordpoints_components_register
 *
 * @uses wordpoints_component_register()
 */
function wordpoints_points_component_register() {

	wordpoints_component_register(
		array(
			'slug'          => 'points',
			'name'          => _x( 'Points', 'component name', 'wordpoints' ),
			'version'       => WORDPOINTS_VERSION,
			'author'        => _x( 'WordPoints', 'component author', 'wordpoints' ),
			'author_uri'    => 'http://wordpoints.org/',
			'component_uri' => 'http://wordpoints.org/',
			'description'   => __( 'Enables a points system for your site.', 'wordpoints' ),
		)
	);
}
add_action( 'wordpoints_components_register', 'wordpoints_points_component_register' );

/**
 * Perform component installation/updates.
 *
 * @since 1.0.0
 *
 * @action wordpoints_activate_component-points
 */
function wordpoints_points_component_activate() {

	$wordpoints_data = wordpoints_get_array_option( 'wordpoints_data' );

	if ( ! isset( $wordpoints_data['components']['points']['version'] ) ) {

		// The component hasn't yet been installed.

		/**
		 * Installs the points component.
		 *
		 * @since 1.0.0
		 */
		require WORDPOINTS_DIR . 'components/points/install.php';
	}
}
add_action( 'wordpoints_component_activate-points', 'wordpoints_points_component_activate' );

/**
 * Perform component uninstallation.
 *
 * @since 1.0.0
 *
 * @action wordpoints_components_uninstall
 */
function wordpoints_points_component_uninstall() {

	$wordpoints_data = wordpoints_get_array_option( 'wordpoints_data' );

	if ( isset( $wordpoints_data['components']['points']['version'] ) ) {

		// The component has been installed.

		/**
		 * Uninstall the component.
		 *
		 * @since 1.0.0
		 */
		require WORDPOINTS_DIR . 'components/points/uninstall.php';
	}
}
add_action( 'wordpoints_uninstall_component-points', 'wordpoints_points_component_uninstall' );

// If the component isn't active, stop here.
if ( ! wordpoints_component_is_active( 'points' ) ) {
	return;
}

/**
 * Register scripts and styles for the component.
 *
 * @since 1.0.0
 *
 * @action wp_enqueue_scripts 5 Register scripts before default enqueue (10).
 * @action admin_enqueue_scripts 5 Register admin scripts so they are ready on 10.
 */
function wordpoints_points_register_scripts() {

	$assets_url = plugins_url( 'assets/', __FILE__ );

	wp_register_style(
		'wordpoints-top-users'
		,$assets_url . 'css/top-users.css'
		,null
		,WORDPOINTS_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'wordpoints_points_register_scripts', 5 );
add_action( 'admin_enqueue_scripts', 'wordpoints_points_register_scripts', 5 );

/**
 * Points component functions.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/functions.php';

/**
 * Points hooks static class.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/class-wordpoints-points-hooks.php';

/**
 * Points hook abstract class.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/class-wordpoints-points-hook.php';

/**
 * Included points hooks.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/hooks.php';

/**
 * Points logs query class.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/class-wordpoints-points-logs-query.php';

/**
 * Shortcodes/template tags.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/shortcodes.php';

/**
 * Widgets.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/widgets.php';

/**
 * Logs related functions.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/logs.php';

if ( is_admin() ) {

	// We are on the administration side of the site.

	/**
	 * Points administration.
	 *
	 * @since 1.0.0
	 */
	include_once WORDPOINTS_DIR . 'components/points/admin/admin.php';
}

// end of file /components/points/points.php
