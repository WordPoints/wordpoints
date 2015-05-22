<?php

/**
 * Test case for the WordPoints_Modules class.
 *
 * @package WordPoints\Tests
 * @since 2.0.0
 */

/**
 * Tests the WordPoints_Modules class.
 *
 * @since 2.0.0
 *
 * @covers WordPoints_Modules
 */
class WordPoints_Modules_Class_Test extends WordPoints_UnitTestCase {

	/**
	 * Test the parsing of the header data.
	 *
	 * @since 2.0.0
	 */
	public function test_parsing_header() {

		WordPoints_Modules::register(
			'
		        Module Name: Demo Module
		        Version:     1.0.0
				Author:      WordPoints Tester
				Author URI:  http://www.example.com/
				Module URI:  http://www.example.com/demo/
				Description: A demo module.
				Text Domain: demo
		    '
			, wordpoints_modules_dir() . '/demo-module/demo-module.php'
		);

		$this->assertEquals(
			array(
				'name'        => 'Demo Module',
				'version'     => '1.0.0',
				'author'      => 'WordPoints Tester',
				'author_uri'  => 'http://www.example.com/',
				'module_uri'  => 'http://www.example.com/demo/',
				'description' => 'A demo module.',
				'text_domain' => 'demo',
				'raw'         => '
		        Module Name: Demo Module
		        Version:     1.0.0
				Author:      WordPoints Tester
				Author URI:  http://www.example.com/
				Module URI:  http://www.example.com/demo/
				Description: A demo module.
				Text Domain: demo
		    ',
			)
			, WordPoints_Modules::get_data( 'demo-module' )
		);
	}

	/**
	 * Test getting just one piece of data.
	 *
	 * @since 2.0.0
	 */
	public function test_get_data() {

		WordPoints_Modules::register(
			'
		        Module Name: Demo Module
		        Version:     1.0.0
				Author:      WordPoints Tester
				Author URI:  http://www.example.com/
				Module URI:  http://www.example.com/demo/
				Description: A demo module.
				Text Domain: demo
		    '
			, wordpoints_modules_dir() . '/demo-module/demo-module.php'
		);

		$this->assertEquals(
			'1.0.0'
			, WordPoints_Modules::get_data( 'demo-module', 'version' )
		);

		$this->assertFalse(
			WordPoints_Modules::get_data( 'demo-module', 'invalid' )
		);
	}

	/**
	 * Test getting data from a nonexistent module.
	 *
	 * @since 2.0.0
	 */
	public function test_get_data_nonexistent_module() {

		$this->assertFalse( WordPoints_Modules::get_data( 'invalid', 'invalid' ) );
		$this->assertFalse( WordPoints_Modules::get_data( 'invalid' ) );
	}

	/**
	 * Test getting the slug of a module.
	 *
	 * @since 2.0.0
	 */
	public function test_get_slug() {

		$this->assertEquals(
			'demo-module'
			, WordPoints_Modules::get_slug(
				wordpoints_modules_dir() . '/demo-module/demo-module.php'
			)
		);
	}

	/**
	 * Test getting the slug of a module outside the modules directory.
	 *
	 * @since 2.0.0
	 */
	public function test_get_slug_module_outside_modules_dir() {

		$this->assertEquals(
			'user'
			, WordPoints_Modules::get_slug(
				'/user/var/other-modules/demo-module/demo-module.php'
			)
		);
	}

	/**
	 * Test getting the slug of a module from a subdirectory file.
	 *
	 * @since 2.0.0
	 */
	public function test_get_slug_subdirectory_file() {

		$this->assertEquals(
			'demo-module'
			, WordPoints_Modules::get_slug(
				wordpoints_modules_dir() . '/demo-module/another/file.php'
			)
		);
	}
}

// EOF
