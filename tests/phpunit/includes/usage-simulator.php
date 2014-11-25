<?php

/**
 * Simulate plugin usage.
 *
 * Used by the install/uninstall tests to provide a more full test of whether
 * everything is properly deleted on uninstall. Uninstalling a fresh install is
 * important, but cleaning up the little things is also important. Doing this little
 * dance here helps us to make sure we're doing that.
 *
 * @package WordPoints\Tests
 * @since 1.2.0
 */

/**
 * Simulate plugin usage.
 *
 * Only available from the plugin uninstall usage simulator.
 *
 * @since 1.2.0
 */
function wordpointstests_simulate_usage() {

	global $wp_test_factory;

	// Add each of our widgets.
	wordpointstests_add_widget( 'wordpoints_points_widget' );
	wordpointstests_add_widget( 'wordpoints_top_users_widget' );
	wordpointstests_add_widget( 'wordpoints_points_logs_widget' );

	// Create a points type.
	wordpoints_add_points_type(
		array(
			'name'   => 'Points',
			'prefix' => '$',
			'suffix' => 'pts.',
		)
	);

	// Add each of our points hooks.
	wordpointstests_add_points_hook( 'wordpoints_registration_points_hook' );
	wordpointstests_add_points_hook( 'wordpoints_post_points_hook' );
	wordpointstests_add_points_hook( 'wordpoints_comment_points_hook' );
	$periodic_hook = wordpointstests_add_points_hook( 'wordpoints_periodic_points_hook' );

	// Award some points to a user.
	$user = $wp_test_factory->user->create_and_get();

	wordpoints_add_points( $user->ID, 10, 'points', 'test', array( 'a' => 'a', 'b' => 'b' ) );

	// Fire the periodic points hook.
	$current_user_id = get_current_user_id();
	wp_set_current_user( $user->ID );
	$periodic_hook->hook();
	wp_set_current_user( $current_user_id );

	// Add a rank group.
	WordPoints_Rank_Groups::register_group(
		'test'
		, array( 'name' => 'Test', 'description' => 'A test group.' )
	);

	// Add a rank type.
	WordPoints_Rank_Types::register_type( 'test', 'WordPoints_Base_Rank_Type' );
	WordPoints_Rank_Groups::register_type_for_group( 'test', 'test' );

	wordpoints_add_rank( 'Test', 'test', 'test', 1, array( 'testing' => true ) );
}

// Include the test functions so we can simulate adding points hooks and widgets.
require_once dirname( __FILE__ ) . '/functions.php';

// Activate the Points component.
$wordpoints_components = WordPoints_Components::instance();
$wordpoints_components->load();
$wordpoints_components->activate( 'ranks' );

if ( is_multisite() && is_wordpoints_network_active() ) {

	global $wpdb;

	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

	$original_blog_id = get_current_blog_id();

	foreach ( $blog_ids as $_blog_id ) {

		// We use $_blog_id instead of $blog_id, because this is in the global scope.
		switch_to_blog( $_blog_id );

		wordpointstests_simulate_usage();
	}

	switch_to_blog( $original_blog_id );

	// See http://wordpress.stackexchange.com/a/89114/27757
	unset( $GLOBALS['_wp_switched_stack'] );
	$GLOBALS['switched'] = false;

} else {

	wordpointstests_simulate_usage();
}

// EOF
