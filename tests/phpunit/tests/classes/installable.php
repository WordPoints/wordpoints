<?php

/**
 * Test case for WordPoints_Installable.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Installable.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Installable
 */
class WordPoints_Installable_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests getting the database version of an entity.
	 *
	 * @since 2.4.0
	 */
	public function test_get_db_version() {

		$installable = $this->get_installable();

		$this->assertFalse( $installable->get_db_version() );

		$installable->set_db_version( '0.9.0' );

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );

		$this->assertSame(
			array( 'test' => array( 'version' => '0.9.0' ) )
			, $wordpoints_data['modules']
		);

		$this->assertSame( '0.9.0', $installable->get_db_version() );

		$installable->unset_db_version();

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );
		$this->assertArrayHasKey( 'test', $wordpoints_data['modules'] );
		$this->assertArrayNotHasKey( 'version', $wordpoints_data['modules']['test'] );

		$this->assertFalse( $installable->get_db_version() );

		$installable->set_db_version();

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );

		$this->assertSame(
			array( 'test' => array( 'version' => '1.0.0' ) )
			, $wordpoints_data['modules']
		);

		$this->assertSame( '1.0.0', $installable->get_db_version() );
	}

	/**
	 * Tests getting the network database version of an entity.
	 *
	 * @since 2.4.0
	 */
	public function test_get_db_version_network() {

		$installable = $this->get_installable();

		$this->assertFalse( $installable->get_db_version( true ) );

		$installable->set_db_version( '0.9.0', true );

		$wordpoints_data = get_site_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );

		$this->assertSame(
			array( 'test' => array( 'version' => '0.9.0' ) )
			, $wordpoints_data['modules']
		);

		$this->assertSame( '0.9.0', $installable->get_db_version( true ) );

		$installable->unset_db_version( true );

		$wordpoints_data = get_site_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );
		$this->assertArrayHasKey( 'test', $wordpoints_data['modules'] );
		$this->assertArrayNotHasKey( 'version', $wordpoints_data['modules']['test'] );

		$this->assertFalse( $installable->get_db_version( true ) );

		$installable->set_db_version( null, true );

		$wordpoints_data = get_site_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'modules', $wordpoints_data );

		$this->assertSame(
			array( 'test' => array( 'version' => '1.0.0' ) )
			, $wordpoints_data['modules']
		);

		$this->assertSame( '1.0.0', $installable->get_db_version( true ) );
	}

	/**
	 * Tests getting the database version of WordPoints.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPoints !network-active
	 */
	public function test_get_db_version_wordpoints() {

		$installable = $this->get_installable( 'plugin', 'wordpoints' );

		$this->assertSame( WORDPOINTS_VERSION, $installable->get_db_version() );

		$installable->set_db_version( '0.9.0' );

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'version', $wordpoints_data );

		$this->assertSame( '0.9.0', $wordpoints_data['version'] );

		$this->assertSame( '0.9.0', $installable->get_db_version() );

		$installable->set_db_version();

		$wordpoints_data = get_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'version', $wordpoints_data );

		$this->assertSame( '1.0.0', $wordpoints_data['version'] );

		$this->assertSame( '1.0.0', $installable->get_db_version() );
	}

	/**
	 * Tests getting the network database version of WordPoints.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_get_db_version_wordpoints_network() {

		$installable = $this->get_installable( 'plugin', 'wordpoints' );

		$this->assertSame( WORDPOINTS_VERSION, $installable->get_db_version( true ) );

		$installable->set_db_version( '0.9.0', true );

		$wordpoints_data = get_site_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'version', $wordpoints_data );

		$this->assertSame( '0.9.0', $wordpoints_data['version'] );

		$this->assertSame( '0.9.0', $installable->get_db_version( true ) );

		$installable->set_db_version( null, true );

		$wordpoints_data = get_site_option( 'wordpoints_data' );
		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'version', $wordpoints_data );

		$this->assertSame( '1.0.0', $wordpoints_data['version'] );

		$this->assertSame( '1.0.0', $installable->get_db_version( true ) );
	}

	/**
	 * Tests checking if the entity is network installed.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_is_network_installed() {

		$installable = $this->get_installable();

		$installable->set_network_installed();

		$network_installed = get_site_option( 'wordpoints_network_installed' );

		$this->assertInternalType( 'array', $network_installed );
		$this->assertArrayHasKey( 'module', $network_installed );
		$this->assertArrayHasKey( 'test', $network_installed['module'] );
		$this->assertTrue( $network_installed['module']['test'] );

		$this->assertTrue( $installable->is_network_installed() );

		$installable->unset_network_installed();

		$network_installed = get_site_option( 'wordpoints_network_installed' );

		$this->assertInternalType( 'array', $network_installed );
		$this->assertArrayHasKey( 'module', $network_installed );
		$this->assertArrayNotHasKey( 'test', $network_installed['module'] );

		$this->assertFalse( $installable->is_network_installed() );
	}

	/**
	 * Tests setting that the entity's network install was skipped.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_network_install_skipped() {

		$installable = $this->get_installable();

		$installable->set_network_install_skipped();

		$install_skipped = get_site_option( 'wordpoints_network_install_skipped' );

		$this->assertInternalType( 'array', $install_skipped );
		$this->assertArrayHasKey( 'module', $install_skipped );
		$this->assertArrayHasKey( 'test', $install_skipped['module'] );
		$this->assertTrue( $install_skipped['module']['test'] );

		$installable->unset_network_install_skipped();

		$install_skipped = get_site_option( 'wordpoints_network_install_skipped' );

		$this->assertInternalType( 'array', $install_skipped );
		$this->assertArrayHasKey( 'module', $install_skipped );
		$this->assertArrayNotHasKey( 'test', $install_skipped['module'] );
	}

	/**
	 * Tests setting that the entity's network update was skipped.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_network_update_skipped() {

		$installable = $this->get_installable();
		$installable->set_db_version( '0.9.0', true );

		$installable->set_network_update_skipped();

		$update_skipped = get_site_option( 'wordpoints_network_update_skipped' );

		$this->assertInternalType( 'array', $update_skipped );
		$this->assertArrayHasKey( 'module', $update_skipped );
		$this->assertArrayHasKey( 'test', $update_skipped['module'] );
		$this->assertSame( '0.9.0', $update_skipped['module']['test'] );

		$installable->unset_network_update_skipped();

		$update_skipped = get_site_option( 'wordpoints_network_update_skipped' );

		$this->assertInternalType( 'array', $update_skipped );
		$this->assertArrayHasKey( 'module', $update_skipped );
		$this->assertArrayNotHasKey( 'test', $update_skipped['module'] );
	}

	/**
	 * Tests that it returns all the site IDs.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_all_site_ids() {

		$ids = array( get_current_blog_id() );
		$ids[] = $this->factory->blog->create();

		// Create another blog on a different site.
		$this->factory->blog->create( array( 'site_id' => 45 ) );

		$installable = $this->get_installable();
		$installable->set_network_installed();

		$this->assertSame( $ids, $installable->get_installed_site_ids() );
	}

	/**
	 * Tests getting the IDs of the sites where the entity is installed.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_installed_site_ids() {

		$site_ids = array( $this->factory->blog->create() );

		update_site_option(
			'wordpoints_module_test_installed_sites'
			, $site_ids
		);

		$installable = $this->get_installable();

		$this->assertSame(
			$site_ids
			, $installable->get_installed_site_ids()
		);
	}

	/**
	 * Tests that it returns all site IDs if the entity is network-installed.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_installed_site_ids_network_wide() {

		$installable = $this->get_installable();
		$installable->set_network_installed();

		$site_id = $this->factory->blog->create();

		update_site_option(
			'wordpoints_module_test_installed_sites'
			, array( $site_id )
		);

		$this->assertSame(
			array( get_current_blog_id(), $site_id )
			, $installable->get_installed_site_ids()
		);
	}

	/**
	 * Tests getting the IDs of the sites where WordPoints is installed.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_installed_site_ids_wordpoints() {

		$installable = $this->get_installable( 'plugin', 'wordpoints' );
		$installable->unset_network_installed();

		$site_ids = array( $this->factory->blog->create() );

		update_site_option(
			'wordpoints_installed_sites'
			, $site_ids
		);

		$this->assertSame(
			$site_ids
			, $installable->get_installed_site_ids()
		);
	}

	/**
	 * Tests that it returns all site IDs if WordPoints is network-installed.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_installed_site_ids_network_wide_wordpoints() {

		$installable = $this->get_installable( 'plugin', 'wordpoints' );
		$installable->set_network_installed();

		$site_id = $this->factory->blog->create();

		update_site_option(
			'wordpoints_installed_sites'
			, array( $site_id )
		);

		$this->assertSame(
			array( get_current_blog_id(), $site_id )
			, $installable->get_installed_site_ids()
		);
	}

	/**
	 * Tests getting the IDs of the sites where a component is installed.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_installed_site_ids_component() {

		$installable = $this->get_installable( 'component', 'test' );

		$site_ids = array( $this->factory->blog->create() );

		update_site_option(
			'wordpoints_test_installed_sites'
			, $site_ids
		);

		$this->assertSame(
			$site_ids
			, $installable->get_installed_site_ids()
		);
	}

	/**
	 * Tests that it returns all site IDs if a component is network-installed.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_installed_site_ids_network_wide_component() {

		$installable = $this->get_installable( 'component', 'test' );
		$installable->set_network_installed();

		$site_id = $this->factory->blog->create();

		update_site_option(
			'wordpoints_test_installed_sites'
			, array( $site_id )
		);

		$this->assertSame(
			array( get_current_blog_id(), $site_id )
			, $installable->get_installed_site_ids()
		);
	}

	/**
	 * Tests validating a list of site IDs against the database.
	 *
	 * @since 2.4.0
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

		update_site_option( 'wordpoints_module_test_installed_sites', $site_ids );

		// Create a site not on the list.
		$this->factory->blog->create();

		$installable = $this->get_installable();

		$this->assertSame(
			array( get_current_blog_id(), $site_id )
			, $installable->get_installed_site_ids()
		);
	}

	/**
	 * Tests validate_site_ids() when the array is empty.
	 *
	 * @since 2.4.0
	 */
	public function test_validate_site_ids_empty() {

		update_site_option( 'wordpoints_module_test_installed_sites', array() );

		$installable = $this->get_installable();

		$this->assertSame(
			array()
			, $installable->get_installed_site_ids()
		);
	}

	/**
	 * Tests validate_site_ids() when the value is not an array.
	 *
	 * @since 2.4.0
	 */
	public function test_validate_site_ids_not_array() {

		update_site_option( 'wordpoints_module_test_installed_sites', 'invalid' );

		$installable = $this->get_installable();

		$this->assertSame(
			array()
			, $installable->get_installed_site_ids()
		);
	}

	/**
	 * Tests adding a site to the list of sites where the entity is installed.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_add_installed_site_id() {

		$site_id = $this->factory->blog->create();

		update_site_option(
			'wordpoints_module_test_installed_sites'
			, array( get_current_blog_id() )
		);

		$installable = $this->get_installable();

		$installable->add_installed_site_id( $site_id );

		$this->assertSame(
			array( get_current_blog_id(), $site_id )
			, $installable->get_installed_site_ids()
		);
	}

	/**
	 * Tests that the current site ID is used if none is supplied.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_add_installed_site_id_default() {

		$installable = $this->get_installable();

		$installable->add_installed_site_id();

		$this->assertSame(
			array( get_current_blog_id() )
			, $installable->get_installed_site_ids()
		);
	}

	/**
	 * Tests adding to the list of sites where WordPoints is installed.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_add_installed_site_id_wordpoints() {

		$installable = $this->get_installable( 'plugin', 'wordpoints' );
		$installable->unset_network_installed();

		$site_id = $this->factory->blog->create();

		update_site_option(
			'wordpoints_installed_sites'
			, array( get_current_blog_id() )
		);

		$installable->add_installed_site_id( $site_id );

		$this->assertSame(
			array( get_current_blog_id(), $site_id )
			, $installable->get_installed_site_ids()
		);
	}

	/**
	 * Tests adding to the list of sites where a component is installed.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_add_installed_site_id_component() {

		$installable = $this->get_installable( 'component', 'test' );

		$site_id = $this->factory->blog->create();

		update_site_option(
			'wordpoints_test_installed_sites'
			, array( get_current_blog_id() )
		);

		$installable->add_installed_site_id( $site_id );

		$this->assertSame(
			array( get_current_blog_id(), $site_id )
			, $installable->get_installed_site_ids()
		);
	}

	/**
	 * Tests deleting the list of sites where the entity is installed.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_delete_installed_site_ids() {

		update_site_option(
			'wordpoints_module_test_installed_sites'
			, array( get_current_blog_id() )
		);

		$installable = $this->get_installable();

		$installable->delete_installed_site_ids();

		$this->assertSame( array(), $installable->get_installed_site_ids() );
	}

	/**
	 * Tests deleting the list of the sites where WordPoints is installed.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_delete_installed_site_ids_wordpoints() {

		$installable = $this->get_installable( 'plugin', 'wordpoints' );
		$installable->unset_network_installed();

		update_site_option(
			'wordpoints_installed_sites'
			, array( get_current_blog_id() )
		);

		$installable->delete_installed_site_ids();

		$this->assertSame( array(), $installable->get_installed_site_ids() );
	}

	/**
	 * Tests deleting the list of the sites where a component is installed.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_delete_installed_site_ids_component() {

		$installable = $this->get_installable( 'component', 'test' );

		update_site_option(
			'wordpoints_test_installed_sites'
			, array( get_current_blog_id() )
		);

		$installable->delete_installed_site_ids();

		$this->assertSame( array(), $installable->get_installed_site_ids() );
	}

	/**
	 * Tests getting the install routines.
	 *
	 * @since 2.4.0
	 */
	public function test_get_install_routines() {

		$db_tables_routine = $this->createMock( 'WordPoints_RoutineI' );
		$caps_routine = $this->createMock( 'WordPoints_RoutineI' );

		$installable = $this->getPartialMockForAbstractClass(
			'WordPoints_Installable'
			, array(
				'get_db_tables_install_routines',
				'get_custom_caps_install_routines',
			)
		);

		$installable->method( 'get_db_tables_install_routines' )
			->willReturn( array( 'single' => array( $db_tables_routine ) ) );

		$installable->method( 'get_custom_caps_install_routines' )
			->willReturn( array( 'single' => array( $caps_routine ) ) );

		$this->assertSame(
			array( 'single' => array( $db_tables_routine, $caps_routine ) )
			, $installable->get_install_routines()
		);
	}

	/**
	 * Tests getting the install routines when there are none.
	 *
	 * @since 2.4.0
	 */
	public function test_get_install_routines_none() {

		$installable = $this->get_installable( 'component', 'test' );

		$this->assertSame( array(), $installable->get_install_routines() );
	}

	/**
	 * Tests getting the install routines for database tables.
	 *
	 * @since 2.4.0
	 *
	 * @dataProvider data_provider_context_shortcuts
	 *
	 * @param string   $shortcut A context shortcut.
	 * @param string[] $contexts The contexts that the shortcut maps to.
	 */
	public function test_get_db_tables_install_routines( $shortcut, $contexts = null ) {

		if ( ! isset( $contexts ) ) {
			$contexts = array( $shortcut );
		}

		$installable = $this->getPartialMockForAbstractClass(
			'WordPoints_Installable'
			, array( 'get_db_tables' )
		);

		$tables = array();
		$tables[ $shortcut ] = array( 'test' => '' );

		$installable->method( 'get_db_tables' )->willReturn( $tables );

		$routines = $installable->get_install_routines();

		foreach ( $contexts as $context ) {

			$this->assertArrayHasKey( $context, $routines );
			$this->assertCount( 1, $routines[ $context ] );
			$this->assertInstanceOf(
				'WordPoints_Installer_DB_Tables'
				, $routines[ $context ][0]
			);
		}
	}

	/**
	 * Provides a list of shortcuts and the contexts that they map to.
	 *
	 * @since 2.4.0
	 *
	 * @return array[] Shortcuts and the contexts that they are for.
	 */
	public function data_provider_context_shortcuts() {
		return array(
			'single' => array( 'single' ),
			'site' => array( 'site' ),
			'network' => array( 'network' ),
			'local' => array( 'local', array( 'single', 'site' ) ),
			'global' => array( 'global', array( 'single', 'network' ) ),
			'universal' => array( 'universal', array( 'single', 'site', 'network' ) ),
		);
	}

	/**
	 * Tests getting the install routines for custom caps.
	 *
	 * @since 2.4.0
	 */
	public function test_get_custom_caps_install_routines() {

		$installable = $this->getPartialMockForAbstractClass(
			'WordPoints_Installable'
			, array( 'get_custom_caps' )
		);

		$custom_caps = array( 'some_cap' => 'manage_options' );

		$installable->method( 'get_custom_caps' )->willReturn( $custom_caps );

		$routines = $installable->get_install_routines();

		$this->assertArrayHasKey( 'single', $routines );
		$this->assertCount( 1, $routines['single'] );
		$this->assertInstanceOf(
			'WordPoints_Installer_Caps'
			, $routines['single'][0]
		);

		$this->assertArrayHasKey( 'site', $routines );
		$this->assertCount( 1, $routines['site'] );
		$this->assertInstanceOf(
			'WordPoints_Installer_Caps'
			, $routines['site'][0]
		);
	}

	/**
	 * Tests getting the update routine factories.
	 *
	 * @since 2.4.0
	 */
	public function test_get_update_routine_factories() {

		$installable = $this->get_installable();

		$this->assertSame( array(), $installable->get_update_routine_factories() );
	}

	/**
	 * Tests getting the uninstall routines.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_get_uninstall_routines() {

		$single = $this->createMock( 'WordPoints_RoutineI' );
		$network = $this->createMock( 'WordPoints_RoutineI' );
		$site = $this->createMock( 'WordPoints_RoutineI' );

		$single_factory = $this->createMock( 'WordPoints_Uninstaller_Factory_SingleI' );
		$single_factory->expects( $this->once() )
			->method( 'get_for_single' )
			->willReturn( array( $single ) );

		$network_factory = $this->createMock( 'WordPoints_Uninstaller_Factory_NetworkI' );
		$network_factory->expects( $this->never() )
			->method( 'get_for_network' )
			->willReturn( array( $network ) );

		$site_factory = $this->createMock( 'WordPoints_Uninstaller_Factory_SiteI' );
		$site_factory->expects( $this->never() )
			->method( 'get_for_site' )
			->willReturn( array( $site ) );

		$installable = $this->getPartialMockForAbstractClass(
			'WordPoints_Installable'
			, array( 'get_uninstall_routine_factories' )
		);

		$installable->method( 'get_uninstall_routine_factories' )
			->willReturn( array( $single_factory, $network_factory, $site_factory ) );

		$this->assertSame(
			array(
				'single' => array( $single ),
				'site' => array(),
				'network' => array(),
			)
			, $installable->get_uninstall_routines()
		);
	}

	/**
	 * Tests getting the uninstall routines when there are none.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_get_uninstall_routines_none() {

		$installable = $this->get_installable();

		$this->assertSame(
			array(
				'single' => array(),
				'site' => array(),
				'network' => array(),
			)
			, $installable->get_uninstall_routines()
		);
	}

	/**
	 * Tests getting the uninstall routines.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_get_uninstall_routines_multiple() {

		$routine = $this->createMock( 'WordPoints_RoutineI' );
		$routine_2 = $this->createMock( 'WordPoints_RoutineI' );

		$factory = $this->createMock( 'WordPoints_Uninstaller_Factory_SingleI' );
		$factory->method( 'get_for_single' )->willReturn( array( $routine ) );

		$factory_2 = $this->createMock( 'WordPoints_Uninstaller_Factory_SingleI' );
		$factory_2->method( 'get_for_single' )->willReturn( array( $routine_2 ) );

		$installable = $this->getPartialMockForAbstractClass(
			'WordPoints_Installable'
			, array( 'get_uninstall_routine_factories' )
		);

		$installable->method( 'get_uninstall_routine_factories' )
			->willReturn( array( $factory, $factory_2 ) );

		$this->assertSame(
			array(
				'single' => array( $routine, $routine_2 ),
				'site' => array(),
				'network' => array(),
			)
			, $installable->get_uninstall_routines()
		);
	}

	/**
	 * Tests getting the uninstall routines on multisite.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_uninstall_routines_multisite() {

		$single = $this->createMock( 'WordPoints_RoutineI' );
		$network = $this->createMock( 'WordPoints_RoutineI' );
		$site = $this->createMock( 'WordPoints_RoutineI' );

		$single_factory = $this->createMock( 'WordPoints_Uninstaller_Factory_SingleI' );
		$single_factory->expects( $this->never() )
			->method( 'get_for_single' )
			->willReturn( array( $single ) );

		$network_factory = $this->createMock( 'WordPoints_Uninstaller_Factory_NetworkI' );
		$network_factory->expects( $this->once() )
			->method( 'get_for_network' )
			->willReturn( array( $network ) );

		$site_factory = $this->createMock( 'WordPoints_Uninstaller_Factory_SiteI' );
		$site_factory->expects( $this->once() )
			->method( 'get_for_site' )
			->willReturn( array( $site ) );

		$installable = $this->getPartialMockForAbstractClass(
			'WordPoints_Installable'
			, array( 'get_uninstall_routine_factories' )
		);

		$installable->method( 'get_uninstall_routine_factories' )
			->willReturn( array( $single_factory, $network_factory, $site_factory ) );

		$this->assertSame(
			array(
				'single' => array(),
				'site' => array( $site ),
				'network' => array( $network ),
			)
			, $installable->get_uninstall_routines()
		);
	}

	/**
	 * Tests getting the uninstall routines for database tables.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_get_uninstall_routines_db_tables() {

		$tables = array( 'local' => array( 'test' => '' ) );

		$installable = $this->getPartialMockForAbstractClass(
			'WordPoints_Installable'
			, array( 'get_db_tables' )
		);

		$installable->method( 'get_db_tables' )->willReturn( $tables );

		$uninstall_routines = $installable->get_uninstall_routines();

		$this->assertCount( 1, $uninstall_routines['single'] );
	}

	/**
	 * Tests getting the uninstall routines for custom caps.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_get_uninstall_routines_caps() {

		$installable = $this->getPartialMockForAbstractClass(
			'WordPoints_Installable'
			, array( 'get_custom_caps' )
		);

		$custom_caps = array( 'some_cap' => 'manage_options' );

		$installable->method( 'get_custom_caps' )->willReturn( $custom_caps );

		$uninstall_routines = $installable->get_uninstall_routines();

		$this->assertCount( 1, $uninstall_routines['single'] );
	}

	/**
	 * Gets the installable object to test.
	 *
	 * @since 2.4.0
	 *
	 * @param string $type The type of installable.
	 * @param string $slug The slug of the installable.
	 *
	 * @return WordPoints_Installable The installable object.
	 */
	protected function get_installable( $type = 'module', $slug = 'test' ) {

		$installable = new WordPoints_Installable_Basic( $type, $slug, '1.0.0' );

		return $installable;
	}
}

// EOF
