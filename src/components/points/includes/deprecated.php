<?php

/**
 * Deprecated code for the points component.
 *
 * @package WordPoints\Points
 * @since 1.2.0
 */

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
