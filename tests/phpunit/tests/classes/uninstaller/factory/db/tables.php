<?php

/**
 * Test case for WordPoints_Uninstaller_Factory_DB_Tables.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Uninstaller_Factory_DB_Tables.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Uninstaller_Factory_DB_Tables
 */
class WordPoints_Uninstaller_Factory_DB_Tables_Test extends WordPoints_PHPUnit_TestCase {

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
	 * Tests getting the uninstaller for a single site.
	 *
	 * @since 2.4.0
	 */
	public function test_get_for_single() {

		global $wpdb;

		$factory = new WordPoints_Uninstaller_Factory_DB_Tables(
			array( 'single' => array( 'test' ) )
		);

		$uninstallers = $factory->get_for_single();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_DB_Tables', $uninstallers[0] );

		$wpdb->query( "CREATE TABLE `{$wpdb->base_prefix}test` ( `id` BIGINT );" );

		$uninstallers[0]->run();

		$this->assertNull( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->base_prefix}test'" ) );
	}

	/**
	 * Tests getting the uninstaller for a single site.
	 *
	 * @since 2.4.0
	 */
	public function test_get_for_site() {

		global $wpdb;

		$factory = new WordPoints_Uninstaller_Factory_DB_Tables(
			array( 'site' => array( 'test' ) )
		);

		$uninstallers = $factory->get_for_site();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_DB_Tables', $uninstallers[0] );

		$wpdb->query( "CREATE TABLE `{$wpdb->prefix}test` ( `id` BIGINT );" );

		$uninstallers[0]->run();

		$this->assertNull( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}test'" ) );
	}

	/**
	 * Tests getting the uninstaller for a network site.
	 *
	 * @since 2.4.0
	 */
	public function test_get_for_network() {

		global $wpdb;

		$factory = new WordPoints_Uninstaller_Factory_DB_Tables(
			array( 'network' => array( 'test' ) )
		);

		$uninstallers = $factory->get_for_network();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_DB_Tables', $uninstallers[0] );

		$wpdb->query( "CREATE TABLE `{$wpdb->base_prefix}test` ( `id` BIGINT );" );

		$uninstallers[0]->run();

		$this->assertNull( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->base_prefix}test'" ) );
	}

	/**
	 * Tests getting the uninstallers when there are no tables to uninstall.
	 *
	 * @since 2.4.0
	 */
	public function test_no_tables() {

		$factory = new WordPoints_Uninstaller_Factory_DB_Tables(
			array( 'single' => array() )
		);

		$uninstallers = $factory->get_for_single();

		$this->assertSame( array(), $uninstallers );
	}

	/**
	 * Tests that it maps context shortcuts.
	 *
	 * @since 2.4.0
	 */
	public function test_maps_shortcuts() {

		$factory = new WordPoints_Uninstaller_Factory_DB_Tables(
			array( 'local' => array( 'test' ) )
		);

		$this->assertCount( 1, $factory->get_for_single() );
		$this->assertCount( 1, $factory->get_for_site() );
		$this->assertCount( 0, $factory->get_for_network() );
	}
}

// EOF
