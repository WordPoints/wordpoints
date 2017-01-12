<?php

/**
 * Top users shortcode class.
 *
 * @package WordPoints\Points
 * @since   2.3.0
 */

/**
 * Handler for the points top shortcode.
 *
 * @since 1.8.0 As WordPoints_Points_Top_Shortcode.
 * @since 2.3.0
 */
class WordPoints_Points_Shortcode_Top_Users extends WordPoints_Points_Shortcode {

	/**
	 * @since 1.8.0 As part of WordPoints_Points_Top_Shortcode.
	 * @since 2.3.0
	 */
	protected $shortcode = 'wordpoints_points_top';

	/**
	 * @since 1.8.0 As part of WordPoints_Points_Top_Shortcode.
	 * @since 2.3.0
	 */
	protected $pairs = array(
		'users'       => 10,
		'points_type' => '',
	);

	/**
	 * @since 1.8.0 As part of WordPoints_Points_Top_Shortcode.
	 * @since 2.3.0
	 */
	protected function verify_atts() {

		if ( ! wordpoints_posint( $this->atts['users'] ) ) {
			return sprintf(
				// translators: 1. Attribute name; 2. Shortcode name; 3. Example of proper usage.
				__( 'The &#8220;%1$s&#8221; attribute of the %2$s shortcode must be a positive integer. Example: %3$s.', 'wordpoints' )
				, 'users'
				, '<code>[' . sanitize_key( $this->shortcode ) . ']</code>'
				, '<code>[' . sanitize_key( $this->shortcode ) . ' <b>users="10"</b> type="points"]</code>'
			);
		}

		return parent::verify_atts();
	}

	/**
	 * @since 1.8.0 As part of WordPoints_Points_Top_Shortcode.
	 * @since 2.3.0
	 */
	protected function generate() {

		ob_start();
		wordpoints_points_show_top_users(
			$this->atts['users']
			, $this->atts['points_type']
			, 'shortcode'
		);

		return ob_get_clean();
	}
}

// EOF
