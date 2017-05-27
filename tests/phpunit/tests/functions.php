<?php

/**
 * A test case for the general core functions.
 *
 * @package WordPoints\Tests
 * @since 1.2.0
 */

/**
 * Test that all of the core functions work properly.
 *
 * @since 1.2.0
 */
class WordPoints_Core_Functions_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.0.0
	 */
	public function tearDown() {

		unset( $GLOBALS['current_screen'] );

		parent::tearDown();
	}

	/**
	 * Test wordpoints_deactivate().
	 *
	 * @since 2.4.0
	 *
	 * @covers ::wordpoints_deactivate
	 * @covers ::wordpoints_schedule_module_update_checks
	 */
	public function test_wordpoints_deactivate() {

		wordpoints_schedule_module_updates_check();

		$this->assertNotFalse( wp_next_scheduled( 'wordpoints_check_for_module_updates' ) );

		wordpoints_deactivate();

		$this->assertFalse( wp_next_scheduled( 'wordpoints_check_for_module_updates' ) );
	}

	/**
	 * Test is_wordpoints_network_active().
	 *
	 * @since 1.2.0
	 *
	 * @covers ::is_wordpoints_network_active
	 */
	public function test_is_wordpoints_network_active() {

		$plugin_file = plugin_basename( WORDPOINTS_DIR . 'wordpoints.php' );

		// Make sure it isn't network active.
		update_site_option( 'active_sitewide_plugins', array() );

		$this->assertFalse( is_wordpoints_network_active() );

		// Now make it network active.
		update_site_option( 'active_sitewide_plugins', array( $plugin_file => true ) );

		if ( is_multisite() ) {
			$this->assertTrue( is_wordpoints_network_active() );
		} else {
			$this->assertFalse( is_wordpoints_network_active() );
		}

		// Without this checkRequirements() will not work for the next test, because
		// the cache is only cleared in setUp(), which is called after that.
		wp_cache_delete(
			"{$GLOBALS['wpdb']->siteid}:active_sitewide_plugins"
			, 'site-options'
		);
	}

	/**
	 * Test that it checks module compatibility when a breaking update is performed.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_breaking_update
	 */
	public function test_wordpoints_breaking_update() {

		$this->wordpoints_set_db_version();

		if ( is_wordpoints_network_active() ) {
			$this->listen_for_filter( 'site_option_wordpoints_sitewide_active_modules' );
		}

		$this->listen_for_filter( 'pre_option_wordpoints_active_modules' );

		wordpoints_breaking_update();

		if ( is_wordpoints_network_active() ) {
			$this->assertNotEmpty(
				$this->filter_was_called(
					'site_option_wordpoints_sitewide_active_modules'
				)
			);
		}

		$this->assertNotEmpty(
			$this->filter_was_called( 'pre_option_wordpoints_active_modules' )
		);
	}

	/**
	 * Test that it skips checking module compatibility if the update isn't breaking.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_breaking_update
	 */
	public function test_wordpoints_breaking_update_not_breaking() {

		$this->wordpoints_set_db_version( '2.0.0' );

		if ( is_wordpoints_network_active() ) {
			$this->listen_for_filter( 'site_option_wordpoints_sitewide_active_modules' );
		}

		$this->listen_for_filter( 'pre_option_wordpoints_active_modules' );

		wordpoints_breaking_update();

		if ( is_wordpoints_network_active() ) {
			$this->assertSame(
				0
				, $this->filter_was_called(
					'site_option_wordpoints_sitewide_active_modules'
				)
			);
		}

		$this->assertSame(
			0
			, $this->filter_was_called( 'pre_option_wordpoints_active_modules' )
		);
	}

	/**
	 * Test that it skips checking module compatibility if the db version isn't set.
	 *
	 * @since 2.0.2
	 *
	 * @covers ::wordpoints_breaking_update
	 */
	public function test_wordpoints_breaking_update_db_version_not_set() {

		$this->wordpoints_set_db_version( '' );

		if ( is_wordpoints_network_active() ) {
			$this->listen_for_filter( 'site_option_wordpoints_sitewide_active_modules' );
		}

		$this->listen_for_filter( 'pre_option_wordpoints_active_modules' );

		wordpoints_breaking_update();

		if ( is_wordpoints_network_active() ) {
			$this->assertSame(
				0
				, $this->filter_was_called(
					'site_option_wordpoints_sitewide_active_modules'
				)
			);
		}

		$this->assertSame(
			0
			, $this->filter_was_called( 'pre_option_wordpoints_active_modules' )
		);
	}

	/**
	 * Test wordpoints_maintenance_shutdown_print_rand_str().
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_maintenance_shutdown_print_rand_str
	 */
	public function test_wordpoints_maintenance_shutdown_print_rand_str() {

		update_option( 'wordpoints_module_check_rand_str', __METHOD__ );
		update_option( 'wordpoints_module_check_nonce', __FUNCTION__ );

		$_GET['check_module'] = 'test';
		$_GET['wordpoints_module_check'] = __FUNCTION__;

		ob_start();
		wordpoints_maintenance_shutdown_print_rand_str();
		$this->assertSame( __METHOD__, ob_get_clean() );
	}

	/**
	 * Test that it does nothing if the nonce is invalid.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_maintenance_shutdown_print_rand_str
	 */
	public function test_wordpoints_maintenance_shutdown_print_rand_str_requires_nonce() {

		update_option( 'wordpoints_module_check_rand_str', __METHOD__ );
		update_option( 'wordpoints_module_check_nonce', __FUNCTION__ );

		$_GET['check_module'] = 'test';
		$_GET['wordpoints_module_check'] = 'invalid';

		ob_start();
		wordpoints_maintenance_shutdown_print_rand_str();
		$this->assertSame( '', ob_get_clean() );
	}

	/**
	 * Test that it uses the site option if in the network admin.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_maintenance_shutdown_print_rand_str
	 */
	public function test_wordpoints_maintenance_shutdown_print_rand_str_network_admin() {

		update_site_option( 'wordpoints_module_check_rand_str', __METHOD__ );
		update_site_option( 'wordpoints_module_check_nonce', __FUNCTION__ );

		$GLOBALS['current_screen'] = WP_Screen::get( 'test-network' );

		$_GET['check_module'] = 'test';
		$_GET['wordpoints_module_check'] = __FUNCTION__;

		ob_start();
		wordpoints_maintenance_shutdown_print_rand_str();
		$this->assertSame( __METHOD__, ob_get_clean() );
	}

	/**
	 * Test wordpoints_maintenance_filter_modules().
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_maintenance_filter_modules
	 */
	public function test_wordpoints_maintenance_filter_modules() {

		update_option( 'wordpoints_module_check_nonce', __FUNCTION__ );

		$_GET['check_module'] = 'test';
		$_GET['wordpoints_module_check'] = __FUNCTION__;

		$modules = wordpoints_maintenance_filter_modules( array( __METHOD__ ) );

		$this->assertSame( array( 'test' ), $modules );
	}

	/**
	 * Test that it requires the check_modules GET parameter to be set.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_maintenance_filter_modules
	 */
	public function test_wordpoints_maintenance_filter_modules_requires_check_modules() {

		update_option( 'wordpoints_module_check_nonce', __FUNCTION__ );

		$_GET['wordpoints_module_check'] = __FUNCTION__;

		$modules = wordpoints_maintenance_filter_modules( array( __METHOD__ ) );

		$this->assertSame( array( __METHOD__ ), $modules );
	}

	/**
	 * Test that it requires a valid nonce.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_maintenance_filter_modules
	 */
	public function test_wordpoints_maintenance_filter_modules_requires_nonce() {

		update_option( 'wordpoints_module_check_nonce', __FUNCTION__ );

		$_GET['check_module'] = 'test';

		$modules = wordpoints_maintenance_filter_modules( array( __METHOD__ ) );

		$this->assertSame( array( __METHOD__ ), $modules );
	}

	/**
	 * Test that it splits a comma-delimited string.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_maintenance_filter_modules
	 */
	public function test_wordpoints_maintenance_filter_modules_multiple() {

		update_option( 'wordpoints_module_check_nonce', __FUNCTION__ );

		$_GET['check_module'] = 'test1,test2';
		$_GET['wordpoints_module_check'] = __FUNCTION__;

		$modules = wordpoints_maintenance_filter_modules( array( __METHOD__ ) );

		$this->assertSame( array( 'test1', 'test2' ), $modules );
	}

	/**
	 * Test wordpoints_maintenance_filter_modules().
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_maintenance_filter_modules
	 */
	public function test_wordpoints_maintenance_filter_modules_network_wide() {

		update_site_option( 'wordpoints_module_check_nonce', __FUNCTION__ );

		$GLOBALS['current_screen'] = WP_Screen::get( 'test-network' );
		$GLOBALS['wp_current_filter'] = array(
			'pre_site_option_wordpoints_sitewide_active_modules',
		);

		$_GET['check_module'] = 'test';
		$_GET['wordpoints_module_check'] = __FUNCTION__;

		$modules = wordpoints_maintenance_filter_modules( array( __METHOD__ ) );

		$this->assertSame( array( 'test' => 0 ), $modules );
	}

	/**
	 * Test wordpoints_hash().
	 *
	 * @since 2.0.1
	 *
	 * @covers ::wordpoints_hash
	 */
	public function test_wordpoints_hash() {

		$data = __METHOD__;

		$this->assertSame( hash( 'sha256', $data ), wordpoints_hash( $data ) );
	}
}

// EOF
