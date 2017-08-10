<?php

/**
 * A test case for the ranks component update to 2.0.0.
 *
 * @package WordPoints\Tests
 * @since 2.0.0
 */

/**
 * Test that the ranks component updates to 2.0.0 properly.
 *
 * @since 2.0.0
 *
 * @group ranks
 * @group update
 *
 * @covers WordPoints_Ranks_Installable::get_update_routines
 * @covers WordPoints_Ranks_Updater_2_0_0_Tables
 */
class WordPoints_Ranks_2_0_0_Update_Test extends WordPoints_PHPUnit_TestCase_Ranks {

	/**
	 * @since 2.0.0
	 */
	protected $previous_version = '1.10.0';

	/**
	 * @since 2.4.0
	 */
	public function get_db_schema() {

		global $wpdb;

		// The schema was changed in 2.4.0, and so we need to create the tables with
		// the old schema, or we'll get errors from the 2.4.0 update code.
		$tables = array(
			'wordpoints_ranks' => '
 					id BIGINT(20) NOT NULL AUTO_INCREMENT,
 					name VARCHAR(255) NOT NULL,
 					type VARCHAR(255) NOT NULL,
 					rank_group VARCHAR(255) NOT NULL,
 					blog_id SMALLINT(5) UNSIGNED NOT NULL,
 					site_id SMALLINT(5) UNSIGNED NOT NULL,
 					PRIMARY KEY  (id),
 					KEY type (type(191)),
 					KEY site (blog_id,site_id)',
			'wordpoints_rankmeta' => '
 					meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
 					wordpoints_rank_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
 					meta_key VARCHAR(255) DEFAULT NULL,
 					meta_value LONGTEXT,
 					PRIMARY KEY  (meta_id),
 					KEY wordpoints_rank_id (wordpoints_rank_id)',
			'wordpoints_user_ranks' => '
 					id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
					rank_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
					PRIMARY KEY  (id)',
		);

		$this->db_schema = '';

		foreach ( $tables as $table_name => $table_schema ) {
			$this->db_schema .= "CREATE TABLE {$wpdb->base_prefix}{$table_name} (
					{$table_schema}
				);\n";
		}

		return $this->db_schema;
	}

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
