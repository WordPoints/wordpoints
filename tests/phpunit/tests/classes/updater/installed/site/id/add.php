<?php

/**
 * Test case for WordPoints_Updater_Installed_Site_ID_Add.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Updater_Installed_Site_ID_Add.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Updater_Installed_Site_ID_Add
 */
class WordPoints_Updater_Installed_Site_ID_Add_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests that the site ID is added.
	 *
	 * @since 2.4.0
	 */
	public function test_adds_id() {

		$installable = $this->createMock( 'WordPoints_InstallableI' );
		$installable->expects( self::once() )->method( 'add_installed_site_id' );

		$installer = new WordPoints_Updater_Installed_Site_ID_Add( $installable );
		$installer->run();
	}
}

// EOF
