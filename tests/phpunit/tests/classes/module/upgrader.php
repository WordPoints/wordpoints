<?php

/**
 * Test case for WordPoints_Module_Upgrader.
 *
 * @package WordPoints\Tests
 * @since 2.4.0
 */

/**
 * Tests the WordPoints_Module_Upgrader class.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Module_Upgrader
 */
class WordPoints_Module_Upgrader_Test extends WordPoints_Module_Installer_Test {

	/**
	 * Whether a bulk update is being performed.
	 *
	 * @since 2.4.0
	 *
	 * @var bool
	 */
	protected $bulk = false;

	/**
	 * Set up for each test.
	 *
	 * @since 2.4.0
	 */
	public function setUp() {

		parent::setUp();

		add_filter(
			'wordpoints_server_object_for_module'
			, array( $this, 'filter_server_for_module' )
			, 10
			, 2
		);

		$updates = new WordPoints_Module_Updates();
		$updates->set_new_versions( array( 'module-7/module-7.php' => '1.0.1' ) );
		$updates->save();

		wp_cache_delete( 'wordpoints_modules', 'wordpoints_modules' );
	}

	/**
	 * Clean up after each test.
	 *
	 * @since 2.4.0
	 */
	public function tearDown() {

		global $wp_filesystem;

		if ( substr( $this->package_name, -7, 7 ) === '-update' && $wp_filesystem ) {

			$module_name = substr( $this->package_name, 0, -7 );

			$wp_filesystem->copy(
				WORDPOINTS_TESTS_DIR . '/data/module-packages/' . $module_name . '/' . $module_name . '.php'
				, wordpoints_modules_dir() . $module_name . '/' . $module_name . '.php'
				, true
			);
		}

		parent::tearDown();
	}

	/**
	 * Filters the server used for the module in the tests.
	 *
	 * @since 2.4.0
	 *
	 * @return WordPoints_Module_ServerI
	 */
	public function filter_server_for_module( $server, $module ) {

		if ( '7' !== $module['ID'] ) {
			return $server;
		}

		$api = $this->getMock( 'WordPoints_Module_Server_API_UpdatesI' );

		$server = $this->getMock(
			'WordPoints_Module_ServerI'
			, array()
			, array( 'test' )
		);

		$server->method( 'get_api' )->willReturn( $api );

		return $server;
	}

	/**
	 * Test the upgrader.
	 *
	 * @since 2.4.0
	 */
	public function test_upgrade() {

		$result = $this->upgrade_test_module(
			'module-7/module-7.php'
			, 'module-7-update'
		);

		$this->assertTrue( $result );

		$this->assertCount( 0, $this->skin->errors );
		$this->assertSame( 1, $this->skin->header_shown );
		$this->assertSame( 1, $this->skin->footer_shown );
	}

	/**
	 * Test with a package that doesn't contain a module.
	 *
	 * @since 2.4.0
	 */
	public function test_package_with_no_module() {

		$result = $this->upgrade_test_module(
			'module-7/module-7.php'
			, 'no-module'
		);

		$this->assertWPError( $result );
		$this->assertSame( 'incompatible_archive_no_modules', $result->get_error_code() );

		$this->assertCount( 1, $this->skin->errors );
	}

	/**
	 * Test the clear_update_cache argument.
	 *
	 * @since 2.4.0
	 */
	public function test_clear_update_cache() {

		$result = $this->upgrade_test_module(
			'module-7/module-7.php'
			, 'module-7-update'
			, array()
			, array( 'clear_update_cache' => false )
		);

		$this->assertTrue( $result );

		// Check that the module updates cache is not cleared.
		$this->assertSame(
			array( 'module-7/module-7.php' => '1.0.1' )
			, wordpoints_get_module_updates()->get_new_versions()
		);

		// The modules cache is still cleared though.
		$this->assertFalse( wp_cache_get( 'wordpoints_modules', 'wordpoints_modules' ) );
	}

	/**
	 * Test with a module that isn't installed.
	 *
	 * @since 2.4.0
	 */
	public function test_not_installed() {

		$result = $this->upgrade_test_module(
			'module-6/module-6.php'
			, 'module-6'
			, array( 'ID' => 6 )
		);

		$this->assertFalse( $result );
		$this->assertCount( 1, $this->skin->errors );
		$this->assertSame( 'not_installed', $this->skin->errors[0] );
	}

	/**
	 * Test with a module that is already up to date.
	 *
	 * @since 2.4.0
	 */
	public function test_up_to_date() {

		$result = $this->upgrade_test_module(
			'module-8/module-8.php'
			, 'module-8-not-really'
			, array( 'ID' => 8 )
		);

		$this->assertTrue( $result );
		$this->assertCount( 0, $this->skin->errors );
		$this->assertCount( 2, $this->skin->feedback );
		$this->assertSame( 'up_to_date', $this->skin->feedback[0] );
	}

	/**
	 * Test with a module that doesn't have a server specified.
	 *
	 * @since 2.4.0
	 */
	public function test_no_server() {

		add_filter( 'wordpoints_server_for_module', '__return_false' );

		$result = $this->upgrade_test_module(
			'module-7/module-7.php'
			, 'module-7-update'
		);

		$this->assertFalse( $result );
		$this->assertCount( 1, $this->skin->errors );
		$this->assertCount( 1, $this->skin->feedback );
		$this->assertSame( 'no_server', $this->skin->errors[0] );
	}

	/**
	 * Test with a module whose server doesn't have a supported API.
	 *
	 * @since 2.4.0
	 */
	public function test_api_not_found() {

		$server = $this->getMock(
			'WordPoints_Module_ServerI'
			, array()
			, array( 'test' )
		);

		$server->method( 'get_api' )->willReturn( false );

		$mock = new WordPoints_PHPUnit_Mock_Filter( $server );
		$mock->add_filter( 'wordpoints_server_object_for_module' );

		$result = $this->upgrade_test_module(
			'module-7/module-7.php'
			, 'module-7-update'
		);

		$this->assertFalse( $result );
		$this->assertCount( 1, $this->skin->errors );
		$this->assertCount( 1, $this->skin->feedback );
		$this->assertSame( 'api_not_found', $this->skin->errors[0] );
	}

	/**
	 * Test with a module whose server uses an API that doesn't support updates.
	 *
	 * @since 2.4.0
	 */
	public function test_api_updates_not_supported() {

		$server = $this->getMock(
			'WordPoints_Module_ServerI'
			, array()
			, array( 'test' )
		);

		$server->method( 'get_api' )->willReturn( new stdClass() );

		$mock = new WordPoints_PHPUnit_Mock_Filter( $server );
		$mock->add_filter( 'wordpoints_server_object_for_module' );

		$result = $this->upgrade_test_module(
			'module-7/module-7.php'
			, 'module-7-update'
		);

		$this->assertFalse( $result );
		$this->assertCount( 1, $this->skin->errors );
		$this->assertCount( 1, $this->skin->feedback );
		$this->assertSame( 'api_updates_not_supported', $this->skin->errors[0] );
	}

	/**
	 * Tests performing a bulk upgrade.
	 *
	 * @since 2.4.0
	 */
	public function test_bulk_upgrade() {

		$this->bulk = true;

		$result = $this->upgrade_test_module(
			'module-7/module-7.php'
			, 'module-7-update'
		);

		$this->assertInternalType( 'array', $result );
		$this->assertArrayHasKey( 'module-7/module-7.php', $result );
		$this->assertInternalType( 'array', $result['module-7/module-7.php'] );

		$this->assertCount( 0, $this->skin->errors );
		$this->assertSame( 1, $this->skin->header_shown );
		$this->assertSame( 1, $this->skin->footer_shown );
		$this->assertSame( 1, $this->skin->bulk_header_shown );
		$this->assertSame( 1, $this->skin->bulk_footer_shown );
	}

	/**
	 * Test with a package that doesn't contain a module.
	 *
	 * @since 2.4.0
	 */
	public function test_bulk_package_with_no_module() {

		$this->bulk = true;

		$result = $this->upgrade_test_module(
			'module-7/module-7.php'
			, 'no-module'
		);

		$this->assertInternalType( 'array', $result );
		$this->assertArrayHasKey( 'module-7/module-7.php', $result );
		$this->assertWPError( $result['module-7/module-7.php'] );
		$this->assertSame( 'incompatible_archive_no_modules', $result['module-7/module-7.php']->get_error_code() );

		$this->assertCount( 1, $this->skin->errors );
	}

	/**
	 * Test the clear_update_cache argument.
	 *
	 * @since 2.4.0
	 */
	public function test_bulk_clear_update_cache() {

		$this->bulk = true;

		$result = $this->upgrade_test_module(
			'module-7/module-7.php'
			, 'module-7-update'
			, array()
			, array( 'clear_update_cache' => false )
		);

		$this->assertInternalType( 'array', $result );
		$this->assertArrayHasKey( 'module-7/module-7.php', $result );
		$this->assertInternalType( 'array', $result['module-7/module-7.php'] );

		// Check that the module updates cache is not cleared.
		$this->assertSame(
			array( 'module-7/module-7.php' => '1.0.1' )
			, wordpoints_get_module_updates()->get_new_versions()
		);

		// The modules cache is still cleared though.
		$this->assertFalse( wp_cache_get( 'wordpoints_modules', 'wordpoints_modules' ) );
	}

	/**
	 * Test with a module that isn't installed.
	 *
	 * @since 2.4.0
	 */
	public function test_bulk_not_installed() {

		$this->bulk = true;

		$result = $this->upgrade_test_module(
			'module-6/module-6.php'
			, 'module-6'
			, array( 'ID' => 6 )
		);

		$this->assertSame( array( 'module-6/module-6.php' => false ), $result );
		$this->assertCount( 1, $this->skin->errors );
		$this->assertSame( 'not_installed', $this->skin->errors[0] );
	}

	/**
	 * Test with a module that is already up to date.
	 *
	 * @since 2.4.0
	 */
	public function test_bulk_up_to_date() {

		$this->bulk = true;

		$result = $this->upgrade_test_module(
			'module-8/module-8.php'
			, 'module-8-not-really'
			, array( 'ID' => 8 )
		);

		$this->assertSame( array( 'module-8/module-8.php' => true ), $result );
		$this->assertCount( 0, $this->skin->errors );

		if ( is_multisite() ) {
			$this->assertSame( 'up_to_date', $this->skin->feedback[1] );
		} else {
			$this->assertSame( 'up_to_date', $this->skin->feedback[0] );
		}
	}
	//
	// Helpers.
	//

	/**
	 * Upgrade a test module.
	 *
	 * @since 2.4.0
	 *
	 * @param string $module       The basename module path.
	 * @param string $package_name The filename of the package to use.
	 * @param array  $api          Optionally override the default API array used.
	 * @param array  $args         Optional arguments passed to upgrade().
	 *
	 * @return mixed The result from the upgrader.
	 */
	public function upgrade_test_module( $module, $package_name, $api = array(), $args = array() ) {

		$this->package_name = $package_name;

		$api = array_merge(
			array(
				'ID'      => 7,
				'version' => '1.0.0',
			)
			, $api
		);

		$this->skin = new WordPoints_PHPUnit_Mock_Module_Installer_Skin(
			array(
				'title'  => 'Updating module',
				'url'    => '',
				'nonce'  => 'install-module_' . $api['ID'],
				'module' => $module,
				'type'   => 'web',
				'api'    => $api,
			)
		);

		$upgrader = new WordPoints_Module_Upgrader( $this->skin );

		if ( $this->bulk ) {
			return $upgrader->bulk_upgrade( array( $module ), $args );
		} else {
			return $upgrader->upgrade( $module, $args );
		}
	}
}

// EOF
