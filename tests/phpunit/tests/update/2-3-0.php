<?php

/**
 * A test case for the plugin update to 2.3.0.
 *
 * @package WordPoints\Tests
 * @since 2.3.0
 */

/**
 * Test that the plugin updates to 2.3.0 properly.
 *
 * @since 2.3.0
 *
 * @group update
 *
 * @covers WordPoints_Installable_Core::get_update_routine_factories
 */
class WordPoints_2_3_0_Update_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.3.0
	 */
	protected $previous_version = '2.2.0';

	/**
	 * Test that the hook hit table is updated.
	 *
	 * @since 2.3.0
	 */
	public function test_hooks_hit_table_primary_arg_guid_column_renamed() {

		global $wpdb;

		$wpdb->query( 'ROLLBACK' );

		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
		remove_filter( 'query', array( $this, 'do_not_alter_tables' ) );

		$wpdb->query( "DROP TABLE `{$wpdb->wordpoints_hook_hits}`" );

		// Create the table with the old schema.
		$wpdb->query(
			"
				CREATE TABLE {$wpdb->wordpoints_hook_hits} (
					id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					action_type VARCHAR(255) NOT NULL,
					primary_arg_guid TEXT NOT NULL,
					event VARCHAR(255) NOT NULL,
					reactor VARCHAR(255) NOT NULL,
					reaction_mode VARCHAR(255) NOT NULL,
					reaction_store VARCHAR(255) NOT NULL,
					reaction_context_id TEXT NOT NULL,
					reaction_id BIGINT(20) UNSIGNED NOT NULL,
					date DATETIME NOT NULL,
					PRIMARY KEY  (id)
				)
			"
		);

		$this->assertSame(
			'primary_arg_guid'
			, $wpdb->get_var(
				"SHOW COLUMNS FROM `{$wpdb->wordpoints_hook_hits}` LIKE 'primary_arg_guid'"
			)
		);

		$this->assertNull(
			$wpdb->get_var(
				"SHOW COLUMNS FROM `{$wpdb->wordpoints_hook_hits}` LIKE 'signature_arg_guids'"
			)
		);

		// Simulate the update.
		$this->update_wordpoints();

		$this->assertSame(
			'signature_arg_guids'
			, $wpdb->get_var(
				"SHOW COLUMNS FROM `{$wpdb->wordpoints_hook_hits}` LIKE 'signature_arg_guids'"
			)
		);

		$this->assertNull(
			$wpdb->get_var(
				"SHOW COLUMNS FROM `{$wpdb->wordpoints_hook_hits}` LIKE 'primary_arg_guid'"
			)
		);
	}
}

// EOF
