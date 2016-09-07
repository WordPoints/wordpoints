<?php

/**
 * Class for mocking a breaking updater object.
 *
 * @package WordPoints
 * @since 2.0.0
 * @deprecated 2.2.0
 */

/**
 * Mock breaking updater.
 *
 * Allows access to all protected methods and properties.
 *
 * @since 2.0.0
 * @deprecated 2.2.0 Use WordPoints_PHPUnit_Mock_Breaking_Updater instead.
 */
class WordPoints_Breaking_Updater_Mock
	extends WordPoints_PHPUnit_Mock_Breaking_Updater {

	/**
	 * @since 2.2.0
	 */
	public function __construct( $slug = null, $version = null ) {

		_deprecated_function(
			__CLASS__
			, '2.2.0'
			, 'WordPoints_PHPUnit_Mock_Breaking_Updater'
		);

		parent::__construct( $slug, $version );
	}
}

// EOF
