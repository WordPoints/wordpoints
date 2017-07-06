<?php

/**
 * Testcase for the WordPoints_Un_Installer_Base class.
 *
 * @package WordPoints\Tests
 * @since 2.0.0
 */

/**
 * Tests for the base un/installer class.
 *
 * @since 2.0.0
 */
class WordPoints_Un_Installer_Base_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * The mock un/installer used in the tests.
	 *
	 * @since 2.0.0
	 *
	 * @var WordPoints_PHPUnit_Mock_Un_Installer
	 */
	protected $un_installer;

	/**
	 * Fake custom capabilities used in the tets.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	protected $custom_caps = array(
		'some_cap' => 'manage_options',
		'another_cap' => 'write',
	);

	/**
	 * @since 2.0.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		/**
		 * An un-installer with the $option_prefix property defined.
		 *
		 * @since 2.0.0
		 */
		require_once( WORDPOINTS_TESTS_DIR . '/includes/mocks/un-installer-option-prefix.php' );
	}

	/**
	 * @since 2.0.0
	 */
	public function setUp() {

		parent::setUp();

		$this->un_installer = new WordPoints_PHPUnit_Mock_Un_Installer( 'test', '1.0.0' );

		delete_site_transient( 'wordpoints_all_site_ids' );
	}

	/**
	 * @since 2.2.1
	 */
	public function tearDown() {

		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}test" );

		parent::tearDown();
	}

	/**
	 * Test that the slug and version are set on construction.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::__construct
	 */
	public function test_construct_slug_version_set() {

		$this->assertSame( 'test', $this->un_installer->slug );
		$this->assertSame( '1.0.0', $this->un_installer->version );
	}

	/**
	 * Test that the $slug parameter is required.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::__construct
	 *
	 * @expectedIncorrectUsage WordPoints_Un_Installer_Base::__construct
	 */
	public function test_construct_requires_slug() {

		new WordPoints_PHPUnit_Mock_Un_Installer( null, '1.0.0' );
	}

	/**
	 * Test that the $version parameter is required.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::__construct
	 *
	 * @expectedIncorrectUsage WordPoints_Un_Installer_Base::__construct
	 */
	public function test_construct_requires_version() {

		new WordPoints_PHPUnit_Mock_Un_Installer( 'test' );
	}

	/**
	 * Test that the $option_prefix property is deprecated.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::__construct
	 *
	 * @expectedDeprecated WordPoints_Un_Installer_Base::__construct
	 */
	public function test_option_prefix_deprecated() {

		new WordPoints_Un_Installer_Option_Prefix_Mock( 'test', '1.0.0' );
	}

	/**
	 * Test the basic behaviour of install().
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install
	 *
	 * @requires WordPress !multisite
	 */
	public function test_install_not_multisite() {

		$this->un_installer->install( false );

		$this->assertSame( 'install', $this->un_installer->action );
		$this->assertSame( 'single', $this->un_installer->context );
		$this->assertFalse( $this->un_installer->network_wide );

		$this->assertSame( '1.0.0', $this->un_installer->get_db_version() );

		$this->assertContainsSame(
			array( 'method' => 'install_single', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'install_site', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'install_network', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test how hooks mode is handled during install().
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install
	 *
	 * @requires WordPress !multisite
	 */
	public function test_install_not_multisite_hooks_mode() {

		$this->mock_apps();

		$hooks = wordpoints_hooks();
		$hooks->set_current_mode( 'test' );

		$this->un_installer = new WordPoints_PHPUnit_Mock_Un_Installer_Hook_Mode( 'test', '1.0.0' );
		$this->un_installer->install( false );

		$this->assertSame(
			array( 'install_single' => 'standard' )
			, $this->un_installer->mode
		);

		$this->assertSame( 'test', $hooks->get_current_mode() );
	}

	/**
	 * Test the basic behaviour of install() on multisite.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install
	 *
	 * @requires WordPress multisite
	 */
	public function test_install_multisite() {

		$this->un_installer->install( false );

		$this->assertSame( 'install', $this->un_installer->action );
		$this->assertSame( 'site', $this->un_installer->context );
		$this->assertFalse( $this->un_installer->network_wide );

		$this->assertSame( '1.0.0', $this->un_installer->get_db_version() );

		$this->assertNotContains(
			array( 'method' => 'set_network_install_skipped', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'install_single', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertContainsSame(
			array( 'method' => 'install_site', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertContainsSame(
			array( 'method' => 'install_network', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test how hooks mode is handled during install() on multisite.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install
	 *
	 * @requires WordPress multisite
	 */
	public function test_install_multisite_hooks_mode() {

		$this->mock_apps();

		$hooks = wordpoints_hooks();
		$hooks->set_current_mode( 'test' );

		$this->un_installer = new WordPoints_PHPUnit_Mock_Un_Installer_Hook_Mode( 'test', '1.0.0' );
		$this->un_installer->install( false );

		$this->assertSame(
			array( 'install_network' => 'network', 'install_site' => 'standard' )
			, $this->un_installer->mode
		);

		$this->assertSame( 'test', $hooks->get_current_mode() );
	}

	/**
	 * Test the basic behaviour of install() when installing network wide.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install
	 *
	 * @requires WordPress multisite
	 */
	public function test_install_network_wide() {

		$this->un_installer->install( true );

		$this->assertSame( 'install', $this->un_installer->action );
		$this->assertSame( 'site', $this->un_installer->context );
		$this->assertTrue( $this->un_installer->network_wide );

		$this->assertSame( '1.0.0', $this->un_installer->get_db_version() );

		$this->assertNotContains(
			array( 'method' => 'set_network_install_skipped', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'install_single', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertContainsSame(
			array( 'method' => 'install_site', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertContainsSame(
			array( 'method' => 'install_network', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test that install() restores the current site after installing network wide.
	 *
	 * @since 2.2.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install
	 *
	 * @requires WordPress multisite
	 */
	public function test_install_network_wide_current_site_restored() {

		global $_wp_switched_stack, $switched;

		$this->factory->blog->create();
		$current_site_id = get_current_blog_id();

		$this->assertSame( array(), $_wp_switched_stack );
		$this->assertFalse( $switched );

		$this->un_installer->install( true );

		$this->assertSame( $current_site_id, get_current_blog_id() );
		$this->assertSame( array(), $_wp_switched_stack );
		$this->assertFalse( $switched );
	}

	/**
	 * Test that install() restores the current site after installing network wide.
	 *
	 * @since 2.2.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install
	 *
	 * @requires WordPress multisite
	 */
	public function test_install_network_wide_current_site_restored_switched() {

		global $_wp_switched_stack, $switched;

		$previous_site_id = get_current_blog_id();

		$site_id = $this->factory->blog->create();

		switch_to_blog( $site_id );

		$this->assertSame( array( $previous_site_id ), $_wp_switched_stack );
		$this->assertTrue( $switched );

		$this->un_installer->install( true );

		$this->assertSame( $site_id, get_current_blog_id() );
		$this->assertSame( array( $previous_site_id ), $_wp_switched_stack );
		$this->assertTrue( $switched );

		restore_current_blog();

		$this->assertSame( $previous_site_id, get_current_blog_id() );
		$this->assertSame( array(), $_wp_switched_stack );
		$this->assertFalse( $switched );
	}

	/**
	 * Test how hooks mode is handled during install() when network-wide.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install
	 *
	 * @requires WordPress multisite
	 */
	public function test_install_network_wide_hooks_mode() {

		$this->mock_apps();

		$hooks = wordpoints_hooks();
		$hooks->set_current_mode( 'test' );

		$this->un_installer = new WordPoints_PHPUnit_Mock_Un_Installer_Hook_Mode( 'test', '1.0.0' );
		$this->un_installer->install( true );

		$this->assertSame(
			array( 'install_network' => 'network', 'install_site' => 'standard' )
			, $this->un_installer->mode
		);

		$this->assertSame( 'test', $hooks->get_current_mode() );
	}

	/**
	 * Test install() when installing network-wide and per-site install is disabled.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install
	 *
	 * @requires WordPress multisite
	 */
	public function test_install_network_wide_site_disabled() {

		add_filter( 'wp_is_large_network', '__return_true' );

		$this->un_installer->context = 'network';
		$this->un_installer->install( true );

		$this->assertSame( 'install', $this->un_installer->action );
		$this->assertSame( 'site', $this->un_installer->context );
		$this->assertTrue( $this->un_installer->network_wide );

		$this->assertSame( '1.0.0', $this->un_installer->get_db_version() );

		$this->assertContainsSame(
			array( 'method' => 'set_network_install_skipped', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'install_single', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'install_site', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertContainsSame(
			array( 'method' => 'install_network', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test install() when per-site install should be skipped without an admin notice.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install
	 *
	 * @requires WordPress multisite
	 */
	public function test_install_network_wide_site_disabled_skip_only() {

		$this->un_installer->skip_per_site_install
			= WordPoints_Un_Installer_Base::SKIP_INSTALL;

		$this->un_installer->context = 'network';
		$this->un_installer->install( true );

		$this->assertSame( 'install', $this->un_installer->action );
		$this->assertSame( 'site', $this->un_installer->context );
		$this->assertTrue( $this->un_installer->network_wide );

		$this->assertSame( '1.0.0', $this->un_installer->get_db_version() );

		$this->assertNotContains(
			array( 'method' => 'set_network_install_skipped', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'install_single', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'install_site', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertContainsSame(
			array( 'method' => 'install_network', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test that uninstall() restores the current site after uninstalling network wide.
	 *
	 * @since 2.2.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall
	 *
	 * @requires WordPress multisite
	 */
	public function test_uninstall_network_wide_current_site_restored() {

		global $_wp_switched_stack, $switched;

		$this->factory->blog->create();
		$current_site_id = get_current_blog_id();

		$this->assertSame( array(), $_wp_switched_stack );
		$this->assertFalse( $switched );

		$this->un_installer = new WordPoints_PHPUnit_Mock_Un_Installer_Hook_Mode( 'test', '1.0.0' );
		$this->un_installer->set_network_installed();
		$this->un_installer->uninstall();

		$this->assertSame( $current_site_id, get_current_blog_id() );
		$this->assertSame( array(), $_wp_switched_stack );
		$this->assertFalse( $switched );
	}

	/**
	 * Test that uninstall() restores the current site after uninstalling network wide.
	 *
	 * @since 2.2.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall
	 *
	 * @requires WordPress multisite
	 */
	public function test_uninstall_network_wide_current_site_restored_switched() {

		global $_wp_switched_stack, $switched;

		$previous_site_id = get_current_blog_id();

		$site_id = $this->factory->blog->create();

		switch_to_blog( $site_id );

		$this->assertSame( array( $previous_site_id ), $_wp_switched_stack );
		$this->assertTrue( $switched );

		$this->un_installer = new WordPoints_PHPUnit_Mock_Un_Installer_Hook_Mode( 'test', '1.0.0' );
		$this->un_installer->set_network_installed();
		$this->un_installer->uninstall();

		$this->assertSame( $site_id, get_current_blog_id() );
		$this->assertSame( array( $previous_site_id ), $_wp_switched_stack );
		$this->assertTrue( $switched );

		restore_current_blog();

		$this->assertSame( $previous_site_id, get_current_blog_id() );
		$this->assertSame( array(), $_wp_switched_stack );
		$this->assertFalse( $switched );
	}

	/**
	 * Test how hooks mode is handled during uninstall().
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall
	 *
	 * @requires WordPress !multisite
	 */
	public function test_uninstall_not_multisite_hooks_mode() {

		$this->mock_apps();

		$hooks = wordpoints_hooks();
		$hooks->set_current_mode( 'test' );

		$this->un_installer = new WordPoints_PHPUnit_Mock_Un_Installer_Hook_Mode( 'test', '1.0.0' );
		$this->un_installer->uninstall();

		$this->assertSame(
			array( 'uninstall_single' => 'standard' )
			, $this->un_installer->mode
		);

		$this->assertSame( 'test', $hooks->get_current_mode() );
	}

	/**
	 * Test how hooks mode is handled during uninstall() on multisite.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall
	 *
	 * @requires WordPress multisite
	 */
	public function test_uninstall_multisite_hooks_mode() {

		$this->mock_apps();

		$hooks = wordpoints_hooks();
		$hooks->set_current_mode( 'test' );

		$this->un_installer = new WordPoints_PHPUnit_Mock_Un_Installer_Hook_Mode( 'test', '1.0.0' );
		$this->un_installer->set_network_installed();
		$this->un_installer->uninstall();

		$this->assertSame(
			array( 'uninstall_site' => 'standard', 'uninstall_network' => 'network' )
			, $this->un_installer->mode
		);

		$this->assertSame( 'test', $hooks->get_current_mode() );
	}

	/**
	 * Test the basic behaviour of update().
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress !multisite
	 */
	public function test_update_not_multisite() {

		$this->un_installer->updates = array(
			'1.0.0' => array( 'single' => true, 'site' => true, 'network' => true ),
		);

		$this->un_installer->update( '0.9.0', '1.0.0', false );

		$this->assertSame( 'update', $this->un_installer->action );
		$this->assertSame( 'single', $this->un_installer->context );
		$this->assertFalse( $this->un_installer->network_wide );

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->assertContainsSame(
			array( 'method' => 'update_single_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_site_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_network_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test how hooks mode is handled during update().
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress !multisite
	 */
	public function test_update_not_multisite_hooks_mode() {

		$this->mock_apps();

		$hooks = wordpoints_hooks();
		$hooks->set_current_mode( 'test' );

		$this->un_installer = new WordPoints_PHPUnit_Mock_Un_Installer_Hook_Mode( 'test', '1.0.0' );

		$this->un_installer->updates = array(
			'1.0.0' => array( 'single' => true, 'site' => true, 'network' => true ),
		);

		$this->un_installer->update( '0.9.0', '1.0.0', false );

		$this->assertSame(
			array( 'update_single_to_1_0_0' => 'standard' )
			, $this->un_installer->mode
		);

		$this->assertSame( 'test', $hooks->get_current_mode() );
	}

	/**
	 * Test update() when there are no updates for single sites.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress !multisite
	 */
	public function test_update_not_multisite_single_disabled() {

		$this->un_installer->updates = array(
			'1.0.0' => array( 'single' => false, 'site' => true, 'network' => true ),
		);

		$this->un_installer->update( '0.9.0', '1.0.0', false );

		$this->assertSame( 'update', $this->un_installer->action );
		$this->assertSame( 'single', $this->un_installer->context );
		$this->assertFalse( $this->un_installer->network_wide );

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->assertNotContains(
			array( 'method' => 'update_single_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_site_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_network_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test the basic behaviour of update() on multisite.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress multisite
	 */
	public function test_update_multisite() {

		$this->un_installer->updates = array(
			'1.0.0' => array( 'single' => true, 'site' => true, 'network' => true ),
		);

		$this->un_installer->update( '0.9.0', '1.0.0', false );

		$this->assertSame( 'update', $this->un_installer->action );
		$this->assertSame( 'site', $this->un_installer->context );
		$this->assertFalse( $this->un_installer->network_wide );

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->assertNotContains(
			array( 'method' => 'set_network_update_skipped', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_single_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertContainsSame(
			array( 'method' => 'update_site_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertContainsSame(
			array( 'method' => 'update_network_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test how hooks mode is handled during update() on multisite.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress multisite
	 */
	public function test_update_multisite_hooks_mode() {

		$this->mock_apps();

		$hooks = wordpoints_hooks();
		$hooks->set_current_mode( 'test' );

		$this->un_installer = new WordPoints_PHPUnit_Mock_Un_Installer_Hook_Mode( 'test', '1.0.0' );

		$this->un_installer->updates = array(
			'1.0.0' => array( 'single' => true, 'site' => true, 'network' => true ),
		);

		$this->un_installer->update( '0.9.0', '1.0.0', false );

		$this->assertSame(
			array(
				'update_network_to_1_0_0' => 'network',
				'update_site_to_1_0_0' => 'standard',
			)
			, $this->un_installer->mode
		);

		$this->assertSame( 'test', $hooks->get_current_mode() );
	}

	/**
	 * Test update() on multisite when site updates are disabled.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress multisite
	 */
	public function test_update_multisite_site_disabled() {

		$this->un_installer->updates = array(
			'1.0.0' => array( 'single' => true, 'site' => false, 'network' => true ),
		);

		$this->un_installer->update( '0.9.0', '1.0.0', false );

		$this->assertSame( 'update', $this->un_installer->action );
		$this->assertSame( 'site', $this->un_installer->context );
		$this->assertFalse( $this->un_installer->network_wide );

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->assertNotContains(
			array( 'method' => 'set_network_update_skipped', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_single_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_site_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertContainsSame(
			array( 'method' => 'update_network_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test update() on multisite when network updates are disabled.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress multisite
	 */
	public function test_update_multisite_network_disabled() {

		$this->un_installer->updates = array(
			'1.0.0' => array( 'single' => true, 'site' => true, 'network' => false ),
		);

		$this->un_installer->update( '0.9.0', '1.0.0', false );

		$this->assertSame( 'update', $this->un_installer->action );
		$this->assertSame( 'site', $this->un_installer->context );
		$this->assertFalse( $this->un_installer->network_wide );

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->assertNotContains(
			array( 'method' => 'set_network_update_skipped', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_single_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertContainsSame(
			array( 'method' => 'update_site_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_network_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test the basic behaviour of update() when updating network wide.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress multisite
	 */
	public function test_update_network_wide() {

		$this->un_installer->set_network_installed();
		$this->un_installer->updates = array(
			'1.0.0' => array( 'single' => true, 'site' => true, 'network' => true ),
		);

		$this->un_installer->update( '0.9.0', '1.0.0', true );

		$this->assertSame( 'update', $this->un_installer->action );
		$this->assertSame( 'site', $this->un_installer->context );
		$this->assertTrue( $this->un_installer->network_wide );

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->assertNotContains(
			array( 'method' => 'set_network_update_skipped', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_single_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertContainsSame(
			array( 'method' => 'update_site_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertContainsSame(
			array( 'method' => 'update_network_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test that update() restores the current site after updating network wide.
	 *
	 * @since 2.2.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress multisite
	 */
	public function test_update_network_wide_current_site_restored() {

		global $_wp_switched_stack, $switched;

		$this->factory->blog->create();
		$current_site_id = get_current_blog_id();

		$this->assertSame( array(), $_wp_switched_stack );
		$this->assertFalse( $switched );

		$this->un_installer->set_network_installed();
		$this->un_installer->updates = array(
			'1.0.0' => array( 'single' => true, 'site' => true, 'network' => true ),
		);

		$this->un_installer->update( '0.9.0', '1.0.0', true );

		$this->assertSame( $current_site_id, get_current_blog_id() );
		$this->assertSame( array(), $_wp_switched_stack );
		$this->assertFalse( $switched );
	}

	/**
	 * Test that update() restores the current site after updating network wide.
	 *
	 * @since 2.2.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress multisite
	 */
	public function test_update_network_wide_current_site_restored_switched() {

		global $_wp_switched_stack, $switched;

		$previous_site_id = get_current_blog_id();

		$site_id = $this->factory->blog->create();

		switch_to_blog( $site_id );

		$this->assertSame( array( $previous_site_id ), $_wp_switched_stack );
		$this->assertTrue( $switched );

		$this->un_installer->set_network_installed();
		$this->un_installer->updates = array(
			'1.0.0' => array( 'single' => true, 'site' => true, 'network' => true ),
		);

		$this->un_installer->update( '0.9.0', '1.0.0', true );

		$this->assertSame( $site_id, get_current_blog_id() );
		$this->assertSame( array( $previous_site_id ), $_wp_switched_stack );
		$this->assertTrue( $switched );

		restore_current_blog();

		$this->assertSame( $previous_site_id, get_current_blog_id() );
		$this->assertSame( array(), $_wp_switched_stack );
		$this->assertFalse( $switched );
	}

	/**
	 * Test how hooks mode is handled during update() when network-wide.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress multisite
	 */
	public function test_update_network_wide_hooks_mode() {

		$this->mock_apps();

		$hooks = wordpoints_hooks();
		$hooks->set_current_mode( 'test' );

		$this->un_installer = new WordPoints_PHPUnit_Mock_Un_Installer_Hook_Mode( 'test', '1.0.0' );
		$this->un_installer->set_network_installed();

		$this->un_installer->updates = array(
			'1.0.0' => array( 'single' => true, 'site' => true, 'network' => true ),
		);

		$this->un_installer->update( '0.9.0', '1.0.0', true );

		$this->assertSame(
			array(
				'update_network_to_1_0_0' => 'network',
				'update_site_to_1_0_0' => 'standard',
			)
			, $this->un_installer->mode
		);

		$this->assertSame( 'test', $hooks->get_current_mode() );
	}

	/**
	 * Test update() when updating network wide and site updates are disabled.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress multisite
	 */
	public function test_update_network_wide_site_disabled() {

		$this->un_installer->set_network_installed();
		$this->un_installer->updates = array(
			'1.0.0' => array( 'single' => true, 'site' => false, 'network' => true ),
		);

		$this->un_installer->update( '0.9.0', '1.0.0', true );

		$this->assertSame( 'update', $this->un_installer->action );
		$this->assertSame( 'site', $this->un_installer->context );
		$this->assertTrue( $this->un_installer->network_wide );

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->assertNotContains(
			array( 'method' => 'set_network_update_skipped', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_single_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_site_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertContainsSame(
			array( 'method' => 'update_network_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test update() when updating network wide and network updates are disabled.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress multisite
	 */
	public function test_update_network_wide_networK_disabled() {

		$this->un_installer->set_network_installed();
		$this->un_installer->updates = array(
			'1.0.0' => array( 'single' => true, 'site' => true, 'network' => false ),
		);

		$this->un_installer->update( '0.9.0', '1.0.0', true );

		$this->assertSame( 'update', $this->un_installer->action );
		$this->assertSame( 'site', $this->un_installer->context );
		$this->assertTrue( $this->un_installer->network_wide );

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->assertNotContains(
			array( 'method' => 'set_network_update_skipped', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_single_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertContainsSame(
			array( 'method' => 'update_site_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_network_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test update() when updating network-wide and per-site update is manual.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress multisite
	 */
	public function test_update_network_wide_site_manual() {

		add_filter( 'wp_is_large_network', '__return_true' );

		$this->un_installer->set_network_installed();
		$this->un_installer->context = 'network';
		$this->un_installer->updates = array(
			'1.0.0' => array( 'single' => true, 'site' => true, 'network' => true ),
		);

		$this->un_installer->update( '0.9.0', '1.0.0', true );

		$this->assertSame( 'update', $this->un_installer->action );
		$this->assertSame( 'site', $this->un_installer->context );
		$this->assertTrue( $this->un_installer->network_wide );

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->assertContainsSame(
			array( 'method' => 'set_network_update_skipped', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_single_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_site_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertContainsSame(
			array( 'method' => 'update_network_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test update() when per-site update is manual and site updates are disabled.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress multisite
	 */
	public function test_update_network_wide_site_manual_site_disabled() {

		add_filter( 'wp_is_large_network', '__return_true' );

		$this->un_installer->set_network_installed();
		$this->un_installer->context = 'network';
		$this->un_installer->updates = array(
			'1.0.0' => array( 'single' => true, 'site' => false, 'network' => true ),
		);

		$this->un_installer->update( '0.9.0', '1.0.0', true );

		$this->assertSame( 'update', $this->un_installer->action );
		$this->assertSame( 'site', $this->un_installer->context );
		$this->assertTrue( $this->un_installer->network_wide );

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->assertNotContains(
			array( 'method' => 'set_network_update_skipped', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_single_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_site_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertContainsSame(
			array( 'method' => 'update_network_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test the behaviour of update() when no from version is specified.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress !multisite
	 */
	public function test_update_no_from() {

		$this->un_installer->updates = array(
			'1.0.0' => array( 'single' => true, 'site' => true, 'network' => true ),
		);

		$this->un_installer->update( null, '1.0.0', false );

		$this->assertSame( 'update', $this->un_installer->action );
		$this->assertSame( 'single', $this->un_installer->context );
		$this->assertFalse( $this->un_installer->network_wide );

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->assertContainsSame(
			array( 'method' => 'update_single_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test update() when no from version is specified but the DB version is set.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress !multisite
	 */
	public function test_update_no_from_db_version_set() {

		$this->un_installer->set_db_version( '0.9.0' );
		$this->un_installer->updates = array(
			'1.0.0' => array( 'single' => true, 'site' => true, 'network' => true ),
		);

		$this->un_installer->update( null, '1.0.0', false );

		$this->assertSame( 'update', $this->un_installer->action );
		$this->assertSame( 'single', $this->un_installer->context );
		$this->assertFalse( $this->un_installer->network_wide );

		$this->assertSame( '0.9.0', $this->un_installer->get_db_version() );

		$this->assertContainsSame(
			array( 'method' => 'update_single_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test update() when no from version is set but the DB version is up to date.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress !multisite
	 */
	public function test_update_no_from_db_version_same() {

		$this->un_installer->set_db_version( '1.0.0' );
		$this->un_installer->updates = array(
			'1.0.0' => array( 'single' => true, 'site' => true, 'network' => true ),
		);

		$this->un_installer->update( null, '1.0.0', false );

		$this->assertSame( 'update', $this->un_installer->action );
		$this->assertNull( $this->un_installer->context );
		$this->assertFalse( $this->un_installer->network_wide );

		$this->assertSame( '1.0.0', $this->un_installer->get_db_version() );

		$this->assertNotContains(
			array( 'method' => 'update_single_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test the behaviour of update() when no to version is specified.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress !multisite
	 */
	public function test_update_no_to() {

		$this->un_installer->updates = array( '1.0.0' => array( 'single' => true ) );

		$this->un_installer->update( '0.9.0', null, false );

		$this->assertSame( 'update', $this->un_installer->action );
		$this->assertSame( 'single', $this->un_installer->context );
		$this->assertFalse( $this->un_installer->network_wide );

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->assertContainsSame(
			array( 'method' => 'update_single_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test update() when the to version is different than the version property.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Un_Installer_Base::update
	 *
	 * @requires WordPress !multisite
	 */
	public function test_update_no_to_different_than_version() {

		$this->un_installer->updates = array(
			'0.9.0' => array( 'single' => true ),
			'1.0.0' => array( 'single' => true ),
		);

		$this->un_installer->update( '0.8.0', '0.9.0', false );

		$this->assertSame( 'update', $this->un_installer->action );
		$this->assertSame( 'single', $this->un_installer->context );
		$this->assertFalse( $this->un_installer->network_wide );

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->assertContainsSame(
			array( 'method' => 'update_single_to_0_9_0', 'args' => array() )
			, $this->un_installer->method_calls
		);

		$this->assertNotContains(
			array( 'method' => 'update_single_to_1_0_0', 'args' => array() )
			, $this->un_installer->method_calls
		);
	}

	/**
	 * Test the basic behaviour of install_on_site().
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_on_site
	 *
	 * @requires WordPress multisite
	 */
	public function test_install_on_site() {

		$site_id = get_current_blog_id();

		$this->un_installer->install_on_site( $site_id );

		$this->assertSame( 'install', $this->un_installer->action );
		$this->assertSame( 'site', $this->un_installer->context );
		$this->assertTrue( $this->un_installer->network_wide );

		$this->assertFalse( $this->un_installer->get_db_version() );
	}

	/**
	 * Test how hooks mode is handled during install_on_site().
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_on_site
	 *
	 * @requires WordPress multisite
	 */
	public function test_install_on_site_hooks_mode() {

		$this->mock_apps();

		$hooks = wordpoints_hooks();
		$hooks->set_current_mode( 'test' );

		$site_id = get_current_blog_id();

		$this->un_installer = new WordPoints_PHPUnit_Mock_Un_Installer_Hook_Mode( 'test', '1.0.0' );
		$this->un_installer->install_on_site( $site_id );

		$this->assertSame(
			array( 'install_site' => 'standard' )
			, $this->un_installer->mode
		);

		$this->assertSame( 'test', $hooks->get_current_mode() );
	}

	/**
	 * Test that custom caps are loaded and installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_on_site
	 *
	 * @requires WordPress multisite
	 */
	public function test_install_on_site_custom_caps() {

		$this->un_installer->custom_caps_getter = array( $this, 'custom_caps_getter' );
		$this->un_installer->install_on_site( get_current_blog_id() );

		$this->assertCustomCapsLoaded();

		// We just check that the first cap was added.
		$this->assertCapWasAdded(
			reset( $this->custom_caps )
			, key( $this->custom_caps )
		);
	}

	/**
	 * Test that the schema shortcuts are mapped before install.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_on_site
	 *
	 * @requires WordPress multisite
	 */
	public function test_install_on_site_map_schema_shortcuts() {

		$this->set_un_installer_schema();

		$this->un_installer->install_on_site( get_current_blog_id() );

		$this->assertSchemaMapped();
	}

	/**
	 * Test that the site being installed on is switched to.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_on_site
	 *
	 * @requires WordPress multisite
	 */
	public function test_install_on_site_switch_to_site() {

		$site_id = get_current_blog_id();

		$filter_mock = new WordPoints_PHPUnit_Mock_Filter();
		add_action( 'switch_blog', array( $filter_mock, 'action' ) );

		$this->un_installer->install_on_site( $site_id );

		$this->assertSame(
			array( array( $site_id ), array( $site_id ) )
			, $filter_mock->calls
		);
	}

	/**
	 * Test database schema is created on site install.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_on_site
	 *
	 * @requires WordPress multisite
	 */
	public function test_install_on_site_db_schema() {

		global $wpdb;

		$this->un_installer->schema['site'] = array(
			'tables' => array(
				'test' => 'id BIGINT(20) NOT NULL',
			),
		);

		$this->un_installer->install_on_site( get_current_blog_id() );

		$this->assertSame(
			array()
			, $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}test" )
		);
	}

	/**
	 * Test that we don't attempt to load custom capabilities when no getter is set.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::maybe_load_custom_caps
	 */
	public function test_maybe_load_custom_caps_no_getter() {

		$this->un_installer->maybe_load_custom_caps();

		$this->assertNull( $this->un_installer->custom_caps );
		$this->assertNull( $this->un_installer->custom_caps_keys );
	}

	/**
	 * Test that custom caps are loaded when a getter is set.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::maybe_load_custom_caps
	 */
	public function test_maybe_load_custom_caps_with_getter() {

		$this->un_installer->custom_caps_getter = array( $this, 'custom_caps_getter' );
		$this->un_installer->maybe_load_custom_caps();

		$this->assertCustomCapsLoaded();
	}

	/**
	 * Test that custom caps are added to the uninstall things when a getter is set.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::maybe_load_custom_caps
	 */
	public function test_maybe_load_custom_caps_action_uninstall() {

		$this->un_installer->action = 'uninstall';
		$this->un_installer->custom_caps_getter = array( $this, 'custom_caps_getter' );
		$this->un_installer->maybe_load_custom_caps();

		$this->assertSame(
			array( 'local' => array( 'custom_caps' => true ) )
			, $this->un_installer->uninstall
		);
	}

	/**
	 * Test that custom caps are loaded before install.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::before_install
	 */
	public function test_maybe_load_custom_caps_before_install() {

		$this->un_installer->custom_caps_getter = array( $this, 'custom_caps_getter' );
		$this->un_installer->before_install();

		$this->assertCustomCapsLoaded();
	}

	/**
	 * Test that the schema shortcuts are mapped before install.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::before_install
	 */
	public function test_map_schema_shortcuts_before_install() {

		$this->set_un_installer_schema();

		$this->un_installer->before_install();

		$this->assertSchemaMapped();
	}

	/**
	 * Test getting the database schema.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_db_schema
	 */
	public function test_get_db_schema() {

		global $wpdb;

		$this->un_installer->context = 'single';

		$this->un_installer->schema['single'] = array(
			'tables' => array(
				'test' => 'id BIGINT(20) NOT NULL',
			),
		);

		$this->assertSame(
			"CREATE TABLE {$wpdb->prefix}test (
				id BIGINT(20) NOT NULL
			) " . $wpdb->get_charset_collate() . ';'
			, $this->un_installer->get_db_schema()
		);
	}

	/**
	 * Test getting the database schema when none is defined.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_db_schema
	 */
	public function test_get_db_schema_none() {

		$this->un_installer->context = 'single';

		$this->assertSame( '', $this->un_installer->get_db_schema() );
	}

	/**
	 * Test that the base-prefix is used in network context.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_db_schema
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_db_schema_network() {

		global $wpdb;

		$this->un_installer->context = 'network';

		$this->un_installer->schema['network'] = array(
			'tables' => array(
				'test' => 'id BIGINT(20) NOT NULL',
			),
		);

		$this->assertSame(
			"CREATE TABLE {$wpdb->base_prefix}test (
				id BIGINT(20) NOT NULL
			) " . $wpdb->get_charset_collate() . ';'
			, $this->un_installer->get_db_schema()
		);
	}

	/**
	 * Test that the regular prefix is used in site context.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_db_schema
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_db_schema_site() {

		global $wpdb;

		$this->un_installer->context = 'site';

		$this->un_installer->schema['site'] = array(
			'tables' => array(
				'test' => 'id BIGINT(20) NOT NULL',
			),
		);

		$this->assertSame(
			"CREATE TABLE {$wpdb->prefix}test (
				id BIGINT(20) NOT NULL
			) " . $wpdb->get_charset_collate() . ';'
			, $this->un_installer->get_db_schema()
		);
	}

	/**
	 * Test installing the database schema.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_db_schema
	 */
	public function test_install_db_schema() {

		global $wpdb;

		$this->un_installer->context = 'single';

		$this->un_installer->schema['single'] = array(
			'tables' => array(
				'test' => 'id BIGINT(20) NOT NULL',
			),
		);

		$this->un_installer->install_db_schema();

		$this->assertSame(
			array()
			, $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}test" )
		);
	}

	/**
	 * Test installing the database schema when none is defined.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_db_schema
	 */
	public function test_install_db_schema_none() {

		$this->un_installer->context = 'single';

		$this->un_installer->install_db_schema();
	}

	/**
	 * Test database schema is created on network install.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_network
	 */
	public function test_install_network_db_schema() {

		global $wpdb;

		$this->un_installer->context = 'network';

		$this->un_installer->schema['network'] = array(
			'tables' => array(
				'test' => 'id BIGINT(20) NOT NULL',
			),
		);

		$this->un_installer->install_network();

		$this->assertSame(
			array()
			, $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}test" )
		);
	}

	/**
	 * Test database version is set on network install when network wide.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_network
	 */
	public function test_install_network_set_db_version_network_wide() {

		$this->un_installer->context = 'network';
		$this->un_installer->network_wide = true;

		$this->un_installer->install_network();

		$this->assertSame( '1.0.0', $this->un_installer->get_db_version() );
	}

	/**
	 * Test database version isn't set on network install when not network wide.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_network
	 */
	public function test_install_network_set_db_version() {

		$this->un_installer->context = 'network';

		$this->un_installer->install_network();

		$this->assertFalse( $this->un_installer->get_db_version() );
	}

	/**
	 * Test database schema is created on site install.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_site
	 */
	public function test_install_site_db_schema() {

		global $wpdb;

		$this->un_installer->context = 'site';

		$this->un_installer->schema['site'] = array(
			'tables' => array(
				'test' => 'id BIGINT(20) NOT NULL',
			),
		);

		$this->un_installer->install_site();

		$this->assertSame(
			array()
			, $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}test" )
		);
	}

	/**
	 * Test database version is set on site install when not network wide.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_site
	 */
	public function test_install_site_set_db_version() {

		$this->un_installer->context = 'site';

		$this->un_installer->install_site();

		$this->assertSame( '1.0.0', $this->un_installer->get_db_version() );
	}

	/**
	 * Test database version isn't set on site install when network wide.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_site
	 */
	public function test_install_site_set_db_version_network_wide() {

		$this->un_installer->context = 'site';
		$this->un_installer->network_wide = true;

		$this->un_installer->install_site();

		$this->assertFalse( $this->un_installer->get_db_version() );
	}

	/**
	 * Test that custom caps are added on site install.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_site
	 */
	public function test_install_site_custom_caps() {

		$this->un_installer->custom_caps = $this->custom_caps;
		$this->un_installer->install_site();

		// We just check the first cap.
		$this->assertCapWasAdded(
			reset( $this->custom_caps )
			, key( $this->custom_caps )
		);
	}

	/**
	 * Test database schema is created on single install.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_single
	 */
	public function test_install_single_db_schema() {

		global $wpdb;

		$this->un_installer->context = 'single';

		$this->un_installer->schema['single'] = array(
			'tables' => array(
				'test' => 'id BIGINT(20) NOT NULL',
			),
		);

		$this->un_installer->install_single();

		$this->assertSame(
			array()
			, $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}test" )
		);
	}

	/**
	 * Test database version is set on single install.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_single
	 */
	public function test_install_single_set_db_version() {

		$this->un_installer->context = 'single';

		$this->un_installer->install_single();

		$this->assertSame( '1.0.0', $this->un_installer->get_db_version() );
	}

	/**
	 * Test that custom caps are added on single install.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_single
	 */
	public function test_install_single_custom_caps() {

		$this->un_installer->custom_caps = $this->custom_caps;
		$this->un_installer->install_single();

		// We just check the first cap.
		$this->assertCapWasAdded(
			reset( $this->custom_caps )
			, key( $this->custom_caps )
		);
	}

	/**
	 * Test that custom caps are loaded before update.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::before_update
	 */
	public function test_maybe_load_custom_caps_before_update() {

		$this->un_installer->custom_caps_getter = array( $this, 'custom_caps_getter' );
		$this->un_installer->before_update();

		$this->assertCustomCapsLoaded();
	}

	/**
	 * Test that custom caps are installed by install_custom_caps().
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::install_custom_caps
	 */
	public function test_install_custom_caps() {

		$this->un_installer->custom_caps = $this->custom_caps;
		$this->un_installer->install_custom_caps();

		// We just check the first cap.
		$this->assertCapWasAdded(
			reset( $this->custom_caps )
			, key( $this->custom_caps )
		);
	}

	/**
	 * Test that custom caps are loaded before uninstall.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::before_uninstall
	 */
	public function test_maybe_load_custom_caps_before_uninstall() {

		$this->un_installer->custom_caps_getter = array( $this, 'custom_caps_getter' );
		$this->un_installer->before_uninstall();

		$this->assertCustomCapsLoaded();
	}

	/**
	 * Test preparing a list table for uninstall.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::prepare_uninstall_list_tables
	 *
	 * @expectedDeprecated WordPoints_Un_Installer_Base::prepare_uninstall_list_tables
	 */
	public function test_prepare_uninstall_list_tables() {

		$list_table = array(
			'screen_id' => array(
				'parent' => 'parent_page',
				'options' => array( 'an_option' ),
			),
		);

		$this->un_installer->uninstall['list_tables'] = $list_table;
		$this->un_installer->prepare_uninstall_list_tables();

		$this->assertSame(
			array(
				'list_tables' => $list_table,
				'universal' => array(),
				'global' => array(
					'list_tables' => array(
						'screen_id' => array(
							'parent' => 'parent',
							'options' => array( 'an_option' ),
						),
					),
				),
			)
			, $this->un_installer->uninstall
		);
	}

	/**
	 * Test that list tables are prepared before uninstall.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::before_uninstall
	 *
	 * @expectedDeprecated WordPoints_Un_Installer_Base::prepare_uninstall_list_tables
	 */
	public function test_prepare_list_tables_before_uninstall() {

		$list_table = array(
			'screen_id' => array(
				'parent' => 'parent_page',
				'options' => array( 'an_option' ),
			),
		);

		$this->un_installer->uninstall['list_tables'] = $list_table;
		$this->un_installer->before_uninstall();

		$array = array(
			'list_tables' => array(
				'screen_id' => array(
					'parent'  => 'parent',
					'options' => array( 'an_option' ),
				),
			),
		);

		$this->assertSame(
			array(
				'single' => $array,
				'site' => array(),
				'network' => $array,
				'local' => array(),
				'global' => $array,
				'universal' => array(),
				'list_tables' => $list_table,
			)
			, $this->un_installer->uninstall
		);
	}

	/**
	 * Test preparing a non per-site items for uninstall.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::prepare_uninstall_non_per_site_items
	 */
	public function test_prepare_uninstall_non_per_site_items() {

		$this->un_installer->uninstall['universal']['key'] = 'data';
		$this->un_installer->uninstall['site']['key'] = 'other';

		$this->un_installer->prepare_uninstall_non_per_site_items( 'key' );

		$this->assertSame(
			array(
				'universal' => array(),
				'site'      => array(),
				'global'    => array( 'key' => 'data' ),
				'network'   => array( 'key' => 'other' ),
			)
			, $this->un_installer->uninstall
		);
	}

	/**
	 * Test that meta boxes are prepared before uninstall.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::before_uninstall
	 */
	public function test_prepare_meta_boxes_before_uninstall() {

		$meta_box = array( 'screen_id' => array() );

		$this->un_installer->uninstall['universal']['meta_boxes'] = $meta_box;
		$this->un_installer->before_uninstall();

		$this->assertSame(
			array(
				'single' => array( 'meta_boxes' => $meta_box ),
				'site' => array(),
				'network' => array( 'meta_boxes' => $meta_box ),
				'local' => array(),
				'global' => array( 'meta_boxes' => $meta_box ),
				'universal' => array(),
			)
			, $this->un_installer->uninstall
		);
	}

	/**
	 * Test mapping an uninstall shortcut.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::map_uninstall_shortcut
	 */
	public function test_map_uninstall_shortcut() {

		$this->un_installer->uninstall['single']['shortcut'] = array( 'one', 'two' );
		$this->un_installer->map_uninstall_shortcut( 'shortcut', 'canonical', array( 'prefix' => '_' ) );

		$this->assertArrayHasKey( 'canonical', $this->un_installer->uninstall['single'] );
		$this->assertSame(
			array( '_one', '_two' )
			, $this->un_installer->uninstall['single']['canonical']
		);
	}

	/**
	 * Test default args for mapping an uninstall shortcut.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::map_uninstall_shortcut
	 */
	public function test_map_uninstall_shortcut_defaults() {

		$this->un_installer->uninstall['single']['shortcut'] = array( 'one', 'two' );
		$this->un_installer->map_uninstall_shortcut( 'shortcut', 'canonical', array() );

		$this->assertArrayHasKey( 'canonical', $this->un_installer->uninstall['single'] );
		$this->assertSame(
			array( 'one', 'two' )
			, $this->un_installer->uninstall['single']['canonical']
		);
	}

	/**
	 * Test that the 'widget' uninstall shortcut is mapped before uninstall.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::before_uninstall
	 */
	public function test_map_widget_shortcut_before_uninstall() {

		$this->un_installer->uninstall['global']['widgets'] = array( 'one', 'two' );
		$this->un_installer->before_uninstall();

		$this->assertArrayHasKey( 'options', $this->un_installer->uninstall['global'] );
		$this->assertSame(
			array( 'widget_one', 'widget_two' )
			, $this->un_installer->uninstall['global']['options']
		);
	}

	/**
	 * Test that the 'points_hook' uninstall shortcut is mapped before uninstall.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::before_uninstall
	 */
	public function test_map_points_hook_shortcut_before_uninstall() {

		$this->un_installer->uninstall['site']['points_hooks'] = array( 'one', 'two' );
		$this->un_installer->before_uninstall();

		$this->assertArrayHasKey( 'options', $this->un_installer->uninstall['site'] );
		$this->assertSame(
			array( 'wordpoints_hook-one', 'wordpoints_hook-two' )
			, $this->un_installer->uninstall['site']['options']
		);
	}

	/**
	 * Test that the 'local' uninstall shortcut is mapped.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::map_shortcuts
	 */
	public function test_map_uninstall_shortcuts_local() {

		$this->un_installer->uninstall['single'] = array(
			'other' => array( 'from_single' ),
		);

		$this->un_installer->uninstall['local'] = array(
			'options' => array( 'one', 'two' ),
			'other' => array( 'something' ),
		);

		$this->un_installer->map_shortcuts( 'uninstall' );

		$this->assertSame(
			array(
				'other' => array( 'from_single', 'something' ),
				'options' => array( 'one', 'two' ),
			)
			, $this->un_installer->uninstall['single']
		);

		$this->assertArrayHasKey( 'site', $this->un_installer->uninstall );
		$this->assertSame(
			array(
				'options' => array( 'one', 'two' ),
				'other' => array( 'something' ),
			)
			, $this->un_installer->uninstall['site']
		);

		$this->assertSame( array(), $this->un_installer->uninstall['network'] );
	}

	/**
	 * Test that the 'global' uninstall shortcut is mapped.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::map_shortcuts
	 */
	public function test_map_uninstall_shortcuts_global() {

		$this->un_installer->uninstall['single'] = array(
			'other' => array( 'from_single' ),
		);

		$this->un_installer->uninstall['global'] = array(
			'options' => array( 'one', 'two' ),
			'other' => array( 'something' ),
		);

		$this->un_installer->map_shortcuts( 'uninstall' );

		$this->assertSame(
			array(
				'other' => array( 'from_single', 'something' ),
				'options' => array( 'one', 'two' ),
			)
			, $this->un_installer->uninstall['single']
		);

		$this->assertArrayHasKey( 'network', $this->un_installer->uninstall );
		$this->assertSame(
			array(
				'options' => array( 'one', 'two' ),
				'other' => array( 'something' ),
			)
			, $this->un_installer->uninstall['network']
		);

		$this->assertSame( array(), $this->un_installer->uninstall['site'] );
	}

	/**
	 * Test that the 'universal' uninstall shortcut is mapped.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::map_shortcuts
	 */
	public function test_map_uninstall_shortcuts_universal() {

		$this->un_installer->uninstall['single'] = array(
			'other' => array( 'from_single' ),
		);

		$this->un_installer->uninstall['site'] = array(
			'another' => array( 'bob' ),
		);

		$this->un_installer->uninstall['universal'] = array(
			'options' => array( 'one', 'two' ),
			'other' => array( 'something' ),
		);

		$this->un_installer->map_shortcuts( 'uninstall' );

		$this->assertSame(
			array(
				'other' => array( 'from_single', 'something' ),
				'options' => array( 'one', 'two' ),
			)
			, $this->un_installer->uninstall['single']
		);

		$this->assertSame(
			array(
				'another' => array( 'bob' ),
				'options' => array( 'one', 'two' ),
				'other' => array( 'something' ),
			)
			, $this->un_installer->uninstall['site']
		);

		$this->assertArrayHasKey( 'network', $this->un_installer->uninstall );
		$this->assertSame(
			array(
				'options' => array( 'one', 'two' ),
				'other' => array( 'something' ),
			)
			, $this->un_installer->uninstall['network']
		);
	}

	/**
	 * Test that tables from the schema are added to $uninstall before uninstall.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::before_uninstall
	 */
	public function test_tables_schema_mapped_before_install() {

		$this->un_installer->schema['single'] = array(
			'tables' => array(
				'test' => 'id BIGINT(20) NOT NULL',
			),
		);

		$this->un_installer->before_uninstall();

		$this->assertSame(
			array( 'tables' => array( 'test' ) )
			, $this->un_installer->uninstall['single']
		);
	}

	/**
	 * Test that the uninstall shortcuts are mapped before uninstall.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::before_uninstall
	 */
	public function test_map_uninstall_shortcuts_before_install() {

		$this->un_installer->uninstall['single'] = array(
			'other' => array( 'from_single' ),
		);

		$this->un_installer->uninstall['site'] = array(
			'another' => array( 'bob' ),
		);

		$this->un_installer->uninstall['universal'] = array(
			'options' => array( 'one', 'two' ),
			'other' => array( 'something' ),
		);

		$this->un_installer->before_uninstall();

		$this->assertSame(
			array(
				'other' => array( 'from_single', 'something' ),
				'options' => array( 'one', 'two' ),
			)
			, $this->un_installer->uninstall['single']
		);

		$this->assertSame(
			array(
				'another' => array( 'bob' ),
				'options' => array( 'one', 'two' ),
				'other' => array( 'something' ),
			)
			, $this->un_installer->uninstall['site']
		);

		$this->assertArrayHasKey( 'network', $this->un_installer->uninstall );
		$this->assertSame(
			array(
				'options' => array( 'one', 'two' ),
				'other' => array( 'something' ),
			)
			, $this->un_installer->uninstall['network']
		);
	}

	/**
	 * Test uninstalling custom capabilities.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_custom_caps
	 */
	public function test_uninstall_custom_caps() {

		wordpoints_add_custom_caps( $this->custom_caps );

		$this->un_installer->uninstall_custom_caps(
			array_keys( $this->custom_caps )
		);

		$this->assertCapWasRemoved( key( $this->custom_caps ) );
	}

	/**
	 * Test uninstalling custom capabilities.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_
	 */
	public function test_uninstall__custom_caps() {

		wordpoints_add_custom_caps( $this->custom_caps );

		$this->un_installer->custom_caps_keys = array_keys( $this->custom_caps );
		$this->un_installer->uninstall['site']['custom_caps'] = true;
		$this->un_installer->uninstall_( 'site' );

		$this->assertCapWasRemoved( key( $this->custom_caps ) );
	}

	/**
	 * Test that it doesn't attempt to uninstall them if 'custom_caps' key isn't set.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_
	 */
	public function test_uninstall__custom_caps_not() {

		wordpoints_add_custom_caps( $this->custom_caps );

		$this->un_installer->custom_caps_keys = array_keys( $this->custom_caps );
		$this->un_installer->uninstall_( 'site' );

		$this->assertCapWasAdded(
			reset( $this->custom_caps )
			, key( $this->custom_caps )
		);
	}

	/**
	 * Test uninstalling metadata.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_metadata
	 */
	public function test_uninstall_metadata() {

		$post_id = $this->factory->post->create();
		add_post_meta( $post_id, __METHOD__, 'test' );
		add_post_meta( $post_id, __METHOD__, 'test2' );

		$post_id_2 = $this->factory->post->create();
		add_post_meta( $post_id_2, __METHOD__, 'test' );

		$this->un_installer->uninstall_metadata( 'post', __METHOD__ );

		$this->assertSame( array(), get_post_meta( $post_id, __METHOD__ ) );
		$this->assertSame( array(), get_post_meta( $post_id_2, __METHOD__ ) );
	}

	/**
	 * Test that it supports wildcards.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_metadata
	 */
	public function test_uninstall_metadata_wildcards() {

		$post_id = $this->factory->post->create();
		add_post_meta( $post_id, 'test', 'test' );
		add_post_meta( $post_id, 'test2', 'test' );
		add_post_meta( $post_id, 'other', 'test' );

		$this->un_installer->uninstall_metadata( 'post', 'test%' );

		$this->assertSame( array(), get_post_meta( $post_id, 'test' ) );
		$this->assertSame( array(), get_post_meta( $post_id, 'test2' ) );
		$this->assertSame( 'test', get_post_meta( $post_id, 'other', true ) );
	}

	/**
	 * Test uninstalling user metadata.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_metadata
	 */
	public function test_uninstall_user_metadata() {

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, __METHOD__, 'test2' );

		$this->un_installer->uninstall_metadata( 'user', __METHOD__ );

		$this->assertSame( array(), get_user_meta( $user_id, __METHOD__ ) );
	}

	/**
	 * Test uninstalling user metadata in site context.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_metadata
	 *
	 * @requires WordPress multisite
	 */
	public function test_uninstall_user_metadata_site_context() {

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, __METHOD__, 'test' );
		update_user_option( $user_id, __METHOD__, 'test2' );

		$this->assertSame( 'test2', get_user_option( __METHOD__, $user_id ) );

		$this->un_installer->context = 'site';
		$this->un_installer->uninstall_metadata( 'user', __METHOD__ );

		// If the user option had not been deleted, 'test2' would have been returned.
		$this->assertSame( 'test', get_user_option( __METHOD__, $user_id ) );
		$this->assertSame( 'test', get_user_meta( $user_id, __METHOD__, true ) );
	}

	/**
	 * Test that it supports wildcards for user options.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_metadata
	 */
	public function test_uninstall_user_metadata_site_context_wildcards() {

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, 'test', 'test' );
		update_user_option( $user_id, 'test2', 'test' );
		update_user_option( $user_id, 'other', 'test' );

		$this->un_installer->context = 'site';
		$this->un_installer->uninstall_metadata( 'user', 'test%' );

		$this->assertSame( 'test', get_user_meta( $user_id, 'test', true ) );
		$this->assertFalse( get_user_option( 'test2', $user_id ) );
		$this->assertSame( 'test', get_user_option( 'other', $user_id ) );
	}

	/**
	 * Test uninstalling user metadata.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_
	 */
	public function test_uninstall__user_metadata() {

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, __METHOD__, 'test' );

		$this->un_installer->uninstall['site']['user_meta'] = array( __METHOD__ );
		$this->un_installer->uninstall_( 'site' );

		$this->assertSame( array(), get_user_meta( $user_id, __METHOD__ ) );
	}

	/**
	 * Test uninstalling comment metadata.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_
	 */
	public function test_uninstall__comment_metadata() {

		$comment_id = $this->factory->comment->create(
			array( 'comment_post_ID' => $this->factory->post->create() )
		);
		add_comment_meta( $comment_id, __METHOD__, 'test' );

		$this->un_installer->uninstall['site']['comment_meta'] = array( __METHOD__ );
		$this->un_installer->uninstall_( 'site' );

		$this->assertSame( array(), get_comment_meta( $comment_id, __METHOD__ ) );
	}

	/**
	 * Test uninstalling meta boxes.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_meta_boxes
	 */
	public function test_uninstall_meta_boxes() {

		$parent = 'wordpoints';

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id", 'test' );

		$this->un_installer->uninstall_meta_boxes( 'screen_id', array() );

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id" )
		);
	}

	/**
	 * Test uninstalling meta boxes in network context.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_meta_boxes
	 */
	public function test_uninstall_meta_boxes_network() {

		$parent = 'wordpoints';

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id-network", 'test' );
		add_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id-network", 'test' );
		add_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id-network", 'test' );

		$this->un_installer->context = 'network';
		$this->un_installer->uninstall_meta_boxes( 'screen_id', array() );

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id-network" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id-network" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id-network" )
		);
	}

	/**
	 * Test uninstalling meta boxes with a custom parent page.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_meta_boxes
	 */
	public function test_uninstall_meta_boxes_custom_parent() {

		$parent = 'parent_screen';

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id", 'test' );

		$this->un_installer->uninstall_meta_boxes(
			'screen_id'
			, array( 'parent' => $parent )
		);

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id" )
		);
	}

	/**
	 * Test uninstalling meta boxes with custom options.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_meta_boxes
	 */
	public function test_uninstall_meta_boxes_custom_options() {

		$parent = 'wordpoints';

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "option_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id", 'test' );

		$this->un_installer->uninstall_meta_boxes(
			'screen_id'
			, array( 'options' => array( 'option' ) )
		);

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "option_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id" )
		);
	}

	/**
	 * Test uninstalling meta boxes.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_
	 */
	public function test_uninstall__meta_boxes() {

		$parent = 'wordpoints';

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id", 'test' );

		$this->un_installer->uninstall['site']['meta_boxes'] = array(
			'screen_id' => array(),
		);

		$this->un_installer->uninstall_( 'site' );

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id" )
		);
	}

	/**
	 * Test uninstalling list tables.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_list_table
	 */
	public function test_uninstall_list_table() {

		$parent = 'wordpoints';
		$screen_id = 'screen_id';

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page", 'test' );

		$this->un_installer->uninstall_list_table( $screen_id, array() );

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page}" )
		);
	}

	/**
	 * Test uninstalling list tables in network context.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_list_table
	 */
	public function test_uninstall_list_table_network_context() {

		$parent = 'wordpoints';
		$screen_id = 'screen_id';

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page", 'test' );
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}-networkcolumnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_network_per_page", 'test' );

		$this->un_installer->context = 'network';
		$this->un_installer->uninstall_list_table( $screen_id, array() );

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page}" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}-networkcolumnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_network_per_page}" )
		);
	}

	/**
	 * Test uninstalling list tables with a custom parent page.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_list_table
	 */
	public function test_uninstall_list_table_custom_parent() {

		$parent = 'parent';
		$screen_id = 'screen_id';

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page", 'test' );

		$this->un_installer->uninstall_list_table(
			$screen_id
			, array( 'parent' => $parent )
		);

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page}" )
		);
	}

	/**
	 * Test that the wordpoints_modules screen receives special handling.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_list_table
	 *
	 * @requires WordPress !multisite
	 */
	public function test_uninstall_list_table_wordpoints_modules() {

		$parent = 'wordpoints';
		$screen_id = 'wordpoints_modules';

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page", 'test' );

		$this->un_installer->uninstall_list_table( $screen_id, array() );

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page}" )
		);
	}

	/**
	 * Test that the wordpoints_modules screen receives special handling.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_list_table
	 *
	 * @requires WordPress multisite
	 */
	public function test_uninstall_list_table_wordpoints_modules_multisite() {

		$parent = 'wordpoints';
		$screen_id = 'wordpoints_modules';

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "managetoplevel_page_{$screen_id}columnshidden", 'test' );
		add_user_meta( $user_id, "toplevel_page_{$screen_id}_per_page", 'test' );
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}-networkcolumnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_network_per_page", 'test' );

		$this->un_installer->context = 'network';
		$this->un_installer->uninstall_list_table( $screen_id, array() );

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "managetoplevel_page_{$screen_id}columnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "toplevel_page_{$screen_id}_per_page}" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}-networkcolumnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_network_per_page}" )
		);
	}

	/**
	 * Test that the wordpoints_extensions screen receives special handling.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_list_table
	 *
	 * @requires WordPress !multisite
	 */
	public function test_uninstall_list_table_wordpoints_extensions() {

		$parent = 'wordpoints';
		$screen_id = 'wordpoints_extensions';

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page", 'test' );

		$this->un_installer->uninstall_list_table( $screen_id, array() );

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page}" )
		);
	}

	/**
	 * Test that the wordpoints_extensions screen receives special handling.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_list_table
	 *
	 * @requires WordPress multisite
	 */
	public function test_uninstall_list_table_wordpoints_extensions_multisite() {

		$parent = 'wordpoints';
		$screen_id = 'wordpoints_extensions';

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "managetoplevel_page_{$screen_id}columnshidden", 'test' );
		add_user_meta( $user_id, "toplevel_page_{$screen_id}_per_page", 'test' );
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}-networkcolumnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_network_per_page", 'test' );

		$this->un_installer->context = 'network';
		$this->un_installer->uninstall_list_table( $screen_id, array() );

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "managetoplevel_page_{$screen_id}columnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "toplevel_page_{$screen_id}_per_page}" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}-networkcolumnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_network_per_page}" )
		);
	}

	/**
	 * Test uninstalling list tables with custom options.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_list_table
	 */
	public function test_uninstall_list_table_custom_options() {

		$parent = 'wordpoints';
		$screen_id = 'screen_id';

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_option", 'test' );

		$this->un_installer->uninstall_list_table(
			'screen_id'
			, array( 'options' => array( 'option' ) )
		);

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_option" )
		);
	}

	/**
	 * Test uninstalling list tables.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_
	 */
	public function test_uninstall__list_table() {

		$parent = 'wordpoints';
		$screen_id = 'screen_id';

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page", 'test' );

		$this->un_installer->uninstall['single']['list_tables'] = array(
			$screen_id => array(),
		);

		$this->un_installer->uninstall_( 'single' );

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page}" )
		);
	}

	/**
	 * Test uninstalling options.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_option
	 */
	public function test_uninstall_option() {

		add_option( __METHOD__, 'test' );

		$this->un_installer->uninstall_option( __METHOD__ );

		$this->assertFalse( get_option( __METHOD__ ) );
	}

	/**
	 * Test that it uninstalls network ("site") options in network context.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_option
	 * @covers WordPoints_Un_Installer_Base::uninstall_network_option
	 *
	 * @requires WordPress multisite
	 */
	public function test_uninstall_network_option() {

		add_site_option( __METHOD__, 'test' );

		$this->un_installer->context = 'network';
		$this->un_installer->uninstall_option( __METHOD__ );

		$this->assertFalse( get_site_option( __METHOD__ ) );
	}

	/**
	 * Test that it supports wildcards.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_option
	 */
	public function test_uninstall_option_wildcards() {

		add_option( 'testing', 'test' );
		add_option( 'tester', 'test' );

		$this->un_installer->uninstall_option( 'test%' );

		$this->assertFalse( get_option( 'testing' ) );
		$this->assertFalse( get_option( 'tester' ) );
	}

	/**
	 * Wildcards are now supported for network ("site") options.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_option
	 * @covers WordPoints_Un_Installer_Base::uninstall_network_option
	 *
	 * @requires WordPress multisite
	 */
	public function test_uninstall_network_option_wildcards() {

		add_site_option( 'testing', 'test' );
		add_site_option( 'tester', 'test' );

		$this->un_installer->context = 'network';
		$this->un_installer->uninstall_option( 'test%' );

		$this->assertFalse( get_site_option( 'testing' ) );
		$this->assertFalse( get_site_option( 'tester' ) );
	}

	/**
	 * Test uninstalling options.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_
	 */
	public function test_uninstall__option() {

		add_option( __METHOD__, 'test' );

		$this->un_installer->uninstall['single']['options'] = array( __METHOD__ );
		$this->un_installer->uninstall_( 'single' );

		$this->assertFalse( get_option( __METHOD__ ) );
	}

	/**
	 * Test uninstalling transients.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_transient
	 */
	public function test_uninstall_transient() {

		set_transient( __METHOD__, 'test' );

		$this->un_installer->uninstall_transient( __METHOD__ );

		$this->assertFalse( get_transient( __METHOD__ ) );
	}

	/**
	 * Test that it uninstalls network ("site") transients in network context.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_transient
	 * @covers WordPoints_Un_Installer_Base::uninstall_network_transient
	 *
	 * @requires WordPress multisite
	 */
	public function test_uninstall_network_transient() {

		set_site_transient( __METHOD__, 'test' );

		$this->un_installer->context = 'network';
		$this->un_installer->uninstall_transient( __METHOD__ );

		$this->assertFalse( get_site_transient( __METHOD__ ) );
	}

	/**
	 * Test uninstalling transients.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_
	 */
	public function test_uninstall__transient() {

		set_transient( __METHOD__, 'test' );

		$this->un_installer->uninstall['single']['transients'] = array( __METHOD__ );
		$this->un_installer->uninstall_( 'single' );

		$this->assertFalse( get_transient( __METHOD__ ) );
	}

	/**
	 * Test uninstalling widgets.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_widget
	 */
	public function test_uninstall_widget() {

		add_option( 'widget_test_widget', 'test' );

		$this->un_installer->uninstall_widget( 'test_widget' );

		$this->assertFalse( get_option( 'widget_test_widget' ) );
	}

	/**
	 * Test uninstalling points hooks.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_points_hook
	 */
	public function test_uninstall_points_hook() {

		add_option( 'wordpoints_hook-test_hook', 'test' );

		$this->un_installer->uninstall_points_hook( 'test_hook' );

		$this->assertFalse( get_option( 'wordpoints_hook-test_hook' ) );
	}

	/**
	 * Test uninstalling a table.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_table
	 */
	public function test_uninstall_table() {

		// We use real tables, because we can't check if a temporary table exists.
		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );

		global $wpdb;

		$wpdb->query( "CREATE TABLE `{$wpdb->base_prefix}test` ( `id` BIGINT );" );

		$this->un_installer->uninstall_table( 'test' );

		$this->assertNull( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->base_prefix}test'" ) );
	}

	/**
	 * Test uninstalling a table in site context prepends site prefix.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_table
	 *
	 * @requires WordPress multisite
	 */
	public function test_uninstall_table_site_context() {

		// We use real tables, because we can't check if a temporary table exists.
		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );

		global $wpdb;

		$wpdb->query( "CREATE TABLE `{$wpdb->prefix}test` ( `id` BIGINT );" );

		$this->un_installer->context = 'site';
		$this->un_installer->uninstall_table( 'test' );

		$this->assertNull( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}test'" ) );
	}

	/**
	 * Test uninstalling a table.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_
	 */
	public function test_uninstall__table() {

		// We use real tables, because we can't check if a temporary table exists.
		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );

		global $wpdb;

		$wpdb->query( "CREATE TABLE `{$wpdb->base_prefix}test` ( `id` BIGINT );" );

		$this->un_installer->uninstall['single']['tables'] = array( 'test' );
		$this->un_installer->uninstall_( 'single' );

		$this->assertNull( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->base_prefix}test'" ) );
	}

	/**
	 * Test that the database version of an entity is deleted on uninstall.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_single
	 */
	public function test_uninstall_single_unset_db_version() {

		$this->un_installer->context = 'single';
		$this->un_installer->network_wide = false;

		$this->un_installer->set_db_version();

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );

		$this->assertSame(
			array( 'test' => array( 'version' => '1.0.0' ) )
			, $wordpoints_data['modules']
		);

		$this->assertSame( '1.0.0', $this->un_installer->get_db_version() );

		$this->un_installer->uninstall_single();

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );
		$this->assertArrayHasKey( 'test', $wordpoints_data['modules'] );
		$this->assertArrayNotHasKey( 'version', $wordpoints_data['modules']['test'] );

		$this->assertFalse( $this->un_installer->get_db_version() );
	}

	/**
	 * Test that the database version of an entity is deleted on site uninstall.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_single
	 */
	public function test_uninstall_site_unset_db_version() {

		$this->un_installer->context = 'site';
		$this->un_installer->network_wide = false;

		$this->un_installer->set_db_version();

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );

		$this->assertSame(
			array( 'test' => array( 'version' => '1.0.0' ) )
			, $wordpoints_data['modules']
		);

		$this->assertSame( '1.0.0', $this->un_installer->get_db_version() );

		$this->un_installer->uninstall_site();

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );
		$this->assertArrayHasKey( 'test', $wordpoints_data['modules'] );
		$this->assertArrayNotHasKey( 'version', $wordpoints_data['modules']['test'] );

		$this->assertFalse( $this->un_installer->get_db_version() );
	}


	/**
	 * Test that the database version of an entity is deleted on network uninstall.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_single
	 */
	public function test_uninstall_network_unset_db_version() {

		$this->un_installer->context = 'network';
		$this->un_installer->network_wide = true;

		$this->un_installer->set_db_version();

		$wordpoints_data = get_site_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );

		$this->assertSame(
			array( 'test' => array( 'version' => '1.0.0' ) )
			, $wordpoints_data['modules']
		);

		$this->assertSame( '1.0.0', $this->un_installer->get_db_version() );

		$this->un_installer->network_wide = null;

		$this->un_installer->uninstall_network();

		$wordpoints_data = get_site_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );
		$this->assertArrayHasKey( 'test', $wordpoints_data['modules'] );
		$this->assertArrayNotHasKey( 'version', $wordpoints_data['modules']['test'] );

		$this->assertFalse( $this->un_installer->get_db_version() );
	}

	/**
	 * Test that it uses the value of wp_is_large_network() by default.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::skip_per_site_install
	 *
	 * @requires WordPress multisite
	 */
	public function test_skip_per_site_install() {

		$this->assertSame(
			WordPoints_Un_Installer_Base::DO_INSTALL
			, $this->un_installer->skip_per_site_install()
		);
	}

	/**
	 * Test that it skips and requires manual install if wp_is_large_network().
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::skip_per_site_install
	 *
	 * @requires WordPress multisite
	 */
	public function test_skip_per_site_install_large_network() {

		add_filter( 'wp_is_large_network', '__return_true' );

		$this->assertSame(
			WordPoints_Un_Installer_Base::SKIP_INSTALL
				| WordPoints_Un_Installer_Base::REQUIRES_MANUAL_INSTALL
			, $this->un_installer->skip_per_site_install()
		);
	}

	/**
	 * Test that it uses the value of wp_is_large_network() by default.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::do_per_site_install
	 *
	 * @requires WordPress multisite
	 *
	 * @expectedDeprecated WordPoints_Un_Installer_Base::do_per_site_install
	 */
	public function test_do_per_site_install() {

		$this->assertTrue( $this->un_installer->do_per_site_install() );
	}

	/**
	 * Test that it uses the value of wp_is_large_network() by default.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::do_per_site_install
	 *
	 * @requires WordPress multisite
	 *
	 * @expectedDeprecated WordPoints_Un_Installer_Base::do_per_site_install
	 */
	public function test_do_per_site_install_large_network() {

		add_filter( 'wp_is_large_network', '__return_true' );

		$this->assertFalse( $this->un_installer->do_per_site_install() );
	}

	/**
	 * Test that it returns all the site IDs.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_all_site_ids
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_all_site_ids() {

		$ids = array( get_current_blog_id() );
		$ids[] = $this->factory->blog->create();

		// Create another blog on a different site.
		$this->factory->blog->create( array( 'site_id' => 45 ) );

		$this->assertSame( $ids, $this->un_installer->get_all_site_ids() );
	}

	/**
	 * Test checking if the entity is network installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::is_network_installed
	 * @covers WordPoints_Un_Installer_Base::set_network_installed
	 * @covers WordPoints_Un_Installer_Base::unset_network_installed
	 *
	 * @requires WordPress multisite
	 */
	public function test_is_network_installed() {

		$this->un_installer->set_network_installed();

		$network_installed = get_site_option( 'wordpoints_network_installed' );

		$this->assertInternalType( 'array', $network_installed );
		$this->assertArrayHasKey( 'module', $network_installed );
		$this->assertArrayHasKey( 'test', $network_installed['module'] );
		$this->assertTrue( $network_installed['module']['test'] );

		$this->assertTrue( $this->un_installer->is_network_installed() );

		$this->un_installer->unset_network_installed();

		$network_installed = get_site_option( 'wordpoints_network_installed' );

		$this->assertInternalType( 'array', $network_installed );
		$this->assertArrayHasKey( 'module', $network_installed );
		$this->assertArrayNotHasKey( 'test', $network_installed['module'] );

		$this->assertFalse( $this->un_installer->is_network_installed() );
	}

	/**
	 * Test checking if an entity which uses $option_prefix is network installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::is_network_installed
	 * @covers WordPoints_Un_Installer_Base::set_network_installed
	 * @covers WordPoints_Un_Installer_Base::unset_network_installed
	 *
	 * @expectedDeprecated WordPoints_Un_Installer_Base::__construct
	 *
	 * @requires WordPress multisite
	 */
	public function test_is_network_installed_option_prefix() {

		$installer = new WordPoints_Un_Installer_Option_Prefix_Mock(
			'prefix'
			, '1.0.0'
		);

		$installer->set_network_installed();

		$this->assertTrue( get_site_option( 'prefix_network_installed' ) );

		$this->assertTrue( $installer->is_network_installed() );

		$installer->unset_network_installed();

		$this->assertFalse( get_site_option( 'prefix_network_installed' ) );

		$this->assertFalse( $installer->is_network_installed() );
	}

	/**
	 * Test setting that the entity's network install was skipped.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::set_network_install_skipped
	 * @covers WordPoints_Un_Installer_Base::unset_network_install_skipped
	 *
	 * @requires WordPress multisite
	 */
	public function test_network_install_skipped() {

		$this->un_installer->set_network_install_skipped();

		$install_skipped = get_site_option( 'wordpoints_network_install_skipped' );

		$this->assertInternalType( 'array', $install_skipped );
		$this->assertArrayHasKey( 'module', $install_skipped );
		$this->assertArrayHasKey( 'test', $install_skipped['module'] );
		$this->assertTrue( $install_skipped['module']['test'] );

		$this->un_installer->unset_network_install_skipped();

		$install_skipped = get_site_option( 'wordpoints_network_install_skipped' );

		$this->assertInternalType( 'array', $install_skipped );
		$this->assertArrayHasKey( 'module', $install_skipped );
		$this->assertArrayNotHasKey( 'test', $install_skipped['module'] );
	}

	/**
	 * Test setting that an $option_prefix-using entity's network install was skipped.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::set_network_install_skipped
	 * @covers WordPoints_Un_Installer_Base::unset_network_install_skipped
	 *
	 * @expectedDeprecated WordPoints_Un_Installer_Base::__construct
	 *
	 * @requires WordPress multisite
	 */
	public function test_network_install_skipped_option_prefix() {

		$installer = new WordPoints_Un_Installer_Option_Prefix_Mock(
			'prefix'
			, '1.0.0'
		);

		$installer->set_network_install_skipped();

		$this->assertTrue( get_site_option( 'prefix_network_install_skipped' ) );

		$installer->unset_network_install_skipped();

		$this->assertFalse( get_site_option( 'prefix_network_install_skipped' ) );
	}

	/**
	 * Test setting that the entity's network update was skipped.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::set_network_update_skipped
	 * @covers WordPoints_Un_Installer_Base::unset_network_update_skipped
	 *
	 * @requires WordPress multisite
	 */
	public function test_network_update_skipped() {

		$this->un_installer->updating_from = '0.9.0';
		$this->un_installer->set_network_update_skipped();

		$update_skipped = get_site_option( 'wordpoints_network_update_skipped' );

		$this->assertInternalType( 'array', $update_skipped );
		$this->assertArrayHasKey( 'module', $update_skipped );
		$this->assertArrayHasKey( 'test', $update_skipped['module'] );
		$this->assertSame( '0.9.0', $update_skipped['module']['test'] );

		$this->un_installer->unset_network_update_skipped();

		$update_skipped = get_site_option( 'wordpoints_network_update_skipped' );

		$this->assertInternalType( 'array', $update_skipped );
		$this->assertArrayHasKey( 'module', $update_skipped );
		$this->assertArrayNotHasKey( 'test', $update_skipped['module'] );
	}

	/**
	 * Test setting that an $option_prefix-using entity's network update was skipped.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::set_network_update_skipped
	 * @covers WordPoints_Un_Installer_Base::unset_network_update_skipped
	 *
	 * @expectedDeprecated WordPoints_Un_Installer_Base::__construct
	 *
	 * @requires WordPress multisite
	 */
	public function test_network_update_skipped_option_prefix() {

		$installer = new WordPoints_Un_Installer_Option_Prefix_Mock(
			'prefix'
			, '1.0.0'
		);

		$installer->updating_from = '0.9.0';
		$installer->set_network_update_skipped();

		$this->assertSame(
			'0.9.0'
			, get_site_option( 'prefix_network_update_skipped' )
		);

		$installer->unset_network_update_skipped();

		$this->assertFalse( get_site_option( 'prefix_network_update_skipped' ) );
	}

	/**
	 * Test that it uses the value of wp_is_large_network() by default.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::do_per_site_uninstall
	 *
	 * @requires WordPress multisite
	 */
	public function test_do_per_site_uninstall() {

		$this->assertTrue( $this->un_installer->do_per_site_uninstall() );
	}

	/**
	 * Test that it still does it if the module isn't network-installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::do_per_site_uninstall
	 *
	 * @requires WordPress multisite
	 */
	public function test_do_per_site_uninstall_large_network() {

		add_filter( 'wp_is_large_network', '__return_true' );

		$this->assertTrue( $this->un_installer->do_per_site_uninstall() );
	}

	/**
	 * Test that it skips it if the module is network-installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::do_per_site_uninstall
	 *
	 * @requires WordPress multisite
	 */
	public function test_do_per_site_uninstall_large_network_network_wide() {

		add_filter( 'wp_is_large_network', '__return_true' );

		$this->un_installer->set_network_installed();

		$this->assertFalse( $this->un_installer->do_per_site_uninstall() );
	}

	/**
	 * Test that it skips it if the module is installed on too many sites.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::do_per_site_uninstall
	 *
	 * @requires WordPress multisite
	 */
	public function test_do_per_site_uninstall_large_network_many_sites() {

		add_filter( 'wp_is_large_network', '__return_true' );

		$un_installer = $this->createPartialMock(
			'WordPoints_PHPUnit_Mock_Un_Installer'
			, array( 'get_installed_site_ids' )
		);

		$un_installer->slug = 'test';
		$un_installer->type = 'module';
		$un_installer->installable = new WordPoints_Installable(
			'module'
			, 'test'
			, '1.0.0'
		);

		$un_installer->method( 'get_installed_site_ids' )->willReturn(
			array_fill( 0, 10001, 1 )
		);

		$this->assertFalse( $un_installer->do_per_site_uninstall() );
	}

	/**
	 * Test that it uses the value of wp_is_large_network() by default.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::do_per_site_update
	 *
	 * @requires WordPress multisite
	 */
	public function test_do_per_site_update() {

		$this->assertTrue( $this->un_installer->do_per_site_update() );
	}

	/**
	 * Test that it still does it if the module isn't network-installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::do_per_site_update
	 *
	 * @requires WordPress multisite
	 */
	public function test_do_per_site_update_large_network() {

		add_filter( 'wp_is_large_network', '__return_true' );

		$this->assertTrue( $this->un_installer->do_per_site_update() );
	}

	/**
	 * Test that it skips it if the module is network-installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::do_per_site_update
	 *
	 * @requires WordPress multisite
	 */
	public function test_do_per_site_update_large_network_network_wide() {

		add_filter( 'wp_is_large_network', '__return_true' );

		$this->un_installer->set_network_installed();

		$this->assertFalse( $this->un_installer->do_per_site_update() );
	}

	/**
	 * Test getting the IDs of the sites where the entity is installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_installed_site_ids
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_installed_site_ids() {

		$site_ids = array( $this->factory->blog->create() );

		update_site_option(
			'wordpoints_module_test_installed_sites'
			, $site_ids
		);

		$this->assertSame(
			$site_ids
			, $this->un_installer->get_installed_site_ids()
		);
	}

	/**
	 * Test that it returns all site IDs if the entity is network-installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_installed_site_ids
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_installed_site_ids_network_wide() {

		$this->un_installer->set_network_installed();

		$site_id = $this->factory->blog->create();

		update_site_option(
			'wordpoints_module_test_installed_sites'
			, array( $site_id )
		);

		$this->assertSame(
			array( get_current_blog_id(), $site_id )
			, $this->un_installer->get_installed_site_ids()
		);
	}

	/**
	 * Test for an entity with an option prefix.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_installed_site_ids
	 *
	 * @expectedDeprecated WordPoints_Un_Installer_Base::__construct
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_installed_site_ids_option_prefix() {

		$site_ids = array( $this->factory->blog->create() );

		update_site_option(
			'prefix_installed_sites'
			, $site_ids
		);

		$installer = new WordPoints_Un_Installer_Option_Prefix_Mock(
			'prefix'
			, '1.0.0'
		);

		$this->assertSame(
			$site_ids
			, $installer->get_installed_site_ids()
		);
	}

	/**
	 * Test that it returns all site IDs if the entity is network-installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_installed_site_ids
	 *
	 * @expectedDeprecated WordPoints_Un_Installer_Base::__construct
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_installed_site_ids_network_wide_option_prefix() {

		$installer = new WordPoints_Un_Installer_Option_Prefix_Mock(
			'prefix'
			, '1.0.0'
		);

		$installer->set_network_installed();

		$site_id = $this->factory->blog->create();

		update_site_option(
			'prefix_installed_sites'
			, array( $site_id )
		);

		$this->assertSame(
			array( get_current_blog_id(), $site_id )
			, $installer->get_installed_site_ids()
		);
	}

	/**
	 * Test getting the IDs of the sites where WordPoints is installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_installed_site_ids
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_installed_site_ids_wordpoints() {

		$this->un_installer->slug = 'wordpoints';
		$this->un_installer->installable = new WordPoints_Installable(
			'plugin'
			, 'wordpoints'
			, '1.0.0'
		);

		$this->un_installer->installable->unset_network_installed();

		$site_ids = array( $this->factory->blog->create() );

		update_site_option(
			'wordpoints_installed_sites'
			, $site_ids
		);

		$this->assertSame(
			$site_ids
			, $this->un_installer->get_installed_site_ids()
		);
	}

	/**
	 * Test that it returns all site IDs if WordPoints is network-installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_installed_site_ids
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_installed_site_ids_network_wide_wordpoints() {

		$this->un_installer->slug = 'wordpoints';
		$this->un_installer->installable = new WordPoints_Installable(
			'plugin'
			, 'wordpoints'
			, '1.0.0'
		);

		$this->un_installer->set_network_installed();

		$site_id = $this->factory->blog->create();

		update_site_option(
			'wordpoints_installed_sites'
			, array( $site_id )
		);

		$this->assertSame(
			array( get_current_blog_id(), $site_id )
			, $this->un_installer->get_installed_site_ids()
		);
	}

	/**
	 * Test getting the IDs of the sites where a component is installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_installed_site_ids
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_installed_site_ids_component() {

		$this->un_installer->type = 'component';
		$this->un_installer->installable = new WordPoints_Installable(
			'component'
			, 'test'
			, '1.0.0'
		);

		$site_ids = array( $this->factory->blog->create() );

		update_site_option(
			'wordpoints_test_installed_sites'
			, $site_ids
		);

		$this->assertSame(
			$site_ids
			, $this->un_installer->get_installed_site_ids()
		);
	}

	/**
	 * Test that it returns all site IDs if a component is network-installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_installed_site_ids
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_installed_site_ids_network_wide_component() {

		$this->un_installer->type = 'component';
		$this->un_installer->installable = new WordPoints_Installable(
			'component'
			, 'test'
			, '1.0.0'
		);

		$this->un_installer->set_network_installed();

		$site_id = $this->factory->blog->create();

		update_site_option(
			'wordpoints_test_installed_sites'
			, array( $site_id )
		);

		$this->assertSame(
			array( get_current_blog_id(), $site_id )
			, $this->un_installer->get_installed_site_ids()
		);
	}

	/**
	 * Test adding a site to the list of sites where the entity is installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::add_installed_site_id
	 *
	 * @requires WordPress multisite
	 */
	public function test_add_installed_site_id() {

		$site_id = $this->factory->blog->create();

		update_site_option(
			'wordpoints_module_test_installed_sites'
			, array( get_current_blog_id() )
		);

		$this->un_installer->add_installed_site_id( $site_id );

		$this->assertSame(
			array( get_current_blog_id(), $site_id )
			, $this->un_installer->get_installed_site_ids()
		);
	}

	/**
	 * Test that the current site ID is used if none is supplied.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::add_installed_site_id
	 *
	 * @requires WordPress multisite
	 */
	public function test_add_installed_site_id_default() {

		$this->un_installer->add_installed_site_id();

		$this->assertSame(
			array( get_current_blog_id() )
			, $this->un_installer->get_installed_site_ids()
		);
	}

	/**
	 * Test for an entity with an option prefix.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::add_installed_site_id
	 *
	 * @expectedDeprecated WordPoints_Un_Installer_Base::__construct
	 *
	 * @requires WordPress multisite
	 */
	public function test_add_installed_site_id_option_prefix() {

		$site_id = $this->factory->blog->create();

		update_site_option(
			'prefix_installed_sites'
			, array( get_current_blog_id() )
		);

		$installer = new WordPoints_Un_Installer_Option_Prefix_Mock(
			'prefix'
			, '1.0.0'
		);

		$installer->add_installed_site_id( $site_id );

		$this->assertSame(
			array( get_current_blog_id(), $site_id )
			, $installer->get_installed_site_ids()
		);
	}

	/**
	 * Test adding to the list of sites where WordPoints is installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::add_installed_site_id
	 *
	 * @requires WordPress multisite
	 */
	public function test_add_installed_site_id_wordpoints() {

		$this->un_installer->slug = 'wordpoints';
		$this->un_installer->installable = new WordPoints_Installable(
			'plugin'
			, 'wordpoints'
			, '1.0.0'
		);

		$site_id = $this->factory->blog->create();

		update_site_option(
			'wordpoints_installed_sites'
			, array( get_current_blog_id() )
		);

		$this->un_installer->add_installed_site_id( $site_id );

		$this->assertSame(
			array( get_current_blog_id(), $site_id )
			, $this->un_installer->get_installed_site_ids()
		);
	}

	/**
	 * Test adding to the list of sites where a component is installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::add_installed_site_id
	 *
	 * @requires WordPress multisite
	 */
	public function test_add_installed_site_id_component() {

		$this->un_installer->type = 'component';
		$this->un_installer->installable = new WordPoints_Installable(
			'component'
			, 'test'
			, '1.0.0'
		);

		$site_id = $this->factory->blog->create();

		update_site_option(
			'wordpoints_test_installed_sites'
			, array( get_current_blog_id() )
		);

		$this->un_installer->add_installed_site_id( $site_id );

		$this->assertSame(
			array( get_current_blog_id(), $site_id )
			, $this->un_installer->get_installed_site_ids()
		);
	}

	/**
	 * Test deleting the list of sites where the entity is installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::delete_installed_site_ids
	 *
	 * @requires WordPress multisite
	 */
	public function test_delete_installed_site_ids() {

		update_site_option(
			'wordpoints_module_test_installed_sites'
			, array( get_current_blog_id() )
		);

		$this->un_installer->delete_installed_site_ids();

		$this->assertSame( array(), $this->un_installer->get_installed_site_ids() );
	}

	/**
	 * Test for an entity with an option prefix.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::delete_installed_site_ids
	 *
	 * @expectedDeprecated WordPoints_Un_Installer_Base::__construct
	 *
	 * @requires WordPress multisite
	 */
	public function test_delete_installed_site_ids_option_prefix() {

		update_site_option(
			'prefix_installed_sites'
			, array( get_current_blog_id() )
		);

		$installer = new WordPoints_Un_Installer_Option_Prefix_Mock(
			'prefix'
			, '1.0.0'
		);

		$installer->delete_installed_site_ids();

		$this->assertSame( array(), $installer->get_installed_site_ids() );
	}

	/**
	 * Test deleting the list of the sites where WordPoints is installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::delete_installed_site_ids
	 *
	 * @requires WordPress multisite
	 */
	public function test_delete_installed_site_ids_wordpoints() {

		$this->un_installer->slug = 'wordpoints';
		$this->un_installer->installable = new WordPoints_Installable(
			'plugin'
			, 'wordpoints'
			, '1.0.0'
		);

		$this->un_installer->installable->unset_network_installed();

		update_site_option(
			'wordpoints_installed_sites'
			, array( get_current_blog_id() )
		);

		$this->un_installer->delete_installed_site_ids();

		$this->assertSame( array(), $this->un_installer->get_installed_site_ids() );
	}

	/**
	 * Test deleting the list of the sites where a component is installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::delete_installed_site_ids
	 *
	 * @requires WordPress multisite
	 */
	public function test_delete_installed_site_ids_component() {

		$this->un_installer->type = 'component';
		$this->un_installer->installable = new WordPoints_Installable(
			'component'
			, 'test'
			, '1.0.0'
		);

		update_site_option(
			'wordpoints_test_installed_sites'
			, array( get_current_blog_id() )
		);

		$this->un_installer->delete_installed_site_ids();

		$this->assertSame( array(), $this->un_installer->get_installed_site_ids() );
	}

	/**
	 * Test validating a list of site IDs against the database.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::validate_site_ids
	 *
	 * @requires WordPress multisite
	 */
	public function test_validate_site_ids() {

		$site_on_other_network = $this->factory->blog->create(
			array( 'site_id' => 45 )
		);

		$site_id = $this->factory->blog->create();

		$site_ids = array(
			'invalid',
			4543,
			get_current_blog_id(),
			$site_on_other_network,
			$site_id,
		);

		// Create a site not on the list.
		$this->factory->blog->create();

		$this->assertSame(
			array( get_current_blog_id(), $site_id )
			, $this->un_installer->validate_site_ids( $site_ids )
		);
	}

	/**
	 * Test validate_site_ids() when the array is empty.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::validate_site_ids
	 */
	public function test_validate_site_ids_empty() {

		$this->assertSame(
			array()
			, $this->un_installer->validate_site_ids( array() )
		);
	}

	/**
	 * Test validate_site_ids() when the value is not an array.
	 *
	 * @since 2.1.0
	 *
	 * @covers WordPoints_Un_Installer_Base::validate_site_ids
	 */
	public function test_validate_site_ids_not_array() {

		$this->assertSame(
			array()
			, $this->un_installer->validate_site_ids( 'invalid' )
		);
	}

	/**
	 * Test getting the database version of an entity.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_db_version
	 * @covers WordPoints_Un_Installer_Base::set_db_version
	 * @covers WordPoints_Un_Installer_Base::unset_db_version
	 */
	public function test_get_db_version() {

		$this->un_installer->network_wide = false;
		$this->un_installer->context = is_multisite() ? 'site' : 'single';

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->un_installer->set_db_version( '0.9.0' );

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );

		$this->assertSame(
			array( 'test' => array( 'version' => '0.9.0' ) )
			, $wordpoints_data['modules']
		);

		$this->assertSame( '0.9.0', $this->un_installer->get_db_version() );

		$this->un_installer->unset_db_version();

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );
		$this->assertArrayHasKey( 'test', $wordpoints_data['modules'] );
		$this->assertArrayNotHasKey( 'version', $wordpoints_data['modules']['test'] );

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->un_installer->set_db_version();

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );

		$this->assertSame(
			array( 'test' => array( 'version' => '1.0.0' ) )
			, $wordpoints_data['modules']
		);

		$this->assertSame( '1.0.0', $this->un_installer->get_db_version() );
	}

	/**
	 * Test getting the database version of an entity when it is network-installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_db_version
	 * @covers WordPoints_Un_Installer_Base::set_db_version
	 * @covers WordPoints_Un_Installer_Base::unset_db_version
	 */
	public function test_get_db_version_network_wide() {

		$this->un_installer->network_wide = true;
		$this->un_installer->context = 'network';

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->un_installer->set_db_version( '0.9.0' );

		$wordpoints_data = get_site_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );

		$this->assertSame(
			array( 'test' => array( 'version' => '0.9.0' ) )
			, $wordpoints_data['modules']
		);

		$this->assertSame( '0.9.0', $this->un_installer->get_db_version() );

		$this->un_installer->unset_db_version();

		$wordpoints_data = get_site_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );
		$this->assertArrayHasKey( 'test', $wordpoints_data['modules'] );
		$this->assertArrayNotHasKey( 'version', $wordpoints_data['modules']['test'] );

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->un_installer->set_db_version();

		$wordpoints_data = get_site_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );

		$this->assertSame(
			array( 'test' => array( 'version' => '1.0.0' ) )
			, $wordpoints_data['modules']
		);

		$this->assertSame( '1.0.0', $this->un_installer->get_db_version() );
	}

	/**
	 * Test getting the database version of WordPoints.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_db_version
	 * @covers WordPoints_Un_Installer_Base::set_db_version
	 *
	 * @requires WordPoints !network-active
	 */
	public function test_get_db_version_wordpoints() {

		$this->un_installer->slug = 'wordpoints';
		$this->un_installer->installable = new WordPoints_Installable(
			'plugin'
			, 'wordpoints'
			, '1.0.0'
		);

		$this->assertSame( WORDPOINTS_VERSION, $this->un_installer->get_db_version() );

		$this->un_installer->set_db_version( '0.9.0' );

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'version', $wordpoints_data );

		$this->assertSame( '0.9.0', $wordpoints_data['version'] );

		$this->assertSame( '0.9.0', $this->un_installer->get_db_version() );

		$this->un_installer->set_db_version();

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'version', $wordpoints_data );

		$this->assertSame( '1.0.0', $wordpoints_data['version'] );

		$this->assertSame( '1.0.0', $this->un_installer->get_db_version() );
	}

	/**
	 * Test getting the database version of an entity when it is network-installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_db_version
	 * @covers WordPoints_Un_Installer_Base::set_db_version
	 *
	 * @requires WordPoints network-active
	 */
	public function test_get_db_version_wordpoints_network_wide() {

		$this->un_installer->slug = 'wordpoints';
		$this->un_installer->context = 'network';
		$this->un_installer->network_wide = true;
		$this->un_installer->installable = new WordPoints_Installable(
			'plugin'
			, 'wordpoints'
			, '1.0.0'
		);

		$this->assertSame( WORDPOINTS_VERSION, $this->un_installer->get_db_version() );

		$this->un_installer->set_db_version( '0.9.0' );

		$wordpoints_data = get_site_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'version', $wordpoints_data );

		$this->assertSame( '0.9.0', $wordpoints_data['version'] );

		$this->assertSame( '0.9.0', $this->un_installer->get_db_version() );

		$this->un_installer->set_db_version();

		$wordpoints_data = get_site_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'version', $wordpoints_data );

		$this->assertSame( '1.0.0', $wordpoints_data['version'] );

		$this->assertSame( '1.0.0', $this->un_installer->get_db_version() );
	}

	/**
	 * Test setting the database version of a component.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::set_component_version
	 *
	 * @expectedDeprecated WordPoints_Un_Installer_Base::set_component_version
	 *
	 * @requires WordPoints !network-active
	 */
	public function test_set_component_version() {

		$this->un_installer->set_component_version( 'test', '0.9.0' );

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'components', $wordpoints_data );
		$this->assertArrayHasKey( 'test', $wordpoints_data['components'] );

		$this->assertSame(
			array( 'version' => '0.9.0' )
			, $wordpoints_data['components']['test']
		);
	}

	/**
	 * Test setting the version of a component when WordPoints is network-active.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::set_component_version
	 *
	 * @expectedDeprecated WordPoints_Un_Installer_Base::set_component_version
	 *
	 * @requires WordPoints network-active
	 */
	public function test_set_component_version_network_wide() {

		$this->un_installer->set_component_version( 'test', '0.9.0' );

		$wordpoints_data = get_site_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'components', $wordpoints_data );
		$this->assertArrayHasKey( 'test', $wordpoints_data['components'] );

		$this->assertSame(
			array( 'version' => '0.9.0' )
			, $wordpoints_data['components']['test']
		);
	}

	//
	// Helpers.
	//

	/**
	 * Assert that custom capabilities were loaded.
	 *
	 * @since 2.0.0
	 */
	public function assertCustomCapsLoaded() {

		$this->assertSame( $this->custom_caps, $this->un_installer->custom_caps );
		$this->assertSame(
			array_keys( $this->custom_caps )
			, $this->un_installer->custom_caps_keys
		);
	}

	/**
	 * Assert that a capability was added.
	 *
	 * @since 2.0.0
	 *
	 * @param string $analog A capability from the group where this one should occur.
	 * @param string $cap    The capability that should have been added.
	 */
	public function assertCapWasAdded( $analog, $cap ) {

		/** @var WP_Role $role */
		foreach ( wp_roles()->role_objects as $role ) {
			if ( $role->has_cap( $analog ) ) {
				$this->assertTrue( $role->has_cap( $cap ) );
			}
		}
	}

	/**
	 * Assert that a capability was removed.
	 *
	 * @since 2.0.0
	 *
	 * @param string $cap The capability that should have been removed.
	 */
	public function assertCapWasRemoved( $cap ) {

		/** @var WP_Role $role */
		foreach ( wp_roles()->role_objects as $role ) {
			$this->assertFalse( $role->has_cap( $cap ) );
		}
	}

	/**
	 * Assert that a list table is prepared for uninstallation.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args The list table arguments.
	 */
	public function assertListTablePrepared( $args ) {

		$screen = key( $args );
		$args = reset( $args );

		$this->assertListTableHiddenColumnsPrepared( $screen, $args['parent'] );

		if ( empty( $args['options'] ) ) {
			return;
		}

		foreach ( $args['options'] as $option ) {
			$this->assertListTableOptionPrepared( $screen, $args['parent'], $option );
		}
	}

	/**
	 * Assert that a list table's hidden columns are prepared for uninstallation.
	 *
	 * @since 2.0.0
	 *
	 * @param string $screen The screen slug.
	 * @param string $parent The parent screen slug.
	 */
	public function assertListTableHiddenColumnsPrepared( $screen, $parent ) {

		$this->assertContainsSame(
			"manage{$parent}_{$screen}columnshidden"
			, $this->un_installer->uninstall['single']['user_meta']
		);
		$this->assertContainsSame(
			"manage{$parent}_{$screen}columnshidden"
			, $this->un_installer->uninstall['network']['user_meta']
		);
		$this->assertContainsSame(
			"manage{$parent}_{$screen}-networkcolumnshidden"
			, $this->un_installer->uninstall['network']['user_meta']
		);
	}

	/**
	 * Assert that a list table option is prepared for uninstallation.
	 *
	 * @since 2.0.0
	 *
	 * @param string $screen The screen slug.
	 * @param string $parent The parent screen slug.
	 * @param string $option The option slug.
	 */
	public function assertListTableOptionPrepared( $screen, $parent, $option ) {

		$this->assertContainsSame(
			"{$parent}_{$screen}_{$option}"
			, $this->un_installer->uninstall['single']['user_meta']
		);
		$this->assertContainsSame(
			"{$parent}_{$screen}_{$option}"
			, $this->un_installer->uninstall['network']['user_meta']
		);
		$this->assertContainsSame(
			"{$parent}_{$screen}_network_{$option}"
			, $this->un_installer->uninstall['network']['user_meta']
		);
	}

	/**
	 * Returns a mock array of custom capabilities.
	 *
	 * @since 2.0.0
	 *
	 * @return array Fake custom caps.
	 */
	public function custom_caps_getter() {
		return $this->custom_caps;
	}

	/**
	 * Sets the schema for the uninstaller.
	 *
	 * @since 2.0.0
	 *
	 * @see self::assertSchemaMapped()
	 */
	public function set_un_installer_schema() {

		$this->un_installer->schema['single'] = array(
			'key' => array( 'from_single' ),
		);

		$this->un_installer->schema['site'] = array(
			'key' => array( 'from_site' ),
		);

		$this->un_installer->schema['network'] = array(
			'key' => array( 'from_network' ),
		);

		$this->un_installer->schema['local'] = array(
			'key' => array( 'from_local' ),
		);

		$this->un_installer->schema['global'] = array(
			'key' => array( 'from_global' ),
		);

		$this->un_installer->schema['universal'] = array(
			'key' => array( 'from_universal' ),
		);
	}

	/**
	 * Asserts that the schema was mapped.
	 *
	 * @since 2.0.0
	 */
	public function assertSchemaMapped() {

		$this->assertSame(
			array(
				'key' => array( 'from_single', 'from_local', 'from_global', 'from_universal' ),
			)
			, $this->un_installer->schema['single']
		);

		$this->assertSame(
			array(
				'key' => array( 'from_site', 'from_local', 'from_universal' ),
			)
			, $this->un_installer->schema['site']
		);

		$this->assertSame(
			array(
				'key' => array( 'from_network', 'from_global', 'from_universal' ),
			)
			, $this->un_installer->schema['network']
		);
	}
}

// EOF
