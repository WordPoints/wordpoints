<?php

/**
 * Test case for WordPoints_Updater.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Updater.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Updater
 */
class WordPoints_Updater_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test the basic behaviour not on multisite.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_run_not_multisite() {

		$routine = $this->createMock( 'WordPoints_RoutineI' );
		$routine->expects( $this->once() )->method( 'run' );

		$factory = $this->createMock( 'WordPoints_Updater_FactoryI' );
		$factory->method( 'get_for_single' )->willReturn( array( $routine ) );
		$factory->method( 'get_version' )->willReturn( '1.1.0' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_db_version' )->willReturn( '1.0.0' );
		$installable->method( 'get_update_routine_factories' )
			->willReturn( array( $factory ) );

		$installable->expects( $this->once() )
			->method( 'set_db_version' )
			->with( null, false );

		$updater = new WordPoints_Updater( $installable, false );
		$updater->run();
	}

	/**
	 * Test getting the routines when the factory version is the same as the DB.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_get_update_routines_factory_version_less() {

		$routine = $this->createMock( 'WordPoints_RoutineI' );
		$routine->expects( $this->never() )->method( 'run' );

		$factory = $this->createMock( 'WordPoints_Updater_FactoryI' );
		$factory->method( 'get_for_single' )->willReturn( array( $routine ) );
		$factory->method( 'get_version' )->willReturn( '0.9.0' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_db_version' )->willReturn( '1.0.0' );
		$installable->method( 'get_update_routine_factories' )
			->willReturn( array( $factory ) );

		$installable->expects( $this->once() )
			->method( 'set_db_version' )
			->with( null, false );

		$updater = new WordPoints_Updater( $installable, false );
		$updater->run();
	}

	/**
	 * Test getting the routines when the factory version is the same as the DB.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_get_update_routines_factory_version_same() {

		$routine = $this->createMock( 'WordPoints_RoutineI' );
		$routine->expects( $this->never() )->method( 'run' );

		$factory = $this->createMock( 'WordPoints_Updater_FactoryI' );
		$factory->method( 'get_for_single' )->willReturn( array( $routine ) );
		$factory->method( 'get_version' )->willReturn( '1.0.0' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_db_version' )->willReturn( '1.0.0' );
		$installable->method( 'get_update_routine_factories' )
			->willReturn( array( $factory ) );

		$installable->expects( $this->once() )
			->method( 'set_db_version' )
			->with( null, false );

		$updater = new WordPoints_Updater( $installable, false );
		$updater->run();
	}

	/**
	 * Test the basic behaviour on multisite.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_multisite() {

		$site = $this->createMock( 'WordPoints_RoutineI' );
		$site->expects( $this->once() )->method( 'run' );

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->once() )->method( 'run' );

		$factory = $this->createMock( 'WordPoints_Updater_FactoryI' );
		$factory->method( 'get_for_site' )->willReturn( array( $site ) );
		$factory->method( 'get_for_network' )->willReturn( array( $network ) );
		$factory->method( 'get_version' )->willReturn( '1.1.0' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_db_version' )->willReturn( '1.0.0' );
		$installable->method( 'get_update_routine_factories' )
			->willReturn( array( $factory ) );

		$installable->expects( $this->once() )->method( 'add_installed_site_id' );
		$installable->expects( $this->exactly( 2 ) )
			->method( 'set_db_version' )
			->withConsecutive( array( null, true ), array( null, false ) );

		$updater = new WordPoints_Updater( $installable, false );
		$updater->run();
	}

	/**
	 * Test that the network version is used for the network routines.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_multisite_network_independent() {

		$site = $this->createMock( 'WordPoints_RoutineI' );
		$site->expects( $this->once() )->method( 'run' );

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->never() )->method( 'run' );

		$factory = $this->createMock( 'WordPoints_Updater_FactoryI' );
		$factory->method( 'get_for_site' )->willReturn( array( $site ) );
		$factory->method( 'get_for_network' )->willReturn( array( $network ) );
		$factory->method( 'get_version' )->willReturn( '1.1.0' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_update_routine_factories' )
			->willReturn( array( $factory ) );

		$installable->method( 'get_db_version' )->willReturnMap(
			array( array( false, '1.0.0' ), array( true, '1.1.0' ) )
		);

		$installable->expects( $this->once() )->method( 'add_installed_site_id' );
		$installable->expects( $this->exactly( 2 ) )
			->method( 'set_db_version' )
			->withConsecutive( array( null, true ), array( null, false ) );

		$updater = new WordPoints_Updater( $installable, false );
		$updater->run();
	}

	/**
	 * Test behavior when the network version is not set.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_multisite_network_not_set() {

		$site = $this->createMock( 'WordPoints_RoutineI' );
		$site->expects( $this->once() )->method( 'run' );

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->once() )->method( 'run' );

		$factory = $this->createMock( 'WordPoints_Updater_FactoryI' );
		$factory->method( 'get_for_site' )->willReturn( array( $site ) );
		$factory->method( 'get_for_network' )->willReturn( array( $network ) );
		$factory->method( 'get_version' )->willReturn( '1.1.0' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_update_routine_factories' )
			->willReturn( array( $factory ) );

		$installable->method( 'get_db_version' )->willReturnMap(
			array( array( false, '1.0.0' ), array( true, false ) )
		);

		$installable->method( 'get_installed_site_ids' )
			->willReturn( array( get_current_blog_id() ) );

		$installable->expects( $this->once() )->method( 'add_installed_site_id' );
		$installable->expects( $this->exactly( 2 ) )
			->method( 'set_db_version' )
			->withConsecutive( array( null, true ), array( null, false ) );

		$updater = new WordPoints_Updater( $installable, false );
		$updater->run();
	}

	/**
	 * Test behavior when the network version is not set.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_multisite_network_not_set_site_version_updated() {

		$site_id = $this->factory->blog->create();

		$site = $this->createMock( 'WordPoints_RoutineI' );
		$site->expects( $this->once() )->method( 'run' );

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->once() )->method( 'run' );

		$factory = $this->createMock( 'WordPoints_Updater_FactoryI' );
		$factory->method( 'get_for_site' )->willReturn( array( $site ) );
		$factory->method( 'get_for_network' )->willReturn( array( $network ) );
		$factory->method( 'get_version' )->willReturn( '1.1.0' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_update_routine_factories' )
			->willReturn( array( $factory ) );

		$installable->method( 'get_db_version' )
			->willReturnOnConsecutiveCalls( '1.0.0', false, '1.0.0', '0.9.0' );

		$installable->method( 'get_installed_site_ids' )
			->willReturn( array( get_current_blog_id(), $site_id ) );

		$installable->expects( $this->once() )->method( 'add_installed_site_id' );
		$installable->expects( $this->exactly( 3 ) )
			->method( 'set_db_version' )
			->withConsecutive(
				array( '1.0.0', false )
				, array( null, true )
				, array( null, false )
			);

		$updater = new WordPoints_Updater( $installable, false );
		$updater->run();
	}

	/**
	 * Tests when the network version is not set and the site version is up-to-date.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_multisite_network_not_set_site_same() {

		$site = $this->createMock( 'WordPoints_RoutineI' );
		$site->expects( $this->never() )->method( 'run' );

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->never() )->method( 'run' );

		$factory = $this->createMock( 'WordPoints_Updater_FactoryI' );
		$factory->method( 'get_for_site' )->willReturn( array( $site ) );
		$factory->method( 'get_for_network' )->willReturn( array( $network ) );
		$factory->method( 'get_version' )->willReturn( '1.1.0' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_update_routine_factories' )
			->willReturn( array( $factory ) );

		$installable->method( 'get_db_version' )->willReturnMap(
			array( array( false, '1.1.0' ), array( true, false ) )
		);

		$installable->method( 'get_installed_site_ids' )
			->willReturn( array( get_current_blog_id() ) );

		$installable->expects( $this->once() )->method( 'add_installed_site_id' );
		$installable->expects( $this->exactly( 2 ) )
			->method( 'set_db_version' )
			->withConsecutive( array( null, true ), array( null, false ) );

		$updater = new WordPoints_Updater( $installable, false );
		$updater->run();
	}

	/**
	 * Test the basic behaviour when updating network wide.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_network_wide() {

		$site = $this->createMock( 'WordPoints_RoutineI' );
		$site->expects( $this->once() )->method( 'run' );

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->once() )->method( 'run' );

		$factory = $this->createMock( 'WordPoints_Updater_FactoryI' );
		$factory->method( 'get_for_site' )->willReturn( array( $site ) );
		$factory->method( 'get_for_network' )->willReturn( array( $network ) );
		$factory->method( 'get_version' )->willReturn( '1.1.0' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_db_version' )->willReturn( '1.0.0' );
		$installable->method( 'get_update_routine_factories' )
			->willReturn( array( $factory ) );

		$installable->expects( $this->never() )->method( 'add_installed_site_id' );
		$installable->expects( $this->once() )
			->method( 'set_db_version' )
			->with( null, true );

		$updater = new WordPoints_Updater( $installable, true );
		$updater->run();
	}

	/**
	 * Test that the network version is used for the network and site routines.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_network_wide_network_same() {

		$site = $this->createMock( 'WordPoints_RoutineI' );
		$site->expects( $this->once() )->method( 'run' );

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->once() )->method( 'run' );

		$factory = $this->createMock( 'WordPoints_Updater_FactoryI' );
		$factory->method( 'get_for_site' )->willReturn( array( $site ) );
		$factory->method( 'get_for_network' )->willReturn( array( $network ) );
		$factory->method( 'get_version' )->willReturn( '1.1.0' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_update_routine_factories' )
			->willReturn( array( $factory ) );

		$installable->method( 'get_db_version' )->willReturnMap(
			array( array( false, '1.1.0' ), array( true, '1.0.0' ) )
		);

		$installable->expects( $this->never() )->method( 'add_installed_site_id' );
		$installable->expects( $this->once() )
			->method( 'set_db_version' )
			->with( null, true );

		$updater = new WordPoints_Updater( $installable, true );
		$updater->run();
	}

	/**
	 * Test updating network-wide when there are no per-site update routines.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_network_wide_no_site_routines() {

		$this->listen_for_filter( 'switch_blog' );

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->once() )->method( 'run' );

		$factory = $this->createMock( 'WordPoints_Updater_FactoryI' );
		$factory->method( 'get_for_site' )->willReturn( array() );
		$factory->method( 'get_for_network' )->willReturn( array( $network ) );
		$factory->method( 'get_version' )->willReturn( '1.1.0' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_db_version' )->willReturn( '1.0.0' );
		$installable->method( 'get_update_routine_factories' )
			->willReturn( array( $factory ) );

		$installable->expects( $this->once() )
			->method( 'set_db_version' )
			->with( null, true );

		$updater = new WordPoints_Updater( $installable, true );
		$updater->run();

		$this->assertSame( 0, $this->filter_was_called( 'switch_blog' ) );
	}

	/**
	 * Test updating network-wide when per-site updating is skipped.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_network_wide_site_skipped() {

		add_filter( 'wp_is_large_network', '__return_true' );

		$site = $this->createMock( 'WordPoints_RoutineI' );
		$site->expects( $this->never() )->method( 'run' );

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->once() )->method( 'run' );

		$factory = $this->createMock( 'WordPoints_Updater_FactoryI' );
		$factory->method( 'get_for_site' )->willReturn( array( $site ) );
		$factory->method( 'get_for_network' )->willReturn( array( $network ) );
		$factory->method( 'get_version' )->willReturn( '1.1.0' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_db_version' )->willReturn( '1.0.0' );
		$installable->method( 'get_update_routine_factories' )
			->willReturn( array( $factory ) );

		$installable->expects( $this->once() )->method( 'set_network_update_skipped' );
		$installable->expects( $this->once() )
			->method( 'set_db_version' )
			->with( null, true );

		$updater = new WordPoints_Updater( $installable, true );
		$updater->run();
	}

	/**
	 * Test updating network-wide when per-site updating would be skipped.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_network_wide_site_skipped_no_site_routines() {

		add_filter( 'wp_is_large_network', '__return_true' );

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->once() )->method( 'run' );

		$factory = $this->createMock( 'WordPoints_Updater_FactoryI' );
		$factory->method( 'get_for_site' )->willReturn( array() );
		$factory->method( 'get_for_network' )->willReturn( array( $network ) );
		$factory->method( 'get_version' )->willReturn( '1.1.0' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_db_version' )->willReturn( '1.0.0' );
		$installable->method( 'get_update_routine_factories' )
			->willReturn( array( $factory ) );

		$installable->expects( $this->never() )->method( 'set_network_update_skipped' );
		$installable->expects( $this->once() )
			->method( 'set_db_version' )
			->with( null, true );

		$updater = new WordPoints_Updater( $installable, true );
		$updater->run();
	}
}

// EOF
