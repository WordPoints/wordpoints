<?php

/**
 * Testcase for WordPoints_Extension_Server_API_Extension_License_EDD_SL.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Extension_Server_API_Extension_License_EDD_SL.
 *
 * @since 2.4.0
 *
 * @group extensions
 *
 * @covers WordPoints_Extension_Server_API_Extension_License_EDD_SL
 */
class WordPoints_Extension_Server_API_Extension_License_EDD_SL_Test extends WP_HTTP_TestCase {

	/**
	 * The tests simulator to use to simulate the remote server response.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $tests_simulator = 'Extension_Server_API_EDD_SL';

	/**
	 * @since 2.4.0
	 */
	public function setUp() {

		parent::setUp();

		self::$use_caching = true;

		add_filter( 'http_request_args', array( $this, 'add_extension_api_header' ) );
		add_filter( 'http_request_host_is_external', '__return_true' );
	}

	/**
	 * Add a request header for the extension API.
	 *
	 * @since 2.4.0
	 *
	 * @WordPress\filter http_request_args Added by self::setUp().
	 */
	public function add_extension_api_header( $request ) {

		$request['headers']['x-wordpoints-tests-simulator'] = $this->tests_simulator;

		return $request;
	}

	/**
	 * Constructs an extension license object to test.
	 *
	 * @since 2.4.0
	 *
	 * @param string $status The status to give the license.
	 *
	 * @return WordPoints_Extension_Server_API_Extension_License_EDD_SL The license.
	 */
	protected function get_license( $status ) {

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data;
		$data->set( 'license_status', $status );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			new WordPoints_Extension_Server_API_EDD_SL(
				'test'
				, new WordPoints_Extension_Server( 'example.com' )
			)
			, $data
			, 'test_license_key'
		);

		return $license;
	}

	/**
	 * Tests checking if an extension license is valid.
	 *
	 * @since 2.4.0
	 *
	 * @dataProvider data_provider_license_statuses
	 *
	 * @param string $status     The license status.
	 * @param array  $definition The definition of this status.
	 */
	public function test_is_valid( $status, $definition = array() ) {

		$this->assertSame(
			! empty( $definition['valid'] )
			, $this->get_license( $status )->is_valid()
		);
	}

	/**
	 * Data provider for license statuses.
	 *
	 * @since 2.4.0
	 *
	 * @return array[] License statuses, and what they mean.
	 */
	public function data_provider_license_statuses() {
		return array(
			'valid' => array(
				'valid',
				array(
					'valid' => true,
					'activatable' => true,
					'deactivatable' => true,
					'active' => true,
				),
			),
			'inactive' => array(
				'inactive',
				array(
					'valid' => true,
					'activatable' => true,
					'deactivatable' => true,
				),
			),
			'site_inactive' => array(
				'site_inactive',
				array(
					'valid' => true,
					'activatable' => true,
					'deactivatable' => true,
				),
			),
			'expired' => array(
				'expired',
				array( 'valid' => true, 'expired' => true ),
			),
			'invalid' => array( 'invalid' ),
			'unknown' => array( 'unknown' ),
		);
	}

	/**
	 * Tests checking if an extension license is activatable.
	 *
	 * @since 2.4.0
	 *
	 * @dataProvider data_provider_license_statuses
	 *
	 * @param string $status     The license status.
	 * @param array  $definition The definition of this status.
	 */
	public function test_is_activatable( $status, $definition = array() ) {

		$this->assertSame(
			! empty( $definition['activatable'] )
			, $this->get_license( $status )->is_activatable()
		);
	}

	/**
	 * Tests checking if an extension license is active.
	 *
	 * @since 2.4.0
	 *
	 * @dataProvider data_provider_license_statuses
	 *
	 * @param string $status     The license status.
	 * @param array  $definition The definition of this status.
	 */
	public function test_is_active( $status, $definition = array() ) {

		$this->assertSame(
			! empty( $definition['active'] )
			, $this->get_license( $status )->is_active()
		);
	}

	/**
	 * Tests activating a license.
	 *
	 * @since 2.4.0
	 */
	public function test_activate() {

		$server = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'is_ssl_accessible' )
			, array( 'example.com' )
		);

		$server->method( 'is_ssl_accessible' )->willReturn( false );

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data( 123 );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			new WordPoints_Extension_Server_API_EDD_SL( 'test', $server ),
			$data,
			'test_key'
		);

		$this->assertTrue( $license->activate() );

		$this->assertSame( 'valid', $data->get( 'license_status' ) );

		$this->assertTrue( $license->is_active() );
	}

	/**
	 * Tests activating a license that is already active.
	 *
	 * @since 2.4.0
	 */
	public function test_activate_already_active() {

		$server = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'is_ssl_accessible' )
			, array( 'example.com' )
		);

		$server->method( 'is_ssl_accessible' )->willReturn( false );

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data( 123 );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			new WordPoints_Extension_Server_API_EDD_SL( 'test', $server ),
			$data,
			'test_key_active'
		);

		$this->assertTrue( $license->activate() );

		$this->assertSame( 'valid', $data->get( 'license_status' ) );

		$this->assertTrue( $license->is_active() );
	}

	/**
	 * Tests activating a license when the request fails.
	 *
	 * @since 2.4.0
	 */
	public function test_activate_request_error() {

		self::$use_caching = false;

		$filter = new WordPoints_PHPUnit_Mock_Filter( new WP_Error() );
		$filter->add_filter( 'pre_http_request' );

		$server = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'is_ssl_accessible' )
			, array( 'example.com' )
		);

		$server->method( 'is_ssl_accessible' )->willReturn( false );

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data( 123 );
		$data->set( 'license_status', 'inactive' );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			new WordPoints_Extension_Server_API_EDD_SL( 'test', $server ),
			$data,
			'test_key'
		);

		$this->assertWPError( $license->activate() );

		$this->assertSame( 'inactive', $data->get( 'license_status' ) );

		$this->assertFalse( $license->is_active() );
	}

	/**
	 * Tests activating a license when the response is invalid.
	 *
	 * @since 2.4.0
	 */
	public function test_activate_response_invalid() {

		self::$use_caching = false;

		$filter = new WordPoints_PHPUnit_Mock_Filter( array() );
		$filter->add_filter( 'pre_http_request' );

		$server = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'is_ssl_accessible' )
			, array( 'example.com' )
		);

		$server->method( 'is_ssl_accessible' )->willReturn( false );

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data( 123 );
		$data->set( 'license_status', 'inactive' );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			new WordPoints_Extension_Server_API_EDD_SL( 'test', $server ),
			$data,
			'test_key'
		);

		$this->assertFalse( $license->activate() );

		$this->assertSame( 'inactive', $data->get( 'license_status' ) );

		$this->assertFalse( $license->is_active() );
	}

	/**
	 * Tests activating a license when the activation failed.
	 *
	 * @since 2.4.0
	 */
	public function test_activate_other_status_returned() {

		$server = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'is_ssl_accessible' )
			, array( 'example.com' )
		);

		$server->method( 'is_ssl_accessible' )->willReturn( false );

		$api = new WordPoints_Extension_Server_API_EDD_SL( 'test', $server );

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data( 123 );
		$data->set( 'license_status', 'inactive' );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			$api,
			$data,
			'test_key_expired'
		);

		$this->assertFalse( $license->activate() );

		$this->assertSame( 'invalid', $data->get( 'license_status' ) );

		$this->assertFalse( $license->is_active() );
	}

	/**
	 * Tests checking if an extension license is deactivatable.
	 *
	 * @since 2.4.0
	 *
	 * @dataProvider data_provider_license_statuses
	 *
	 * @param string $status     The license status.
	 * @param array  $definition The definition of this status.
	 */
	public function test_is_deactivatable( $status, $definition = array() ) {

		$this->assertSame(
			! empty( $definition['deactivatable'] )
			, $this->get_license( $status )->is_deactivatable()
		);
	}

	/**
	 * Tests deactivating a license.
	 *
	 * @since 2.4.0
	 */
	public function test_deactivate() {

		$server = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'is_ssl_accessible' )
			, array( 'example.com' )
		);

		$server->method( 'is_ssl_accessible' )->willReturn( false );

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data( 123 );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			new WordPoints_Extension_Server_API_EDD_SL( 'test', $server ),
			$data,
			'test_key_active'
		);

		$this->assertTrue( $license->deactivate() );

		$this->assertSame( 'inactive', $data->get( 'license_status' ) );

		$this->assertFalse( $license->is_active() );
	}

	/**
	 * Tests deactivating a license that is already inactive.
	 *
	 * @since 2.4.0
	 */
	public function test_deactivate_already_inactive() {

		$server = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'is_ssl_accessible' )
			, array( 'example.com' )
		);

		$server->method( 'is_ssl_accessible' )->willReturn( false );

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data( 123 );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			new WordPoints_Extension_Server_API_EDD_SL( 'test', $server ),
			$data,
			'test_key'
		);

		$this->assertTrue( $license->deactivate() );

		$this->assertSame( 'inactive', $data->get( 'license_status' ) );

		$this->assertFalse( $license->is_active() );
	}

	/**
	 * Tests deactivating a license when the request fails.
	 *
	 * @since 2.4.0
	 */
	public function test_deactivate_request_error() {

		self::$use_caching = false;

		$filter = new WordPoints_PHPUnit_Mock_Filter( new WP_Error() );
		$filter->add_filter( 'pre_http_request' );

		$server = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'is_ssl_accessible' )
			, array( 'example.com' )
		);

		$server->method( 'is_ssl_accessible' )->willReturn( false );

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data( 123 );
		$data->set( 'license_status', 'valid' );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			new WordPoints_Extension_Server_API_EDD_SL( 'test', $server ),
			$data,
			'test_key_inactive'
		);

		$this->assertWPError( $license->deactivate() );

		$this->assertSame( 'valid', $data->get( 'license_status' ) );

		$this->assertTrue( $license->is_active() );
	}

	/**
	 * Tests deactivating a license when the response is invalid.
	 *
	 * @since 2.4.0
	 */
	public function test_deactivate_response_invalid() {

		self::$use_caching = false;

		$filter = new WordPoints_PHPUnit_Mock_Filter( array() );
		$filter->add_filter( 'pre_http_request' );

		$server = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'is_ssl_accessible' )
			, array( 'example.com' )
		);

		$server->method( 'is_ssl_accessible' )->willReturn( false );

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data( 123 );
		$data->set( 'license_status', 'valid' );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			new WordPoints_Extension_Server_API_EDD_SL( 'test', $server ),
			$data,
			'test_key_inactive'
		);

		$this->assertFalse( $license->deactivate() );

		$this->assertSame( 'valid', $data->get( 'license_status' ) );

		$this->assertTrue( $license->is_active() );
	}

	/**
	 * Tests deactivating a license when the deactivation failed.
	 *
	 * @since 2.4.0
	 */
	public function test_deactivate_other_status_returned() {

		$server = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'is_ssl_accessible' )
			, array( 'example.com' )
		);

		$server->method( 'is_ssl_accessible' )->willReturn( false );

		$api = new WordPoints_Extension_Server_API_EDD_SL( 'test', $server );

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data( 123 );
		$data->set( 'license_status', 'valid' );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			$api,
			$data,
			'test_key_expired'
		);

		$this->assertFalse( $license->deactivate() );

		$this->assertSame( 'expired', $data->get( 'license_status' ) );

		$this->assertFalse( $license->is_active() );
	}

	/**
	 * Tests checking if an extension license expires.
	 *
	 * @since 2.4.0
	 */
	public function test_expires() {

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data;
		$data->set( 'license_expiration', date( 'Y-m-d 23:59:59' ) );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			new WordPoints_Extension_Server_API_EDD_SL(
				'test'
				, new WordPoints_Extension_Server( 'example.com' )
			)
			, $data
			, 'test_license_key'
		);

		$this->assertTrue( $license->expires() );
	}

	/**
	 * Tests checking if a lifetime extension license expires.
	 *
	 * @since 2.4.0
	 */
	public function test_expires_lifetime() {

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data;
		$data->set( 'license_expiration', 'lifetime' );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			new WordPoints_Extension_Server_API_EDD_SL(
				'test'
				, new WordPoints_Extension_Server( 'example.com' )
			)
			, $data
			, 'test_license_key'
		);

		$this->assertFalse( $license->expires() );
	}

	/**
	 * Tests checking if an extension license is expired.
	 *
	 * @since 2.4.0
	 *
	 * @dataProvider data_provider_license_statuses
	 *
	 * @param string $status     The license status.
	 * @param array  $definition The definition of this status.
	 */
	public function test_is_expired( $status, $definition = array() ) {

		$this->assertSame(
			! empty( $definition['expired'] )
			, $this->get_license( $status )->is_expired()
		);
	}

	/**
	 * Tests getting the expiration for a license.
	 *
	 * @since 2.4.0
	 */
	public function test_get_expiration_date() {

		$date = date( 'Y-m-d 23:59:59' );

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data;
		$data->set( 'license_expiration', $date );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			new WordPoints_Extension_Server_API_EDD_SL(
				'test'
				, new WordPoints_Extension_Server( 'example.com' )
			)
			, $data
			, 'test_license_key'
		);

		$expiration = $license->get_expiration_date();

		$this->assertInstanceOf( 'DateTime', $expiration );
		$this->assertSame( $date, $expiration->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * Tests getting the expiration for a lifetime license.
	 *
	 * @since 2.4.0
	 */
	public function test_get_expiration_date_lifetime() {

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data;
		$data->set( 'license_expiration', 'lifetime' );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			new WordPoints_Extension_Server_API_EDD_SL(
				'test'
				, new WordPoints_Extension_Server( 'example.com' )
			)
			, $data
			, 'test_license_key'
		);

		$this->assertFalse( $license->get_expiration_date() );
	}

	/**
	 * Tests getting the expiration for a license when the date is invalid.
	 *
	 * @since 2.4.0
	 */
	public function test_get_expiration_date_invalid() {

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data;
		$data->set( 'license_expiration', 'invalid' );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			new WordPoints_Extension_Server_API_EDD_SL(
				'test'
				, new WordPoints_Extension_Server( 'example.com' )
			)
			, $data
			, 'test_license_key'
		);

		$this->assertFalse( $license->get_expiration_date() );
	}

	/**
	 * Tests checking if an extension license is renewable.
	 *
	 * @since 2.4.0
	 *
	 * @dataProvider data_provider_license_statuses
	 *
	 * @param string $status     The license status.
	 * @param array  $definition The definition of this status.
	 */
	public function test_is_renewable( $status, $definition = array() ) {

		$this->assertSame(
			! empty( $definition['valid'] )
			, $this->get_license( $status )->is_renewable()
		);
	}

	/**
	 * Tests getting the renewal URL for a license.
	 *
	 * @since 2.4.0
	 */
	public function test_get_renewal_url() {

		$url = 'https://example.com/extension/renew/';

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data;
		$data->set( 'homepage', $url );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			new WordPoints_Extension_Server_API_EDD_SL(
				'test'
				, new WordPoints_Extension_Server( 'example.com' )
			)
			, $data
			, 'test_license_key'
		);

		$this->assertSame( $url, $license->get_renewal_url() );
	}

	/**
	 * Tests getting a piece of info about a license will return the cached value.
	 *
	 * @since 2.4.0
	 */
	public function test_get_info_returns_cached_if_available() {

		$data = $this->getMock( 'WordPoints_Extension_Server_API_Extension_DataI' );
		$data->expects( $this->once() )->method( 'get' )->willReturn( 'test_value' );

		$license = $this->getMock(
			'WordPoints_Extension_Server_API_Extension_License_EDD_SL'
			, array( 'request_info' )
			, array(
				new WordPoints_Extension_Server_API_EDD_SL(
					'test'
					, new WordPoints_Extension_Server( 'example.com' )
				),
				$data,
				'test_license_key',
			)
		);

		$license->expects( $this->never() )->method( 'request_info' );

		$license->is_valid();
	}

	/**
	 * Tests getting a piece of info about a license will request the value.
	 *
	 * @since 2.4.0
	 */
	public function test_get_info_requests_from_remote_if_needed() {

		$server = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'is_ssl_accessible' )
			, array( 'example.com' )
		);

		$server->method( 'is_ssl_accessible' )->willReturn( false );

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data( 123 );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			new WordPoints_Extension_Server_API_EDD_SL(
				'test'
				, new WordPoints_Extension_Server( 'example.com' )
			)
			, $data
			, 'test_key'
		);

		$license->is_valid();

		$this->assertSame( 'inactive', $data->get( 'license_status' ) );
		$this->assertNotEmpty( $data->get( 'license_expiration' ) );
	}

	/**
	 * Tests getting a piece of info about a license when the request gives an error.
	 *
	 * @since 2.4.0
	 */
	public function test_request_info_when_there_is_an_error() {

		self::$use_caching = false;

		$filter = new WordPoints_PHPUnit_Mock_Filter( new WP_Error() );
		$filter->add_filter( 'pre_http_request' );

		$data = new WordPoints_PHPUnit_Mock_Extension_Server_API_Extension_Data( 123 );

		$license = new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			new WordPoints_Extension_Server_API_EDD_SL(
				'test'
				, new WordPoints_Extension_Server( 'example.com' )
			)
			, $data
			, 'test_key'
		);

		$license->is_valid();

		$this->assertSame( null, $data->get( 'license_status' ) );
	}
}

// EOF
