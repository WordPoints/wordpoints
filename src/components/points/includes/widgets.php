<?php

/**
 * WordPoints widgets.
 *
 * @package WordPoints\Points
 * @since 1.0.0
 * @deprecated 2.3.0
 */

/**
 * My Points widget.
 *
 * @since 1.0.0
 * @deprecated 2.3.0 Use WordPoints_Points_Widget_User_Points instead.
 */
class WordPoints_My_Points_Widget extends WordPoints_Points_Widget_User_Points {

	/**
	 * @since 1.0.0
	 */
	public function __construct() {

		_deprecated_function(
			__METHOD__
			, '2.3.0'
			, 'WordPoints_Points_Widget_User_Points::__construct'
		);

		parent::__construct();
	}
}

/**
 * WordPoints Top Users Widget.
 *
 * @since 1.0.0
 * @deprecated 2.3.0 Use WordPoints_Points_Widget_Top_Users instead.
 */
class WordPoints_Top_Users_Points_Widget extends WordPoints_Points_Widget_Top_Users {

	/**
	 * @since 1.0.0
	 */
	public function __construct() {

		_deprecated_function(
			__METHOD__
			, '2.3.0'
			, 'WordPoints_Points_Widget_Top_Users::__construct'
		);

		parent::__construct();
	}
}

/**
 * Recent points logs widget.
 *
 * @since 1.0.0
 * @deprecated 2.3.0 Use WordPoints_Points_Widget_Logs instead.
 */
class WordPoints_Points_Logs_Widget extends WordPoints_Points_Widget_Logs {

	/**
	 * @since 1.0.0
	 */
	public function __construct() {

		_deprecated_function(
			__METHOD__
			, '2.3.0'
			, 'WordPoints_Points_Widget_Logs::__construct'
		);

		parent::__construct();
	}
}

// EOF
