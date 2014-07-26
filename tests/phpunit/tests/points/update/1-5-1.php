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
 */
class WordPoints_Points_1_5_1_Update_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test that database table charachter sets are updated.
	 *
	 * @since 1.5.1
	 */
	public function test_db_table_charsets_updated() {

		global $wpdb;

		$wpdb->query( 'ROLLBACK' );

		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
		remove_filter( 'query', array( $this, 'do_not_alter_tables' ) );

		$wpdb->query( "DROP TABLE `{$wpdb->wordpoints_points_logs}`" );
		$wpdb->query( "DROP TABLE `{$wpdb->wordpoints_points_log_meta}`" );

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// Create the tables without special charset.
		dbDelta(
			"CREATE TABLE {$wpdb->wordpoints_points_logs} (
				id BIGINT(20) NOT NULL AUTO_INCREMENT,
				user_id BIGINT(20) NOT NULL,
				log_type VARCHAR(255) NOT NULL,
				points BIGINT(20) NOT NULL,
				points_type VARCHAR(255) NOT NULL,
				text LONGTEXT,
				blog_id SMALLINT(5) UNSIGNED NOT NULL,
				site_id SMALLINT(5) UNSIGNED NOT NULL,
				date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				PRIMARY KEY  (id),
				KEY user_id (user_id),
				KEY points_type (points_type),
				KEY log_type (log_type)
			);
			CREATE TABLE {$wpdb->wordpoints_points_log_meta} (
				meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				log_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
				meta_key VARCHAR(255) DEFAULT NULL,
				meta_value LONGTEXT,
				PRIMARY KEY  (meta_id),
				KEY log_id (log_id),
				KEY meta_key (meta_key)
			);"
		);

		// Simulate the update.
		$this->set_points_db_version();
		wordpoints_points_component_update();

		$this->assertStringEndsWith(
			'DEFAULT CHARSET=utf8'
			, $wpdb->get_var( "SHOW CREATE TABLE `{$wpdb->wordpoints_points_logs}`", 1 )
		);

		$this->assertStringEndsWith(
			'DEFAULT CHARSET=utf8'
			, $wpdb->get_var( "SHOW CREATE TABLE `{$wpdb->wordpoints_points_log_meta}`", 1 )
		);

		$this->start_transaction();
		$this->create_points_type();

		wordpoints_alter_points( $this->factory->user->create(), 10, 'points', 'БВГД' );
		$this->assertEquals( 'БВГД', wordpoints_get_points_logs_query( 'points' )->get( 'row' )->log_type );
	}
}

// EOF
