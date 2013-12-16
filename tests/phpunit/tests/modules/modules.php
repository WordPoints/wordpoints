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
	 * Test wordpoints_module_basename().
	 *
	 * @since 1.1.0
	 */
	public function test_module_basename() {

		$this->assertEquals( 'module/module.php', wordpoints_module_basename( wordpoints_modules_dir() . '/module/module.php' ) );
	}

	/**
	 * Test wordpoints_get_modules().
	 *
	 * @since 1.1.0
	 */
	public function test_get_modules() {

		$this->assertEquals(
			array(
				'test-3.php' => array(
					'name' => 'Test 3',
					'module_uri' => 'http://www.example.com/test-3/',
					'version' => '1.0.0-beta',
					'description' => 'A test module.',
					'author' => 'WordPoints Tester',
					'author_uri' => 'http://www.example.com/',
					'text_domain' => 'test-3',
					'domain_path' => '',
					'network' => false,
					'title' => 'Test 3',
					'author_name' => 'WordPoints Tester',
				),
				'test-4/test-4.php' => array(
					'name' => 'Test 4',
					'module_uri' => 'http://www.example.com/test-4/',
					'version' => '1.0.0',
					'description' => 'Another test module.',
					'author' => 'WordPoints Tester',
					'author_uri' => 'http://www.example.com/',
					'text_domain' => 'test-4',
					'domain_path' => '',
					'network' => false,
					'title' => 'Test 4',
					'author_name' => 'WordPoints Tester',
				),
			)
			, wordpoints_get_modules()
		);

		$this->assertEquals(
			array(
				'test-4.php' => array(
					'name' => 'Test 4',
					'module_uri' => 'http://www.example.com/test-4/',
					'version' => '1.0.0',
					'description' => 'Another test module.',
					'author' => 'WordPoints Tester',
					'author_uri' => 'http://www.example.com/',
					'text_domain' => 'test-4',
					'domain_path' => '',
					'network' => false,
					'title' => 'Test 4',
					'author_name' => 'WordPoints Tester',
				),
			)
			, wordpoints_get_modules( '/test-4' )
		);
	}
}
