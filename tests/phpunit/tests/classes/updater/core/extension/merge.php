<?php

/**
 * Test case for WordPoints_Updater_Core_Extension_Merge.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Updater_Core_Extension_Merge.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Updater_Core_Extension_Merge
 */
class WordPoints_Updater_Core_Extension_Merge_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests that it deactivates the extension.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_deactivates_extension() {

		add_filter( 'wordpoints_extensions_dir', 'wordpoints_phpunit_extensions_dir' );

		$extension = 'wordpointsorg/wordpointsorg.php';

		wordpoints_activate_module( $extension );

		$this->assertTrue( is_wordpoints_module_active( $extension ) );

		$installer = new WordPoints_Updater_Core_Extension_Merge( $extension );
		$installer->run();

		$this->assertFalse( is_wordpoints_module_active( $extension ) );
		$this->assertSame(
			array( $extension )
			, get_option( 'wordpoints_merged_extensions' )
		);
	}

	/**
	 * Tests that it still updates the option if the extension isn't active.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_extension_not_active() {

		add_filter( 'wordpoints_extensions_dir', 'wordpoints_phpunit_extensions_dir' );

		$extension = 'wordpointsorg/wordpointsorg.php';

		$this->assertFalse( is_wordpoints_module_active( $extension ) );

		$installer = new WordPoints_Updater_Core_Extension_Merge( $extension );
		$installer->run();

		$this->assertFalse( is_wordpoints_module_active( $extension ) );
		$this->assertSame(
			array( $extension )
			, get_option( 'wordpoints_merged_extensions' )
		);
	}

	/**
	 * Tests that it does nothing if the extension is not installed.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_extension_not_installed() {

		$extension = 'nonexistent/nonexistent.php';

		$installer = new WordPoints_Updater_Core_Extension_Merge( $extension );
		$installer->run();

		$this->assertFalse( get_option( 'wordpoints_merged_extensions' ) );
	}

	/**
	 * Tests that it deactivates the extension.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_deactivates_extension_multisite() {

		add_filter( 'wordpoints_extensions_dir', 'wordpoints_phpunit_extensions_dir' );

		$extension = 'wordpointsorg/wordpointsorg.php';

		wordpoints_activate_module( $extension, '', true );

		$this->assertTrue( is_wordpoints_module_active_for_network( $extension ) );

		$installer = new WordPoints_Updater_Core_Extension_Merge( $extension );
		$installer->run();

		$this->assertFalse( is_wordpoints_module_active_for_network( $extension ) );
		$this->assertSame(
			array( $extension )
			, get_site_option( 'wordpoints_merged_extensions' )
		);
	}
}

// EOF
