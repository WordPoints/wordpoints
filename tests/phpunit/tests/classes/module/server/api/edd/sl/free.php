<?php

/**
 * Testcase for WordPoints_Module_Server_API_EDD_SL_Free.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Module_Server_API_EDD_SL_Free.
 *
 * @since 2.4.0
 *
 * @group modules
 *
 * @covers WordPoints_Module_Server_API_EDD_SL_Free
 */
class WordPoints_Module_Server_API_EDD_SL_Free_Test
	extends WordPoints_Module_Server_API_EDD_SL_Test {

	/**
	 * @since 2.4.0
	 */
	protected $server_api_class = 'WordPoints_Module_Server_API_EDD_SL_Free';

	/**
	 * @since 2.4.0
	 */
	protected $tests_simulator = 'Module_Server_API_EDD_SL_Free';

	/**
	 * Tests that modules require a license.
	 *
	 * @since 2.4.0
	 */
	public function test_module_requires_license() {

		$api = $this->get_server_api();

		$module_data = new WordPoints_PHPUnit_Mock_Module_Server_API_Module_Data();
		$module_data->set( 'is_free', false );

		$this->assertTrue( $api->module_requires_license( $module_data ) );
	}

	/**
	 * Tests that free modules don't require a license.
	 *
	 * @since 2.4.0
	 */
	public function test_module_requires_license_free() {

		$api = $this->get_server_api();

		$module_data = new WordPoints_PHPUnit_Mock_Module_Server_API_Module_Data();
		$module_data->set( 'is_free', true );

		$this->assertFalse( $api->module_requires_license( $module_data ) );
	}

	/**
	 * Tests attempting to get a license object for a free module.
	 *
	 * @since 2.4.0
	 */
	public function test_get_module_license_object_free() {

		$api = $this->get_server_api();

		$module_data = new WordPoints_PHPUnit_Mock_Module_Server_API_Module_Data();
		$module_data->set( 'is_free', true );

		$this->assertFalse(
			$api->get_module_license_object( $module_data, 'test_license_key' )
		);
	}

	/**
	 * Tests getting a piece of info about a module will request the value.
	 *
	 * @since 2.4.0
	 */
	public function test_get_module_info_requests_from_remote_if_needed() {

		$server = $this->getMock(
			'WordPoints_Module_Server'
			, array( 'is_ssl_accessible' )
			, array( 'example.com' )
		);

		$server->method( 'is_ssl_accessible' )->willReturn( false );

		$api = $this->get_server_api( $server );

		$data = new WordPoints_PHPUnit_Mock_Module_Server_API_Module_Data( 123 );
		$data->set( 'license_key', 'test_key' );

		$this->assertSame( null, $api->get_module_info( $data, 'test' ) );
		$this->assertSame( '1.2.3', $api->get_module_info( $data, 'latest_version' ) );
		$this->assertStringMatchesFormat(
			'%aA test changelog.%a'
			, $api->get_module_info( $data, 'changelog' )
		);

		$this->assertStringMatchesFormat(
			'%s://example.org/edd-sl/package_download/%s'
			, $api->get_module_info( $data, 'package' )
		);

		$this->assertFalse( $api->get_module_info( $data, 'is_free' ) );
	}

	/**
	 * Tests getting a piece of info about a module will request the value.
	 *
	 * @since 2.4.0
	 */
	public function test_get_module_info_requests_from_remote_if_needed_free() {

		$server = $this->getMock(
			'WordPoints_Module_Server'
			, array( 'is_ssl_accessible' )
			, array( 'example.com' )
		);

		$server->method( 'is_ssl_accessible' )->willReturn( false );

		$api = $this->get_server_api( $server );

		$data = new WordPoints_PHPUnit_Mock_Module_Server_API_Module_Data( 124 );
		$data->set( 'license_key', 'test_key' );

		$this->assertSame( null, $api->get_module_info( $data, 'test' ) );
		$this->assertSame( '1.2.4', $api->get_module_info( $data, 'latest_version' ) );
		$this->assertStringMatchesFormat(
			'%aA test changelog.%a'
			, $api->get_module_info( $data, 'changelog' )
		);

		$this->assertStringMatchesFormat(
			'%s://example.org/edd-sl/package_download/%s'
			, $api->get_module_info( $data, 'package' )
		);

		$this->assertTrue( $api->get_module_info( $data, 'is_free' ) );
	}
}

// EOF
