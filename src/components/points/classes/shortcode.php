<?php

/**
 * Points shortcode class.
 *
 * @package WordPoints\Points
 * @since   2.3.0
 */

/**
 * Base shortcode class for the points component's shortcodes.
 *
 * The points shortcodes can extend this class to get automatic validation of the
 * points_type attribute, if it is used.
 *
 * @since 1.8.0
 */
abstract class WordPoints_Points_Shortcode extends WordPoints_Shortcode {

	/**
	 * @since 1.8.0
	 */
	protected function verify_atts() {

		if ( isset( $this->pairs['points_type'] ) ) {

			$points_type = $this->get_points_type();

			if ( is_wp_error( $points_type ) ) {
				return $points_type;
			}

			$this->atts['points_type'] = $points_type;
		}

		return parent::verify_atts();
	}

	/**
	 * Get the points type from a shortcode's attributes.
	 *
	 * For use with shortcodes which display content relative to a specific points
	 * type. If the points type isn't valid, the default points type will be used if
	 * one is set. In the case that no default points type is set, a WP_Error will
	 * be returned.
	 *
	 * @since 1.8.0
	 *
	 * @return string|WP_Error The points type slug, or a WP_Error on failure.
	 */
	protected function get_points_type() {

		$points_type = $this->atts['points_type'];

		if ( ! wordpoints_is_points_type( $points_type ) ) {

			$points_type = wordpoints_get_default_points_type();

			if ( ! $points_type ) {

				$points_type = new WP_Error(
					'wordpoints_shortcode_no_points_type'
					, sprintf(
						// translators: 1. Attribute name; 2. Shortcode name; 3. Example of proper usage.
						__( 'The &#8220;%1$s&#8221; attribute of the %2$s shortcode must be the slug of a points type. Example: %3$s.', 'wordpoints' )
						, 'points_type'
						, '<code>[' . sanitize_key( $this->shortcode ) . ']</code>'
						, '<code>[' . sanitize_key( $this->shortcode ) . ' points_type="points"]</code>'
					)
				);
			}
		}

		/**
		 * Filter the points type for a shortcode.
		 *
		 * @since 1.8.0
		 *
		 * @param string|WP_Error $points_type The points type, or a WP_Error on failure.
		 * @param array           $atts        The shortcode attributes.
		 * @param string          $shortcode   The shortcode.
		 */
		return apply_filters( 'wordpoints_shortcode_points_type', $points_type, $this->atts, $this->shortcode );
	}
}

// EOF
