<?php

/**
 * Deprecated code for the points component.
 *
 * @package WordPoints\Points
 * @since 1.2.0
 */

/**
 * Display top users.
 *
 * @since 1.0.0
 * @deprecated 1.8.0 Use WordPoints_Shortcodes::do_shortcode() instead.
 *
 * @param array $atts The shortcode attributes. {
 *        @type int    $users       The number of users to display.
 *        @type string $points_type The type of points.
 * }
 *
 * @return string
 */
function wordpoints_points_top_shortcode( $atts ) {

	_deprecated_function( __FUNCTION__, '1.8.0', 'WordPoints_Shortcodes::do_shortcode()' );

	return WordPoints_Shortcodes::do_shortcode( $atts, null, 'wordpoints_points_top' );
}

/**
 * Points logs shortcode.
 *
 * @since 1.0.0
 * @since 1.6.0 The datatables attribute is deprecated in favor of paginate.
 * @since 1.6.0 The searchable attribute is added.
 * @deprecated 1.8.0 Use WordPoints_Shortcodes::do_shortcode() instead.
 *
 * @param array $atts The shortcode attributes. {
 *        @type string $points_type The type of points to display. Required.
 *        @type string $query       The logs query to display.
 *        @type int    $paginate    Whether to paginate the table. 1 or 0.
 *        @type int    $searchable  Whether to display a search form. 1 or 0.
 *        @type int    $datatables  Whether the table should be a datatable. 1 or 0.
 *                                  Deprecated in favor of paginate.
 *        @type int    $show_users  Whether to show the 'Users' column in the table.
 * }
 *
 * @return string
 */
function wordpoints_points_logs_shortcode( $atts ) {

	_deprecated_function( __FUNCTION__, '1.8.0', 'WordPoints_Shortcodes::do_shortcode()' );

	return WordPoints_Shortcodes::do_shortcode( $atts, null, 'wordpoints_points_logs' );
}

/**
 * Display the points of a user.
 *
 * @since 1.3.0
 * @since 1.8.0 Added support for the post_author value of the user_id attribute.
 * @deprecated 1.8.0 Use WordPoints_Shortcodes::do_shortcode() instead.
 *
 * @param array $atts {
 *        The shortcode attributes.
 *
 *        @type string $points_type The type of points to display.
 *        @type mixed  $user_id     The ID of the user whose points should be
 *                                  displayed. Defaults to the current user. If set
 *                                  to post_author, the author of the current post.
 * }
 *
 * @return string The points for the user.
 */
function wordpoints_points_shortcode( $atts ) {

	_deprecated_function( __FUNCTION__, '1.8.0', 'WordPoints_Shortcodes::do_shortcode()' );

	return WordPoints_Shortcodes::do_shortcode( $atts, null, 'wordpoints_points' );
}

/**
 * Display a list of ways users can earch points.
 *
 * @since 1.4.0
 * @deprecated 1.8.0 Use WordPoints_Shortcodes::do_shortcode() instead.
 *
 * @param array $atts {
 *        The shortcode attributes.
 *
 *        @type string $points_type The type of points to display the list for.
 * }
 *
 * @return string A list of points hooks describing how the user can earn points.
 */
function wordpoints_how_to_get_points_shortcode( $atts ) {

	_deprecated_function( __FUNCTION__, '1.8.0', 'WordPoints_Shortcodes::do_shortcode()' );

	return WordPoints_Shortcodes::do_shortcode( $atts, null, 'wordpoints_how_to_get_points' );
}

// EOF
