<?php

/**
 * Test case for the WordPoints_Installables class.
 *
 * @package WordPoints\Tests
 * @since 2.0.0
 */

/**
 * Tests the WordPoints_Installables class.
 *
 * @since 2.0.0
 *
 * @covers WordPoints_Installables
 */
class WordPoints_Installables_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.0.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		/**
		 * The administration-side code of the plugin.
		 *
		 * We need to include this for testing the admin_notice() method.
		 *
		 * @since 2.0.0
		 */
		require_once( WORDPOINTS_DIR . '/admin/admin.php' );

		WordPoints_Installables::register(
			'module'
			, 'test'
			, array(
				'version'      => '1.0.0',
				'network_wide' => false,
				'un_installer' => WORDPOINTS_TESTS_DIR . '/includes/mocks/un-installer-module.php',
			)
		);

		if ( is_wordpoints_network_active() ) {
			WordPoints_Installables::register(
				'module'
				, 'network_test'
				, array(
					'version'      => '1.0.0',
					'network_wide' => true,
					'un_installer' => WORDPOINTS_TESTS_DIR . '/includes/mocks/un-installer-module2.php',
				)
			);
		}
	}

	/**
	 * Test installing an entity.
	 *
	 * @since 2.0.0
	 */
	public function test_install() {

		$installer = WordPoints_Installables::install( 'module', 'test' );

		$this->assertInstanceOf( 'WordPoints_Un_Installer_Module_Mock', $installer );

		$this->assertSame( '1.0.0', $installer->version );
		$this->assertSame( 'test', $installer->slug );
		$this->assertSame( 'install', $installer->action );
		$this->assertFalse( $installer->network_wide );
	}

	/**
	 * Test installing a network-activated entity.
	 *
	 * @since 2.0.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_install_network_wide() {

		$installer = WordPoints_Installables::install( 'module', 'network_test', true );

		$this->assertInstanceOf( 'WordPoints_Un_Installer_Module_Mock2', $installer );

		$this->assertSame( '1.0.0', $installer->version );
		$this->assertSame( 'network_test', $installer->slug );
		$this->assertSame( 'install', $installer->action );
		$this->assertTrue( $installer->network_wide );
	}

	/**
	 * Test installing an unregistered entity.
	 *
	 * @since 2.0.0
	 */
	public function test_install_not_registered() {

		$this->assertFalse(
			WordPoints_Installables::install( 'module', 'invalid' )
		);
	}

	/**
	 * Test installing an entity with no un/installer.
	 *
	 * @since 2.0.0
	 */
	public function test_install_no_un_installer() {

		WordPoints_Installables::register(
			'module'
			, __METHOD__
			, array(
				'version'      => '1.0.0',
				'network_wide' => false,
				'un_installer' => WORDPOINTS_TESTS_DIR . '/invalid.php',
			)
		);

		$this->assertFalse(
			WordPoints_Installables::install( 'module', __METHOD__ )
		);
	}

	/**
	 * Test uninstalling an entity.
	 *
	 * @since 2.0.0
	 */
	public function test_uninstall() {

		// First we need to install it.
		WordPoints_Installables::install( 'module', 'test' );

		$installer = WordPoints_Installables::uninstall( 'module', 'test' );

		$this->assertInstanceOf( 'WordPoints_Un_Installer_Module_Mock', $installer );

		$this->assertSame( '1.0.0', $installer->version );
		$this->assertSame( 'test', $installer->slug );
		$this->assertSame( 'uninstall', $installer->action );
	}

	/**
	 * Test uninstalling an unregistered entity.
	 *
	 * @since 2.0.0
	 */
	public function test_uninstall_not_registered() {

		$this->assertFalse(
			WordPoints_Installables::uninstall( 'module', 'invalid' )
		);
	}

	/**
	 * Test uninstalling an entity with no un/installer.
	 *
	 * @since 2.0.0
	 */
	public function test_uninstall_no_un_installer() {

		WordPoints_Installables::register(
			'module'
			, __METHOD__
			, array(
				'version'      => '1.0.0',
				'network_wide' => false,
				'un_installer' => WORDPOINTS_TESTS_DIR . '/invalid.php',
			)
		);

		$this->assertFalse(
			WordPoints_Installables::uninstall( 'module', __METHOD__ )
		);
	}

	/**
	 * Test updating an entity.
	 *
	 * @since 2.0.0
	 */
	public function test_maybe_do_updates() {

		$this->set_module_db_version( 'test', '0.9.2' );
		$this->assertSame( '0.9.2', $this->get_module_db_version( 'test' ) );

		WordPoints_Installables::maybe_do_updates();

		$this->assertSame( '1.0.0', $this->get_module_db_version( 'test' ) );
	}

	/**
	 * Test updating a network-wide entity.
	 *
	 * @since 2.0.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_maybe_do_updates_network() {

		$this->set_module_db_version( 'network_test', '0.9.2', true );
		$this->assertSame( '0.9.2', $this->get_module_db_version( 'network_test', true ) );

		WordPoints_Installables::maybe_do_updates();

		$this->assertSame( '1.0.0', $this->get_module_db_version( 'network_test', true ) );
	}

	/**
	 * Test updating a uninstalled entity.
	 *
	 * @since 2.0.0
	 */
	public function test_maybe_do_updates_not_installed() {

		$this->assertSame( '', $this->get_module_db_version( 'test' ) );

		WordPoints_Installables::maybe_do_updates();

		$this->assertSame( '', $this->get_module_db_version( 'test' ) );
	}

	/**
	 * Test updating WordPoints when it is uninstalled.
	 *
	 * @since 2.0.2
	 */
	public function test_maybe_do_updates_wordpoints_not_installed() {

		wordpoints_update_maybe_network_option(
			'wordpoints_data'
			, array( 'a' => 'b' )
		);

		$this->assertSame( '', $this->wordpoints_get_db_version() );

		WordPoints_Installables::maybe_do_updates();

		$this->assertSame( WORDPOINTS_VERSION, $this->wordpoints_get_db_version() );
	}

	/**
	 * Test updating an entity that is up to date.
	 *
	 * @since 2.0.0
	 */
	public function test_maybe_do_updates_up_to_date() {

		$this->set_module_db_version( 'test', '1.0.0' );
		$this->assertSame( '1.0.0', $this->get_module_db_version( 'test' ) );

		WordPoints_Installables::maybe_do_updates();

		$this->assertSame( '1.0.0', $this->get_module_db_version( 'test' ) );
	}

	/**
	 * Test updating an entity with a newer db version.
	 *
	 * @since 2.0.0
	 */
	public function test_maybe_do_updates_newer_db_version() {

		$this->set_module_db_version( 'test', '1.1.0' );
		$this->assertSame( '1.1.0', $this->get_module_db_version( 'test' ) );

		WordPoints_Installables::maybe_do_updates();

		$this->assertSame( '1.1.0', $this->get_module_db_version( 'test' ) );
	}

	/**
	 * Test that no admin notices are shown by default.
	 *
	 * @since 2.0.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_admin_notices_none() {

		$this->give_current_user_caps( 'wordpoints_manage_network_modules' );

		ob_start();
		WordPoints_Installables::admin_notices();
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	/**
	 * Test that a notice is displayed when a module's install has been skipped.
	 *
	 * @since 2.0.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_admin_notices_module_install_skipped() {

		$this->give_current_user_caps( 'wordpoints_manage_network_modules' );

		update_site_option(
			'wordpoints_network_install_skipped'
			, array( 'module' => array( 'network_test' => true ) )
		);

		ob_start();
		WordPoints_Installables::admin_notices();
		$output = ob_get_clean();

		$this->assertWordPointsAdminNotice(
			$output
			, array(
				'type'        => 'error',
				'dismissible' => true,
				'option'      => 'wordpoints_network_install_skipped',
			)
		);
	}


	/**
	 * Test that a notice is displayed when a module's update has been skipped.
	 *
	 * @since 2.0.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_admin_notices_module_update_skipped() {

		$this->give_current_user_caps( 'wordpoints_manage_network_modules' );

		update_site_option(
			'wordpoints_network_update_skipped'
			, array( 'module' => array( 'network_test' => '3.0.0' ) )
		);

		ob_start();
		WordPoints_Installables::admin_notices();
		$output = ob_get_clean();

		$this->assertWordPointsAdminNotice(
			$output
			, array(
				'type'        => 'error',
				'dismissible' => true,
				'option'      => 'wordpoints_network_update_skipped',
			)
		);
	}

	/**
	 * Test that no notice is displayed when a module isn't network-active.
	 *
	 * @since 2.0.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_admin_notices_module_not_network_wide() {

		$this->give_current_user_caps( 'wordpoints_manage_network_modules' );

		update_site_option(
			'wordpoints_network_install_skipped'
			, array( 'module' => array( 'test' => true ) )
		);

		ob_start();
		WordPoints_Installables::admin_notices();
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	/**
	 * Test that no notice is displayed when the current user has insufficient caps.
	 *
	 * @since 2.0.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_admin_notices_insufficient_caps() {

		update_site_option(
			'wordpoints_network_install_skipped'
			, array( 'module' => array( 'network_test' => true ) )
		);

		ob_start();
		WordPoints_Installables::admin_notices();
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	/**
	 * Test getting the installer for a module.
	 *
	 * @since 2.0.0
	 */
	public function test_get_installer() {

		$installer = WordPoints_Installables::get_installer( 'module', 'test' );

		$this->assertInstanceOf( 'WordPoints_Un_Installer_Module_Mock', $installer );

		$this->assertSame( '1.0.0', $installer->version );
		$this->assertSame( 'test', $installer->slug );
	}

	/**
	 * Test getting the installer for a nonexistent installable.
	 *
	 * @since 2.0.0
	 */
	public function test_get_installer_nonexistent() {

		$this->assertFalse(
			WordPoints_Installables::get_installer( 'module', 'invalid' )
		);
	}

	/**
	 * Test getting the installer for a nonexistent installable.
	 *
	 * @since 2.0.0
	 */
	public function test_get_installer_nonexistent_file() {

		WordPoints_Installables::register(
			'module'
			, __METHOD__
			, array(
				'version'      => '1.0.0',
				'network_wide' => false,
				'un_installer' => WORDPOINTS_TESTS_DIR . '/invalid.php',
			)
		);

		$this->assertFalse(
			WordPoints_Installables::get_installer( 'module', __METHOD__ )
		);
	}

	/**
	 * Test that network-wide installables are installed on new sites on creation.
	 *
	 * @since 2.0.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_wpmu_new_blog() {

		$blog_id = $this->factory->blog->create();

		switch_to_blog( $blog_id );
		$this->assertTrue( get_role( 'administrator' )->has_cap( 'install_wordpoints_modules' ) );
		restore_current_blog();
	}
}

// EOF
