<?php

/**
 * Test case for WordPoints_Installer_Site.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Installer_Site.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Installer_Site
 */
class WordPoints_Installer_Site_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test the basic behaviour of run().
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run() {

		$site_id = $this->factory->blog->create();

		$site = $this->createMock( 'WordPoints_RoutineI' );
		$site->expects( $this->once() )->method( 'run' );

		$network = $this->createMock( 'WordPoints_RoutineI' );
		$network->expects( $this->never() )->method( 'run' );

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->method( 'get_install_routines' )
			->willReturn(
				array( 'site' => array( $site ), 'network' => array( $network ) )
			);

		$installable->expects( $this->once() )->method( 'add_installed_site_id' );
		$installable->expects( $this->never() )->method( 'set_db_version' );

		$installer = new WordPoints_Installer_Site( $installable, $site_id );
		$installer->run();
	}
}

// EOF
