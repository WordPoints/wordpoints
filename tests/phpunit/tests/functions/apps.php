<?php

/**
 * Test case for the apps functions.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests the apps functions.
 *
 * @since 2.1.0
 */
class WordPoints_Apps_Functions_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test initializing the API registers the actions.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_apps_init
	 */
	public function test_init() {

		$apps = new WordPoints_App( 'test' );

		wordpoints_apps_init( $apps );

		$sub_apps = $apps->sub_apps();

		$this->assertTrue( $sub_apps->is_registered( 'hooks' ) );
		$this->assertTrue( $sub_apps->is_registered( 'entities' ) );
		$this->assertTrue( $sub_apps->is_registered( 'data_types' ) );
		$this->assertTrue( $sub_apps->is_registered( 'components' ) );
		$this->assertTrue( $sub_apps->is_registered( 'modules' ) );
		$this->assertTrue( $sub_apps->is_registered( 'extension_server_apis' ) );
	}

	/**
	 * Test getting the apps.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_apps
	 */
	public function test_get_apps() {

		$this->mock_apps();

		$this->assertInstanceOf( 'WordPoints_App', wordpoints_apps() );
	}

	/**
	 * Test getting the apps when they haven't been initialized.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_apps
	 */
	public function test_get_apps_not_initialized() {

		$this->mock_apps();

		WordPoints_App::$main = null;

		$this->assertInstanceOf( 'WordPoints_App', wordpoints_apps() );
	}

	/**
	 * Test the get post types for auto-integration function.
	 *
	 * @since 2.2.0
	 *
	 * @covers ::wordpoints_get_post_types_for_auto_integration
	 */
	public function test_get_post_types_for_auto_integration() {

		$filter = 'wordpoints_post_types_for_auto_integration';
		$this->listen_for_filter( $filter );

		$this->assertSame(
			get_post_types( array( 'public' => true ) )
			, wordpoints_get_post_types_for_auto_integration()
		);

		$this->assertSame( 1, $this->filter_was_called( $filter ) );
	}

	/**
	 * Test the get taxonomies for auto-integration function.
	 *
	 * @since 2.4.0
	 *
	 * @covers ::wordpoints_get_taxonomies_for_auto_integration
	 */
	public function test_get_taxonomies_for_auto_integration() {

		$filter = 'wordpoints_taxonomies_for_auto_integration';
		$this->listen_for_filter( $filter );

		$this->assertSame(
			get_taxonomies( array( 'public' => true ) )
			, wordpoints_get_taxonomies_for_auto_integration()
		);

		$this->assertSame( 1, $this->filter_was_called( $filter ) );
	}

	/**
	 * Test getting the app for a component.
	 *
	 * @since 2.2.0
	 *
	 * @covers ::wordpoints_component
	 */
	public function test_get_component_app() {

		$this->assertInstanceOf(
			'WordPoints_App'
			, wordpoints_component( 'points' )
		);
	}

	/**
	 * Test getting the app for an extension.
	 *
	 * @since 2.4.0
	 *
	 * @covers ::wordpoints_extension
	 */
	public function test_get_extension_app() {

		$this->mock_apps()
			->get_sub_app( 'extensions' )
			->sub_apps()
			->register( 'test', 'WordPoints_App' );

		$this->assertInstanceOf(
			'WordPoints_App'
			, wordpoints_extension( 'test' )
		);
	}

	/**
	 * Test getting the app for a module.
	 *
	 * @since 2.2.0
	 *
	 * @covers ::wordpoints_module
	 *
	 * @expectedDeprecated wordpoints_module
	 */
	public function test_get_module_app() {

		$this->mock_apps()
			->get_sub_app( 'modules' )
			->sub_apps()
			->register( 'test', 'WordPoints_App' );

		$this->assertInstanceOf(
			'WordPoints_App'
			, wordpoints_module( 'test' )
		);
	}

	/**
	 * Test initializing the Data Types app.
	 *
	 * @since 2.3.0
	 *
	 * @covers ::wordpoints_data_types_init
	 */
	public function test_data_types_init() {

		$this->mock_apps();

		$data_types = new WordPoints_Class_Registry();

		wordpoints_data_types_init( $data_types );

		$this->assertTrue( $data_types->is_registered( 'decimal_number' ) );
		$this->assertTrue( $data_types->is_registered( 'integer' ) );
		$this->assertTrue( $data_types->is_registered( 'text' ) );
	}

	/**
	 * Test initializing the Extension Server APIs app.
	 *
	 * @since 2.4.0
	 *
	 * @covers ::wordpoints_extension_server_apis_init
	 */
	public function test_extension_server_apis_init() {

		$this->mock_apps();

		$server_apis = new WordPoints_Class_Registry();

		wordpoints_extension_server_apis_init( $server_apis );

		$this->assertTrue( $server_apis->is_registered( 'edd_software_licensing' ) );
		$this->assertTrue( $server_apis->is_registered( 'edd_software_licensing_free' ) );
	}
}

// EOF
