<?php

/**
 * Shortcodes.
 *
 * @package WordPoints\Points
 * @since 1.0.0
 * @deprecated 2.3.0
 */

/**
 * Handler for the How To Get Points shortcode.
 *
 * @since 1.8.0
 * @deprecated 2.3.0 Use WordPoints_Points_Shortcode_HTGP instead.
 */
class WordPoints_How_To_Get_Points_Shortcode extends WordPoints_Points_Shortcode_HTGP {

	/**
	 * @since 2.3.0
	 */
	public function __construct( $atts, $content, $shortcode = null ) {

		_deprecated_function(
			__METHOD__
			, '2.3.0'
			, 'WordPoints_Points_Shortcode_HTGP::__construct'
		);

		parent::__construct( $atts, $content, $shortcode );
	}
}

/**
 * Handler for the user points shortcode.
 *
 * @since 1.8.0
 * @deprecated 2.3.0 Use WordPoints_Points_Shortcode_HTGP instead.
 */
class WordPoints_User_Points_Shortcode extends WordPoints_Points_Shortcode_User_Points {

	/**
	 * @since 2.3.0
	 */
	public function __construct( $atts, $content, $shortcode = null ) {

		_deprecated_function(
			__METHOD__
			, '2.3.0'
			, 'WordPoints_Points_Shortcode_User_Points::__construct'
		);

		parent::__construct( $atts, $content, $shortcode );
	}
}

/**
 * Handler for the points logs shortcode.
 *
 * @since 1.8.0
 * @deprecated 2.3.0 Use WordPoints_Points_Shortcode_Logs instead.
 */
class WordPoints_Points_Logs_Shortcode extends WordPoints_Points_Shortcode_Logs {

	/**
	 * @since 2.3.0
	 */
	public function __construct( $atts, $content, $shortcode = null ) {

		_deprecated_function(
			__METHOD__
			, '2.3.0'
			, 'WordPoints_Points_Shortcode_Logs::__construct'
		);

		parent::__construct( $atts, $content, $shortcode );
	}
}

/**
 * Handler for the points top shortcode.
 *
 * @since 1.8.0
 * @deprecated 2.3.0 Use WordPoints_Points_Shortcode_Top_Users instead.
 */
class WordPoints_Points_Top_Shortcode extends WordPoints_Points_Shortcode_Top_Users {

	/**
	 * @since 2.3.0
	 */
	public function __construct( $atts, $content, $shortcode = null ) {

		_deprecated_function(
			__METHOD__
			, '2.3.0'
			, 'WordPoints_Points_Shortcode_Top_Users::__construct'
		);

		parent::__construct( $atts, $content, $shortcode );
	}
}

// EOF
