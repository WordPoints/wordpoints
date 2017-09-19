<?php

/**
 * A test case for wordpoints_extension_data_missing_server_headers_filter().
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests wordpoints_extension_data_missing_server_headers_filter().
 *
 * @since 2.4.0
 *
 * @covers ::wordpoints_extension_data_missing_server_headers_filter
 */
class WordPoints_Extension_Data_Missing_Server_Headers_Filter_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests that the data is filled in for the Beta Tester extension.
	 *
	 * @since 2.4.0
	 *
	 * @expectedDeprecated wordpoints_get_module_data
	 */
	public function test_beta_tester() {

		$data = wordpoints_get_module_data(
			WORDPOINTS_TESTS_DIR . '/data/extensions-other/beta-tester.php'
			, false
			, false
		);

		$this->assertSame( 'wordpoints.org', $data['server'] );
		$this->assertSame( '316', $data['ID'] );
	}

	/**
	 * Tests that the data isn't filled in if the extension wasn't authored by us.
	 *
	 * @since 2.4.0
	 *
	 * @expectedDeprecated wordpoints_get_module_data
	 */
	public function test_beta_tester_custom() {

		$data = wordpoints_get_module_data(
			WORDPOINTS_TESTS_DIR . '/data/extensions-other/custom-beta-tester.php'
			, false
			, false
		);

		$this->assertSame( '', $data['server'] );
		$this->assertSame( '', $data['ID'] );
	}

	/**
	 * Tests that the data is filled in for the Importer extension.
	 *
	 * @since 2.4.0
	 *
	 * @expectedDeprecated wordpoints_get_module_data
	 */
	public function test_importer() {

		$data = wordpoints_get_module_data(
			WORDPOINTS_TESTS_DIR . '/data/extensions-other/importer.php'
			, false
			, false
		);

		$this->assertSame( 'wordpoints.org', $data['server'] );
		$this->assertSame( '430', $data['ID'] );
	}

	/**
	 * Tests that the data is filled in for the WooCommerce extension.
	 *
	 * @since 2.4.0
	 *
	 * @expectedDeprecated wordpoints_get_module_data
	 */
	public function test_woocommerce() {

		$data = wordpoints_get_module_data(
			WORDPOINTS_TESTS_DIR . '/data/extensions-other/woocommerce.php'
			, false
			, false
		);

		$this->assertSame( 'wordpoints.org', $data['server'] );
		$this->assertSame( '445', $data['ID'] );
	}

	/**
	 * Tests that the data is filled in for the Points Logs Regenerator extension.
	 *
	 * @since 2.4.0
	 *
	 * @expectedDeprecated wordpoints_get_module_data
	 */
	public function test_points_logs_regenerator() {

		$data = wordpoints_get_module_data(
			WORDPOINTS_TESTS_DIR . '/data/extensions-other/points-logs-regenerator.php'
			, false
			, false
		);

		$this->assertSame( 'wordpoints.org', $data['server'] );
		$this->assertSame( '530', $data['ID'] );
	}

	/**
	 * Tests that the data is filled in for the Reset Points extension.
	 *
	 * @since 2.4.0
	 *
	 * @expectedDeprecated wordpoints_get_module_data
	 */
	public function test_reset_points() {

		$data = wordpoints_get_module_data(
			WORDPOINTS_TESTS_DIR . '/data/extensions-other/reset-points.php'
			, false
			, false
		);

		$this->assertSame( 'wordpoints.org', $data['server'] );
		$this->assertSame( '540', $data['ID'] );
	}
}

// EOF
