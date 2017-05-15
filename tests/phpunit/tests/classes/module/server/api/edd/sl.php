<?php

/**
 * Testcase for WordPoints_Module_Server_API_EDD_SL.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Module_Server_API_EDD_SL.
 *
 * @since 2.4.0
 *
 * @group modules
 *
 * @covers WordPoints_Module_Server_API_EDD_SL
 */
class WordPoints_Module_Server_API_EDD_SL_Test extends WP_HTTP_TestCase {

	/**
	 * Module server API class being tested.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $server_api_class = 'WordPoints_Module_Server_API_EDD_SL';

	/**
	 * The tests simulator to use to simulate the remote server response.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $tests_simulator = 'Module_Server_API_EDD_SL';

	/**
	 * @since 2.4.0
	 */
	public function setUp() {

		parent::setUp();

		add_filter( 'http_request_args', array( $this, 'add_module_api_header' ) );
		add_filter( 'http_request_host_is_external', '__return_true' );
	}

	/**
	 * Add a request header for the module API.
	 *
	 * @since 2.4.0
	 *
	 * @WordPress\filter http_request_args Added by self::setUp().
	 */
	public function add_module_api_header( $request ) {

		$request['headers']['x-wordpoints-tests-simulator'] = $this->tests_simulator;

		return $request;
	}

	/**
	 * Constructs a server API object to test.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Module_Server $server The server object.
	 *
	 * @return WordPoints_Module_Server_API_EDD_SL The server API object.
	 */
	protected function get_server_api( WordPoints_Module_Server $server = null ) {

		if ( ! isset( $server ) ) {
			$server = new WordPoints_Module_Server( 'example.com' );
		}

		return new $this->server_api_class( $server );
	}

	/**
	 * Tests that modules require a license.
	 *
	 * @since 2.4.0
	 */
	public function test_module_requires_license() {

		$api = $this->get_server_api();

		$mock = $this->getMock( 'WordPoints_Module_Server_API_Module_DataI' );

		$this->assertTrue( $api->module_requires_license( $mock ) );
	}

	/**
	 * Tests getting a license object for a module.
	 *
	 * @since 2.4.0
	 */
	public function test_get_module_license_object() {

		$api = $this->get_server_api();

		$mock = $this->getMock( 'WordPoints_Module_Server_API_Module_DataI' );

		$this->assertInstanceOf(
			'WordPoints_Module_Server_API_Module_LicenseI'
			, $api->get_module_license_object( $mock, 'test_license_key' )
		);
	}

	/**
	 * Tests getting the latest version of a module.
	 *
	 * @since 2.4.0
	 */
	public function test_get_module_latest_version() {

		$api = $this->get_server_api();

		$version = '2.3.1';

		$data = new WordPoints_PHPUnit_Mock_Module_Server_API_Module_Data;
		$data->set( 'latest_version', $version );

		$this->assertSame( $version, $api->get_module_latest_version( $data ) );
	}

	/**
	 * Tests getting the latest version of a module.
	 *
	 * @since 2.4.0
	 */
	public function test_get_module_package_url() {

		$api = $this->get_server_api();

		$url = 'https://example.com/module/?download=1';

		$data = new WordPoints_PHPUnit_Mock_Module_Server_API_Module_Data;
		$data->set( 'package', $url );

		$this->assertSame( $url, $api->get_module_package_url( $data ) );
	}

	/**
	 * Tests getting the changelog for a module.
	 *
	 * @since 2.4.0
	 */
	public function test_get_module_changelog() {

		$api = $this->get_server_api();

		$changelog = 'A list of changes...';

		$data = new WordPoints_PHPUnit_Mock_Module_Server_API_Module_Data;
		$data->set( 'changelog', $changelog );

		$this->assertSame( $changelog, $api->get_module_changelog( $data ) );
	}

	/**
	 * Tests getting a piece of info about a module will return the cached value.
	 *
	 * @since 2.4.0
	 */
	public function test_get_module_info_returns_cached_if_available() {

		$api = $this->get_server_api();

		$data = new WordPoints_PHPUnit_Mock_Module_Server_API_Module_Data;
		$data->set( 'test', 'test_value' );

		$this->assertSame( 'test_value', $api->get_module_info( $data, 'test' ) );
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

		$this->assertStringMatchesFormat(
			'%s://example.org/?download=test-download'
			, $api->get_module_info( $data, 'homepage' )
		);
	}

	/**
	 * Tests getting a piece of info about a module when the request gives an error.
	 *
	 * @since 2.4.0
	 */
	public function test_request_module_info_when_there_is_an_error() {

		$filter = new WordPoints_PHPUnit_Mock_Filter( new WP_Error() );
		$filter->add_filter( 'pre_http_request' );

		$api = $this->get_server_api();

		$data = new WordPoints_PHPUnit_Mock_Module_Server_API_Module_Data( 123 );

		$this->assertSame( null, $api->get_module_info( $data, 'latest_version' ) );
	}

	/**
	 * Tests sending a request that results in an error.
	 *
	 * @since 2.4.0
	 */
	public function test_request_returns_error_on_failure() {

		$filter = new WordPoints_PHPUnit_Mock_Filter( new WP_Error() );
		$filter->add_filter( 'pre_http_request' );

		$api = $this->get_server_api();

		$this->assertInstanceOf( 'WP_Error', $api->request( 'get_version', 123 ) );
	}

	/**
	 * Tests that if the response has a sections key its value will be unserialized.
	 *
	 * @since 2.4.0
	 */
	public function test_request_unserializes_sections() {

		$server = $this->getMock(
			'WordPoints_Module_Server'
			, array( 'is_ssl_accessible' )
			, array( 'example.com' )
		);

		$server->method( 'is_ssl_accessible' )->willReturn( false );

		$api = $this->get_server_api( $server );

		$response = $api->request( 'get_version', 123, 'test_key' );

		$this->assertArrayHasKey( 'sections', $response );
		$this->assertInternalType( 'array', $response['sections'] );
	}

	/**
	 * Tests that the sections will only attempt to be unserialized if set.
	 *
	 * @since 2.4.0
	 */
	public function test_request_sections_not_set() {

		$response = array( 'body' => wp_json_encode( array() ) );

		$filter = new WordPoints_PHPUnit_Mock_Filter( $response );
		$filter->add_filter( 'pre_http_request' );

		$api = $this->get_server_api();

		$response = $api->request( 'get_version', 123, 'test_key' );

		$this->assertArrayNotHasKey( 'sections', $response );
	}

	/**
	 * Tests that the sections will only attempt to be unserialized if a string.
	 *
	 * @since 2.4.0
	 */
	public function test_request_sections_not_string() {

		$response = array(
			'body' => wp_json_encode( array( 'sections' => array() ) ),
		);

		$filter = new WordPoints_PHPUnit_Mock_Filter( $response );
		$filter->add_filter( 'pre_http_request' );

		$api = $this->get_server_api();

		$response = $api->request( 'get_version', 123, 'test_key' );

		$this->assertArrayHasKey( 'sections', $response );
		$this->assertInternalType( 'array', $response['sections'] );
	}

	/**
	 * Tests the sections won't be unserialized if containing a serialized object.
	 *
	 * @since 2.4.0
	 */
	public function test_request_sections_not_serialized_array() {

		$response = array(
			'body' => wp_json_encode(
				array( 'sections' => serialize( new stdClass() ) )
			),
		);

		$filter = new WordPoints_PHPUnit_Mock_Filter( $response );
		$filter->add_filter( 'pre_http_request' );

		$api = $this->get_server_api();

		$response = $api->request( 'get_version', 123, 'test_key' );

		$this->assertArrayHasKey( 'sections', $response );
		$this->assertSame( serialize( new stdClass() ), $response['sections'] );
	}
}

// EOF
