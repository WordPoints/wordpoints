<?php

/**
 * A test case for the update to 1.10.3.
 *
 * @package WordPoints\Tests
 * @since 1.10.3
 */

/**
 * Test that the plugin updates to 1.10.3 properly.
 *
 * @since 1.10.3
 *
 * @group update
 */
class WordPoints_1_10_3_Update_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 1.10.3
	 */
	protected $previous_version = '1.10.2';

	/**
	 * @since 1.10.3
	 */
	public function setUp() {

		parent::setUp();

		$this->mock_filesystem();
		$this->mock_fs->mkdir_p( wordpoints_modules_dir() );
	}

	/**
	 * Test that the index.php file is added to the modules directory.
	 *
	 * @since 1.10.3
	 */
	public function test_index_file_created_in_modules_dir() {

		$this->update_wordpoints();

		$modules_dir = wordpoints_modules_dir();

		$this->assertTrue( $this->mock_fs->exists( $modules_dir . '/index.php' ) );
		$this->assertSame(
			'<?php // Gold is silent.'
			, $this->mock_fs->get_file_attr( $modules_dir . '/index.php', 'contents' )
		);
	}

	/**
	 * Test that the index.php file isn't overwritten if it already exists.
	 *
	 * @since 1.10.3
	 */
	public function test_index_file_not_overwritten() {

		$modules_dir = wordpoints_modules_dir();

		$this->assertTrue(
			$this->mock_fs->add_file(
				$modules_dir . '/index.php'
				, array( 'contents' => '<?php // test' )
			)
		);

		$this->update_wordpoints();

		$this->assertTrue( $this->mock_fs->exists( $modules_dir . '/index.php' ) );
		$this->assertSame(
			'<?php // test'
			, $this->mock_fs->get_file_attr( $modules_dir . '/index.php', 'contents' )
		);
	}

	/**
	 * Test that nothing is done if the modules directory doesn't exist.
	 *
	 * @since 1.10.3
	 */
	public function test_does_nothing_if_no_modules_dir() {

		$modules_dir = wordpoints_modules_dir();

		$this->assertTrue( $this->mock_fs->delete( $modules_dir ) );
		$this->assertFalse( $this->mock_fs->exists( $modules_dir ) );

		$this->update_wordpoints();

		$this->assertFalse( $this->mock_fs->exists( $modules_dir . '/index.php' ) );
	}
}

// EOF
