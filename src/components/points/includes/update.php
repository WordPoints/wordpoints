<?php

/**
 * Functions to update the points component.
 *
 * This file is loaded only when WordPoints is being updated. This functions should
 * be considered private, and are not intended for general use.
 *
 * @package WordPoints\Points
 * @since 1.2.0
 *
 * @access private
 */

/**
 * Clean up the database when updating to 1.2.0.
 *
 * @since 1.2.0
 */
function wordpoints_points_update_1_2_0() {

	global $wpdb;

	// If any users have been deleted, remove the logs for them.
	$log_ids = $wpdb->get_col(
		"
			SELECT wppl.id
			FROM {$wpdb->wordpoints_points_logs} AS wppl
			LEFT JOIN {$wpdb->users} as u
				ON wppl.user_id = u.ID
			WHERE u.ID IS NULL
		"
	);

	if ( $log_ids && is_array( $log_ids ) ) {

		$log_ids = implode( ',', array_map( 'absint', $log_ids ) );

		$wpdb->query(
			"
				DELETE
				FROM {$wpdb->wordpoints_points_logs}
				WHERE `id` IN ({$log_ids})
			"
		);

		$wpdb->query(
			"
				DELETE
				FROM {$wpdb->wordpoints_points_log_meta}
				WHERE `log_id` IN ({$log_ids})
			"
		);
	}

	// Regenerate the logs for deleted posts.
	$post_ids = $wpdb->get_col(
		"
			SELECT wpplm.meta_value
			FROM {$wpdb->wordpoints_points_log_meta} AS wpplm
			LEFT JOIN {$wpdb->posts} AS p
				ON p.ID = wpplm.meta_value
			WHERE p.ID IS NULL
				AND wpplm.meta_key = 'post_id'
		"
	);

	$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_post_points_hook' );

	if ( $post_ids && is_array( $post_ids ) && $hook ) {
		foreach ( $post_ids AS $post_id ) {
			$hook->clean_logs_on_post_deletion( $post_id );
		}
	}

	// Regenerate the logs for deleted comments.
	$comment_ids = $wpdb->get_col(
		"
			SELECT wpplm.meta_value
			FROM {$wpdb->wordpoints_points_log_meta} AS wpplm
			LEFT JOIN {$wpdb->comments} AS c
				ON c.comment_ID = wpplm.meta_value
			WHERE c.comment_ID IS NULL
				AND wpplm.meta_key = 'comment_id'
		"
	);

	$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_comment_points_hook' );

	if ( $comment_ids && is_array( $comment_ids ) && $hook ) {
		foreach ( $comment_ids AS $comment_id ) {
			$hook->clean_logs_on_comment_deletion( $comment_id );
		}
	}

} // function wordpoints_points_update_1_2_0()

/**
 * Update the points component to 1.4.0.
 *
 * @since 1.4.0
 */
function wordpoints_points_update_1_4_0() {

	// If the points hooks haven't been registered yet, try again later.
	if ( ! did_action( 'wordpoints_points_hooks_registered' ) ) {
		add_action( 'wordpoints_points_hooks_registered', __FUNCTION__ );
		return;
	}

	/*
	 * Split the post points hooks into post publish and post delete points hooks.
	 */

	add_filter( 'wordpoints_points_hook_update_callback', 'wordpoints_points_update_1_4_0_clean_hook_settings', 10, 4 );

	if ( is_wordpoints_network_active() ) {

		global $wpdb;

		$network_mode = WordPoints_Points_Hooks::get_network_mode();

		// Split the regular points hooks for each site.
		WordPoints_Points_Hooks::set_network_mode( false );

		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

		foreach ( $blog_ids as $blog_id ) {

			switch_to_blog( $blog_id );
			wordpoints_points_update_1_4_0_split_post_hooks();
			wordpoints_points_update_1_4_0_split_comment_hooks();
			wordpoints_add_custom_caps( wordpoints_points_get_custom_caps() );
			wordpoints_points_update_1_4_0_clean_points_logs();
			restore_current_blog();
		}

		// Split the network-wide points hooks.
		WordPoints_Points_Hooks::set_network_mode( true );
		wordpoints_points_update_1_4_0_split_post_hooks();
		wordpoints_points_update_1_4_0_split_comment_hooks();
		WordPoints_Points_Hooks::set_network_mode( $network_mode );

	} else {

		// WordPoints isn't network active, so this will run for each site.
		wordpoints_points_update_1_4_0_split_post_hooks();
		wordpoints_points_update_1_4_0_split_comment_hooks();
		wordpoints_points_update_1_4_0_clean_points_logs();
	}

	remove_filter( 'wordpoints_points_hook_update_callback', 'wordpoints_points_update_1_4_0_clean_hook_settings', 10, 4 );

} // function wordpoints_points_update_1_4_0()

/**
 * Split the post delete points hooks from the post points hooks.
 *
 * @since 1.4.0
 */
function wordpoints_points_update_1_4_0_split_post_hooks() {

	if ( WordPoints_Points_Hooks::get_network_mode() ) {
		$hook_type = 'network';
	} else {
		$hook_type = 'standard';
	}

	$post_delete_hook  = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_post_delete_points_hook' );
	$post_publish_hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_post_points_hook' );

	$points_types_hooks = WordPoints_Points_Hooks::get_points_types_hooks();
	$post_publish_hooks = $post_publish_hook->get_instances( $hook_type );

	// Loop through all of the post hook instances.
	foreach ( $post_publish_hooks as $number => $settings ) {

		// Don't split the hook if it is just a placeholder, or it's already split.
		if ( 0 == $number || ! isset( $settings['trash'], $settings['publish'] ) ) {
			continue;
		}

		if ( ! isset( $settings['post_type'] ) ) {
			$settings['post_type'] = 'ALL';
		}

		// If the trash points are set, create a post delete points hook instead.
		if ( isset( $settings['trash'] ) && wordpoints_posint( $settings['trash'] ) ) {

			$post_delete_hook->update_callback(
				array(
					'points' => $settings['trash'],
					'post_type' => $settings['post_type'],
				)
				, $post_delete_hook->next_hook_id_number()
			);

			// Make sure the correct points type is retrieved for network hooks.
			if ( 'network' === $hook_type ) {
				$points_type = $post_publish_hook->points_type( 'network_' . $number );
			} else {
				$points_type = $post_publish_hook->points_type( $number );
			}

			// Add this instance to the points-types-hooks list.
			$points_types_hooks[ $points_type ][] = $post_delete_hook->get_id( $number );
		}

		// If the publish points are set, update the settings of the hook.
		if ( isset( $settings['publish'] ) && wordpoints_posint( $settings['publish'] ) ) {

			$settings['points'] = $settings['publish'];

			$post_publish_hook->update_callback( $settings, $number );

		} else {

			// If not, delete this instance.
			$post_publish_hook->delete_callback( $post_publish_hook->get_id( $number ) );
		}
	}

	WordPoints_Points_Hooks::save_points_types_hooks( $points_types_hooks );
}

/**
 * Split the commend removed points hooks from the comment points hooks.
 *
 * @since 1.4.0
 */
function wordpoints_points_update_1_4_0_split_comment_hooks() {

	if ( WordPoints_Points_Hooks::get_network_mode() ) {
		$hook_type = 'network';
	} else {
		$hook_type = 'standard';
	}

	$comment_removed_hook  = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_comment_removed_points_hook' );
	$comment_approved_hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_comment_points_hook' );

	$points_types_hooks = WordPoints_Points_Hooks::get_points_types_hooks();
	$comment_approved_hooks = $comment_approved_hook->get_instances( $hook_type );

	// Loop through all of the comment hook instances.
	foreach ( $comment_approved_hooks as $number => $settings ) {

		// Don't split the hook if it is just a placeholder, or it's already split.
		if ( 0 == $number || ! isset( $settings['approve'], $settings['disapprove'] ) ) {
			continue;
		}

		// If the disapprove points are set, create a comment removed hook instead.
		if ( isset( $settings['disapprove'] ) && wordpoints_posint( $settings['disapprove'] ) ) {

			$comment_removed_hook->update_callback(
				array(
					'points' => $settings['disapprove'],
				)
				, $comment_removed_hook->next_hook_id_number()
			);

			// Make sure the correct points type is retrieved for network hooks.
			if ( 'network' === $hook_type ) {
				$points_type = $comment_approved_hook->points_type( 'network_' . $number );
			} else {
				$points_type = $comment_approved_hook->points_type( $number );
			}

			// Add this instance to the points-types-hooks list.
			$points_types_hooks[ $points_type ][] = $comment_removed_hook->get_id();
		}

		// If the approve points are set, update the settings of the hook.
		if ( isset( $settings['approve'] ) && wordpoints_posint( $settings['approve'] ) ) {

			$settings['points'] = $settings['approve'];

			$comment_approved_hook->update_callback( $settings, $number );

		} else {

			// If not, delete this instance.
			$comment_approved_hook->delete_callback( $comment_approved_hook->get_id( $number ) );
		}
	}

	WordPoints_Points_Hooks::save_points_types_hooks( $points_types_hooks );
}

/**
 * Clean the settings for the post and comment points hooks.
 *
 * Removes old and no longer used settings from the comment and post points hooks.
 *
 * @since 1.4.0
 *
 * @filter wordpoints_points_hook_update_callback Added during the update to 1.4.0.
 *
 * @param array                  $instance     The settings for the instance.
 * @param array                  $new_instance The new settings for the instance.
 * @param array                  $old_instance The old settings for the instance.
 * @param WordPoints_Points_Hook $hook         The hook object.
 *
 * @return array The filtered instance settings.
 */
function wordpoints_points_update_1_4_0_clean_hook_settings( $instance, $new_instance, $old_instance, $hook ) {

	if ( $hook instanceof WordPoints_Post_Points_Hook ) {
		unset( $instance['trash'], $instance['publish'] );
	} elseif ( $hook instanceof WordPoints_Comment_Points_Hook ) {
		unset( $instance['approve'], $instance['disapprove'] );
	}

	return $instance;
}

/**
 * Clean the comment_approve points logs for posts that have been deleted.
 *
 * @since 1.4.0
 */
function wordpoints_points_update_1_4_0_clean_points_logs() {

	global $wpdb;

	$post_ids = $wpdb->get_col(
		"
			SELECT wpplm.meta_value
			FROM {$wpdb->wordpoints_points_log_meta} AS wpplm
			LEFT JOIN {$wpdb->posts} AS p
				ON p.ID = wpplm.meta_value
			LEFT JOIN {$wpdb->wordpoints_points_logs} As wppl
				ON wppl.id = wpplm.log_id
			WHERE p.ID IS NULL
				AND wpplm.meta_key = 'post_id'
				AND wppl.log_type = 'comment_approve'
		"
	);

	$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_comment_points_hook' );

	if ( $post_ids && is_array( $post_ids ) && $hook ) {
		foreach ( $post_ids AS $post_id ) {
			$hook->clean_logs_on_post_deletion( $post_id );
		}
	}
}

/**
 * Update the points component to 1.5.0.
 *
 * Prior to 1.5.0, capabilities weren't automatically added to new sites when
 * WordPoints was in network mode.
 *
 * @since 1.5.0
 */
function wordpoints_points_update_1_5_0() {

	if ( ! is_wordpoints_network_active() ) {
		return;
	}

	global $wpdb;

	$capabilities = wordpoints_points_get_custom_caps();

	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

	foreach ( $blog_ids as $blog_id ) {

		switch_to_blog( $blog_id );
		wordpoints_add_custom_caps( $capabilities );
		restore_current_blog();
	}
}
