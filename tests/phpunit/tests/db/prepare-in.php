<?php

/**
 * A test case for the wordpoints_prepare__in() function.
 *
 * Since 1.0.0 this testcase was bundled with another one in the db.php file.
 *
 * @package WordPoints\Tests\DB
 * @since 1.5.0
 */

/**
 * Test the wordpoints_prepare__in() function.
 *
 * @since 1.0.0
 */
class WordPoints_Prepare_In_Test extends WP_UnitTestCase {

	/**
	 * Test behavior on invalid $_in parameter.
	 *
	 * @since 1.0.0
	 *
	 * @expectedIncorrectUsage wordpoints_prepare__in
	 */
	public function test_invalid_in() {

		$result = wordpoints_prepare__in( array(), '%d' );
		$this->assertFalse( $result );
	}

	/**
	 * Test behavior with an invalid $format.
	 *
	 * @since 1.0.0
	 *
	 * @expectedIncorrectUsage wordpoints_prepare__in
	 */
	public function test_invalid_format() {

		$result = wordpoints_prepare__in( array( 1, 2, 3 ), '%D' );
		$this->assertEquals( "'1','2','3'", $result );
	}

	/**
	 * Test behavior with proper parameters.
	 *
	 * @since 1.0.0
	 */
	public function test_valid_params() {

		$result_d = wordpoints_prepare__in( array( 1, 2, 3 ), '%d' );
		$this->assertEquals( '1,2,3', $result_d );

		$result_s = wordpoints_prepare__in( array( 'a', 'b', 'c' ), '%s' );
		$this->assertEquals( "'a','b','c'", $result_s );

		// We can't reliably test %f, becuase the precision is system dependant.
	}
}

// EOF
