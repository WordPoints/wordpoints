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

// EOF
