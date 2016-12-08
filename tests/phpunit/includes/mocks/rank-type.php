<?php

/**
 * Class for a mock rank to use in the tests.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 * @deprecated 2.2.0
 */

/**
 * A mock rank to use in the tests.
 *
 * @since 1.7.0
 * @deprecated 2.2.0 Use WordPoints_PHPUnit_Mock_Rank_Type instead.
 */
class WordPoints_Test_Rank_Type extends WordPoints_PHPUnit_Mock_Rank_Type {

	/**
	 * @since 1.9.1
	 */
	public function __construct( array $args ) {

		_deprecated_function(
			__CLASS__
			, '2.2.0'
			, 'WordPoints_PHPUnit_Mock_Rank_Type'
		);

		parent::__construct( $args );
	}
}

// EOF
