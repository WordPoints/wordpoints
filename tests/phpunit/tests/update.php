<?php

/**
 * A test case for WordPoints updates.
 *
 * @package WordPoints\Tests
 * @since 1.3.0
 */

/**
 * Test that the plugin updates itself properly.
 *
 * @since 1.3.0
 */
class WordPoints_Update_Test extends WP_UnitTestCase {

	//
	// Helper Methods.
	//

	/**
	 * Set the version of the plugin.
	 *
	 * @since 1.3.0
	 *
	 * @param string $version The version to set. Defaults to 1.0.0.
	 */
	protected function wordpoints_set_db_version( $version = '1.0.0' ) {

		$wordpoints_data = wordpoints_get_network_option( 'wordpoints_data' );
		$wordpoints_data['version'] = $version;
		wordpoints_update_network_option( 'wordpoints_data', $wordpoints_data );
	}

	/**
	 * Get the version of the plugin.
	 *
	 * @since 1.3.0
	 *
	 * @return string The version of the plugin.
	 */
	protected function wordpoints_get_db_version() {

		$wordpoints_data = wordpoints_get_network_option( 'wordpoints_data' );

		return ( isset( $wordpoints_data['version'] ) )
			? $wordpoints_data['version']
			: '';
	}

	//
	// Tests.
	//

	/**
	 * Test the update to 1.3.0.
	 *
	 * @since 1.3.0
	 */
	public function test_update_to_1_3_0() {

		// First remove the capabilities if they have been added.
		wordpoints_remove_custom_caps( array_keys( wordpoints_get_custom_caps() ) );

		$administrator = get_role( 'administrator' );
		$this->assertFalse( $administrator->has_cap( 'install_wordpoints_modules' ) );
		$this->assertFalse( $administrator->has_cap( 'activate_wordpoints_modules' ) );
		$this->assertFalse( $administrator->has_cap( 'delete_wordpoints_modules' ) );

		// Mock an update.
		$this->wordpoints_set_db_version( '1.2.0' );
		wordpoints_update();
		$this->assertEquals( WORDPOINTS_VERSION, $this->wordpoints_get_db_version() );

		// Check that the capabilities were added.
		$administrator = get_role( 'administrator' );
		$this->assertTrue( $administrator->has_cap( 'install_wordpoints_modules' ) );
		$this->assertTrue( $administrator->has_cap( 'activate_wordpoints_modules' ) );
		$this->assertTrue( $administrator->has_cap( 'delete_wordpoints_modules' ) );
	}
}
