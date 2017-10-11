<?php

/**
 * Rank shortcode class.
 *
 * @package WordPoints\Ranks
 * @since   2.3.0
 */

/**
 * Handler for rank shortcodes.
 *
 * @since 1.8.0
 */
abstract class WordPoints_Rank_Shortcode extends WordPoints_Shortcode {

	/**
	 * @since 1.8.0
	 */
	protected function verify_atts() {

		if ( isset( $this->pairs['rank_group'] ) && empty( $this->atts['rank_group'] ) ) {

			return sprintf(
				// translators: 1. Attribute name; 2. Shortcode name.
				__( 'The &#8220;%1$s&#8221; attribute of the %2$s shortcode must be a rank group slug.', 'wordpoints' )
				, 'rank_group'
				, "<code>[{$this->shortcode}]</code>"
			);
		}

		return parent::verify_atts();
	}
}

// EOF
