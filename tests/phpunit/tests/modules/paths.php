<?php

/**
 * A test case for WordPoints_Module_Paths.
 *
 * @package WordPoints\Tests
 * @since 1.6.0
 */

/**
 * Test the WordPoints_Module_Paths class.
 *
 * @since 1.6.0
 */
class WordPoints_Module_Paths_Test extends WP_UnitTestCase {

	/**
	 * Test that paths are normalized.
	 *
	 * @since 1.6.0
	 */
	public function test_paths_normalized() {

		$result = WordPoints_Module_Paths::register(
			wordpoints_modules_dir() . '///test-4\test-4.php'
		);

		$this->assertTrue( $result );

		$result = WordPoints_Module_Paths::register(
			wordpoints_modules_dir() . '/\/test-3.php'
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test that single-file module paths aren't registered.
	 *
	 * @since 1.6.0
	 */
	public function test_single_file_module() {

		$result = WordPoints_Module_Paths::register(
			wordpoints_modules_dir() . '/test-3.php'
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test resolve from real path.
	 *
	 * @since 1.6.0
	 */
	public function test_resolve_from_realpath() {

		WordPoints_Module_Paths::register(
			wordpoints_modules_dir() . '/test-4/test-4.php'
		);

		$file = WordPoints_Module_Paths::resolve(
			dirname( wordpoints_modules_dir() ) . '/modules/symlink-modules/test-4/test-4.php'
		);

		$this->assertEquals( wordpoints_modules_dir() . 'test-4/test-4.php', $file );
	}

	/**
	 * Test that similar paths don't get tangled.
	 *
	 * @link https://core.trac.wordpress.org/ticket/28441
	 *
	 * @since 1.6.0
	 */
	public function test_paths_do_not_tangle() {

		WordPoints_Module_Paths::register(
			wordpoints_modules_dir() . '/test-4/test-4.php'
		);

		WordPoints_Module_Paths::register(
			wordpoints_modules_dir() . '/test-1/test-1.php'
		);

		$file = WordPoints_Module_Paths::resolve(
			dirname( wordpoints_modules_dir() ) . '/modules/symlink-modules/test/test-1.php'
		);

		$this->assertEquals( wordpoints_modules_dir() . 'test-1/test-1.php', $file );
	}
}

// EOF
