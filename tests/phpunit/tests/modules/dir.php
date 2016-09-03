<?php

/**
 * A test case for wordpoints_modules_dir().
 *
 * @package WordPoints\Tests
 * @since 2.2.0
 */

/**
 * Test the wordpoints_modules_dir() function.
 *
 * @since 2.2.0
 *
 * @covers ::wordpoints_modules_dir
 */
class WordPoints_Modules_Dir_Test extends WordPoints_PHPUnit_TestCase  {

	/**
	 * Test that it is filterable.
	 *
	 * @since 2.2.0
	 */
	public function test_filter() {

		$original = wordpoints_modules_dir();

		$filter = new WordPoints_Mock_Filter( 'test_directory' );
		$filter->add_filter( 'wordpoints_modules_dir' );

		$this->assertEquals( 'test_directory', wordpoints_modules_dir() );

		$filter->remove_filter( 'wordpoints_modules_dir' );

		$this->assertEquals( $original, wordpoints_modules_dir() );
	}
}

// EOF
