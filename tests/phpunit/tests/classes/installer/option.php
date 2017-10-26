<?php

/**
 * Test case for WordPoints_Installer_Option.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Installer_Option.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Installer_Option
 */
class WordPoints_Installer_Option_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests that the option is added.
	 *
	 * @since 2.4.0
	 */
	public function test_adds_option() {

		$installer = new WordPoints_Installer_Option( 'test', 'a' );
		$installer->run();

		$this->assertSame( 'a', get_option( 'test' ) );
	}

	/**
	 * Tests that it doesn't overwrite an existing option.
	 *
	 * @since 2.4.0
	 */
	public function test_not_overwrites_existing() {

		add_option( 'test', 'a' );

		$installer = new WordPoints_Installer_Option( 'test', 'b' );
		$installer->run();

		$this->assertSame( 'a', get_option( 'test' ) );
	}

	/**
	 * Tests that the option is added.
	 *
	 * @since 2.4.0
	 */
	public function test_adds_option_network() {

		$installer = new WordPoints_Installer_Option( 'test', 'a', true );
		$installer->run();

		$this->assertSame( 'a', get_site_option( 'test' ) );
	}
}

// EOF
