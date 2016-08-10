<?php

/**
 * A test case for the plugin update to 2.1.0.
 *
 * @package WordPoints\Tests
 * @since 2.1.0
 */

/**
 * Test that the plugin updates to 2.1.0 properly.
 *
 * @since 2.1.0
 *
 * @group update
 *
 * @covers WordPoints_Un_Installer::update_network_to_2_1_0_alpha_3
 * @covers WordPoints_Un_Installer::update_network_to_2_1_0_alpha_3
 */
class WordPoints_2_1_0_Update_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.1.0
	 */
	protected $previous_version = '2.0.0';

	/**
	 * Test that the hooks tables are created.
	 *
	 * @since 2.1.0
	 */
	public function test_hooks_tables_created() {

		global $wpdb;

		$wpdb->query( 'ROLLBACK' );

		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
		remove_filter( 'query', array( $this, 'do_not_alter_tables' ) );

		$wpdb->query( "DROP TABLE `{$wpdb->wordpoints_hook_hits}`" );
		$wpdb->query( "DROP TABLE `{$wpdb->wordpoints_hook_hitmeta}`" );
		$wpdb->query( "DROP TABLE `{$wpdb->wordpoints_hook_periods}`" );

		// Simulate the update.
		$this->update_wordpoints();

		$this->assertDBTableExists( $wpdb->wordpoints_hook_hits );
		$this->assertDBTableExists( $wpdb->wordpoints_hook_hitmeta );
		$this->assertDBTableExists( $wpdb->wordpoints_hook_periods );
	}
}

// EOF
