<?php

/**
 * Test case for WordPoints_Uninstaller.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Uninstaller.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Uninstaller
 */
class WordPoints_Uninstaller_Test extends WordPoints_PHPUnit_TestCase {

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
		$installable->method( 'get_uninstall_routines' )
			->willReturn( array( 'single' => array( $routine ) ) );

		$installable->expects( $this->once() )
			->method( 'unset_db_version' )
			->with( false );

		$installer = new WordPoints_Uninstaller( $installable );
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
		$installable->method( 'get_installed_site_ids' )
			->willReturn( array( get_current_blog_id() ) );

		$installable->method( 'get_uninstall_routines' )
			->willReturn(
				array( 'site' => array( $site ), 'network' => array( $network ) )
			);

		$installable->expects( $this->exactly( 2 ) )
			->method( 'unset_db_version' )
			->withConsecutive( array( false ), array( true ) );

		$installable->expects( $this->once() )
			->method( 'delete_installed_site_ids' );

		$installable->expects( $this->once() )
			->method( 'unset_network_installed' );

		$installable->expects( $this->once() )
			->method( 'unset_network_install_skipped' );

		$installable->expects( $this->once() )
			->method( 'unset_network_update_skipped' );

		$installer = new WordPoints_Uninstaller( $installable );
		$installer->run();
	}

	/**
	 * Tests that the network routines are run after the site routines.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_multisite_run_network_after_site() {

		$counter = new WordPoints_PHPUnit_Mock_Object();

		$site = $this->createMock( 'WordPoints_RoutineI' );
		$site->expects( $this->once() )->method( 'run' )->will(
			$this->returnCallback( array( $counter, 'site' ) )
		);

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->once() )->method( 'run' )->will(
			$this->returnCallback( array( $counter, 'network' ) )
		);

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_installed_site_ids' )
			->willReturn( array( get_current_blog_id() ) );

		$installable->method( 'get_uninstall_routines' )
			->willReturn(
				array( 'site' => array( $site ), 'network' => array( $network ) )
			);

		$uninstaller = new WordPoints_Uninstaller( $installable );
		$uninstaller->run();

		$this->assertSame( 'site', $counter->calls[1]['name'] );
		$this->assertSame( 'network', $counter->calls[2]['name'] );
	}

	/**
	 * Test the basic behaviour when uninstalling WordPoints itself.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_wordpoints() {

		$site = $this->createMock( 'WordPoints_RoutineI' );
		$site->expects( $this->once() )->method( 'run' );

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->once() )->method( 'run' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_slug' )->willReturn( 'wordpoints' );
		$installable->method( 'get_installed_site_ids' )
			->willReturn( array( get_current_blog_id() ) );

		$installable->method( 'get_uninstall_routines' )
			->willReturn(
				array( 'site' => array( $site ), 'network' => array( $network ) )
			);

		$installable->expects( $this->exactly( 2 ) )
			->method( 'unset_db_version' )
			->withConsecutive( array( false ), array( true ) );

		$installable->expects( $this->once() )
			->method( 'delete_installed_site_ids' );

		$installable->expects( $this->never() )
			->method( 'unset_network_installed' );

		$installable->expects( $this->never() )
			->method( 'unset_network_install_skipped' );

		$installable->expects( $this->never() )
			->method( 'unset_network_update_skipped' );

		$installer = new WordPoints_Uninstaller( $installable );
		$installer->run();
	}

	/**
	 * Tests uninstalling on multisite when there are no per-site routines.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_no_site_routines() {

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->once() )->method( 'run' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_installed_site_ids' )
			->willReturn( array( get_current_blog_id() ) );

		$installable->method( 'get_uninstall_routines' )
			->willReturn( array( 'network' => array( $network ) ) );

		$installable->expects( $this->exactly( 2 ) )
			->method( 'unset_db_version' )
			->withConsecutive( array( false ), array( true ) );

		$installer = new WordPoints_Uninstaller( $installable );
		$installer->run();
	}

	/**
	 * Tests uninstalling on a large network.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_large_network() {

		add_filter( 'wp_is_large_network', '__return_true' );

		$site = $this->createMock( 'WordPoints_RoutineI' );
		$site->expects( $this->once() )->method( 'run' );

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->once() )->method( 'run' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_installed_site_ids' )
			->willReturn( array( get_current_blog_id() ) );

		$installable->method( 'get_uninstall_routines' )
			->willReturn(
				array( 'site' => array( $site ), 'network' => array( $network ) )
			);

		$installable->expects( $this->exactly( 2 ) )
			->method( 'unset_db_version' )
			->withConsecutive( array( false ), array( true ) );

		$installer = new WordPoints_Uninstaller( $installable );
		$installer->run();
	}

	/**
	 * Tests uninstalling on a large network when network installed.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_large_network_network_installed() {

		add_filter( 'wp_is_large_network', '__return_true' );

		$site = $this->createMock( 'WordPoints_RoutineI' );
		$site->expects( $this->never() )->method( 'run' );

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->once() )->method( 'run' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'is_network_installed' )
			->willReturn( true );

		$installable->method( 'get_uninstall_routines' )
			->willReturn(
				array( 'site' => array( $site ), 'network' => array( $network ) )
			);

		$installable->expects( $this->once() )
			->method( 'unset_db_version' )
			->with( true );

		$installer = new WordPoints_Uninstaller( $installable );
		$installer->run();
	}

	/**
	 * Tests uninstalling on a large network when installed on many sites.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_large_network_many_sites() {

		add_filter( 'wp_is_large_network', '__return_true' );

		$site = $this->createMock( 'WordPoints_RoutineI' );
		$site->expects( $this->never() )->method( 'run' );

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->once() )->method( 'run' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_installed_site_ids' )->willReturn(
			array_fill( 0, 10001, 1 )
		);

		$installable->method( 'get_uninstall_routines' )
			->willReturn(
				array( 'site' => array( $site ), 'network' => array( $network ) )
			);

		$installable->expects( $this->once() )
			->method( 'unset_db_version' )
			->with( true );

		$installer = new WordPoints_Uninstaller( $installable );
		$installer->run();
	}
}

// EOF
