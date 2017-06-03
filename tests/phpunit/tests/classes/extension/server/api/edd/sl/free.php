<?php

/**
 * Testcase for WordPoints_Extension_Server_API_EDD_SL_Free.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Extension_Server_API_EDD_SL_Free.
 *
 * @since 2.4.0
 *
 * @group extensions
 *
 * @covers WordPoints_Extension_Server_API_EDD_SL_Free
 */
class WordPoints_Extension_Server_API_EDD_SL_Free_Test
	extends WordPoints_Extension_Server_API_EDD_SL_Test {

	/**
	 * @since 2.4.0
	 */
	protected $server_api_class = 'WordPoints_Extension_Server_API_EDD_SL_Free';

	/**
	 * @since 2.4.0
	 */
	protected $tests_simulator = 'Extension_Server_API_EDD_SL_Free';

	/**
	 * Tests that extensions require a license.
	 *
	 * @since 2.4.0
	 */
	public function test_extension_requires_license() {

		$api = $this->get_server_api();

		$extension_data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data();
		$extension_data->set( 'is_free', false );

		$this->assertTrue( $api->extension_requires_license( $extension_data ) );
	}

	/**
	 * Tests that free extensions don't require a license.
	 *
	 * @since 2.4.0
	 */
	public function test_extension_requires_license_free() {

		$api = $this->get_server_api();

		$extension_data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data();
		$extension_data->set( 'is_free', true );

		$this->assertFalse( $api->extension_requires_license( $extension_data ) );
	}

	/**
	 * Tests attempting to get a license object for a free extension.
	 *
	 * @since 2.4.0
	 */
	public function test_get_extension_license_object_free() {

		$api = $this->get_server_api();

		$extension_data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data();
		$extension_data->set( 'is_free', true );

		$this->assertFalse(
			$api->get_extension_license_object( $extension_data, 'test_license_key' )
		);
	}

	/**
	 * Tests getting a piece of info about an extension will request the value.
	 *
	 * @since 2.4.0
	 */
	public function test_get_extension_info_requests_from_remote_if_needed() {

		$server = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'is_ssl_accessible' )
			, array( 'example.com' )
		);

		$server->method( 'is_ssl_accessible' )->willReturn( false );

		$api = $this->get_server_api( $server );

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data( 123 );
		$data->set( 'license_key', 'test_key' );

		$this->assertSame( null, $api->get_extension_info( $data, 'test' ) );
		$this->assertSame( '1.2.3', $api->get_extension_info( $data, 'latest_version' ) );
		$this->assertStringMatchesFormat(
			'%aA test changelog.%a'
			, $api->get_extension_info( $data, 'changelog' )
		);

		$this->assertStringMatchesFormat(
			'%s://%s/edd-sl/package_download/%s'
			, $api->get_extension_info( $data, 'package' )
		);

		$this->assertFalse( $api->get_extension_info( $data, 'is_free' ) );
	}

	/**
	 * Tests getting a piece of info about an extension will request the value.
	 *
	 * @since 2.4.0
	 */
	public function test_get_extension_info_requests_from_remote_if_needed_free() {

		$server = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'is_ssl_accessible' )
			, array( 'example.com' )
		);

		$server->method( 'is_ssl_accessible' )->willReturn( false );

		$api = $this->get_server_api( $server );

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data( 124 );
		$data->set( 'license_key', 'test_key' );

		$this->assertSame( null, $api->get_extension_info( $data, 'test' ) );
		$this->assertSame( '1.2.4', $api->get_extension_info( $data, 'latest_version' ) );
		$this->assertStringMatchesFormat(
			'%aA test changelog.%a'
			, $api->get_extension_info( $data, 'changelog' )
		);

		$this->assertStringMatchesFormat(
			'%s://example.org/edd-sl/package_download/%s'
			, $api->get_extension_info( $data, 'package' )
		);

		$this->assertTrue( $api->get_extension_info( $data, 'is_free' ) );
	}
}

// EOF
