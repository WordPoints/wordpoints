<?php

/**
 * A test case parent for the points AJAX tests.
 *
 * @package WordPoints\Tests
 * @since 1.3.0
 * @deprecated 2.2.0
 */

/**
 * AJAX points unit test case.
 *
 * This test case creates the 'Points' points type on set up so that doesn't have to
 * be repeated in each of the tests.
 *
 * @since 1.3.0
 * @deprecated 2.2.0 Use WordPoints_PHPUnit_TestCase_Ajax_Points instead.
 */
abstract class WordPoints_Points_AJAX_UnitTestCase extends WordPoints_PHPUnit_TestCase_Ajax_Points {

	/**
	 * Set up before the tests begin.
	 *
	 * @since 1.3.0
	 * @deprecated 2.2.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		_deprecated_function(
			__CLASS__
			, '2.2.0'
			, 'WordPoints_PHPUnit_TestCase_Ajax_Points'
		);
	}

} // class WordPoints_Points_AJAX_UnitTestCase

// EOF
