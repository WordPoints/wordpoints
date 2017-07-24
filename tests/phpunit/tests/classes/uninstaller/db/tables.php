<?php

/**
 * Test case for WordPoints_Uninstaller_DB_Tables.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Uninstaller_DB_Tables.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Uninstaller_DB_Tables
 */
class WordPoints_Uninstaller_DB_Tables_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.4.0
	 */
	public function setUp() {

		parent::setUp();

		// We use real tables, because we can't check if a temporary table exists.
		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
	}

	/**
	 * @since 2.4.0
	 */
	public function tearDown() {

		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}test" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}test" );

		parent::tearDown();
	}

	/**
	 * Test uninstalling a table.
	 *
	 * @since 2.4.0
	 */
	public function test_uninstalls_table() {

		global $wpdb;

		$wpdb->query( "CREATE TABLE `{$wpdb->prefix}test` ( `id` BIGINT );" );

		$uninstaller = new WordPoints_Uninstaller_DB_Tables( array( 'test' ) );
		$uninstaller->run();

		$this->assertNull( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}test'" ) );
	}

	/**
	 * Test uninstalling a table with the base prefix.
	 *
	 * @since 2.4.0
	 */
	public function test_uninstalls_table_base_prefix() {

		global $wpdb;

		$wpdb->query( "CREATE TABLE `{$wpdb->base_prefix}test` ( `id` BIGINT );" );

		$uninstaller = new WordPoints_Uninstaller_DB_Tables( array( 'test' ), 'base' );
		$uninstaller->run();

		$this->assertNull( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->base_prefix}test'" ) );
	}

	/**
	 * Test uninstalling a table that doesn't exist.
	 *
	 * @since 2.4.0
	 */
	public function test_table_not_exists() {

		global $wpdb;

		$uninstaller = new WordPoints_Uninstaller_DB_Tables( array( 'test' ) );
		$uninstaller->run();

		$this->assertNull( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}test'" ) );
	}
}

// EOF
