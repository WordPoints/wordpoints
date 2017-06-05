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
 *
 * @covers WordPoints_Module_Paths
 */
class WordPoints_Module_Paths_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.2.0
	 */
	public function setUp() {

		parent::setUp();

		add_filter( 'wordpoints_extensions_dir', 'wordpointstests_modules_dir' );
	}

	/**
	 * Test that paths are normalized.
	 *
	 * @since 1.6.0
	 */
	public function test_paths_normalized() {

		$result = WordPoints_Module_Paths::register(
			wordpoints_extensions_dir() . '///test-4\test-4.php'
		);

		$this->assertTrue( $result );

		$result = WordPoints_Module_Paths::register(
			wordpoints_extensions_dir() . '/\/test-3.php'
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
			wordpoints_extensions_dir() . '/test-3.php'
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
			wordpoints_extensions_dir() . '/test-4/test-4.php'
		);

		$file = WordPoints_Module_Paths::resolve(
			dirname( wordpoints_extensions_dir() ) . '/modules/symlink-modules/test-4/test-4.php'
		);

		$this->assertSame( wordpoints_extensions_dir() . 'test-4/test-4.php', $file );
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
			wordpoints_extensions_dir() . '/test-4/test-4.php'
		);

		WordPoints_Module_Paths::register(
			wordpoints_extensions_dir() . '/test-5/test-5.php'
		);

		$file = WordPoints_Module_Paths::resolve(
			wordpoints_extensions_dir() . '/symlink-modules/test-5/test-5.php'
		);

		$this->assertSame( wordpoints_extensions_dir() . 'test-5/test-5.php', $file );
	}
}

// EOF
