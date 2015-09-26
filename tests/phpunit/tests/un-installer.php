<?php

/**
 * Test case for the WordPoints_Un_Installer class.
 *
 * @package WordPoints\Tests
 * @since 2.0.0
 */

/**
 * Tests for the WordPoints un/installer class.
 *
 * @since 2.0.0
 */
class WordPoints_Un_Installer_Test extends WordPoints_UnitTestCase {

	/**
	 * The mock un/installer used in the tests.
	 *
	 * @since 2.0.0
	 *
	 * @var WordPoints_Un_Installer
	 */
	protected $un_installer;

	/**
	 * @since 2.0.0
	 */
	public function setUp() {

		parent::setUp();

		$this->un_installer = new WordPoints_Un_Installer(
			'wordpoints'
			, WORDPOINTS_VERSION
		);

		delete_site_transient( 'wordpoints_all_site_ids' );
	}

	/**
	 * Test that custom caps are loaded and installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer::install_on_site
	 *
	 * @requires WordPress multisite
	 */
	public function test_install_on_site_custom_caps() {

		$this->un_installer->install_on_site( get_current_blog_id() );

		$this->assertCustomCapsAdded();
	}

	/**
	 * Test database version is set on network install when network wide.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer::install_network
	 *
	 * @requires WordPoints network-active
	 */
	public function test_install_network_set_db_version_network_wide() {

		$this->un_installer->install( true );

		$this->assertEquals( WORDPOINTS_VERSION, $this->wordpoints_get_db_version() );
	}

	/**
	 * Test database version is set on site install when not network wide.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer::install_site
	 *
	 * @requires WordPoints !network-active
	 * @requires WordPress multisite
	 */
	public function test_install_site_set_db_version() {

		$this->un_installer->install( false );

		$this->assertEquals( WORDPOINTS_VERSION, $this->wordpoints_get_db_version() );
	}

	/**
	 * Test that custom caps are added on site install.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer::install_site
	 *
	 * @requires WordPress multisite
	 */
	public function test_install_site_custom_caps() {

		$this->un_installer->install( true );

		$this->assertCustomCapsAdded();
	}

	/**
	 * Test database version is set on single install.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer::install_single
	 *
	 * @requires WordPress !multisite
	 */
	public function test_install_single_set_db_version() {

		$this->un_installer->install( false );

		$this->assertEquals( WORDPOINTS_VERSION, $this->wordpoints_get_db_version() );
	}

	/**
	 * Test that custom caps are added on single install.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer::install_single
	 *
	 * @requires WordPress !multisite
	 */
	public function test_install_single_custom_caps() {

		$this->un_installer->install( false );

		$this->assertCustomCapsAdded();
	}

	/**
	 * Test database version is set on reactivation.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer::install_network
	 */
	public function test_reactivate_set_db_version() {

		wordpoints_update_network_option( 'wordpoints_data', array( 'a' => 'b' ) );

		$this->un_installer->install( is_wordpoints_network_active() );

		$this->assertEquals( WORDPOINTS_VERSION, $this->wordpoints_get_db_version() );
	}
	//
	// Helpers.
	//

	/**
	 * Assert that custom capabilities were loaded.
	 *
	 * @since 2.0.0
	 */
	public function assertCustomCapsAdded() {

		// Flush the cache.
		unset( $GLOBALS['wp_roles'] );

		// Check that the capabilities were added.
		$administrator = get_role( 'administrator' );
		$this->assertTrue( $administrator->has_cap( 'install_wordpoints_modules' ) );
		$this->assertTrue( $administrator->has_cap( 'activate_wordpoints_modules' ) );
		$this->assertTrue( $administrator->has_cap( 'delete_wordpoints_modules' ) );
	}
}

// EOF
