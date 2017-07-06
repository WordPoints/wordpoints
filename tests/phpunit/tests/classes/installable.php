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
	 * Tests that the slug and version are set on construction.
	 *
	 * @since 2.4.0
	 */
	public function test_construct_slug_version_set() {

		$installable = new WordPoints_Installable( 'module', 'test', '1.0.0' );

		$this->assertSame( 'test', $installable->get_slug() );
		$this->assertSame( '1.0.0', $installable->get_version() );
	}

	/**
	 * Tests getting the database version of an entity.
	 *
	 * @since 2.4.0
	 */
	public function test_get_db_version() {

		$installable = new WordPoints_Installable( 'module', 'test', '1.0.0' );

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

		$installable = new WordPoints_Installable( 'module', 'test', '1.0.0' );

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

		$installable = new WordPoints_Installable( 'plugin', 'wordpoints', '1.0.0' );

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

		$installable = new WordPoints_Installable( 'plugin', 'wordpoints', '1.0.0' );

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

		$installable = new WordPoints_Installable( 'module', 'test', '1.0.0' );

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

		$installable = new WordPoints_Installable( 'module', 'test', '1.0.0' );

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

		$installable = new WordPoints_Installable( 'module', 'test', '1.0.0' );
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

		$installable = new WordPoints_Installable( 'module', 'test', '1.0.0' );
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

		$installable = new WordPoints_Installable( 'module', 'test', '1.0.0' );

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

		$installable = new WordPoints_Installable( 'module', 'test', '1.0.0' );
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

		$installable = new WordPoints_Installable( 'plugin', 'wordpoints', '1.0.0' );
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

		$installable = new WordPoints_Installable( 'plugin', 'wordpoints', '1.0.0' );
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

		$installable = new WordPoints_Installable( 'component', 'test', '1.0.0' );

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

		$installable = new WordPoints_Installable( 'component', 'test', '1.0.0' );
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

		$installable = new WordPoints_Installable( 'module', 'test', '1.0.0' );

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

		$installable = new WordPoints_Installable( 'module', 'test', '1.0.0' );

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

		$installable = new WordPoints_Installable( 'module', 'test', '1.0.0' );

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

		$installable = new WordPoints_Installable( 'module', 'test', '1.0.0' );

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

		$installable = new WordPoints_Installable( 'module', 'test', '1.0.0' );

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

		$installable = new WordPoints_Installable( 'plugin', 'wordpoints', '1.0.0' );
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

		$installable = new WordPoints_Installable( 'component', 'test', '1.0.0' );

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

		$installable = new WordPoints_Installable( 'module', 'test', '1.0.0' );

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

		$installable = new WordPoints_Installable( 'plugin', 'wordpoints', '1.0.0' );
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

		$installable = new WordPoints_Installable( 'component', 'test', '1.0.0' );

		update_site_option(
			'wordpoints_test_installed_sites'
			, array( get_current_blog_id() )
		);

		$installable->delete_installed_site_ids();

		$this->assertSame( array(), $installable->get_installed_site_ids() );
	}
}

// EOF
