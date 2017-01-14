<?php

/**
 * Shortcodes of the ranks component.
 *
 * @package WordPoints\Ranks
 * @since 1.8.0
 * @deprecated 2.3.0
 */

/**
 * Handler for the user rank shortcode.
 *
 * @since 1.8.0
 * @deprecated 2.3.0 Use WordPoints_Rank_Shortcode_User_Rank instead.
 */
class WordPoints_User_Rank_Shortcode extends WordPoints_Rank_Shortcode_User_Rank {

	/**
	 * @since 2.3.0
	 */
	public function __construct( $atts, $content, $shortcode = null ) {

		_deprecated_function(
			__METHOD__
			, '2.3.0'
			, 'WordPoints_Rank_Shortcode_User_Rank::__construct'
		);

		parent::__construct( $atts, $content, $shortcode );
	}
}

// EOF
