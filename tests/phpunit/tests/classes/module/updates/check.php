<?php

/**
 * Testcase for WordPoints_Module_Updates_Check.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Test WordPoints_Module_Updates_Check.
 *
 * @since 2.4.0
 *
 * @group modules
 *
 * @covers WordPoints_Module_Updates_Check
 */
class WordPoints_Module_Updates_Check_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.4.0
	 */
	public function setUp() {

		parent::setUp();

		add_filter(
			'wordpoints_server_object_for_module'
			, array( $this, 'filter_server_for_module' )
		);

		wp_cache_set(
			'wordpoints_modules'
			, array(
				'' => array(
					'test-7/test-7.php' => array(
						'version' => '1.0.0',
						'server' => 'wordpoints.org',
						'ID' => '7',
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
	 * Filters the server used for the module in the tests.
	 *
	 * @since 2.4.0
	 *
	 * @return WordPoints_Module_ServerI
	 */
	public function filter_server_for_module() {

		$api = $this->getMock( 'WordPoints_Module_Server_API_UpdatesI' );
		$api->method( 'get_module_latest_version' )->willReturn( '1.1.0' );

		$server = $this->getMock(
			'WordPoints_Module_ServerI'
			, array()
			, array( 'test' )
		);

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

		$check = new WordPoints_Module_Updates_Check();
		$updates = $check->run();

		$this->assertGreaterThanOrEqual( $time, $updates->get_time_checked() );
		$this->assertSame( array( 'test-7/test-7.php' => '1.1.0' ), $updates->get_new_versions() );
		$this->assertSame(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
			, $updates->get_versions_checked()
		);

		// Check that the cache was updated.
		$updates = wordpoints_get_module_updates();

		$this->assertGreaterThanOrEqual( $time, $updates->get_time_checked() );
		$this->assertSame( array( 'test-7/test-7.php' => '1.1.0' ), $updates->get_new_versions() );
		$this->assertSame(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
			, $updates->get_versions_checked()
		);
	}

	/**
	 * Tests that it returns false if there are no modules supporting updates.
	 *
	 * @since 2.4.0
	 */
	public function test_no_modules_supporting_updates() {

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

		$check = new WordPoints_Module_Updates_Check();
		$updates = $check->run();

		$this->assertInstanceOf( 'WordPoints_Module_UpdatesI', $updates );

		$this->assertGreaterThanOrEqual( $time, $updates->get_time_checked() );
		$this->assertSame( array(), $updates->get_new_versions() );
		$this->assertSame(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
			, $updates->get_versions_checked()
		);
	}

	/**
	 * Tests that it bails out if recently checked and no module versions have changed.
	 *
	 * @since 2.4.0
	 */
	public function test_no_module_versions_changed() {

		$updates = new WordPoints_Module_Updates();
		$updates->set_versions_checked(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
		);
		$updates->save();

		$check = new WordPoints_Module_Updates_Check();

		$this->assertFalse( $check->run() );
	}

	/**
	 * Tests that it checks again module versions have changed.
	 *
	 * @since 2.4.0
	 */
	public function test_module_versions_changed() {

		$updates = new WordPoints_Module_Updates();
		$updates->set_versions_checked(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.4.0' )
		);
		$updates->save();

		$time = time();

		$check = new WordPoints_Module_Updates_Check();
		$updates = $check->run();

		$this->assertGreaterThanOrEqual( $time, $updates->get_time_checked() );
		$this->assertSame( array( 'test-7/test-7.php' => '1.1.0' ), $updates->get_new_versions() );
		$this->assertSame(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
			, $updates->get_versions_checked()
		);
	}

	/**
	 * Tests that it doesn't bail out if recently checked and modules were deleted.
	 *
	 * @since 2.4.0
	 */
	public function test_modules_deleted() {

		$updates = new WordPoints_Module_Updates();
		$updates->set_versions_checked(
			array(
				'test-7/test-7.php' => '1.0.0',
				'test-8/test-8.php' => '2.5.0',
				'module-34/module-34.php' => '0.5.5',
			)
		);
		$updates->save();

		$time = time();

		$check = new WordPoints_Module_Updates_Check();
		$updates = $check->run();

		$this->assertGreaterThanOrEqual( $time, $updates->get_time_checked() );
		$this->assertSame( array( 'test-7/test-7.php' => '1.1.0' ), $updates->get_new_versions() );
		$this->assertSame(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
			, $updates->get_versions_checked()
		);
	}

	/**
	 * Tests that it doesn't bail out if recently checked and modules were added.
	 *
	 * @since 2.4.0
	 */
	public function test_modules_added() {

		$updates = new WordPoints_Module_Updates();
		$updates->set_versions_checked( array( 'test-7/test-7.php' => '1.0.0' ) );
		$updates->save();

		$time = time();

		$check = new WordPoints_Module_Updates_Check();
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

		$updates = new WordPoints_Module_Updates();
		$updates->set_time_checked( time() - DAY_IN_SECONDS );
		$updates->set_versions_checked(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
		);
		$updates->save();

		$time = time();

		$check = new WordPoints_Module_Updates_Check();
		$updates = $check->run();

		$this->assertGreaterThanOrEqual( $time, $updates->get_time_checked() );
		$this->assertSame( array( 'test-7/test-7.php' => '1.1.0' ), $updates->get_new_versions() );
		$this->assertSame(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
			, $updates->get_versions_checked()
		);
	}

	/**
	 * Tests that it bails out if recently checked and no module versions have changed.
	 *
	 * @since 2.4.0
	 */
	public function test_custom_cache_length() {

		$updates = new WordPoints_Module_Updates();
		$updates->set_versions_checked(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
		);
		$updates->save();

		$check = new WordPoints_Module_Updates_Check( MINUTE_IN_SECONDS );

		$this->assertFalse( $check->run() );
	}

	/**
	 * Tests that it checks again if there are no changes but the cache is expired.
	 *
	 * @since 2.4.0
	 */
	public function test_cache_expired_custom_cache_length() {

		$updates = new WordPoints_Module_Updates();
		$updates->set_time_checked( time() - 2 * MINUTE_IN_SECONDS );
		$updates->set_versions_checked(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
		);
		$updates->save();

		$time = time();

		$check = new WordPoints_Module_Updates_Check( MINUTE_IN_SECONDS );
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
	 * @covers ::wordpoints_check_for_module_updates
	 */
	public function test_cache_expired_custom_cache_length_procedural() {

		$updates = new WordPoints_Module_Updates();
		$updates->set_time_checked( time() - 2 * MINUTE_IN_SECONDS );
		$updates->set_versions_checked(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
		);
		$updates->save();

		$time = time();

		$updates = wordpoints_check_for_module_updates( MINUTE_IN_SECONDS );

		$this->assertGreaterThanOrEqual( $time, $updates->get_time_checked() );
		$this->assertSame( array( 'test-7/test-7.php' => '1.1.0' ), $updates->get_new_versions() );
		$this->assertSame(
			array( 'test-7/test-7.php' => '1.0.0', 'test-8/test-8.php' => '2.5.0' )
			, $updates->get_versions_checked()
		);
	}
}

// EOF
