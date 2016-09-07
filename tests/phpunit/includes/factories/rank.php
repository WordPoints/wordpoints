<?php

/**
 * A rank factory for use in the unit tests.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 * @deprecated 2.2.0
 */

/**
 * Factory for ranks.
 *
 * @since 1.7.0
 * @deprecated 2.2.0 Use WordPoints_PHPUnit_Factory_For_Rank instead.
 */
class WordPoints_UnitTest_Factory_For_Rank
	extends WordPoints_PHPUnit_Factory_For_Rank {

	/**
	 * @since 1.7.0
	 */
	public function __construct( $factory = null ) {

		parent::__construct( $factory );

		_deprecated_function(
			__CLASS__
			, '2.2.0'
			, 'WordPoints_PHPUnit_Factory_For_Rank'
		);
	}
}

// EOF
