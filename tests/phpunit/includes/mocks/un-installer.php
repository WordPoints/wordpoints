<?php

/**
 * Class for mocking an un/installer object.
 *
 * @package WordPoints
 * @since 2.0.0
 * @deprecated 2.2.0
 */

/**
 * Mock un/installer.
 *
 * Allows access to all protected methods and properties.
 *
 * @since 2.0.0
 * @deprecated 2.2.0 Use WordPoints_PHPUnit_Mock_Un_Installer instead.
 */
class WordPoints_Un_Installer_Mock extends WordPoints_PHPUnit_Mock_Un_Installer {

	/**
	 * @since 2.2.0
	 */
	public function __construct( $slug = null, $version = null ) {

		_deprecated_function(
			__CLASS__
			, '2.2.0'
			, 'WordPoints_PHPUnit_Mock_Un_Installer'
		);

		parent::__construct( $slug, $version );
	}
}

// EOF
