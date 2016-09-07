<?php

/**
 * A parent test case class for rank tests.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 * @deprecated 2.2.0
 */

/**
 * Parent test case for rank tests.
 *
 * @since 1.7.0
 * @deprecated 2.2.0 Use WordPoints_PHPUnit_TestCase_Ranks instead.
 */
class WordPoints_Ranks_UnitTestCase extends WordPoints_PHPUnit_TestCase_Ranks {

	/**
	 * @since 2.2.0
	 */
	public static function setUpBeforeClass() {

		_deprecated_function(
			__CLASS__
			, '2.2.0'
			, 'WordPoints_PHPUnit_TestCase_Ranks'
		);

		parent::setUpBeforeClass();
	}
}

// EOF
