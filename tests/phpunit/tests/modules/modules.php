<?php

/**
 * Modules test case.
 *
 * @package WordPoints\Tests
 * @since 1.0.1
 */

/**
 * Test the module related code.
 *
 * @since 1.0.1
 * @since 1.1.0 Tests the new module code, old tests moved to legacy.php
 *
 * @group modules
 */
class WordPoints_Modules_Test extends WP_UnitTestCase {

	/**
	 * Expected basic module header data.
	 *
	 * @since 2.0.0
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
		'update_api'  => '',
		'channel'     => '',
		'ID'          => '',
	);

	/**
	 * Test wordpoints_module_basename().
	 *
	 * @since 1.1.0
	 *
	 * @covers ::wordpoints_module_basename
	 */
	public function test_module_basename() {

		$this->assertEquals( 'module/module.php', wordpoints_module_basename( wordpoints_modules_dir() . '/module/module.php' ) );
	}

	/**
	 * Test wordpoints_get_modules().
	 *
	 * @since 1.1.0
	 *
	 * @covers ::wordpoints_get_modules
	 */
	public function test_get_modules() {

		$modules = wordpoints_get_modules();

		$this->assertInternalType( 'array', $modules );
		$this->assertArrayHasKey( 'test-3.php', $modules );

		$this->assertEquals( $this->expected_headers, $modules['test-3.php'] );

		$this->assertArrayHasKey( 'test-4/test-4.php', $modules );
		$this->assertEquals(
			array(
				'name'        => 'Test 4',
				'module_uri'  => 'http://www.example.com/test-4/',
				'version'     => '1.0.0',
				'description' => 'Another test module.',
				'author'      => 'WordPoints Tester',
				'author_uri'  => 'http://www.example.com/',
				'text_domain' => 'test-4',
				'domain_path' => '',
				'network'     => false,
				'title'       => 'Test 4',
				'author_name' => 'WordPoints Tester',
				'update_api'  => '',
				'channel'     => '',
				'ID'          => '',
			)
			, $modules['test-4/test-4.php']
		);

		$this->assertEquals(
			array(
				'test-4.php' => array(
					'name'        => 'Test 4',
					'module_uri'  => 'http://www.example.com/test-4/',
					'version'     => '1.0.0',
					'description' => 'Another test module.',
					'author'      => 'WordPoints Tester',
					'author_uri'  => 'http://www.example.com/',
					'text_domain' => 'test-4',
					'domain_path' => '',
					'network'     => false,
					'title'       => 'Test 4',
					'author_name' => 'WordPoints Tester',
					'update_api'  => '',
					'channel'     => '',
					'ID'          => '',
				),
			)
			, wordpoints_get_modules( '/test-4' )
		);
	}

	/**
	 * Test wordpoints_get_modules() with markup.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_get_modules
	 */
	public function test_get_modules_markup() {

		$modules = wordpoints_get_modules( '', true );

		$this->assertInternalType( 'array', $modules );
		$this->assertArrayHasKey( 'test-3.php', $modules );

		$this->expected_headers['title']       = '<a href="http://www.example.com/test-3/">Test 3</a>';
		$this->expected_headers['author']      = '<a href="http://www.example.com/">WordPoints Tester</a>';
		$this->expected_headers['description'] = 'A test module. <cite>By <a href="http://www.example.com/">WordPoints Tester</a>.</cite>';

		$this->assertEquals( $this->expected_headers, $modules['test-3.php'] );
	}
}

// EOF
