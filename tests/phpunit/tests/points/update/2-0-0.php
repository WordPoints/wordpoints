<?php

/**
 * A test case for the points component update to 2.0.0.
 *
 * @package WordPoints\Tests
 * @since 2.0.0
 */

/**
 * Test that the points component updates to 2.0.0 properly.
 *
 * @since 2.0.0
 *
 * @group points
 * @group update
 *
 * @covers WordPoints_Points_Installable::get_update_routine_factories
 * @covers WordPoints_Points_Updater_2_0_0_Tables
 */
class WordPoints_Points_2_0_0_Update_Test extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * @since 2.0.0
	 */
	protected $previous_version = '1.10.0';

	/**
	 * Test that database table character sets are updated.
	 *
	 * @since 2.0.0
	 */
	public function test_db_table_charsets_updated() {

		global $wpdb;

		if ( 'utf8mb4' !== $wpdb->charset ) {
			$this->markTestSkipped( 'wpdb database charset must be utf8mb4.' );
		}

		$this->create_tables_with_charset( 'utf8' );

		// Simulate the update.
		$this->update_component();

		$this->assertTablesHaveCharset( 'utf8mb4' );
	}
}

// EOF
