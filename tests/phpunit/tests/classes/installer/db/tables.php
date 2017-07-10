<?php

/**
 * Test case for WordPoints_Installer_DB_Tables.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Installer_DB_Tables.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Installer_DB_Tables
 */
class WordPoints_Installer_DB_Tables_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.4.0
	 */
	public function setUp() {

		parent::setUp();

		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
	}

	/**
	 * @since 2.4.0
	 */
	public function tearDown() {

		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}test" );
		$wpdb->query( 'DROP TABLE IF EXISTS prefix_test' );

		parent::tearDown();
	}

	/**
	 * Tests that it creates the database tables.
	 *
	 * @since 2.4.0
	 */
	public function test_creates_db_tables() {

		global $wpdb;

		$installer = new WordPoints_Installer_DB_Tables(
			array( 'test' => 'id BIGINT(20) NOT NULL' )
		);

		$installer->run();

		$table_name = $wpdb->base_prefix . 'test';

		$this->assertDBTableExists( $table_name );

		$this->assertStringMatchesFormat(
			"CREATE TABLE `{$table_name}` ("
				. "\n  `id` bigint(20) NOT NULL"
				. "\n) %a{$wpdb->charset} %a{$wpdb->collate}"
			, $wpdb->get_var( "SHOW CREATE TABLE `{$table_name}`", 1 )
		);
	}

	/**
	 * Tests that it creates database tables with the specified pefix.
	 *
	 * @since 2.4.0
	 */
	public function test_creates_db_tables_with_prefix() {

		global $wpdb;

		$installer = new WordPoints_Installer_DB_Tables(
			array( 'test' => 'id BIGINT(20) NOT NULL' )
			, 'prefix_'
		);

		$installer->run();

		$table_name = 'prefix_test';

		$this->assertDBTableExists( $table_name );

		$this->assertStringMatchesFormat(
			"CREATE TABLE `{$table_name}` ("
				. "\n  `id` bigint(20) NOT NULL"
				. "\n) %a{$wpdb->charset} %a{$wpdb->collate}"
			, $wpdb->get_var( "SHOW CREATE TABLE `{$table_name}`", 1 )
		);
	}
}

// EOF
