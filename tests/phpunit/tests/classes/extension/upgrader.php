<?php

/**
 * Test case for WordPoints_Extension_Upgrader.
 *
 * @package WordPoints\Tests
 * @since 2.4.0
 */

/**
 * Tests the WordPoints_Extension_Upgrader class.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Extension_Upgrader
 */
class WordPoints_Extension_Upgrader_Test extends WordPoints_Module_Installer_Test {

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
			'wordpoints_server_object_for_extension'
			, array( $this, 'filter_server_for_extension' )
			, 10
			, 2
		);

		$updates = new WordPoints_Extension_Updates();
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

			$extension_name = substr( $this->package_name, 0, -7 );

			$wp_filesystem->copy(
				WORDPOINTS_TESTS_DIR . '/data/module-packages/' . $extension_name . '/' . $extension_name . '.php'
				, wordpoints_extensions_dir() . $extension_name . '/' . $extension_name . '.php'
				, true
			);
		}

		parent::tearDown();
	}

	/**
	 * Filters the server used for the extension in the tests.
	 *
	 * @since 2.4.0
	 *
	 * @return WordPoints_Extension_ServerI
	 */
	public function filter_server_for_extension( $server, $extension ) {

		if ( '7' !== $extension['ID'] ) {
			return $server;
		}

		$api = $this->createMock(
			'WordPoints_Extension_Server_API_EDD_SL'
		);

		// We hard-code the key here so that the tests won't be broken if the actual
		// key is ever updated.
		$api->method( 'get_extension_public_key_ed25519' )->willReturn(
			'9c564cdb1763a72a81f2ddee1e27230ea4c18748ee14324ac4671d4be701492e'
		);

		$signatures = array(
			'module-7-update' => 'ff6910b2f3760d62bf6cccda5c5d558bf8544a783757bcff2f4625e039c7b1231b14de3bdceec2f385841c90f77bbc0bfd1f93a859ae24ff2fc0e42eb712f608',
			'no-module'       => '6d319e5c561f0caf97ed83b37693b3009be14a4bfbb304c2c8bbdbf4b508f0505d851c727334445f693f8e2e3624ce246bcc71082f014452fe8e047558228205',
		);

		$signature = $signatures[ $this->package_name ];

		$api->method( 'get_extension_package_signature_ed25519' )->willReturn(
			$signature
		);

		$server = $this->createMock( 'WordPoints_Extension_ServerI' );
		$server->method( 'get_api' )->willReturn( $api );

		return $server;
	}

	/**
	 * Test the upgrader.
	 *
	 * @since 2.4.0
	 */
	public function test_upgrade() {

		$result = $this->upgrade_test_extension(
			'module-7/module-7.php'
			, 'module-7-update'
		);

		$this->assertTrue( $result );

		$this->assertCount( 0, $this->skin->errors );
		$this->assertSame( 1, $this->skin->header_shown );
		$this->assertSame( 1, $this->skin->footer_shown );
	}

	/**
	 * Test the upgrader with an API that doesn't support Ed25519 verification.
	 *
	 * @since 2.5.0
	 *
	 * @expectedIncorrectUsage WordPoints_Extension_Upgrader::verify_package_signature
	 */
	public function test_upgrade_no_ed25519_verification() {

		$api = $this->createMock(
			'WordPoints_Extension_Server_API_Updates_InstallableI'
		);

		$server = $this->createMock( 'WordPoints_Extension_ServerI' );
		$server->method( 'get_api' )->willReturn( $api );

		$mock = new WordPoints_PHPUnit_Mock_Filter( $server );
		$mock->add_filter( 'wordpoints_server_object_for_extension' );

		$result = $this->upgrade_test_extension(
			'module-7/module-7.php'
			, 'module-7-update'
		);

		$this->assertTrue( $result );

		$this->assertCount( 0, $this->skin->errors );
		$this->assertSame( 1, $this->skin->header_shown );
		$this->assertSame( 1, $this->skin->footer_shown );
	}

	/**
	 * Test the upgrader with an API without an Ed25519 public key for an extension.
	 *
	 * @since 2.5.0
	 *
	 * @expectedIncorrectUsage WordPoints_Extension_Upgrader::verify_package_signature
	 */
	public function test_upgrade_no_ed25519_public_key() {

		$api = $this->createMock(
			'WordPoints_Extension_Server_API_EDD_SL'
		);

		$server = $this->createMock( 'WordPoints_Extension_ServerI' );
		$server->method( 'get_api' )->willReturn( $api );

		$mock = new WordPoints_PHPUnit_Mock_Filter( $server );
		$mock->add_filter( 'wordpoints_server_object_for_extension' );

		$result = $this->upgrade_test_extension(
			'module-7/module-7.php'
			, 'module-7-update'
		);

		$this->assertTrue( $result );

		$this->assertCount( 0, $this->skin->errors );
		$this->assertSame( 1, $this->skin->header_shown );
		$this->assertSame( 1, $this->skin->footer_shown );
	}

	/**
	 * Test the upgrader with an invalid Ed25519 signature for an extension.
	 *
	 * @since 2.5.0
	 */
	public function test_upgrade_invalid_ed25519_package_signature() {

		$api = $this->createMock(
			'WordPoints_Extension_Server_API_EDD_SL'
		);

		$api->method( 'get_extension_public_key_ed25519' )->willReturn(
			'9c564cdb1763a72a81f2ddee1e27230ea4c18748ee14324ac4671d4be701492e'
		);

		$api->method( 'get_extension_package_signature_ed25519' )->willReturn(
			'6d319e5c561f0caf97ed83b37693b3009be14a4bfbb304c2c8bbdbf4b508f0505d851c727334445f693f8e2e3624ce246bcc71082f014452fe8e047558228205'
		);

		$server = $this->createMock( 'WordPoints_Extension_ServerI' );
		$server->method( 'get_api' )->willReturn( $api );

		$mock = new WordPoints_PHPUnit_Mock_Filter( $server );
		$mock->add_filter( 'wordpoints_server_object_for_extension' );

		$result = $this->upgrade_test_extension(
			'module-7/module-7.php'
			, 'module-7-update'
		);

		$this->assertWPError( $result );
		$this->assertSame( 'ed25519_mismatch', $result->get_error_code() );

		$this->assertCount( 1, $this->skin->errors );
	}

	/**
	 * Test with a package that doesn't contain an extension.
	 *
	 * @since 2.4.0
	 */
	public function test_package_with_no_module() {

		$result = $this->upgrade_test_extension(
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

		$result = $this->upgrade_test_extension(
			'module-7/module-7.php'
			, 'module-7-update'
			, array()
			, array( 'clear_update_cache' => false )
		);

		$this->assertTrue( $result );

		// Check that the extension updates cache is not cleared.
		$this->assertSame(
			array( 'module-7/module-7.php' => '1.0.1' )
			, wordpoints_get_extension_updates()->get_new_versions()
		);

		// The extensions cache is still cleared though.
		$this->assertFalse( wp_cache_get( 'wordpoints_modules', 'wordpoints_modules' ) );
	}

	/**
	 * Test with an extension that isn't installed.
	 *
	 * @since 2.4.0
	 */
	public function test_not_installed() {

		$result = $this->upgrade_test_extension(
			'module-6/module-6.php'
			, 'module-6'
			, array( 'ID' => 6 )
		);

		$this->assertFalse( $result );
		$this->assertCount( 1, $this->skin->errors );
		$this->assertSame( 'not_installed', $this->skin->errors[0] );
	}

	/**
	 * Test with an extension that is already up to date.
	 *
	 * @since 2.4.0
	 */
	public function test_up_to_date() {

		$result = $this->upgrade_test_extension(
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
	 * Test with an extension that doesn't have a server specified.
	 *
	 * @since 2.4.0
	 */
	public function test_no_server() {

		add_filter( 'wordpoints_server_for_extension', '__return_false' );

		$result = $this->upgrade_test_extension(
			'module-7/module-7.php'
			, 'module-7-update'
		);

		$this->assertFalse( $result );
		$this->assertCount( 1, $this->skin->errors );
		$this->assertCount( 1, $this->skin->feedback );
		$this->assertSame( 'no_server', $this->skin->errors[0] );
	}

	/**
	 * Test with an extension whose server doesn't have a supported API.
	 *
	 * @since 2.4.0
	 */
	public function test_api_not_found() {

		$server = $this->createMock( 'WordPoints_Extension_ServerI' );
		$server->method( 'get_api' )->willReturn( false );

		$mock = new WordPoints_PHPUnit_Mock_Filter( $server );
		$mock->add_filter( 'wordpoints_server_object_for_extension' );

		$result = $this->upgrade_test_extension(
			'module-7/module-7.php'
			, 'module-7-update'
		);

		$this->assertFalse( $result );
		$this->assertCount( 1, $this->skin->errors );
		$this->assertCount( 1, $this->skin->feedback );
		$this->assertSame( 'api_not_found', $this->skin->errors[0] );
	}

	/**
	 * Test with an extension whose server uses an API that doesn't support updates.
	 *
	 * @since 2.4.0
	 */
	public function test_api_updates_not_supported() {

		$server = $this->createMock( 'WordPoints_Extension_ServerI' );
		$server->method( 'get_api' )->willReturn( new stdClass() );

		$mock = new WordPoints_PHPUnit_Mock_Filter( $server );
		$mock->add_filter( 'wordpoints_server_object_for_extension' );

		$result = $this->upgrade_test_extension(
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

		$result = $this->upgrade_test_extension(
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
	 * Test with a package that doesn't contain an extension.
	 *
	 * @since 2.4.0
	 */
	public function test_bulk_package_with_no_extension() {

		$this->bulk = true;

		$result = $this->upgrade_test_extension(
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

		$result = $this->upgrade_test_extension(
			'module-7/module-7.php'
			, 'module-7-update'
			, array()
			, array( 'clear_update_cache' => false )
		);

		$this->assertInternalType( 'array', $result );
		$this->assertArrayHasKey( 'module-7/module-7.php', $result );
		$this->assertInternalType( 'array', $result['module-7/module-7.php'] );

		// Check that the extension updates cache is not cleared.
		$this->assertSame(
			array( 'module-7/module-7.php' => '1.0.1' )
			, wordpoints_get_extension_updates()->get_new_versions()
		);

		// The extensions cache is still cleared though.
		$this->assertFalse( wp_cache_get( 'wordpoints_modules', 'wordpoints_modules' ) );
	}

	/**
	 * Test with an extension that isn't installed.
	 *
	 * @since 2.4.0
	 */
	public function test_bulk_not_installed() {

		$this->bulk = true;

		$result = $this->upgrade_test_extension(
			'module-6/module-6.php'
			, 'module-6'
			, array( 'ID' => 6 )
		);

		$this->assertSame( array( 'module-6/module-6.php' => false ), $result );
		$this->assertCount( 1, $this->skin->errors );
		$this->assertSame( 'not_installed', $this->skin->errors[0] );
	}

	/**
	 * Test with an extension that is already up to date.
	 *
	 * @since 2.4.0
	 */
	public function test_bulk_up_to_date() {

		$this->bulk = true;

		$result = $this->upgrade_test_extension(
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
	 * Upgrade a test extension.
	 *
	 * @since 2.4.0
	 *
	 * @param string $extension    The basename extension path.
	 * @param string $package_name The filename of the package to use.
	 * @param array  $api          Optionally override the default API array used.
	 * @param array  $args         Optional arguments passed to upgrade().
	 *
	 * @return mixed The result from the upgrader.
	 */
	public function upgrade_test_extension( $extension, $package_name, $api = array(), $args = array() ) {

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
				'title'  => 'Updating extension',
				'url'    => '',
				'nonce'  => 'install-module_' . $api['ID'],
				'module' => $extension,
				'type'   => 'web',
				'api'    => $api,
			)
		);

		$upgrader = new WordPoints_Extension_Upgrader( $this->skin );

		if ( $this->bulk ) {
			return $upgrader->bulk_upgrade( array( $extension ), $args );
		} else {
			return $upgrader->upgrade( $extension, $args );
		}
	}
}

// EOF
