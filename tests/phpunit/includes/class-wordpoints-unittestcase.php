<?php

/**
 * A parent class for the WordPoints unit tests.
 *
 * @package WordPoints\Tests
 * @since 1.5.0
 */

/**
 * Test case parent for the unit tests.
 *
 * @since 1.5.0
 */
abstract class WordPoints_UnitTestCase extends WP_UnitTestCase {

	/**
	 * Set the version of the plugin.
	 *
	 * Since 1.3.0, this was a part of the WordPoints_Points_Update_Test.
	 *
	 * @since 1.5.0
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
	 * Since 1.3.0, this was a part of the WordPoints_Points_Update_Test.
	 *
	 * @since 1.5.0
	 *
	 * @return string The version of the plugin.
	 */
	protected function wordpoints_get_db_version() {

		$wordpoints_data = wordpoints_get_network_option( 'wordpoints_data' );

		return ( isset( $wordpoints_data['version'] ) )
			? $wordpoints_data['version']
			: '';
	}
}

// EOF
