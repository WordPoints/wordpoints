<?php

/**
 * A parent test case class for the Ajax tests.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 * @deprecated 2.1.0
 */

/**
 * Parent testcase for testing Ajax callbacks.
 *
 * @since 1.7.0
 * @deprecated 2.1.0 Use WordPoints_PHPUnit_TestCase_Ajax instead.
 */
abstract class WordPoints_Ajax_UnitTestCase
	extends WordPoints_PHPUnit_TestCase_Ajax {

	/**
	 * @since 2.2.0
	 */
	public static function setUpBeforeClass() {

		_deprecated_function(
			__CLASS__
			, '2.1.0'
			, 'WordPoints_PHPUnit_TestCase_Ajax'
		);

		parent::setUpBeforeClass();
	}
}

// EOF
