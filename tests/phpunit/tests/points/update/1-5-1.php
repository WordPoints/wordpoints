<?php

/**
 * A test case for the points component update to 1.5.1.
 *
 * @package WordPoints\Tests
 * @since 1.5.1
 */

/**
 * Test that the points component updates to 1.5.1 properly.
 *
 * @since 1.5.1
 *
 * @group points
 * @group update
 *
 * @covers WordPoints_Points_Un_Installer::update_network_to_1_5_1
 * @covers WordPoints_Points_Un_Installer::update_single_to_1_5_1
 *
 * @expectedDeprecated WordPoints_Comment_Removed_Points_Hook::__construct
 * @expectedDeprecated WordPoints_Post_Delete_Points_Hook::__construct
 */
class WordPoints_Points_1_5_1_Update_Test extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * @since 2.0.0
	 */
	protected $previous_version = '1.5.0';

	/**
	 * Test that database table character sets are updated.
	 *
	 * @since 1.5.1
	 *
	 * @covers WordPoints_Points_Un_Installer::update_network_to_1_5_1
	 * @covers WordPoints_Points_Un_Installer::update_single_to_1_5_1
	 */
	public function test_db_table_charsets_updated() {

		global $wpdb;

		if ( 'latin1' === $wpdb->charset ) {
			$this->markTestSkipped( 'wpdb database charset must not be latin1.' );
		}

		$this->create_tables_with_charset( 'latin1' );

		// Simulate the update.
		$this->update_component();

		$this->assertTablesHaveCharset( $wpdb->charset );

		$this->start_transaction();
		$this->create_points_type();

		wordpoints_alter_points( $this->factory->user->create(), 10, 'points', 'БВГД' );
		$this->assertSame( 'БВГД', wordpoints_get_points_logs_query( 'points' )->get( 'row' )->log_type );
	}
}

// EOF
