<?php

/**
 * A test case for wordpoints_extensions_dir().
 *
 * @package WordPoints\Tests
 * @since 2.4.0
 */

/**
 * Test the wordpoints_extensions_dir() function.
 *
 * @since 2.4.0
 *
 * @covers ::wordpoints_extensions_dir
 */
class WordPoints_Extensions_Dir_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test that it is filterable.
	 *
	 * @since 2.4.0
	 */
	public function test_filter() {

		$original = wordpoints_extensions_dir();

		$filter = new WordPoints_PHPUnit_Mock_Filter( 'test_directory' );
		$filter->add_filter( 'wordpoints_extensions_dir' );

		$this->assertSame( 'test_directory', wordpoints_extensions_dir() );

		$filter->remove_filter( 'wordpoints_extensions_dir' );

		$this->assertSame( $original, wordpoints_extensions_dir() );
	}

	/**
	 * Test that it is filterable.
	 *
	 * @since 2.4.0
	 *
	 * @expectedDeprecated wordpoints_modules_dir
	 */
	public function test_deprecated_filter() {

		$original = wordpoints_extensions_dir();

		$filter = new WordPoints_PHPUnit_Mock_Filter( 'test_directory' );
		$filter->add_filter( 'wordpoints_modules_dir' );

		$this->assertSame( 'test_directory', wordpoints_extensions_dir() );

		$filter->remove_filter( 'wordpoints_modules_dir' );

		$this->assertSame( $original, wordpoints_extensions_dir() );
	}
}

// EOF
