<?php

/**
 * Test database helpers in functions.php.
 *
 * @package WordPoints\Tests\DB
 * @since 1.0.0
 */

/**
 * Test the wordpoints_db_table_exists() function.
 *
 * @since 1.0.0
 */
class WordPoints_Table_Exists_Test extends WP_UnitTestCase {

	/**
	 * Test behavior if the table exists.
	 *
	 * @since 1.0.0
	 */
	public function test_exists() {

		global $wpdb;

		$exists = wordpoints_db_table_exists( "$wpdb->users" );
		$this->assertTrue( $exists );
	}

	/**
	 * Test behavior if table doesn't exist.
	 *
	 * @since 1.0.0
	 */
	public function test_not_exists() {

		$exists = wordpoints_db_table_exists( 'wordpoints_not_exists' );
		$this->assertFalse( $exists );
	}
}

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
	 */
	public function test_invalid_in() {

		$result = @wordpoints_prepare__in( 'foo', '%d' );
		$this->assertFalse( $result );

		$result = @wordpoints_prepare__in( array(), '%d' );
		$this->assertFalse( $result );
	}

	/**
	 * Test behavior with an invalid $format.
	 *
	 * @since 1.0.0
	 */
	public function test_invalid_format() {

		$result = @wordpoints_prepare__in( array( 1, 2, 3 ), '%D' );
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
