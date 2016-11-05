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
class WordPoints_Module_Header_Test extends WordPoints_UnitTestCase {

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
		'module_uri'  => 'https://www.example.com/test-3/',
		'description' => 'A test module.',
		'text_domain' => 'test-3',
		'domain_path' => '',
		'network'     => false,
		'title'       => 'Test 3',
		'author_name' => 'WordPoints Tester',
		'update_api'  => '',
		'channel'     => '',
		'ID'          => '',
	);

	/**
	 * Test basic module header retrieval.
	 *
	 * @since 1.1.0
	 */
	public function test_basic_module_header_parsing() {

		$found_headers = wordpoints_get_module_data( WORDPOINTS_TESTS_DIR . '/data/modules/test-3.php', false, false );

		$this->assertEquals( $this->expected_headers, $found_headers );
	}

	/**
	 * Test module header retrieval with markup.
	 *
	 * @since 1.1.0
	 */
	public function test_module_header_parsing_with_markup() {

		$this->expected_headers['title'] = '<a href="https://www.example.com/test-3/">Test 3</a>';
		$this->expected_headers['author'] = '<a href="https://www.example.com/">WordPoints Tester</a>';
		$this->expected_headers['description'] = 'A test module. <cite>By <a href="https://www.example.com/">WordPoints Tester</a>.</cite>';

		$marked_up_headers = wordpoints_get_module_data( WORDPOINTS_TESTS_DIR . '/data/modules/test-3.php', true, false );

		$this->assertEquals( $this->expected_headers, $marked_up_headers );
	}

	/**
	 * Test that the header info from WordPoints_Modules is used if available.
	 *
	 * @since 2.0.0
	 */
	public function test_uses_wordpoints_modules_data_if_available() {

		$mock_filter = new WordPoints_Mock_Filter();

		add_filter( 'extra_wordpoints_module_headers', array( $mock_filter, 'filter' ) );

		$found_headers = wordpoints_get_module_data(
			WORDPOINTS_TESTS_DIR . '/data/modules/test-3.php'
			, false
			, false
		);

		$this->assertEquals( $this->expected_headers, $found_headers );

		$this->assertEquals( 1, $mock_filter->call_count );

		WordPoints_Modules::register(
			'
				Module Name: Test 3
				Version:     1.0.0-beta
				Author:      WordPoints Tester
				Author URI:  https://www.example.com/
				Module URI:  https://www.example.com/test-3/
				Description: A test module.
				Text Domain: test-3
			'
			, WORDPOINTS_TESTS_DIR . '/data/modules/test-3.php'
		);

		$found_headers = wordpoints_get_module_data(
			WORDPOINTS_TESTS_DIR . '/data/modules/test-3.php'
			, false
			, false
		);

		$this->assertEquals( $this->expected_headers, $found_headers );

		$this->assertEquals( 1, $mock_filter->call_count );
	}
}

// EOF
