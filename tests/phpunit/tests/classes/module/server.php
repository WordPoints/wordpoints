<?php

/**
 * Testcase for WordPoints_Extension_Server.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Test WordPoints_Extension_Server.
 *
 * @since 2.4.0
 *
 * @group extensions
 *
 * @covers WordPoints_Extension_Server
 */
class WordPoints_Extension_Server_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test the constructor.
	 *
	 * @since 2.4.0
	 */
	public function test_construct() {

		$slug = 'example.com';

		$server = new WordPoints_Extension_Server( $slug );

		$this->assertSame( $slug, $server->get_slug() );
	}

	/**
	 * Test getting the server URL.
	 *
	 * @since 2.4.0
	 */
	public function test_get_url() {

		$slug = 'example.com';

		$mock = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'is_ssl_accessible' )
			, array( $slug )
		);

		$mock->method( 'is_ssl_accessible' )->willReturn( false );

		$this->assertSame( 'http://example.com', $mock->get_url() );
	}

	/**
	 * Test getting the server URL when the server is SSL accessible.
	 *
	 * @since 2.4.0
	 */
	public function test_get_url_ssl_accessible() {

		$slug = 'example.com';

		$mock = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'is_ssl_accessible' )
			, array( $slug )
		);

		$mock->method( 'is_ssl_accessible' )->willReturn( true );

		$this->assertSame( 'https://example.com', $mock->get_url() );
	}

	/**
	 * Test checking if the server is SSL accessible when WP HTTP doesn't support it.
	 *
	 * @since 2.4.0
	 */
	public function test_is_ssl_accessible_not_supported_by_wp_http() {

		$slug = 'example.com';

		add_filter( 'http_api_transports', '__return_empty_array' );

		$server = new WordPoints_Extension_Server( $slug );

		$this->assertSame( 'http://example.com', $server->get_url() );
	}

	/**
	 * Test checking if the server is SSL accessible when WP HTTP doesn't support it.
	 *
	 * @since 2.4.0
	 */
	public function test_is_ssl_accessible_not_supported_by_wp_http_cached() {

		$slug = 'example.com';

		$filter = new WordPoints_PHPUnit_Mock_Filter( array() );
		$filter->add_filter( 'http_api_transports' );

		$server = new WordPoints_Extension_Server( $slug );

		$this->assertSame( 'http://example.com', $server->get_url() );

		$this->assertSame( 1, $filter->call_count );

		$this->assertSame( 'http://example.com', $server->get_url() );

		$this->assertSame( 1, $filter->call_count );
	}

	/**
	 * Test checking if the server is SSL accessible when WP HTTP doesn't support it.
	 *
	 * @since 2.4.0
	 */
	public function test_is_ssl_accessible_wp_http_error() {

		$slug = 'example.com';

		$filter = new WordPoints_PHPUnit_Mock_Filter( new WP_Error() );
		$filter->add_filter( 'pre_http_request' );

		$server = new WordPoints_Extension_Server( $slug );

		$this->assertSame( 'http://example.com', $server->get_url() );
	}

	/**
	 * Test checking if the server is SSL accessible when WP HTTP doesn't support it.
	 *
	 * @since 2.4.0
	 */
	public function test_is_ssl_accessible_wp_http_error_cached() {

		$slug = 'example.com';

		$filter = new WordPoints_PHPUnit_Mock_Filter( new WP_Error() );
		$filter->add_filter( 'pre_http_request' );

		$server = new WordPoints_Extension_Server( $slug );

		$this->assertSame( 'http://example.com', $server->get_url() );

		$this->assertSame( 1, $filter->call_count );

		$this->assertSame( 'http://example.com', $server->get_url() );

		$this->assertSame( 1, $filter->call_count );
	}

	/**
	 * Test checking if the server is SSL accessible.
	 *
	 * @since 2.4.0
	 *
	 * @dataProvider data_provider_is_ssl_response_codes
	 *
	 * @param int  $response_code The response code to simulate the server giving.
	 * @param bool $expect_ssl    Whether the code is expected to indicate support.
	 */
	public function test_is_ssl_accessible(
		$response_code,
		$expect_ssl = true
	) {

		$slug = 'example.com';

		$filter = new WordPoints_PHPUnit_Mock_Filter(
			array( 'response' => array( 'code' => $response_code ) )
		);

		$filter->add_filter( 'pre_http_request' );

		$server = new WordPoints_Extension_Server( $slug );

		if ( $expect_ssl ) {
			$url = 'https://example.com';
		} else {
			$url = 'http://example.com';
		}

		$this->assertSame( $url, $server->get_url() );

		$this->assertSame( 1, $filter->call_count );

		// Test that the result is cached.
		$this->assertSame( $url, $server->get_url() );

		$this->assertSame( 1, $filter->call_count );
	}

	/**
	 * Data provider for response codes for the SSL support check.
	 *
	 * @since 2.4.0
	 *
	 * @return array Possible response codes, and whether they indicate support.
	 */
	public function data_provider_is_ssl_response_codes() {
		return array(
			'200' => array( 200 ),
			'401' => array( 401 ),
			'500' => array( 500, false ),
		);
	}

	/**
	 * Test getting the server API.
	 *
	 * @since 2.4.0
	 */
	public function test_get_api() {

		$this->mock_apps();

		wordpoints_apps()
			->get_sub_app( 'extension_server_apis' )
			->register( 'test', 'stdClass' );

		$slug = 'example.com';

		$mock = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'get_api_slug' )
			, array( $slug )
		);

		$mock->method( 'get_api_slug' )->willReturn( 'test' );

		$this->assertInternalType( 'object', $mock->get_api() );
	}

	/**
	 * Test getting the server API when it isn't registered.
	 *
	 * @since 2.4.0
	 */
	public function test_get_api_unregistered() {

		$slug = 'example.com';

		$mock = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'get_api_slug' )
			, array( $slug )
		);

		$mock->method( 'get_api_slug' )->willReturn( 'unregistered_api' );

		$this->assertFalse( $mock->get_api() );
	}

	/**
	 * Test getting the server API when it isn't known.
	 *
	 * @since 2.4.0
	 */
	public function test_get_api_unknown() {

		$slug = 'example.com';

		$mock = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'get_api_slug' )
			, array( $slug )
		);

		$mock->method( 'get_api_slug' )->willReturn( false );

		$this->assertFalse( $mock->get_api() );
	}

	/**
	 * Test retrieving the API header from the remote server.
	 *
	 * @since 2.4.0
	 *
	 * @dataProvider data_provider_api_headers
	 *
	 * @param string $api_header The API header to simulate the server setting.
	 * @param bool   $expect_api Whether the API is expected to be available.
	 */
	public function test_get_api_from_header( $api_header, $expect_api = true ) {

		$this->mock_apps();

		wordpoints_apps()
			->get_sub_app( 'extension_server_apis' )
			->register( 'test', 'stdClass' );

		$slug = 'example.com';

		$filter = new WordPoints_PHPUnit_Mock_Filter(
			array( 'headers' => array( 'x-wordpoints-module-api' => $api_header ) )
		);

		$filter->add_filter( 'pre_http_request' );

		$server = new WordPoints_Extension_Server( $slug );

		if ( $expect_api ) {
			$this->assertInternalType( 'object', $server->get_api() );
		} else {
			$this->assertFalse( $server->get_api() );
		}

		$this->assertSame( 2, $filter->call_count );

		// Test that the result is cached.
		if ( $expect_api ) {
			$this->assertInternalType( 'object', $server->get_api() );
		} else {
			$this->assertFalse( $server->get_api() );
		}

		$this->assertSame( 2, $filter->call_count );
	}

	/**
	 * Data provider for response codes for the SSL support check.
	 *
	 * @since 2.4.0
	 *
	 * @return array Possible response codes, and whether they indicate support.
	 */
	public function data_provider_api_headers() {
		return array(
			'registered' => array( 'test' ),
			'unregistered' => array( 'other', false ),
			'unset' => array( null, false ),
		);
	}
}

// EOF
