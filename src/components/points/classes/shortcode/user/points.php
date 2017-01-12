<?php

/**
 * User points shortcode.
 *
 * @package WordPoints\Points
 * @since   2.3.0
 */

/**
 * Handler for the user points shortcode.
 *
 * @since 1.8.0 As WordPoints_User_Points_Shortcode.
 * @since 2.3.0
 */
class WordPoints_Points_Shortcode_User_Points extends WordPoints_Points_Shortcode {

	/**
	 * @since 1.8.0 As part of WordPoints_User_Points_Shortcode.
	 * @since 2.3.0
	 */
	protected $shortcode = 'wordpoints_points';

	/**
	 * @since 1.8.0 As part of WordPoints_User_Points_Shortcode.
	 * @since 2.3.0
	 */
	protected $pairs = array(
		'user_id'     => 0,
		'points_type' => '',
	);

	/**
	 * @since 1.8.0 As part of WordPoints_User_Points_Shortcode.
	 * @since 2.3.0
	 */
	protected function generate() {

		return wordpoints_get_formatted_points(
			$this->atts['user_id']
			, $this->atts['points_type']
			, 'points-shortcode'
		);
	}
}

// EOF
