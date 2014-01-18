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

// Include the test functions so we can simulate adding points hooks and widgets.
require_once dirname( __FILE__ ) . '/functions.php';

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
wordpointstests_add_points_hook( 'wordpoints_periodic_points_hook' );
