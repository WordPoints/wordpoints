<?php

/**
 * Class for un/installing the points component.
 *
 * @package WordPoints
 * @since 1.8.0
 * @deprecated 2.4.0
 */

_deprecated_file( __FILE__, '2.4.0' );

/**
 * Un/installs the points component.
 *
 * @since 1.8.0
 * @deprecated 2.4.0 Use WordPoints_Points_Installable instead.
 */
class WordPoints_Points_Un_Installer extends WordPoints_Un_Installer_Base {

	/**
	 * Disable the legacy points hooks.
	 *
	 * @since 2.1.0
	 * @deprecated 2.4.0
	 */
	protected function disable_legacy_hooks() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update the plugin to 1.2.0.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function update_network_to_1_2_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update the plugin to 1.2.0.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function update_single_to_1_2_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Remove the points logs of users who have been deleted.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function _1_2_0_remove_points_logs_for_deleted_users() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Regenerate the points logs for deleted posts.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function _1_2_0_regenerate_points_logs_for_deleted_posts() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Regenerate the points logs for deleted comments.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function _1_2_0_regenerate_points_logs_for_deleted_comments() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a network to 1.4.0.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function update_network_to_1_4_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a site on the network to 1.4.0.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function update_site_to_1_4_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a single site to 1.4.0.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function update_single_to_1_4_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Split the post delete points hooks from the post points hooks.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function _1_4_0_split_post_hooks() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Split the commend removed points hooks from the comment points hooks.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function _1_4_0_split_comment_hooks() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Split a set of points hooks.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 *
	 * @param string $hook      The slug of the hook type to split.
	 * @param string $new_hook  The slug of the new hook that this one is being split into.
	 * @param string $key       The settings key for the hook that holds the points.
	 * @param string $split_key The settings key for points that is being split.
	 */
	protected function _1_4_0_split_points_hooks( $hook, $new_hook, $key, $split_key ) {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Clean the settings for the post and comment points hooks.
	 *
	 * Removes old and no longer used settings from the comment and post points hooks.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
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
	public function _1_4_0_clean_hook_settings( $instance, $new_instance, $old_instance, $hook ) {

		_deprecated_function( __METHOD__, '2.4.0' );

		return $instance;
	}

	/**
	 * Clean the comment_approve points logs for posts that have been deleted.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function _1_4_0_clean_points_logs() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a site on the network to 1.5.0.
	 *
	 * Prior to 1.5.0, capabilities weren't automatically added to new sites when
	 * WordPoints was in network mode.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function update_site_to_1_5_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update the network to 1.5.1.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function update_network_to_1_5_1() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a single site to 1.5.1.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function update_single_to_1_5_1() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a site to 1.8.0.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function update_site_to_1_8_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a network to 1.9.0.
	 *
	 * @since 1.9.0
	 * @deprecated 2.4.0
	 */
	protected function update_network_to_1_9_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a site to 1.9.0.
	 *
	 * @since 1.9.0
	 * @deprecated 2.4.0
	 */
	protected function update_site_to_1_9_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a single site to 1.9.0.
	 *
	 * @since 1.9.0
	 * @deprecated 2.4.0
	 */
	protected function update_single_to_1_9_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Combine any Comment/Comment Removed or Post/Post Delete hook instance pairs.
	 *
	 * @since 1.9.0
	 * @deprecated 2.4.0
	 *
	 * @param string $type         The primary hook type that awards the points.
	 * @param string $reverse_type The counterpart hook type that reverses points.
	 */
	protected function _1_9_0_combine_hooks( $type, $reverse_type ) {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a site to 1.10.0.
	 *
	 * @since 1.10.0
	 * @deprecated 2.4.0
	 */
	protected function update_network_to_1_10_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a single site to 1.10.0.
	 *
	 * @since 1.10.0
	 * @deprecated 2.4.0
	 */
	protected function update_single_to_1_10_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Delete the no longer used 'post_title' metadata from post delete points logs.
	 *
	 * @since 1.10.0
	 * @deprecated 2.4.0
	 *
	 * @param bool $network_wide Whether to delete all of the metadata for the whole
	 *                           network, or just the current site (default).
	 */
	protected function _1_10_0_delete_post_title_points_log_meta( $network_wide = false ) {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a site to 2.0.0.
	 *
	 * @since 2.0.0
	 * @deprecated 2.4.0
	 */
	protected function update_network_to_2_0_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a single site to 2.0.0.
	 *
	 * @since 2.0.0
	 * @deprecated 2.4.0
	 */
	protected function update_single_to_2_0_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a multisite network to 2.1.0
	 *
	 * @since 2.1.0
	 * @deprecated 2.4.0
	 */
	protected function update_network_to_2_1_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a single site to 2.1.0
	 *
	 * @since 2.1.0
	 * @deprecated 2.4.0
	 */
	protected function update_single_to_2_1_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a site on the network to 2.1.4.
	 *
	 * @since 2.1.4
	 * @deprecated 2.4.0
	 */
	protected function update_site_to_2_1_4() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a single site to 2.1.4.
	 *
	 * @since 2.1.4
	 * @deprecated 2.4.0
	 */
	protected function update_single_to_2_1_4() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Get the post types used by the hooks API.
	 *
	 * @since 2.1.4
	 * @deprecated 2.4.0
	 *
	 * @return string[] The post types that we award points for.
	 */
	protected function _2_1_4_get_post_types() {

		_deprecated_function( __METHOD__, '2.4.0' );

		return array();
	}

	/**
	 * Get the slugs of the reversal events that we are interested in.
	 *
	 * @since 2.1.4
	 * @deprecated 2.4.0
	 *
	 * @return array The slugs of the events.
	 */
	protected function _2_1_4_get_reversal_log_types( $post_types ) {

		_deprecated_function( __METHOD__, '2.4.0' );

		return array();
	}

	/**
	 * Get all duplicate logs for a single hit.
	 *
	 * Finds all points logs where two logs are for the same hit, and returns the
	 * IDs of those hits and the IDs of the logs for each.
	 *
	 * @since 2.1.4
	 * @deprecated 2.4.0
	 *
	 * @return object[] Array of rows, each row consisting of the `hit_id` and
	 *                  `log_ids`, the later being the IDs of all of the logs
	 *                  concatenated together using commas.
	 */
	protected function _2_1_4_get_hits_with_multiple_logs() {

		_deprecated_function( __METHOD__, '2.4.0' );

		return array();
	}

	/**
	 * Get the IDs of the original logs for a bunch of reversal logs.
	 *
	 * @since 2.1.4
	 * @deprecated 2.4.0
	 *
	 * @param int[] $log_ids  The IDs of the reversal logs.
	 *
	 * @return array The IDs of the original logs.
	 */
	protected function _2_1_4_get_original_log_ids( $log_ids ) {

		_deprecated_function( __METHOD__, '2.4.0' );

		return array();
	}

	/**
	 * Delete some points logs.
	 *
	 * The hits for the logs will also be deleted.
	 *
	 * @since 2.1.4
	 * @deprecated 2.4.0
	 *
	 * @param int[] $log_ids The IDs of the logs to delete.
	 */
	protected function _2_1_4_delete_logs( $log_ids ) {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Delete some hook hits.
	 *
	 * @since 2.1.4
	 * @deprecated 2.4.0
	 *
	 * @param int[] $hit_ids The IDs of the hits to delete.
	 */
	protected function _2_1_4_delete_hits( $hit_ids ) {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Cleans out any other logs relating to a post.
	 *
	 * @since 2.1.4
	 * @deprecated 2.4.0
	 *
	 * @param int    $post_id        The ID of the post to clean logs for.
	 * @param string $post_type      The post type that this post is of.
	 * @param int[]  $logs_to_delete A list of logs that are being deleted.
	 *
	 * @return int[] The list of logs to be deleted.
	 */
	protected function _2_1_4_clean_other_logs( $post_id, $post_type, $logs_to_delete ) {

		_deprecated_function( __METHOD__, '2.4.0' );

		return array();
	}

	/**
	 * Give a user back the points that a log removed.
	 *
	 * @since 2.1.4
	 * @deprecated 2.4.0
	 *
	 * @param object $log The points log object.
	 */
	protected function _2_1_4_revert_log( $log ) {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Mark a points log as unreversed.
	 *
	 * @since 2.1.4
	 * @deprecated 2.4.0
	 *
	 * @param int $log_id The ID of the log to mark as unreversed.
	 */
	protected function _2_1_4_mark_unreversed( $log_id ) {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Get all of the legacy logs grouped by post ID.
	 *
	 * @since 2.1.4
	 * @deprecated 2.4.0
	 *
	 * @param string[] $post_types The post types to retrieve logs for.
	 *
	 * @return array[] The logs, grouped by post ID.
	 */
	protected function _2_1_4_get_legacy_reactor_logs( $post_types ) {

		_deprecated_function( __METHOD__, '2.4.0' );

		return array();
	}

	/**
	 * Get the post IDs from the old points hooks logs.
	 *
	 * @since 2.1.4
	 * @deprecated 2.4.0
	 *
	 * @return int[] The post IDs for the points hooks logs.
	 */
	protected function _2_1_4_get_legacy_points_hook_post_ids() {

		_deprecated_function( __METHOD__, '2.4.0' );

		return array();
	}
}

return 'WordPoints_Points_Un_Installer';

// EOF
