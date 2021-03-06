<?php

/**
 * Test case for WordPoints_Installer.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Installer.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Installer
 */
class WordPoints_Installer_Test extends WordPoints_PHPUnit_TestCase {

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

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_install_routines' )
			->willReturn( array( 'single' => array( $routine ) ) );

		$installable->expects( $this->once() )
			->method( 'set_db_version' )
			->with( null, false );

		$installer = new WordPoints_Installer( $installable, false );
		$installer->run();
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

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_install_routines' )
			->willReturn(
				array( 'site' => array( $site ), 'network' => array( $network ) )
			);

		$installable->expects( $this->once() )->method( 'add_installed_site_id' );
		$installable->expects( $this->exactly( 2 ) )
			->method( 'set_db_version' )
			->withConsecutive( array( null, true ), array( null, false ) );

		$installer = new WordPoints_Installer( $installable, false );
		$installer->run();
	}

	/**
	 * Test the basic behaviour when installing network wide.
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

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_install_routines' )
			->willReturn(
				array( 'site' => array( $site ), 'network' => array( $network ) )
			);

		$installable->expects( $this->never() )->method( 'add_installed_site_id' );
		$installable->expects( $this->once() )->method( 'set_network_installed' );
		$installable->expects( $this->once() )
			->method( 'set_db_version' )
			->with( null, true );

		$installer = new WordPoints_Installer( $installable, true );
		$installer->run();
	}

	/**
	 * Test installing network-wide when there are no per-site install routines.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_network_wide_no_site_routines() {

		$this->listen_for_filter( 'switch_blog' );

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->once() )->method( 'run' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_install_routines' )
			->willReturn( array( 'network' => array( $network ) ) );

		$installable->expects( $this->never() )->method( 'add_installed_site_id' );
		$installable->expects( $this->once() )->method( 'set_network_installed' );
		$installable->expects( $this->once() )
			->method( 'set_db_version' )
			->with( null, true );

		$installer = new WordPoints_Installer( $installable, true );
		$installer->run();

		$this->assertSame( 0, $this->filter_was_called( 'switch_blog' ) );
	}

	/**
	 * Test installing network-wide when per-site install is skipped.
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

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_install_routines' )
			->willReturn(
				array( 'site' => array( $site ), 'network' => array( $network ) )
			);

		$installable->expects( $this->never() )->method( 'add_installed_site_id' );
		$installable->expects( $this->once() )->method( 'set_network_installed' );
		$installable->expects( $this->once() )->method( 'set_network_install_skipped' );
		$installable->expects( $this->once() )
			->method( 'set_db_version' )
			->with( null, true );

		$installer = new WordPoints_Installer( $installable, true );
		$installer->run();
	}

	/**
	 * Test installing network-wide when per-site install would be skipped.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_network_wide_site_skipped_no_site_routines() {

		add_filter( 'wp_is_large_network', '__return_true' );

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->once() )->method( 'run' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_install_routines' )
			->willReturn( array( 'network' => array( $network ) ) );

		$installable->expects( $this->never() )->method( 'add_installed_site_id' );
		$installable->expects( $this->never() )->method( 'set_network_install_skipped' );
		$installable->expects( $this->once() )->method( 'set_network_installed' );
		$installable->expects( $this->once() )
			->method( 'set_db_version' )
			->with( null, true );

		$installer = new WordPoints_Installer( $installable, true );
		$installer->run();
	}
}

// EOF
