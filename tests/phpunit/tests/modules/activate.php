<?php

/**
 * Module activation test case.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.2.0
 */

/**
 * Test the module activation code.
 *
 * @since 2.2.0
 *
 * @group modules
 */
class WordPoints_Module_Activate_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.2.0
	 */
	public function setUp() {

		parent::setUp();

		add_filter( 'wordpoints_extensions_dir', 'wordpoints_phpunit_extensions_dir' );
	}

	/**
	 * Test wordpoints_activate_module().
	 *
	 * @since 2.2.0
	 *
	 * @covers ::wordpoints_activate_module
	 *
	 * @dataProvider data_provider_valid_modules
	 */
	public function test_activate( $module ) {

		$this->assertNull( wordpoints_activate_module( 'test-5/test-5.php' ) );
		$this->assertTrue( is_wordpoints_module_active( 'test-5/test-5.php' ) );

		$basename = wordpoints_module_basename( $module );

		$this->assertFalse( is_wordpoints_module_active( $basename ) );

		$this->assertNull( wordpoints_activate_module( $module ) );

		$this->assertTrue( is_wordpoints_module_active( $basename ) );
		$this->assertTrue( is_wordpoints_module_active( 'test-5/test-5.php' ) );
	}

	/**
	 * Test wordpoints_activate_module().
	 *
	 * @since 2.2.0
	 *
	 * @covers ::wordpoints_activate_module
	 *
	 * @dataProvider data_provider_valid_modules
	 *
	 * @requires WordPoints network-active
	 *
	 * @param string $module The module path.
	 */
	public function test_activate_network_wide( $module ) {

		$this->assertNull( wordpoints_activate_module( 'test-5/test-5.php', '', true ) );
		$this->assertTrue( is_wordpoints_module_active_for_network( 'test-5/test-5.php' ) );

		$basename = wordpoints_module_basename( $module );

		$this->assertFalse( is_wordpoints_module_active_for_network( $basename ) );

		$this->assertNull( wordpoints_activate_module( $module, '', true ) );

		$this->assertTrue( is_wordpoints_module_active_for_network( $basename ) );
		$this->assertTrue( is_wordpoints_module_active_for_network( 'test-5/test-5.php' ) );
	}

	/**
	 * Data provider for valid modules.
	 *
	 * @since 2.2.0
	 *
	 * @return array[] A list of valid modules.
	 */
	public function data_provider_valid_modules() {
		return array(
			'full_path' => array( wordpoints_phpunit_extensions_dir() . '/module-7/module-7.php' ),
			'basename_path' => array( 'module-7/module-7.php' ),
			'full_path_single_file' => array( wordpoints_phpunit_extensions_dir() . '/test-3.php' ),
			'basename_path_single_file' => array( 'test-3.php' ),
		);
	}

	/**
	 * Test wordpoints_activate_module().
	 *
	 * @since 2.2.0
	 *
	 * @covers ::wordpoints_activate_module
	 *
	 * @dataProvider data_provider_invalid_modules
	 *
	 * @param string $module The module path.
	 */
	public function test_activate_invalid( $module ) {

		$this->assertNull( wordpoints_activate_module( 'test-5/test-5.php' ) );
		$this->assertTrue( is_wordpoints_module_active( 'test-5/test-5.php' ) );

		$basename = wordpoints_module_basename( $module );

		$this->assertFalse( is_wordpoints_module_active( $basename ) );

		$this->assertWPError( wordpoints_activate_module( $module ) );

		$this->assertFalse( is_wordpoints_module_active( $basename ) );
		$this->assertTrue( is_wordpoints_module_active( 'test-5/test-5.php' ) );
	}

	/**
	 * Test wordpoints_activate_module().
	 *
	 * @since 2.2.0
	 *
	 * @covers ::wordpoints_activate_module
	 *
	 * @dataProvider data_provider_invalid_modules
	 *
	 * @requires WordPoints network-active
	 *
	 * @param string $module The module path.
	 */
	public function test_activate_invalid_network_wide( $module ) {

		$this->assertNull( wordpoints_activate_module( 'test-5/test-5.php', '', true ) );
		$this->assertTrue( is_wordpoints_module_active_for_network( 'test-5/test-5.php' ) );

		$basename = wordpoints_module_basename( $module );

		$this->assertFalse( is_wordpoints_module_active_for_network( $basename ) );

		$this->assertWPError( wordpoints_activate_module( $module, '', true ) );

		$this->assertFalse( is_wordpoints_module_active_for_network( $basename ) );
		$this->assertTrue( is_wordpoints_module_active_for_network( 'test-5/test-5.php' ) );
	}

	/**
	 * Data provider for invalid modules.
	 *
	 * @since 2.2.0
	 *
	 * @return array[] A list of invalid modules.
	 */
	public function data_provider_invalid_modules() {
		return array(
			'unresolved_path' => array( 'module-7/../../../wp-config.php' ),
			'nonexistent' => array( 'module-4/module-4.php' ),
			'not_a_module' => array( 'test-6/index.php' ),
		);
	}

	/**
	 * Test wordpoints_activate_module().
	 *
	 * @since 2.2.0
	 *
	 * @covers ::wordpoints_activate_module
	 *
	 * @dataProvider data_provider_valid_modules
	 */
	public function test_activate_active() {

		$this->assertNull( wordpoints_activate_module( 'test-5/test-5.php' ) );
		$this->assertTrue( is_wordpoints_module_active( 'test-5/test-5.php' ) );
		$this->assertNull( wordpoints_activate_module( 'test-5/test-5.php' ) );
		$this->assertTrue( is_wordpoints_module_active( 'test-5/test-5.php' ) );
	}

	/**
	 * Test wordpoints_activate_module().
	 *
	 * @since 2.2.0
	 *
	 * @covers ::wordpoints_activate_module
	 *
	 * @requires WordPoints network-active
	 */
	public function test_activate_active_network_wide() {

		$this->assertNull( wordpoints_activate_module( 'test-5/test-5.php', '', true ) );
		$this->assertTrue( is_wordpoints_module_active_for_network( 'test-5/test-5.php' ) );
		$this->assertNull( wordpoints_activate_module( 'test-5/test-5.php', '', true ) );
		$this->assertTrue( is_wordpoints_module_active_for_network( 'test-5/test-5.php' ) );
	}
}

// EOF
