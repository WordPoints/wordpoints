<?php

/**
 * A test case for the module installer class.
 *
 * @package WordPoints\Tests
 * @since 2.0.0
 */

/**
 * Test that modules are installed correctly.
 *
 * @since 2.0.0
 *
 * @covers WordPoints_Module_Installer
 */
class WordPoints_Module_Installer_Test extends WordPoints_PHPUnit_TestCase_Admin {

	/**
	 * The name of the module package to use in the test.
	 *
	 * @since 2.0.0
	 *
	 * @type string $package_name
	 */
	protected $package_name;

	/**
	 * The mock installer skin used in the tests.
	 *
	 * @since 2.0.0
	 *
	 * @var WordPoints_PHPUnit_Mock_Module_Installer_Skin
	 */
	protected $skin;

	/**
	 * @since 2.0.0
	 */
	public function setUp() {

		parent::setUp();

		add_filter( 'wordpoints_extensions_dir', 'wordpoints_phpunit_extensions_dir' );
		add_filter( 'filesystem_method', array( $this, 'use_direct_filesystem_method' ) );
		add_filter( 'upgrader_pre_download', array( $this, 'module_package' ) );

		$updates = new WordPoints_Extension_Updates();
		$updates->set_new_versions( array( 'test' => '1.2.0' ) );
		$updates->save();

		wp_cache_set( 'wordpoints_modules', array( 'test' ), 'wordpoints_modules' );

		// Remove unnecessary actions that will result in requests to WordPress.org.
		remove_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );
		remove_action( 'upgrader_process_complete', 'wp_version_check' );
		remove_action( 'upgrader_process_complete', 'wp_update_plugins' );
		remove_action( 'upgrader_process_complete', 'wp_update_themes' );
		remove_action( 'upgrader_process_complete', 'wordpoints_recheck_for_extension_updates_after_upgrade' );
	}

	/**
	 * @since 2.0.0
	 */
	public function tearDown() {

		/** @var WP_FileSystem_Base $wp_filesystem */
		global $wp_filesystem;

		if ( $wp_filesystem && $wp_filesystem->exists( wordpoints_extensions_dir() . '/' . $this->package_name ) ) {
			$wp_filesystem->delete( wordpoints_extensions_dir() . '/' . $this->package_name, true );
		}

		remove_filter( 'filesystem_method', array( $this, 'use_direct_filesystem_method' ) );
		remove_filter( 'upgrader_pre_download', array( $this, 'module_package' ) );

		parent::tearDown();
	}

	/**
	 * Test that installation works.
	 *
	 * @since 2.0.0
	 */
	public function test_install() {

		$result = $this->install_test_package( 'module-6' );

		$this->assertTrue( $result );

		$this->assertFileExists( wordpoints_extensions_dir() . '/module-6/module-6.php' );

		// Check that the module cache is cleared.
		$this->assertFalse( wp_cache_get( 'wordpoints_modules', 'wordpoints_modules' ) );
		$this->assertSame( array(), wordpoints_get_extension_updates()->get_new_versions() );

		$this->assertCount( 0, $this->skin->errors );
		$this->assertSame( 1, $this->skin->header_shown );
		$this->assertSame( 1, $this->skin->footer_shown );
	}

	/**
	 * Test the clear_update_cache argument.
	 *
	 * @since 2.4.0
	 */
	public function test_clear_update_cache() {

		$result = $this->install_test_package(
			'module-6'
			, array( 'clear_update_cache' => false )
		);

		$this->assertTrue( $result );

		$this->assertFileExists( wordpoints_extensions_dir() . '/module-6/module-6.php' );

		// Check that the module updates cache is not cleared.
		$this->assertSame(
			array( 'test' => '1.2.0' )
			, wordpoints_get_extension_updates()->get_new_versions()
		);

		// The modules cache is still cleared though.
		$this->assertFalse( wp_cache_get( 'wordpoints_modules', 'wordpoints_modules' ) );
	}

	/**
	 * Test that the package must contain a module to be installed.
	 *
	 * @since 2.0.0
	 */
	public function test_package_with_no_module() {

		$result = $this->install_test_package( 'no-module' );

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertSame( 'incompatible_archive_no_modules', $result->get_error_code() );

		$this->assertFileNotExists( wordpoints_extensions_dir() . '/on-module/plugin.php' );
	}

	/**
	 * Test that it doesn't overwrite existing modules.
	 *
	 * @since 2.0.0
	 */
	public function test_doesnt_overwrite_existing() {

		$result = $this->install_test_package( 'module-7' );

		// So that the module won't be deleted.
		$this->package_name = 'do_not_delete';

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertSame( 'folder_exists', $result->get_error_code() );

		$this->assertFileExists( wordpoints_extensions_dir() . '/module-7/module-7.php' );
	}

	//
	// Helpers.
	//

	/**
	 * Use the direct filesystem method.
	 *
	 * @since 2.0.0
	 *
	 * @WordPress\filter filesystem_method Added by self::setUp().
	 */
	public function use_direct_filesystem_method() {

		return 'direct';
	}

	/**
	 * Get the path to the module package to use in the tests.
	 *
	 * @since 2.0.0
	 *
	 * @WordPress\filter upgrader_pre_download Added by self::setUp().
	 */
	public function module_package() {

		$package_name = WORDPOINTS_TESTS_DIR . '/data/module-packages/' . $this->package_name;

		if ( ! file_exists( $package_name . '.zip' ) ) {
			copy( $package_name . '.bk.zip', $package_name . '.zip' );
		}

		return $package_name . '.zip';
	}

	/**
	 * Run the installer with one of the test packages.
	 *
	 * @since 2.0.0
	 * @since 2.4.0 Added the $args parameter.
	 *
	 * @param string $package_name The name of the package file, without extension.
	 * @param array  $args         Optional arguments to pass to install().
	 *
	 * @return mixed The result from the upgrader.
	 */
	protected function install_test_package( $package_name, $args = array() ) {

		$this->package_name = $package_name;

		$api = array(
			'ID'      => 15,
			'version' => '1.0.0',
		);

		$this->skin = new WordPoints_PHPUnit_Mock_Module_Installer_Skin(
			array(
				'title'  => 'Installing module',
				'url'    => '',
				'nonce'  => 'install-module_' . $api['ID'],
				'module' => $api['ID'],
				'type'   => 'web',
				'api'    => $api,
			)
		);

		$upgrader = new WordPoints_Module_Installer( $this->skin );

		return $upgrader->install(
			WORDPOINTS_TESTS_DIR . '/data/module-packages/' . $package_name . '.zip'
			, $args
		);
	}
}

// EOF
