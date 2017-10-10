<?php

/**
 * Module header parsing test case.
 *
 * @package WordPoints\Tests
 * @since 1.1.0
 */

/**
 * Test that module headers are properly parsed.
 *
 * @since 1.1.0
 *
 * @group modules
 *
 * @covers ::wordpoints_get_module_data
 */
class WordPoints_Module_Header_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Expected basic module header data.
	 *
	 * @since 1.1.0
	 *
	 * @type array $expected_headers
	 */
	private $expected_headers = array(
		'name'        => 'Test 3',
		'version'     => '1.0.0-beta',
		'author'      => 'WordPoints Tester',
		'author_uri'  => 'https://www.example.com/',
		'uri'         => 'https://www.example.com/test-3/',
		'module_uri'  => 'https://www.example.com/test-3/',
		'description' => 'A test module.',
		'text_domain' => 'test-3',
		'domain_path' => '',
		'network'     => false,
		'title'       => 'Test 3',
		'author_name' => 'WordPoints Tester',
		'update_api'  => '',
		'channel'     => '',
		'server'      => '',
		'ID'          => '',
		'namespace'   => '',
	);

	/**
	 * @since 2.4.0
	 */
	public function setUp() {

		parent::setUp();

		add_filter( 'wordpoints_extensions_dir', 'wordpoints_phpunit_extensions_dir' );
	}

	/**
	 * Test basic module header retrieval.
	 *
	 * @since 1.1.0
	 */
	public function test_basic_module_header_parsing() {

		$found_headers = wordpoints_get_module_data( WORDPOINTS_TESTS_DIR . '/data/modules/test-3.php', false, false );

		$this->assertSameSetsWithIndex( $this->expected_headers, $found_headers );
	}

	/**
	 * Test module header retrieval with markup.
	 *
	 * @since 1.1.0
	 */
	public function test_module_header_parsing_with_markup() {

		$this->expected_headers['title']       = '<a href="https://www.example.com/test-3/">Test 3</a>';
		$this->expected_headers['author']      = '<a href="https://www.example.com/">WordPoints Tester</a>';
		$this->expected_headers['description'] = 'A test module. <cite>By <a href="https://www.example.com/">WordPoints Tester</a>.</cite>';

		$marked_up_headers = wordpoints_get_module_data( WORDPOINTS_TESTS_DIR . '/data/modules/test-3.php', true, false );

		$this->assertSameSetsWithIndex( $this->expected_headers, $marked_up_headers );
	}

	/**
	 * Test that the header info from WordPoints_Modules is used if available.
	 *
	 * @since 2.0.0
	 */
	public function test_uses_wordpoints_modules_data_if_available() {

		$this->mock_apps();

		$mock_filter = new WordPoints_PHPUnit_Mock_Filter();

		add_filter( 'extra_wordpoints_module_headers', array( $mock_filter, 'filter' ) );

		$found_headers = wordpoints_get_module_data(
			WORDPOINTS_TESTS_DIR . '/data/modules/test-3.php'
			, false
			, false
		);

		$this->assertSameSetsWithIndex( $this->expected_headers, $found_headers );

		$this->assertSame( 1, $mock_filter->call_count );

		WordPoints_Modules::register(
			'
				Extension Name: Test 3
				Version:        1.0.0-beta
				Author:         WordPoints Tester
				Author URI:     https://www.example.com/
				Extension URI:  https://www.example.com/test-3/
				Description:    A test module.
				Text Domain:    test-3
			'
			, WORDPOINTS_TESTS_DIR . '/data/modules/test-3.php'
		);

		$found_headers = wordpoints_get_module_data(
			WORDPOINTS_TESTS_DIR . '/data/modules/test-3.php'
			, false
			, false
		);

		$this->assertSameSetsWithIndex( $this->expected_headers, $found_headers );

		$this->assertSame( 1, $mock_filter->call_count );
	}

	/**
	 * Test that it gives a deprecated error if using the Update API header.
	 *
	 * @since 2.4.0
	 *
	 * @expectedDeprecated wordpoints_get_module_data
	 */
	public function test_update_api_header_deprecated() {

		$this->mock_apps();

		WordPoints_Modules::register(
			'
				Extension Name: Test 3
				Version:        1.0.0-beta
				Author:         WordPoints Tester
				Author URI:     https://www.example.com/
				Extension URI:  https://www.example.com/test-3/
				Description:    A test module.
				Text Domain:    test-3
				Update API:     test
			'
			, WORDPOINTS_TESTS_DIR . '/data/modules/imaginary-1.php'
		);

		$found_headers = wordpoints_get_module_data(
			WORDPOINTS_TESTS_DIR . '/data/modules/imaginary-1.php'
			, false
			, false
		);

		$this->assertArrayHasKey( 'update_api', $found_headers );
		$this->assertSame( 'test', $found_headers['update_api'] );

		$this->assertArrayHasKey( 'server', $found_headers );
		$this->assertSame( '', $found_headers['server'] );
	}

	/**
	 * Test that it gives a deprecated error if using the Channel header.
	 *
	 * @since 2.4.0
	 *
	 * @expectedDeprecated wordpoints_get_module_data
	 */
	public function test_channel_header_deprecated() {

		$this->mock_apps();

		WordPoints_Modules::register(
			'
				Extension Name: Test 3
				Version:        1.0.0-beta
				Author:         WordPoints Tester
				Author URI:     https://www.example.com/
				Extension URI:  https://www.example.com/test-3/
				Description:    A test module.
				Text Domain:    test-3
				Channel:        wordpoints.org
			'
			, WORDPOINTS_TESTS_DIR . '/data/modules/imaginary-2.php'
		);

		$found_headers = wordpoints_get_module_data(
			WORDPOINTS_TESTS_DIR . '/data/modules/imaginary-2.php'
			, false
			, false
		);

		$this->assertArrayHasKey( 'channel', $found_headers );
		$this->assertSame( 'wordpoints.org', $found_headers['channel'] );

		$this->assertArrayHasKey( 'server', $found_headers );
		$this->assertSame( 'wordpoints.org', $found_headers['server'] );
	}

	/**
	 * Test that it gives a deprecated error if using the Module Name header.
	 *
	 * @since 2.4.0
	 *
	 * @expectedDeprecated wordpoints_get_module_data
	 */
	public function test_module_name_header_deprecated() {

		$this->mock_apps();

		WordPoints_Modules::register(
			'
				Module Name:   Test 3
				Version:       1.0.0-beta
				Author:        WordPoints Tester
				Author URI:    https://www.example.com/
				Extension URI: https://www.example.com/test-3/
				Description:   A test module.
				Text Domain:   test-3
			'
			, WORDPOINTS_TESTS_DIR . '/data/modules/imaginary-3.php'
		);

		$found_headers = wordpoints_get_module_data(
			WORDPOINTS_TESTS_DIR . '/data/modules/imaginary-3.php'
			, false
			, false
		);

		$this->assertArrayHasKey( 'name', $found_headers );
		$this->assertSame( 'Test 3', $found_headers['name'] );

		$this->assertArrayNotHasKey( 'module_name', $found_headers );
	}

	/**
	 * Test that it gives a deprecated error if using the Module URI header.
	 *
	 * @since 2.4.0
	 *
	 * @expectedDeprecated wordpoints_get_module_data
	 */
	public function test_module_uri_header_deprecated() {

		$this->mock_apps();

		WordPoints_Modules::register(
			'
				Extension Name: Test 3
				Version:        1.0.0-beta
				Author:         WordPoints Tester
				Author URI:     https://www.example.com/
				Module URI:     https://www.example.com/test-3/
				Description:    A test module.
				Text Domain:    test-3
			'
			, WORDPOINTS_TESTS_DIR . '/data/modules/imaginary-4.php'
		);

		$found_headers = wordpoints_get_module_data(
			WORDPOINTS_TESTS_DIR . '/data/modules/imaginary-4.php'
			, false
			, false
		);

		$this->assertArrayHasKey( 'uri', $found_headers );
		$this->assertSame( 'https://www.example.com/test-3/', $found_headers['uri'] );

		$this->assertArrayHasKey( 'module_uri', $found_headers );
		$this->assertSame( 'https://www.example.com/test-3/', $found_headers['module_uri'] );
	}

	/**
	 * Tests that it calls a filter.
	 *
	 * @since 2.4.0
	 */
	public function test_filter() {

		$headers         = $this->expected_headers;
		$headers['name'] = 'Test Three';

		$filter = new WordPoints_PHPUnit_Mock_Filter( $headers );
		$filter->add_filter( 'wordpoints_extension_data' );

		$data = wordpoints_get_module_data(
			WORDPOINTS_TESTS_DIR . '/data/modules/test-3.php'
			, false
			, false
		);

		$this->assertSame( 1, $filter->call_count );

		$this->assertSame( $headers['name'], $data['name'] );
	}

	/**
	 * Tests it applies the filter before adding markup.
	 *
	 * @since 2.4.0
	 */
	public function test_filter_markup() {

		$headers         = $this->expected_headers;
		$headers['name'] = 'Test Three';

		$filter = new WordPoints_PHPUnit_Mock_Filter( $headers );
		$filter->add_filter( 'wordpoints_extension_data' );

		$data = wordpoints_get_module_data(
			WORDPOINTS_TESTS_DIR . '/data/modules/test-3.php'
			, true
		);

		$this->assertSame( 1, $filter->call_count );

		$this->assertSame( $headers['name'], $data['name'] );
		$this->assertSame(
			'<a href="https://www.example.com/test-3/">Test Three</a>'
			, $data['title']
		);
	}
}

// EOF
