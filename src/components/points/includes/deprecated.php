<?php

/**
 * Deprecated code for the points component.
 *
 * @package WordPoints\Points
 * @since 1.2.0
 */

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

/*#@+*
 * @deprecated 1.2.0
 */

/**
 * Points types class.
 *
 * @since 1.0.0
 * @deprecated Use the respective functions instead.
 */
class WordPoints_Points_Types {

	//
	// Public Methods.
	//

	/**
	 * Get all points types.
	 *
	 * @since 1.0.0
	 * @deprecated Use wordpoints_get_points_types() instead.
	 */
	public static function get() {

		_deprecated_function( __METHOD__, '1.2.0', 'wordpoints_get_points_types' );

		return wordpoints_get_points_types();
	}

	/**
	 * Get the settings for a single points type.
	 *
	 * @since 1.0.0
	 * @deprecated Use wordpoints_get_points_type() instead.
	 */
	public static function get_type( $slug ) {

		_deprecated_function( __METHOD__, '1.2.0', 'wordpoints_get_points_type' );

		return wordpoints_get_points_type( $slug );
	}

	/**
	 * Get a setting for a type of points.
	 *
	 * @since 1.0.0
	 * @deprecated Use wordpoints_get_points_type_setting() instead.
	 */
	public static function get_type_setting( $slug, $setting ) {

		_deprecated_function( __METHOD__, '1.2.0', 'wordpoints_get_points_type_setting' );

		return wordpoints_get_points_type_setting( $slug, $setting );
	}

	/**
	 * Create a new type of points.
	 *
	 * @since 1.0.0
	 * @deprecated Use wordpoints_add_points_type() instead.
	 */
	public static function add_type( $settings ) {

		_deprecated_function( __METHOD__, '1.2.0', 'wordpoints_add_points_type' );

		return wordpoints_add_points_type( $settings );
	}

	/**
	 * Update the settings for a type of points.
	 *
	 * @since 1.0.0
	 * @deprecated Use wordpoints_add_points_type() instead.
	 */
	public static function update_type( $slug, $settings ) {

		_deprecated_function( __METHOD__, '1.2.0', 'wordpoints_update_points_type' );

		return wordpoints_update_points_type( $slug, $settings );
	}

	/**
	 * Check if a type of points exists.
	 *
	 * @since 1.0.0
	 * @deprecated Use wordpoints_is_points_type() instead.
	 */
	public static function is_type( $slug ) {

		_deprecated_function( __METHOD__, '1.2.0', 'wordpoints_is_points_type' );

		return wordpoints_is_points_type( $slug );
	}

	/**
	 * Delete a points type.
	 *
	 * @since 1.0.0
	 * @deprecated Use wordpoints_delete_points_type() instead.
	 */
	public static function delete_type( $slug ) {

		_deprecated_function( __METHOD__, '1.2.0', 'wordpoints_delete_points_type' );

		return wordpoints_delete_points_type( $slug );
	}

	/**
	 * Set up the $types private var.
	 *
	 * @since 1.0.0
	 * @deprecated No longer needed.
	 */
	public static function _reset() {

		_deprecated_function( __METHOD__, '1.2.0' );
	}
}

/*#@-*/

/**
 * Add 'set_wordpoints_points' psuedo capability.
 *
 * Filters a user's capabilities, e.g., when current_user_can() is called. Adds
 * the pseudo-capability 'set_wordpoints_points', which can be checked for as
 * with any other capability:
 *
 * current_user_can( 'set_wordpoints_points' );
 *
 * Default is that this will be true if the user can 'manage_options'. Override
 * this by adding your own filter with a lower priority (e.g., 15), and
 * manipulating the $all_capabilities array.
 *
 * @since 1.0.0
 * @since 1.2.0 Adds the capability 'manage_wordpoints_points_types'.
 *
 * @deprecated 1.3.0
 *
 * @see http://codex.wordpress.org/Plugin_API/Filter_Reference/user_has_cap
 *
 * @param array $all_capabilities All of the capabilities of a user.
 */
function wordpoints_points_user_cap_filter( $all_capabilities ) {

	return $all_capabilities;
}

/**
 * Uninstall the points component.
 *
 * @since 1.0.0
 * @deprecated 1.7.0 Use WordPoints_Components::uninstall( 'points' ) instead.
 */
function wordpoints_points_component_uninstall() {

	_deprecated_function(
		__FUNCTION__
		, '1.7.0'
		, "WordPoints_Components::uninstall( 'points' )"
	);

	/**
	 * Uninstall the component.
	 *
	 * @since 1.0.0
	 */
	require WORDPOINTS_DIR . 'components/points/uninstall.php';
}

/**
 * Install the points component.
 *
 * @since 1.0.0
 * @deprecated 1.8.0 Use WordPoints_Components::activate( 'points' ) instead.
 */
function wordpoints_points_component_activate() {

	_deprecated_function(
		__FUNCTION__
		, '1.8.0'
		, "WordPoints_Components::activate( 'points' )"
	);

	/**
	 * The points component installer.
	 *
	 * @since 1.8.0
	 */
	require_once WORDPOINTS_DIR . 'components/points/includes/class-un-installer.php';

	$installer = new WordPoints_Points_Un_Installer;
	$installer->install( is_wordpoints_network_active() );
}


/**
 * Update the points component.
 *
 * @since 1.2.0
 * @deprecated 1.8.0 Use WordPoints_Components::maybe_do_updates() instead.
 */
function wordpoints_points_component_update() {

	_deprecated_function(
		__FUNCTION__
		, '1.8.0'
		, 'WordPoints_Components::maybe_do_updates()'
	);

	WordPoints_Components::maybe_do_updates();
}


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
