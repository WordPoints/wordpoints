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
class WordPoints_Un_Installer_Base_Test extends WordPoints_UnitTestCase {

	/**
	 * The mock un/installer used in the tests.
	 *
	 * @since 2.0.0
	 *
	 * @var WordPoints_Un_Installer_Mock
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

		$this->un_installer = new WordPoints_Un_Installer_Mock( 'test', '1.0.0' );
		$this->un_installer->type = 'module';
	}

	/**
	 * Test that the slug and version are set on construction.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::__construct
	 */
	public function test_construct_slug_version_set() {

		$this->assertEquals( 'test', $this->un_installer->slug );
		$this->assertEquals( '1.0.0', $this->un_installer->version );
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

		new WordPoints_Un_Installer_Mock( null, '1.0.0' );
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

		new WordPoints_Un_Installer_Mock( 'test' );
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
	 * Test that we don't attempt to load custom capabilities when no getter is set.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::maybe_load_custom_caps
	 */
	public function test_maybe_load_custom_caps_no_getter() {

		$this->un_installer->maybe_load_custom_caps();

		$this->assertEmpty( $this->un_installer->custom_caps );
		$this->assertEmpty( $this->un_installer->custom_caps_keys );
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

		$this->assertEquals(
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

		$this->un_installer->before_install();

		$this->assertEquals(
			array(
				'key' => array( 'from_single', 'from_local', 'from_global', 'from_universal' ),
			)
			, $this->un_installer->schema['single']
		);

		$this->assertEquals(
			array(
				'key' => array( 'from_site', 'from_local', 'from_universal' ),
			)
			, $this->un_installer->schema['site']
		);

		$this->assertEquals(
			array(
				'key' => array( 'from_network', 'from_global', 'from_universal' ),
			)
			, $this->un_installer->schema['network']
		);
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

		$this->assertEquals(
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

		$this->assertEmpty( $this->un_installer->get_db_schema() );
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals( '1.0.0', $this->un_installer->get_db_version() );
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

		$this->assertEmpty( $this->un_installer->get_db_version() );
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

		$this->assertEquals(
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

		$this->assertEquals( '1.0.0', $this->un_installer->get_db_version() );
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

		$this->assertEmpty( $this->un_installer->get_db_version() );
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

		$this->assertEquals(
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

		$this->assertEquals( '1.0.0', $this->un_installer->get_db_version() );
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
	 */
	public function test_prepare_uninstall_list_tables() {

		$list_table = array(
			'screen_id' => array(
				'parent' => 'parent_screen',
				'options' => array( 'an_option' ),
			),
		);

		$this->un_installer->uninstall['list_tables'] = $list_table;
		$this->un_installer->prepare_uninstall_list_tables();

		$this->assertListTablePrepared( $list_table );
	}

	/**
	 * Test the default list table args used when preparing for uninstall.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::prepare_uninstall_list_tables
	 */
	public function test_prepare_uninstall_list_tables_defaults() {

		$list_table = array( 'screen_id' => array() );

		$this->un_installer->uninstall['list_tables'] = $list_table;
		$this->un_installer->prepare_uninstall_list_tables();

		$this->assertListTableHiddenColumnsPrepared( 'screen_id', 'wordpoints_page' );
		$this->assertListTableOptionPrepared( 'screen_id', 'wordpoints_page', 'per_page' );
	}

	/**
	 * Test that the wordpoints_modules screen receives special handling.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::prepare_uninstall_list_tables
	 */
	public function test_prepare_uninstall_list_tables_wordpoints_modules() {

		$list_table = array( 'wordpoints_modules' => array() );

		$this->un_installer->uninstall['list_tables'] = $list_table;
		$this->un_installer->prepare_uninstall_list_tables();

		$this->assertContains( 'managewordpoints_page_wordpoints_modulescolumnshidden', $this->un_installer->uninstall['single']['user_meta'] );
		$this->assertContains( 'managetoplevel_page_wordpoints_modulescolumnshidden', $this->un_installer->uninstall['network']['user_meta'] );
		$this->assertContains( 'managewordpoints_page_wordpoints_modules-networkcolumnshidden', $this->un_installer->uninstall['network']['user_meta'] );

		$this->assertContains( 'wordpoints_page_wordpoints_modules_per_page', $this->un_installer->uninstall['single']['user_meta'] );
		$this->assertContains( 'toplevel_page_wordpoints_modules_per_page', $this->un_installer->uninstall['network']['user_meta'] );
		$this->assertContains( 'wordpoints_page_wordpoints_modules_network_per_page', $this->un_installer->uninstall['network']['user_meta'] );
	}

	/**
	 * Test that list tables are prepared before uninstall.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::before_uninstall
	 */
	public function test_prepare_list_tables_before_uninstall() {

		$list_table = array(
			'screen_id' => array(
				'parent' => 'parent_screen',
				'options' => array( 'an_option' ),
			),
		);

		$this->un_installer->uninstall['list_tables'] = $list_table;
		$this->un_installer->before_uninstall();

		$this->assertListTablePrepared( $list_table );
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
		$this->assertEquals(
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
		$this->assertEquals(
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
		$this->assertEquals(
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
		$this->assertEquals(
			array( 'wordpoints_hook-one', 'wordpoints_hook-two' )
			, $this->un_installer->uninstall['site']['options']
		);
	}

	/**
	 * Test that the 'local' uninstall shortcut is mapped.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::map_uninstall_shortcuts
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

		$this->assertEquals(
			array(
				'options' => array( 'one', 'two' ),
				'other' => array( 'from_single', 'something' ),
			)
			, $this->un_installer->uninstall['single']
		);

		$this->assertArrayHasKey( 'site', $this->un_installer->uninstall );
		$this->assertEquals(
			array(
				'options' => array( 'one', 'two' ),
				'other' => array( 'something' ),
			)
			, $this->un_installer->uninstall['site']
		);

		$this->assertEmpty( $this->un_installer->uninstall['network'] );
	}

	/**
	 * Test that the 'global' uninstall shortcut is mapped.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::map_uninstall_shortcuts
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

		$this->assertEquals(
			array(
				'options' => array( 'one', 'two' ),
				'other' => array( 'from_single', 'something' ),
			)
			, $this->un_installer->uninstall['single']
		);

		$this->assertArrayHasKey( 'network', $this->un_installer->uninstall );
		$this->assertEquals(
			array(
				'options' => array( 'one', 'two' ),
				'other' => array( 'something' ),
			)
			, $this->un_installer->uninstall['network']
		);

		$this->assertEmpty( $this->un_installer->uninstall['site'] );
	}

	/**
	 * Test that the 'universal' uninstall shortcut is mapped.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::map_uninstall_shortcuts
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

		$this->assertEquals(
			array(
				'options' => array( 'one', 'two' ),
				'other' => array( 'from_single', 'something' ),
			)
			, $this->un_installer->uninstall['single']
		);

		$this->assertEquals(
			array(
				'options' => array( 'one', 'two' ),
				'other' => array( 'something' ),
				'another' => array( 'bob' ),
			)
			, $this->un_installer->uninstall['site']
		);

		$this->assertArrayHasKey( 'network', $this->un_installer->uninstall );
		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
			array(
				'options' => array( 'one', 'two' ),
				'other' => array( 'from_single', 'something' ),
			)
			, $this->un_installer->uninstall['single']
		);

		$this->assertEquals(
			array(
				'options' => array( 'one', 'two' ),
				'other' => array( 'something' ),
				'another' => array( 'bob' ),
			)
			, $this->un_installer->uninstall['site']
		);

		$this->assertArrayHasKey( 'network', $this->un_installer->uninstall );
		$this->assertEquals(
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

		$this->assertEmpty( get_post_meta( $post_id, __METHOD__ ) );
		$this->assertEmpty( get_post_meta( $post_id_2, __METHOD__ ) );
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

		$this->assertEmpty( get_user_meta( $user_id, __METHOD__ ) );
	}

	/**
	 * Test uninstalling user metadata in site context.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_metadata
	 */
	public function test_uninstall_user_metadata_site_context() {

		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Multisite must be enabled.' );
		}

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, __METHOD__, 'test' );
		update_user_option( $user_id, __METHOD__, 'test2' );

		$this->un_installer->context = 'site';
		$this->un_installer->uninstall_metadata( 'user', __METHOD__ );

		$this->assertEmpty( get_user_option( $user_id, __METHOD__ ) );
		$this->assertEquals( 'test', get_user_meta( $user_id, __METHOD__, true ) );
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

		$this->assertEmpty( get_user_meta( $user_id, __METHOD__ ) );
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

		$this->assertEmpty( get_comment_meta( $comment_id, __METHOD__ ) );
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
	 */
	public function test_uninstall_network_option() {

		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Multisite must be enabled.' );
		}

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
	 * Wildcards aren't currently supported for network ("site") options.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::uninstall_option
	 */
	public function test_uninstall_network_option_wildcards() {

		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Multisite must be enabled.' );
		}

		add_site_option( 'test%', 'test' );
		add_site_option( 'testing', 'test' );
		add_site_option( 'tester', 'test' );

		$this->un_installer->context = 'network';
		$this->un_installer->uninstall_option( 'test%' );

		$this->assertFalse( get_site_option( 'test%' ) );
		$this->assertEquals( 'test', get_site_option( 'testing' ) );
		$this->assertEquals( 'test', get_site_option( 'tester' ) );
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
	 */
	public function test_uninstall_table_site_context() {

		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Multisite must be enabled.' );
		}

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

		global $wpdb;

		$wpdb->query( "CREATE TABLE `{$wpdb->base_prefix}test` ( `id` BIGINT );" );

		$this->un_installer->uninstall['single']['tables'] = array( 'test' );
		$this->un_installer->uninstall_( 'single' );

		$this->assertNull( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->base_prefix}test'" ) );
	}

	/**
	 * Test that it uses the value of wp_is_large_network() by default.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::do_per_site_install
	 *
	 * @requires WordPress multisite
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

		$this->assertEquals( $ids, $this->un_installer->get_all_site_ids() );
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

		$this->assertEmpty( get_site_option( 'prefix_network_installed' ) );

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

		$this->assertEmpty( get_site_option( 'prefix_network_install_skipped' ) );
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
		$this->assertEquals( '0.9.0', $update_skipped['module']['test'] );

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

		$this->assertEquals(
			'0.9.0'
			, get_site_option( 'prefix_network_update_skipped' )
		);

		$installer->unset_network_update_skipped();

		$this->assertEmpty( get_site_option( 'prefix_network_update_skipped' ) );
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

		// We don't actually create 10000 sites in the database.
		$mock_filter = new WordPoints_Mock_Filter( array_fill( 0, 10001, 1 ) );

		add_filter(
			'pre_site_option_wordpoints_module_test_installed_sites'
			, array( $mock_filter, 'filter' )
		);

		$this->assertFalse( $this->un_installer->do_per_site_uninstall() );
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$site_ids = array( $this->factory->blog->create() );

		update_site_option(
			'wordpoints_installed_sites'
			, $site_ids
		);

		$this->assertEquals(
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
		$this->un_installer->set_network_installed();

		$site_id = $this->factory->blog->create();

		update_site_option(
			'wordpoints_installed_sites'
			, array( $site_id )
		);

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
			array( get_current_blog_id(), $site_id )
			, $installer->get_installed_site_ids()
		);
	}

	/**
	 * Test getting the IDs of the sites where WordPoints is installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::add_installed_site_id
	 *
	 * @requires WordPress multisite
	 */
	public function test_add_installed_site_id_wordpoints() {

		$this->un_installer->slug = 'wordpoints';

		$site_id = $this->factory->blog->create();

		update_site_option(
			'wordpoints_installed_sites'
			, array( get_current_blog_id() )
		);

		$this->un_installer->add_installed_site_id( $site_id );

		$this->assertEquals(
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

		$this->assertEmpty( $this->un_installer->get_installed_site_ids() );
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

		$this->assertEmpty( $installer->get_installed_site_ids() );
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

		update_site_option(
			'wordpoints_installed_sites'
			, array( get_current_blog_id() )
		);

		$this->un_installer->delete_installed_site_ids();

		$this->assertEmpty( $this->un_installer->get_installed_site_ids() );
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
			'invalid'
			, 4543
			, get_current_blog_id()
			, $site_on_other_network
			, $site_id
		);

		// Create a site not on the list.
		$this->factory->blog->create();

		$this->assertEquals(
			array( get_current_blog_id(), $site_id )
			, $this->un_installer->validate_site_ids( $site_ids )
		);
	}

	/**
	 * Test getting the database version of an entity.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_db_version
	 * @covers WordPoints_Un_Installer_Base::set_db_version
	 */
	public function test_get_db_version() {

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->un_installer->set_db_version( '0.9.0' );

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );

		$this->assertEquals(
			array( 'test' => array( 'version' => '0.9.0' ) )
			, $wordpoints_data['modules']
		);

		$this->assertEquals( '0.9.0', $this->un_installer->get_db_version() );

		$this->un_installer->set_db_version();

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );

		$this->assertEquals(
			array( 'test' => array( 'version' => '1.0.0' ) )
			, $wordpoints_data['modules']
		);

		$this->assertEquals( '1.0.0', $this->un_installer->get_db_version() );
	}

	/**
	 * Test getting the database version of an entity when it is network-installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_db_version
	 * @covers WordPoints_Un_Installer_Base::set_db_version
	 */
	public function test_get_db_version_network_wide() {

		$this->un_installer->context = 'network';

		$this->assertFalse( $this->un_installer->get_db_version() );

		$this->un_installer->set_db_version( '0.9.0' );

		$wordpoints_data = get_site_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );

		$this->assertEquals(
			array( 'test' => array( 'version' => '0.9.0' ) )
			, $wordpoints_data['modules']
		);

		$this->assertEquals( '0.9.0', $this->un_installer->get_db_version() );

		$this->un_installer->set_db_version();

		$wordpoints_data = get_site_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );

		$this->assertEquals(
			array( 'test' => array( 'version' => '1.0.0' ) )
			, $wordpoints_data['modules']
		);

		$this->assertEquals( '1.0.0', $this->un_installer->get_db_version() );
	}

	/**
	 * Test getting the database version of WordPoints.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_db_version
	 * @covers WordPoints_Un_Installer_Base::set_db_version
	 */
	public function test_get_db_version_wordpoints() {

		$this->un_installer->slug = 'wordpoints';

		if ( is_wordpoints_network_active() ) {
			$this->assertFalse( $this->un_installer->get_db_version() );
		} else {
			$this->assertEquals( WORDPOINTS_VERSION, $this->un_installer->get_db_version() );
		}

		$this->un_installer->set_db_version( '0.9.0' );

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'version', $wordpoints_data );

		$this->assertEquals( '0.9.0', $wordpoints_data['version'] );

		$this->assertEquals( '0.9.0', $this->un_installer->get_db_version() );

		$this->un_installer->set_db_version();

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'version', $wordpoints_data );

		$this->assertEquals( '1.0.0', $wordpoints_data['version'] );


		$this->assertEquals( '1.0.0', $this->un_installer->get_db_version() );
	}

	/**
	 * Test getting the database version of an entity when it is network-installed.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Un_Installer_Base::get_db_version
	 * @covers WordPoints_Un_Installer_Base::set_db_version
	 */
	public function test_get_db_version_wordpoints_network_wide() {

		$this->un_installer->slug = 'wordpoints';
		$this->un_installer->context = 'network';

		if ( is_wordpoints_network_active() ) {
			$this->assertEquals( WORDPOINTS_VERSION, $this->un_installer->get_db_version() );
		} else {
			$this->assertFalse( $this->un_installer->get_db_version() );
		}

		$this->un_installer->set_db_version( '0.9.0' );

		$wordpoints_data = get_site_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'version', $wordpoints_data );

		$this->assertEquals( '0.9.0', $wordpoints_data['version'] );

		$this->assertEquals( '0.9.0', $this->un_installer->get_db_version() );

		$this->un_installer->set_db_version();

		$wordpoints_data = get_site_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'version', $wordpoints_data );

		$this->assertEquals( '1.0.0', $wordpoints_data['version'] );

		$this->assertEquals( '1.0.0', $this->un_installer->get_db_version() );
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals( $this->custom_caps, $this->un_installer->custom_caps );
		$this->assertEquals(
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

		global $wp_roles;

		if ( ! $wp_roles instanceof WP_Roles ) {
			$wp_roles = new WP_Roles;
		}

		foreach ( $wp_roles->role_objects as $role ) {
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

		global $wp_roles;

		if ( ! $wp_roles instanceof WP_Roles ) {
			$wp_roles = new WP_Roles;
		}

		foreach ( $wp_roles->role_objects as $role ) {
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

		$this->assertContains( "manage{$parent}_{$screen}columnshidden", $this->un_installer->uninstall['single']['user_meta'] );
		$this->assertContains( "manage{$parent}_{$screen}columnshidden", $this->un_installer->uninstall['network']['user_meta'] );
		$this->assertContains( "manage{$parent}_{$screen}-networkcolumnshidden", $this->un_installer->uninstall['network']['user_meta'] );
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

		$this->assertContains( "{$parent}_{$screen}_{$option}", $this->un_installer->uninstall['single']['user_meta'] );
		$this->assertContains( "{$parent}_{$screen}_{$option}", $this->un_installer->uninstall['network']['user_meta'] );
		$this->assertContains( "{$parent}_{$screen}_network_{$option}", $this->un_installer->uninstall['network']['user_meta'] );
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
}

// EOF
