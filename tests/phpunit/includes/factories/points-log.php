<?php

/**
 * A points log factory for use in the unit tests.
 *
 * @package WordPoints\Tests
 * @since 1.6.0
 * @deprecated 2.2.0
 */

/**
 * Factory for points logs.
 *
 * @since 1.6.0
 * @deprecated 2.2.0 Use WordPoints_PHPUnit_Factory_For_Points_Log instead.
 */
class WordPoints_UnitTest_Factory_For_Points_Log
	extends WordPoints_PHPUnit_Factory_For_Points_Log {

	/**
	 * @since 1.6.0
	 */
	public function __construct( $factory = null ) {

		parent::__construct( $factory );

		_deprecated_function(
			__CLASS__
			, '2.2.0'
			, 'WordPoints_PHPUnit_Factory_For_Points_Log'
		);
	}
}

// EOF
