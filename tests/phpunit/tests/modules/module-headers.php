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
 * @todo translation parsing test.
 */
class WordPoints_Module_Header_Test extends WP_UnitTestCase {

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
		'author_uri'  => 'http://www.example.com/',
		'module_uri'  => 'http://www.example.com/test-3/',
		'description' => 'A test module.',
		'text_domain' => 'test-3',
		'domain_path' => '',
		'network'     => false,
		'title'       => 'Test 3',
		'author_name' => 'WordPoints Tester',
	);

	/**
	 * Test basic module header retrieval.
	 *
	 * @since 1.1.0
	 */
	function test_basic_module_header_parsing() {

		$found_headers = wordpoints_get_module_data( WORDPOINTS_TESTS_DIR . '/data/modules/test-3.php', false, false );

		$this->assertEquals( $this->expected_headers, $found_headers );
	}

	/**
	 * Test module header retrieval with markup.
	 *
	 * @since 1.1.0
	 */
	function test_module_header_parsing_with_markup() {

		$this->expected_headers['title'] = '<a href="http://www.example.com/test-3/" title="Visit module homepage">Test 3</a>';
		$this->expected_headers['author'] = '<a href="http://www.example.com/" title="Visit author homepage">WordPoints Tester</a>';
		$this->expected_headers['description'] = 'A test module. <cite>By <a href="http://www.example.com/" title="Visit author homepage">WordPoints Tester</a>.</cite>';

		$marked_up_headers = wordpoints_get_module_data( WORDPOINTS_TESTS_DIR . '/data/modules/test-3.php', true, false );

		$this->assertEquals( $this->expected_headers, $marked_up_headers );
	}
}
