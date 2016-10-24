<?php

/**
 * Apps functions.
 *
 * @package WordPoints
 * @since 2.2.0
 */

/**
 * Register points component app when the Components registry is initialized.
 *
 * @since 2.2.0
 *
 * @WordPress\action wordpoints_init_app-components
 *
 * @param WordPoints_App $components The components app.
 */
function wordpoints_points_components_app_init( $components ) {

	$apps = $components->sub_apps();

	$apps->register( 'points', 'WordPoints_App' );
}

/**
 * Register sub apps when the points app is initialized.
 *
 * @since 2.2.0
 *
 * @WordPress\action wordpoints_init_app-components-points
 *
 * @param WordPoints_App $app The points app.
 */
function wordpoints_points_apps_init( $app ) {

	$apps = $app->sub_apps();

	$apps->register( 'logs', 'WordPoints_App' );
}

/**
 * Register sub apps when the points logs app is initialized.
 *
 * @since 2.2.0
 *
 * @WordPress\action wordpoints_init_app-components-points-logs
 *
 * @param WordPoints_App $app The points logs app.
 */
function wordpoints_points_logs_apps_init( $app ) {

	$apps = $app->sub_apps();

	$apps->register( 'views', 'WordPoints_Class_Registry' );
}

/**
 * Register points log views when the registry is initialized.
 *
 * @since 2.2.0
 *
 * @WordPress\action wordpoints_init_app_registry-components-points-logs-views
 *
 * @param WordPoints_Class_RegistryI $views The points log views registry.
 */
function wordpoints_points_logs_views_init( $views ) {

	$views->register( 'table', 'WordPoints_Points_Logs_View_Table' );
}

// EOF
