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
	public function setUp() {

		parent::setUp();

		$this->un_installer = new WordPoints_Un_Installer_Mock();
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

		$this->un_installer->map_uninstall_shortcuts();

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

		$this->un_installer->map_uninstall_shortcuts();

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

		$this->un_installer->map_uninstall_shortcuts();

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
	 * Test that the 'universal' uninstall shortcut is mapped.
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

		$this->assertNull( $wpdb->get_var( "SHOW TABLES LIKE `{$wpdb->base_prefix}test`" ) );
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

		$this->assertNull( $wpdb->get_var( "SHOW TABLES LIKE `{$wpdb->prefix}test`" ) );
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

		$this->assertNull( $wpdb->get_var( "SHOW TABLES LIKE `{$wpdb->base_prefix}test`" ) );
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
