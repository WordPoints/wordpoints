<?php

/**
 * Base admin test case class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Parent test case for admin-side code tests.
 *
 * @since 2.1.0
 */
abstract class WordPoints_PHPUnit_TestCase_Admin extends WordPoints_PHPUnit_TestCase {

	/**
	 * Whether the admin-side code has been included yet.
	 *
	 * @since 2.1.0
	 *
	 * @var bool
	 */
	protected static $included_files = false;

	/**
	 * @since 2.1.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		if ( ! self::$included_files ) {

			/**
			 * WordPoints administration-side code.
			 *
			 * @since 2.1.0
			 */
			require_once( WORDPOINTS_DIR . '/admin/admin.php' );

			self::$included_files = true;
		}
	}
}

// EOF
