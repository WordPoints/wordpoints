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
class WordPoints_1_10_3_Update_Test extends WordPoints_UnitTestCase {

	/**
	 * @since 1.10.3
	 */
	protected $previous_version = '1.10.2';

	/**
	 * The mock filesystem used in the tests.
	 *
	 * @since 0.1.0
	 *
	 * @var WP_Mock_Filesystem
	 */
	protected $mock_fs;

	/**
	 * @since 1.10.3
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		/**
		 * The base filesystem class.
		 *
		 * @since 1.10.3
		 */
		require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );

		/**
		 * The filesystem mocker.
		 *
		 * @since 1.10.3
		 */
		require_once( WORDPOINTS_TESTS_DIR . '/../../vendor/jdgrimes/wp-filesystem-mock/src/wp-filesystem-mock.php' );

		/**
		 * The mock filesystem.
		 *
		 * @since 1.10.3
		 */
		require_once( WORDPOINTS_TESTS_DIR . '/../../vendor/jdgrimes/wp-filesystem-mock/src/wp-mock-filesystem.php' );
	}

	/**
	 * @since 1.10.3
	 */
	public function setUp() {

		parent::setUp();

		$this->mock_fs = new WP_Mock_Filesystem;
		$this->mock_fs->mkdir_p( wordpoints_modules_dir() );

		WP_Filesystem_Mock::set_mock( $this->mock_fs );
		WP_Filesystem_Mock::start();
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
		$this->assertEquals(
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
		$this->assertEquals(
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
