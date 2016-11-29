<?php

/**
 * Test case parent for the points tests.
 *
 * @package WordPoints\Tests\Points
 * @since 1.0.0
 * @deprecated 2.2.0
 */

/**
 * Points unit test case.
 *
 * This test case creates the 'Points' points type on set up so that doesn't have to
 * be repeated in each of the tests.
 *
 * @since 1.0.0
 * @since 1.7.0 Now extends WordPoints_UnitTestCase, not WP_UnitTestCase directly.
 * @since 2.2.0 Now extends WordPoints_PHPUnit_TestCase_Points.
 * @deprecated 2.2.0 Use WordPoints_PHPUnit_TestCase_Points instead.
 */
abstract class WordPoints_Points_UnitTestCase extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * @since 2.2.0
	 */
	public static function setUpBeforeClass() {

		_deprecated_function(
			__CLASS__
			, '2.2.0'
			, 'WordPoints_PHPUnit_TestCase_Points'
		);

		parent::setUpBeforeClass();
	}

} // class WordPoints_Points_UnitTestCase

// EOF
