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
