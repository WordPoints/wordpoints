<?php

/**
 * A test case for the plugin update to 2.4.0.
 *
 * @package WordPoints\Tests
 * @since 2.4.0
 */

/**
 * Test that the plugin updates to 2.4.0 properly.
 *
 * @since 2.4.0
 *
 * @group update
 *
 * @covers WordPoints_Installable_Core::get_update_routine_factories
 * @covers WordPoints_Updater_Core_2_4_0_Extensions_Directory_Rename
 * @covers WordPoints_Updater_Hook_Hits_Signature_Arg_GUIDs_Int
 */
class WordPoints_2_4_0_Update_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.4.0
	 */
	protected $previous_version = '2.3.0';

	/**
	 * Test that the new custom caps are added.
	 *
	 * @since 2.4.0
	 */
	public function test_adds_new_custom_caps() {

		wordpoints_remove_custom_caps( array_keys( wordpoints_get_custom_caps() ) );

		$administrator = get_role( 'administrator' );
		$this->assertFalse( $administrator->has_cap( 'install_wordpoints_extensions' ) );
		$this->assertFalse( $administrator->has_cap( 'activate_wordpoints_extensions' ) );
		$this->assertFalse( $administrator->has_cap( 'delete_wordpoints_extensions' ) );
		$this->assertFalse( $administrator->has_cap( 'update_wordpoints_extensions' ) );

		// Simulate the update.
		$this->update_wordpoints();

		// Check that the capabilities were added.
		$administrator = get_role( 'administrator' );
		$this->assertTrue( $administrator->has_cap( 'install_wordpoints_extensions' ) );
		$this->assertTrue( $administrator->has_cap( 'activate_wordpoints_extensions' ) );
		$this->assertTrue( $administrator->has_cap( 'delete_wordpoints_extensions' ) );
		$this->assertTrue( $administrator->has_cap( 'update_wordpoints_extensions' ) );
	}

	/**
	 * Test that the WordPoints.org extension is deactivated.
	 *
	 * @since 2.4.0
	 */
	public function test_deactivates_wordpoints_org_extension() {

		add_filter( 'wordpoints_extensions_dir', 'wordpoints_phpunit_extensions_dir' );

		$extension = 'wordpointsorg/wordpointsorg.php';

		wordpoints_activate_module( $extension );

		$this->assertTrue( is_wordpoints_module_active( $extension ) );

		// Simulate the update.
		$this->update_wordpoints();

		$this->assertFalse( is_wordpoints_module_active( $extension ) );
		$this->assertSame(
			array( $extension )
			, get_site_option( 'wordpoints_merged_extensions' )
		);
	}

	/**
	 * Tests that it moves the extensions directory.
	 *
	 * @since 2.4.0
	 */
	public function test_moves_extensions_directory() {

		$legacy = WP_CONTENT_DIR . '/wordpoints-modules';
		$new = WP_CONTENT_DIR . '/wordpoints-extensions';

		$this->mock_filesystem();
		$this->mock_fs->mkdir_p( $legacy );

		$this->assertTrue( $this->mock_fs->exists( $legacy ) );

		// Simulate the update.
		$this->update_wordpoints();

		$this->assertTrue( $this->mock_fs->exists( $new ) );
		$this->assertFalse( $this->mock_fs->exists( $legacy ) );
	}

	/**
	 * Tests that it corrects signature arg GUIDs to integers in the hook hits table.
	 *
	 * @since 2.4.0
	 *
	 * @dataProvider data_provider_entity_slugs
	 *
	 * @param string $slug The entity slug.
	 */
	public function test_corrects_hook_hit_signature_arg_guids( $slug ) {

		global $wpdb;

		$wpdb->insert(
			$wpdb->wordpoints_hook_hits
			, array(
				'action_type' => 'test_fire',
				'signature_arg_guids' => wp_json_encode( array( $slug => '1' ) ),
				'event' => 'test',
				'reactor' => 'test',
				'reaction_mode' => 'test',
				'reaction_store' => 'test',
				'reaction_context_id' => '{}',
				'reaction_id' => 5,
				'date' => current_time( 'mysql', true ),
			)
		);

		$id = $wpdb->insert_id;

		// Simulate the update.
		$this->update_wordpoints();

		$query = new WordPoints_Hook_Hit_Query(
			array( 'id' => $id, 'fields' => 'signature_arg_guids' )
		);

		$guids = json_decode( $query->get( 'var' ), true );

		$this->assertSame( 1, $guids[ $slug ] );
	}

	/**
	 * Provides a list of entity slugs to use with the GUID int update test.
	 *
	 * @since 2.4.0
	 *
	 * @return array The entity slugs.
	 */
	public function data_provider_entity_slugs() {
		return array(
			'user' => array( 'user' ),
			'post\\page' => array( 'post\\page' ),
			'comment\\post' => array( 'comment\\post' ),
		);
	}

	/**
	 * Tests that it corrects signature arg GUIDs to integers in the hook hits table.
	 *
	 * @since 2.4.0
	 */
	public function test_corrects_hook_hit_signature_arg_guids_multiple() {

		global $wpdb;

		$wpdb->insert(
			$wpdb->wordpoints_hook_hits
			, array(
				'action_type' => 'test_fire',
				'signature_arg_guids' => wp_json_encode(
					array(
						'user'       => array( 'user' => '1' ),
						'post\\post' => array( 'post\\post' => '5' ),
					)
				),
				'event' => 'test',
				'reactor' => 'test',
				'reaction_mode' => 'test',
				'reaction_store' => 'test',
				'reaction_context_id' => '{}',
				'reaction_id' => 5,
				'date' => current_time( 'mysql', true ),
			)
		);

		$id = $wpdb->insert_id;

		// Simulate the update.
		$this->update_wordpoints();

		$query = new WordPoints_Hook_Hit_Query(
			array( 'id' => $id, 'fields' => 'signature_arg_guids' )
		);

		$guids = json_decode( $query->get( 'var' ), true );

		$this->assertSame( 1, $guids['user']['user'] );
		$this->assertSame( 5, $guids['post\\post']['post\\post'] );
	}
}

// EOF
