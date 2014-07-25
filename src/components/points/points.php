<?php

/**
 * WordPoints Points component
 *
 * @package WordPoints\Points
 * @since 1.0.0
 */

global $wpdb;

$wpdb->wordpoints_points_logs     = "{$wpdb->base_prefix}wordpoints_points_logs";
$wpdb->wordpoints_points_log_meta = "{$wpdb->base_prefix}wordpoints_points_log_meta";

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

	// The component isn't loaded on activation, so we must include dependencies.
	include_once WORDPOINTS_DIR . 'components/points/includes/functions.php';

	/*
	 * Regenerate the custom caps every time on multisite, because they depend on
	 * network activation status.
	 */
	if ( is_multisite() ) {

		global $wpdb;

		$custom_caps = wordpoints_points_get_custom_caps();
		$custom_caps_keys = array_keys( $custom_caps );

		$network_active = is_wordpoints_network_active();

		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

		foreach ( $blog_ids as $blog_id ) {

			switch_to_blog( $blog_id );

			wordpoints_remove_custom_caps( $custom_caps_keys );

			if ( $network_active ) {
				wordpoints_add_custom_caps( $custom_caps );
			}

			restore_current_blog();
		}

		if ( ! $network_active ) {
			wordpoints_add_custom_caps( $custom_caps );
		}
	}

	$wordpoints_data = wordpoints_get_array_option( 'wordpoints_data', 'network' );

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

	$wordpoints_data = wordpoints_get_array_option( 'wordpoints_data', 'network' );

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
 * Update the points component.
 *
 * @since 1.2.0
 *
 * @action wordpoints_components_loaded
 */
function wordpoints_points_component_update() {

	$db_version = '1.0.0';

	$wordpoints_data = wordpoints_get_network_option( 'wordpoints_data' );

	if ( isset( $wordpoints_data['components']['points']['version'] ) ) {
		$db_version = $wordpoints_data['components']['points']['version'];
	}

	// If the DB version isn't less than the code version, we don't need to upgrade.
	if ( version_compare( $db_version, WORDPOINTS_VERSION ) != -1 ) {
		return;
	}

	/**
	 * The update functions for the points component.
	 *
	 * @since 1.2.0
	 */
	require_once WORDPOINTS_DIR . 'components/points/includes/update.php';

	switch ( 1 ) {

		case version_compare( '1.2.0', $db_version ):
			wordpoints_points_update_1_2_0();
		// fallthru

		case version_compare( '1.4.0', $db_version ):
			wordpoints_points_update_1_4_0();
		// fallthru

		case version_compare( '1.5.0', $db_version ):
			if ( 1 != version_compare( '1.4.0', $db_version ) ) {
				// This doesn't need to run if we just ran the 1.4.0 update.
				wordpoints_points_update_1_5_0();
			}
		// fallthru

		case version_compare( '1.5.1', $db_version ):
			wordpoints_points_update_1_5_1();
		// fallthru
	}

	$wordpoints_data['components']['points']['version'] = WORDPOINTS_VERSION;

	wordpoints_update_network_option( 'wordpoints_data', $wordpoints_data );
}
add_action( 'wordpoints_components_loaded', 'wordpoints_points_component_update' );

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
 * Post type points hook abstract class.
 *
 * @since 1.5.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/hooks/abstracts/post-type.php';

/**
 * The registration points hook
 *
 * @since 1.4.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/hooks/registration.php';

/**
 * The post publish points hook
 *
 * @since 1.4.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/hooks/post.php';

/**
 * The post delete points hook
 *
 * @since 1.4.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/hooks/post-delete.php';

/**
 * The comment points hook
 *
 * @since 1.4.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/hooks/comment.php';

/**
 * The comment removed points hook
 *
 * @since 1.4.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/hooks/comment-removed.php';

/**
 * The periodic points hook
 *
 * @since 1.4.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/hooks/periodic.php';

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

/**
 * Deprecated functions and classes.
 *
 * @since 1.2.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/deprecated.php';

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
