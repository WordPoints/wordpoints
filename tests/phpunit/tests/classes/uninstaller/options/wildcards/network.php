<?php

/**
 * Test case for WordPoints_Uninstaller_Options_Wildcards_Network.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Uninstaller_Options_Wildcards_Network.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Uninstaller_Options_Wildcards_Network
 */
class WordPoints_Uninstaller_Options_Wildcards_Network_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests that it supports wildcards.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_uninstall_option_wildcards() {

		add_site_option( 'testing', 'a' );
		add_site_option( 'tester', 'b' );
		add_site_option( 'other', 'c' );

		$uninstaller = new WordPoints_Uninstaller_Options_Wildcards_Network( 'test%' );
		$uninstaller->run();

		$this->assertFalse( get_site_option( 'testing' ) );
		$this->assertFalse( get_site_option( 'tester' ) );
		$this->assertSame( 'c', get_site_option( 'other' ) );
	}
}

// EOF
