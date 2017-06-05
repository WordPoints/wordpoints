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
 *
 * @group modules
 */
class WordPoints_Modules_Class_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test the parsing of the header data.
	 *
	 * @since 2.0.0
	 */
	public function test_parsing_header() {

		WordPoints_Modules::register(
			'
				Extension Name: Demo Module
				Version:        1.0.0
				Author:         WordPoints Tester
				Author URI:     https://www.example.com/
				Extension URI:  https://www.example.com/demo/
				Description:    A demo module.
				Text Domain:    demo
				Namespace:      Demo
		    '
			, wordpoints_modules_dir() . '/demo-module/demo-module.php'
		);

		$this->assertSameSetsWithIndex(
			array(
				'name'        => 'Demo Module',
				'module_name' => '',
				'version'     => '1.0.0',
				'author'      => 'WordPoints Tester',
				'author_uri'  => 'https://www.example.com/',
				'uri'         => 'https://www.example.com/demo/',
				'module_uri'  => '',
				'description' => 'A demo module.',
				'text_domain' => 'demo',
				'domain_path' => '',
				'network'     => '',
				'update_api'  => '',
				'channel'     => '',
				'server'      => '',
				'ID'          => '',
				'namespace'   => 'Demo',
				'raw_file'    => wordpoints_modules_dir() . 'demo-module/demo-module.php',
				'raw'         => '
				Extension Name: Demo Module
				Version:        1.0.0
				Author:         WordPoints Tester
				Author URI:     https://www.example.com/
				Extension URI:  https://www.example.com/demo/
				Description:    A demo module.
				Text Domain:    demo
				Namespace:      Demo
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
				Extension Name: Demo Module
				Version:        1.0.0
				Author:         WordPoints Tester
				Author URI:     https://www.example.com/
				Extension URI:  https://www.example.com/demo/
				Description:    A demo module.
				Text Domain:    demo
				Namespace:      Demo
		    '
			, wordpoints_modules_dir() . '/demo-module/demo-module.php'
		);

		$this->assertSame(
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

		$this->assertSame(
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

		$this->assertSame(
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

		$this->assertSame(
			'demo-module'
			, WordPoints_Modules::get_slug(
				wordpoints_modules_dir() . '/demo-module/another/file.php'
			)
		);
	}
}

// EOF
