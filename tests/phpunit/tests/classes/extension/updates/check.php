<?php

/**
 * Testcase for WordPoints_Extension_Updates_Check.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Test WordPoints_Extension_Updates_Check.
 *
 * @since 2.4.0
 *
 * @group extensions
 *
 * @covers WordPoints_Extension_Updates_Check
 */
class WordPoints_Extension_Updates_Check_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.4.0
	 */
	public function setUp() {

		parent::setUp();

		add_filter(
			'wordpoints_server_object_for_extension'
			, array( $this, 'filter_server_for_extension' )
		);

		wp_cache_set(
			'wordpoints_modules'
			, array(
				'' => array(
					'test-7/test-7.php' => array(
						'version' => '1.0.0',
						'server'  => 'wordpoints.org',
						'ID'      => '7',
					),
					'test-8/test-8.php' => array(
						'version' => '2.5.0',
					),
				),
			)
			, 'wordpoints_modules'
		);
	}

	/**
	 * Filters the server used for the extension in the tests.
	 *
	 * @since 2.4.0
	 *
	 * @return WordPoints_Extension_ServerI
	 */
	public function filter_server_for_extension() {

		$api = $this->createMock( 'WordPoints_Extension_Server_API_UpdatesI' );
		$api->method( 'get_extension_latest_version' )->willReturn( '1.1.0' );

		$server = $this->createMock( 'WordPoints_Extension_ServerI' );
		$server->method( 'get_api' )->willReturn( $api );

		return $server;
	}

	/**
	 * Tests that it checks if there is no cache.
	 *
	 * @since 2.4.0
	 */
	public function test_no_cache() {

		$time = time();

		$check   = new WordPoints_Extension_Updates_Check();
		$updates = $check->run();

		$this->assertGreaterThanOrEqual( $time, $updates->get_time_checked() );
		$this->assertSame( array( 'test-7/test-7.php' => '1.1.0' ), $updates->get_new_versions() );
		$this->assertSame(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
			, $updates->get_versions_checked()
		);

		// Check that the cache was updated.
		$updates = wordpoints_get_extension_updates();

		$this->assertGreaterThanOrEqual( $time, $updates->get_time_checked() );
		$this->assertSame( array( 'test-7/test-7.php' => '1.1.0' ), $updates->get_new_versions() );
		$this->assertSame(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
			, $updates->get_versions_checked()
		);
	}

	/**
	 * Tests that it returns false if there are no extensions supporting updates.
	 *
	 * @since 2.4.0
	 */
	public function test_no_extensions_supporting_updates() {

		wp_cache_set(
			'wordpoints_modules'
			, array(
				'' => array(
					'test-7/test-7.php' => array( 'version' => '1.0.0' ),
					'test-8/test-8.php' => array( 'version' => '2.5.0' ),
				),
			)
			, 'wordpoints_modules'
		);

		$time = time();

		$check   = new WordPoints_Extension_Updates_Check();
		$updates = $check->run();

		$this->assertInstanceOf( 'WordPoints_Extension_UpdatesI', $updates );

		$this->assertGreaterThanOrEqual( $time, $updates->get_time_checked() );
		$this->assertSame( array(), $updates->get_new_versions() );
		$this->assertSame(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
			, $updates->get_versions_checked()
		);
	}

	/**
	 * Tests that it bails out if recently checked and no extension versions have changed.
	 *
	 * @since 2.4.0
	 */
	public function test_no_extension_versions_changed() {

		$updates = new WordPoints_Extension_Updates();
		$updates->set_versions_checked(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
		);
		$updates->save();

		$check = new WordPoints_Extension_Updates_Check();

		$this->assertFalse( $check->run() );
	}

	/**
	 * Tests that it checks again extension versions have changed.
	 *
	 * @since 2.4.0
	 */
	public function test_extension_versions_changed() {

		$updates = new WordPoints_Extension_Updates();
		$updates->set_versions_checked(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.4.0' )
		);
		$updates->save();

		$time = time();

		$check   = new WordPoints_Extension_Updates_Check();
		$updates = $check->run();

		$this->assertGreaterThanOrEqual( $time, $updates->get_time_checked() );
		$this->assertSame( array( 'test-7/test-7.php' => '1.1.0' ), $updates->get_new_versions() );
		$this->assertSame(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
			, $updates->get_versions_checked()
		);
	}

	/**
	 * Tests that it doesn't bail out if recently checked and extensions were deleted.
	 *
	 * @since 2.4.0
	 */
	public function test_extensions_deleted() {

		$updates = new WordPoints_Extension_Updates();
		$updates->set_versions_checked(
			array(
				'test-7/test-7.php'       => '1.0.0',
				'test-8/test-8.php'       => '2.5.0',
				'module-34/module-34.php' => '0.5.5',
			)
		);
		$updates->save();

		$time = time();

		$check   = new WordPoints_Extension_Updates_Check();
		$updates = $check->run();

		$this->assertGreaterThanOrEqual( $time, $updates->get_time_checked() );
		$this->assertSame( array( 'test-7/test-7.php' => '1.1.0' ), $updates->get_new_versions() );
		$this->assertSame(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
			, $updates->get_versions_checked()
		);
	}

	/**
	 * Tests that it doesn't bail out if recently checked and extensions were added.
	 *
	 * @since 2.4.0
	 */
	public function test_extensions_added() {

		$updates = new WordPoints_Extension_Updates();
		$updates->set_versions_checked( array( 'test-7/test-7.php' => '1.0.0' ) );
		$updates->save();

		$time = time();

		$check   = new WordPoints_Extension_Updates_Check();
		$updates = $check->run();

		$this->assertGreaterThanOrEqual( $time, $updates->get_time_checked() );
		$this->assertSame( array( 'test-7/test-7.php' => '1.1.0' ), $updates->get_new_versions() );
		$this->assertSame(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
			, $updates->get_versions_checked()
		);
	}

	/**
	 * Tests that it checks again if there are no changes but the cache is expired.
	 *
	 * @since 2.4.0
	 */
	public function test_cache_expired() {

		$updates = new WordPoints_Extension_Updates();
		$updates->set_time_checked( time() - DAY_IN_SECONDS );
		$updates->set_versions_checked(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
		);
		$updates->save();

		$time = time();

		$check   = new WordPoints_Extension_Updates_Check();
		$updates = $check->run();

		$this->assertGreaterThanOrEqual( $time, $updates->get_time_checked() );
		$this->assertSame( array( 'test-7/test-7.php' => '1.1.0' ), $updates->get_new_versions() );
		$this->assertSame(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
			, $updates->get_versions_checked()
		);
	}

	/**
	 * Tests that it bails out if recently checked and no extension versions have changed.
	 *
	 * @since 2.4.0
	 */
	public function test_custom_cache_length() {

		$updates = new WordPoints_Extension_Updates();
		$updates->set_versions_checked(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
		);
		$updates->save();

		$check = new WordPoints_Extension_Updates_Check( MINUTE_IN_SECONDS );

		$this->assertFalse( $check->run() );
	}

	/**
	 * Tests that it checks again if there are no changes but the cache is expired.
	 *
	 * @since 2.4.0
	 */
	public function test_cache_expired_custom_cache_length() {

		$updates = new WordPoints_Extension_Updates();
		$updates->set_time_checked( time() - 2 * MINUTE_IN_SECONDS );
		$updates->set_versions_checked(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
		);
		$updates->save();

		$time = time();

		$check   = new WordPoints_Extension_Updates_Check( MINUTE_IN_SECONDS );
		$updates = $check->run();

		$this->assertGreaterThanOrEqual( $time, $updates->get_time_checked() );
		$this->assertSame( array( 'test-7/test-7.php' => '1.1.0' ), $updates->get_new_versions() );
		$this->assertSame(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
			, $updates->get_versions_checked()
		);
	}

	/**
	 * Tests that it checks again if there are no changes but the cache is expired.
	 *
	 * @since 2.4.0
	 *
	 * @covers ::wordpoints_check_for_extension_updates
	 */
	public function test_cache_expired_custom_cache_length_procedural() {

		$updates = new WordPoints_Extension_Updates();
		$updates->set_time_checked( time() - 2 * MINUTE_IN_SECONDS );
		$updates->set_versions_checked(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
		);
		$updates->save();

		$time = time();

		$updates = wordpoints_check_for_extension_updates( MINUTE_IN_SECONDS );

		$this->assertGreaterThanOrEqual( $time, $updates->get_time_checked() );
		$this->assertSame( array( 'test-7/test-7.php' => '1.1.0' ), $updates->get_new_versions() );
		$this->assertSame(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
			, $updates->get_versions_checked()
		);
	}
}

// EOF
