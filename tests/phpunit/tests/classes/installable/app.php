<?php

/**
 * Test case for WordPoints_Installables_App.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Installables_App.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Installables_App
 */
class WordPoints_Installables_App_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests that the maybe_update() method runs the update if needed.
	 *
	 * @since 2.4.0
	 */
	public function test_maybe_update_runs_update_if_version_not_set() {

		delete_option( 'wordpoints_installable_versions' );

		$type    = 'type';
		$slug    = 'slug';
		$version = '1.0.0';

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_db_version' )->willReturn( '0.9.0' );
		$installable->expects( $this->once() )
			->method( 'get_update_routine_factories' )
			->willReturn( array() );

		$loader = array( new WordPoints_PHPUnit_Mock_Filter( $installable ), 'filter' );

		$installables = new WordPoints_Installables_App();
		$installables->register( $type, $slug, $loader, $version );

		$installables->maybe_update();

		$data = get_option( 'wordpoints_installable_versions' );

		$this->assertSame( array( $type => array( $slug => $version ) ), $data );
	}

	/**
	 * Tests that the maybe_update() method runs install of WordPoints if needed.
	 *
	 * @since 2.4.0
	 */
	public function test_maybe_update_runs_install_if_db_version_not_set_wordpoints() {

		delete_option( 'wordpoints_installable_versions' );

		$type    = 'plugin';
		$slug    = 'wordpoints';
		$version = '1.0.0';

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->expects( $this->never() )->method( 'get_update_routine_factories' );
		$installable->expects( $this->once() )
			->method( 'get_install_routines' )
			->willReturn( array() );

		$loader = array( new WordPoints_PHPUnit_Mock_Filter( $installable ), 'filter' );

		$installables = new WordPoints_Installables_App();
		$installables->register( $type, $slug, $loader, $version );

		$installables->maybe_update();

		$data = get_option( 'wordpoints_installable_versions' );

		$this->assertSame( array( $type => array( $slug => $version ) ), $data );
	}

	/**
	 * Tests that the maybe_update() method doesn't runs install for other entities.
	 *
	 * @since 2.4.0
	 */
	public function test_maybe_update_not_runs_install_if_db_version_not_set() {

		delete_option( 'wordpoints_installable_versions' );

		$type    = 'type';
		$slug    = 'slug';
		$version = '1.0.0';

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_installed_site_ids' )->willReturn( array() );
		$installable->expects( $this->never() )->method( 'get_install_routines' );
		$installable->expects( $this->once() )->method( 'get_update_routine_factories' )
			->willReturn( array() );

		$loader = array( new WordPoints_PHPUnit_Mock_Filter( $installable ), 'filter' );

		$installables = new WordPoints_Installables_App();
		$installables->register( $type, $slug, $loader, $version );

		$installables->maybe_update();

		$data = get_option( 'wordpoints_installable_versions' );

		$this->assertSame( array( $type => array( $slug => $version ) ), $data );
	}

	/**
	 * Tests that the maybe_update() method runs the update if needed.
	 *
	 * @since 2.4.0
	 */
	public function test_maybe_update_runs_update_if_version_changed() {

		$type    = 'type';
		$slug    = 'slug';
		$version = '1.0.0';

		update_option(
			'wordpoints_installable_versions'
			, array( $type => array( $slug => '0.9.0' ) )
		);

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_db_version' )->willReturn( '0.9.0' );
		$installable->expects( $this->once() )
			->method( 'get_update_routine_factories' )
			->willReturn( array() );

		$loader = array( new WordPoints_PHPUnit_Mock_Filter( $installable ), 'filter' );

		$installables = new WordPoints_Installables_App();
		$installables->register( $type, $slug, $loader, $version );

		$installables->maybe_update();

		$data = get_option( 'wordpoints_installable_versions' );

		$this->assertSame( array( $type => array( $slug => $version ) ), $data );
	}

	/**
	 * Tests that the maybe_update() method doesn't run the update if not needed.
	 *
	 * @since 2.4.0
	 */
	public function test_maybe_update_not_runs_update_if_version_same() {

		$type    = 'type';
		$slug    = 'slug';
		$version = '1.0.0';

		update_option(
			'wordpoints_installable_versions'
			, array( $type => array( $slug => $version ) )
		);

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_db_version' )->willReturn( '0.9.0' );
		$installable->expects( $this->never() )
			->method( 'get_update_routine_factories' )
			->willReturn( array() );

		$loader = array( new WordPoints_PHPUnit_Mock_Filter( $installable ), 'filter' );

		$installables = new WordPoints_Installables_App();
		$installables->register( $type, $slug, $loader, $version );

		$installables->maybe_update();

		$data = get_option( 'wordpoints_installable_versions' );

		$this->assertSame( array( $type => array( $slug => $version ) ), $data );
	}

	/**
	 * Tests that the maybe_update() method doesn't run the update if not needed.
	 *
	 * @since 2.4.0
	 */
	public function test_maybe_update_not_runs_update_if_db_version_newer() {

		$type    = 'type';
		$slug    = 'slug';
		$version = '1.0.0';

		update_option(
			'wordpoints_installable_versions'
			, array( $type => array( $slug => '0.9.0' ) )
		);

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_db_version' )->willReturn( '1.1.0' );
		$installable->expects( $this->never() )
			->method( 'get_update_routine_factories' )
			->willReturn( array() );

		$loader = array( new WordPoints_PHPUnit_Mock_Filter( $installable ), 'filter' );

		$installables = new WordPoints_Installables_App();
		$installables->register( $type, $slug, $loader, $version );

		$installables->maybe_update();

		$data = get_option( 'wordpoints_installable_versions' );

		$this->assertSame( array( $type => array( $slug => $version ) ), $data );
	}

	/**
	 * Tests that maybe_update() only runs the update for entities that changed.
	 *
	 * @since 2.4.0
	 */
	public function test_maybe_update_runs_update_only_for_changed() {

		$type    = 'type';
		$slug    = 'slug';
		$version = '1.0.0';

		update_option(
			'wordpoints_installable_versions'
			, array( $type => array( $slug => $version, 'other' => '0.9.0' ) )
		);

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_db_version' )->willReturn( '0.9.0' );
		$installable->expects( $this->once() )
			->method( 'get_update_routine_factories' )
			->willReturn( array() );

		$loader = new WordPoints_PHPUnit_Mock_Filter( $installable );

		$installables = new WordPoints_Installables_App();
		$installables->register( $type, $slug, array( $loader, 'filter' ), $version );
		$installables->register( $type, 'other', array( $loader, 'filter' ), $version );

		$installables->maybe_update();

		$this->assertSame( array( array( $type, 'other' ) ), $loader->calls );

		$data = get_option( 'wordpoints_installable_versions' );

		$this->assertSame(
			array( $type => array( $slug => $version, 'other' => '1.0.0' ) )
			, $data
		);
	}

	/**
	 * Tests that maybe_update() only runs the update for entities that changed.
	 *
	 * @since 2.4.0
	 */
	public function test_maybe_update_runs_update_only_for_added() {

		$type    = 'type';
		$slug    = 'slug';
		$version = '1.0.0';

		update_option(
			'wordpoints_installable_versions'
			, array( $type => array( $slug => $version ) )
		);

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_db_version' )->willReturn( '0.9.0' );
		$installable->expects( $this->once() )
			->method( 'get_update_routine_factories' )
			->willReturn( array() );

		$loader = new WordPoints_PHPUnit_Mock_Filter( $installable );

		$installables = new WordPoints_Installables_App();
		$installables->register( $type, $slug, array( $loader, 'filter' ), $version );
		$installables->register( $type, 'other', array( $loader, 'filter' ), $version );

		$installables->maybe_update();

		$this->assertSame( array( array( $type, 'other' ) ), $loader->calls );

		$data = get_option( 'wordpoints_installable_versions' );

		$this->assertSame(
			array( $type => array( $slug => $version, 'other' => '1.0.0' ) )
			, $data
		);
	}

	/**
	 * Tests that maybe_update() ignores an entity that isn't registered.
	 *
	 * @since 2.4.0
	 */
	public function test_maybe_update_ignores_unregistered() {

		$type    = 'type';
		$slug    = 'slug';
		$version = '1.0.0';

		update_option(
			'wordpoints_installable_versions'
			, array( $type => array( $slug => $version, 'other' => '1.0.0' ) )
		);

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->expects( $this->never() )
			->method( 'get_update_routine_factories' )
			->willReturn( array() );

		$loader = new WordPoints_PHPUnit_Mock_Filter( $installable );

		$installables = new WordPoints_Installables_App();
		$installables->register( $type, $slug, array( $loader, 'filter' ), $version );

		$installables->maybe_update();

		$this->assertSame( array(), $loader->calls );

		$data = get_option( 'wordpoints_installable_versions' );

		$this->assertSame(
			array( $type => array( $slug => $version ) )
			, $data
		);
	}

	/**
	 * Tests that the maybe_update() method runs the update if needed.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_maybe_update_runs_update_if_version_not_set_network() {

		delete_site_option( 'wordpoints_installable_versions' );

		$type    = 'type';
		$slug    = 'slug';
		$version = '1.0.0';

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->expects( $this->atLeastOnce() )
			->method( 'get_db_version' )
			->with( true )
			->willReturn( '0.9.0' );

		$installable->expects( $this->once() )
			->method( 'get_update_routine_factories' )
			->willReturn( array() );

		$loader = array( new WordPoints_PHPUnit_Mock_Filter( $installable ), 'filter' );

		$installables = new WordPoints_Installables_App();
		$installables->register( $type, $slug, $loader, $version, true );

		$installables->maybe_update();

		$data = get_site_option( 'wordpoints_installable_versions' );

		$this->assertSame( array( $type => array( $slug => $version ) ), $data );
	}

	/**
	 * Tests that the maybe_update() method runs the update if needed.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_maybe_update_runs_update_if_version_changed_network() {

		$type    = 'type';
		$slug    = 'slug';
		$version = '1.0.0';

		update_site_option(
			'wordpoints_installable_versions'
			, array( $type => array( $slug => '0.9.0' ) )
		);

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->expects( $this->atLeastOnce() )
			->method( 'get_db_version' )
			->with( true )
			->willReturn( '0.9.0' );

		$installable->expects( $this->once() )
			->method( 'get_update_routine_factories' )
			->willReturn( array() );

		$loader = array( new WordPoints_PHPUnit_Mock_Filter( $installable ), 'filter' );

		$installables = new WordPoints_Installables_App();
		$installables->register( $type, $slug, $loader, $version, true );

		$installables->maybe_update();

		$data = get_site_option( 'wordpoints_installable_versions' );

		$this->assertSame( array( $type => array( $slug => $version ) ), $data );
	}

	/**
	 * Tests that the maybe_update() method doesn't run the update if not needed.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPoints !network-active
	 */
	public function test_maybe_update_not_runs_network_if_not_network_active() {

		delete_site_option( 'wordpoints_installable_versions' );

		$type    = 'type';
		$slug    = 'slug';
		$version = '1.0.0';

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->expects( $this->never() )
			->method( 'get_update_routine_factories' )
			->willReturn( array() );

		$loader = array( new WordPoints_PHPUnit_Mock_Filter( $installable ), 'filter' );

		$installables = new WordPoints_Installables_App();
		$installables->register( $type, $slug, $loader, $version, true );

		$installables->maybe_update();

		$this->assertFalse( get_site_option( 'wordpoints_installable_versions' ) );
	}

	/**
	 * Tests that the install_on_new_site() method runs the installer on the site.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_install_on_new_site() {

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->expects( $this->once() )
			->method( 'get_install_routines' )
			->willReturn( array() );

		$loader = array( new WordPoints_PHPUnit_Mock_Filter( $installable ), 'filter' );

		$installables = new WordPoints_Installables_App();
		$installables->register( 'type', 'slug', $loader, '1.0.0', true );

		$installables->install_on_new_site( get_current_blog_id() );
	}

	/**
	 * Tests that the install_on_new_site() method runs the installer on the site.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_install_on_new_site_not_network() {

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->expects( $this->never() )
			->method( 'get_install_routines' )
			->willReturn( array() );

		$loader = array( new WordPoints_PHPUnit_Mock_Filter( $installable ), 'filter' );

		$installables = new WordPoints_Installables_App();
		$installables->register( 'type', 'slug', $loader, '1.0.0' );

		$installables->install_on_new_site( get_current_blog_id() );
	}
}

// EOF
