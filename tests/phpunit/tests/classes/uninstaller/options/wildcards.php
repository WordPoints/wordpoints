<?php

/**
 * Test case for WordPoints_Uninstaller_Options_Wildcards.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Uninstaller_Options_Wildcards.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Uninstaller_Options_Wildcards
 */
class WordPoints_Uninstaller_Options_Wildcards_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests that it supports wildcards.
	 *
	 * @since 2.4.0
	 */
	public function test_uninstall_option_wildcards() {

		add_option( 'testing', 'a' );
		add_option( 'tester', 'b' );
		add_option( 'other', 'c' );

		$uninstaller = new WordPoints_Uninstaller_Options_Wildcards( 'test%' );
		$uninstaller->run();

		$this->assertFalse( get_option( 'testing' ) );
		$this->assertFalse( get_option( 'tester' ) );
		$this->assertSame( 'c', get_option( 'other' ) );
	}
}

// EOF
