<?php

/**
 * Test case for the WordPoints_Breaking_Updater class.
 *
 * @package WordPoints\Tests
 * @since 2.0.0
 */

/**
 * Tests the WordPoints_Breaking_Updater class.
 *
 * @since 2.0.0
 */
class WordPoints_Breaking_Updater_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * The breaking updater mock.
	 *
	 * @since 2.0.0
	 *
	 * @var WordPoints_PHPUnit_Mock_Breaking_Updater
	 */
	protected $updater;

	/**
	 * The HTTP requests caught.
	 *
	 * Each of the requests has the following keys:
	 * {
	 *    var string $url     The URL for the request.
	 *    var array  $request The request arguments.
	 * }
	 *
	 * @since 2.0.0
	 *
	 * @var array $http_requests
	 */
	protected $http_requests;

	/**
	 * A function to simulate responses to requests.
	 *
	 * @since 1.0.0
	 *
	 * @var callable|false $http_responder
	 */
	protected $http_responder;

	/**
	 * @since 2.0.0
	 */
	public function setUp() {

		parent::setUp();

		$this->updater = new WordPoints_PHPUnit_Mock_Breaking_Updater(
			'wordpoints_breaking'
			, 'breaking'
		);

		$this->http_requests = array();

		add_filter( 'pre_http_request', array( $this, 'http_request_listner' ), 10, 3 );

		add_filter( 'wordpoints_extensions_dir', 'wordpointstests_modules_dir' );
	}

	/**
	 * Mock responses to HTTP requests coming from WordPress.
	 *
	 * @since 2.0.0
	 *
	 * @WordPress\filter pre_http_request Added by self::setUp().
	 *
	 * @param mixed  $preempt Response to the request, or false to not preempt it.
	 * @param array  $request The request arguments.
	 * @param string $url     The URL the request is being made to.
	 *
	 * @return mixed A response, or false.
	 */
	public function http_request_listner( $preempt, $request, $url ) {

			$this->http_requests[] = array( 'url' => $url, 'request' => $request );

		if ( $this->http_responder ) {
			$preempt = call_user_func( $this->http_responder, $request, $url );
		}

		return $preempt;
	}

	//
	// Tests.
	//

	/**
	 * Test is_network_installed().
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::is_network_installed
	 */
	public function test_is_network_installed() {

		$this->updater->network_wide = false;
		$this->assertFalse( $this->updater->is_network_installed() );

		$this->updater->network_wide = true;
		$this->assertTrue( $this->updater->is_network_installed() );
	}

	/**
	 * Test that maintenance mode is enabled by before_update().
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::before_update
	 */
	public function test_before_update_enables_maintenance_mode() {

		$this->mock_filesystem();

		$this->mock_fs->mkdir_p( ABSPATH );

		$this->assertFalse( $this->mock_fs->exists( ABSPATH . '.maintenance' ) );

		$this->updater->before_update();

		$this->assertTrue( $this->mock_fs->exists( ABSPATH . '.maintenance' ) );
	}

	/**
	 * Test that before_update() still works when unable to access the filesystem.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::before_update
	 */
	public function test_before_update_no_filesystem() {

		add_filter( 'filesystem_method', '__return_false' );

		$this->updater->before_update();
	}

	/**
	 * Test that maintenance mode is disabled by after_update().
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::after_update
	 */
	public function test_after_update_disables_maintenance_mode() {

		$this->mock_filesystem();

		$this->mock_fs->mkdir_p( ABSPATH );
		$this->mock_fs->add_file( ABSPATH . '.maintenance' );

		$this->assertTrue( $this->mock_fs->exists( ABSPATH . '.maintenance' ) );

		$this->updater->after_update();

		$this->assertFalse( $this->mock_fs->exists( ABSPATH . '.maintenance' ) );
	}

	/**
	 * Test that the list of breaking modules is saved by after_update().
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::after_update
	 */
	public function test_after_update_saves_breaking_modules() {

		$this->updater->network_wide = true;
		$this->updater->checked_modules = array( 'test-1' => true, 'test-2' => false );

		$this->updater->after_update();

		$this->assertSame(
			array( 'test-2' )
			, get_site_option( 'wordpoints_breaking_deactivated_modules' )
		);
	}

	/**
	 * Test that the list of breaking modules isn't if not network-wide.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::after_update
	 */
	public function test_after_update_saves_breaking_modules_not_network_wide() {

		$this->updater->checked_modules = array( 'test-1' => true, 'test-2' => false );

		$this->updater->after_update();

		$this->assertFalse(
			get_site_option( 'wordpoints_breaking_deactivated_modules' )
		);
	}

	/**
	 * Test enabling maintenance mode.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::maintenance_mode
	 */
	public function test_maintenance_mode_enable() {

		$this->mock_filesystem();

		$this->mock_fs->mkdir_p( ABSPATH );

		$this->assertFalse( $this->mock_fs->exists( ABSPATH . '.maintenance' ) );

		$this->updater->maintenance_mode();

		$this->assertTrue( $this->mock_fs->exists( ABSPATH . '.maintenance' ) );
	}

	/**
	 * Test enabling maintenance mode when unable to connect to the filesystem.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::maintenance_mode
	 */
	public function test_maintenance_mode_enable_no_filesystem() {

		$this->mock_filesystem();

		$this->mock_fs->mkdir_p( ABSPATH );

		$this->assertFalse( $this->mock_fs->exists( ABSPATH . '.maintenance' ) );

		unset( $GLOBALS['wp_filesystem'] );

		$this->updater->maintenance_mode();

		$this->assertFalse( $this->mock_fs->exists( ABSPATH . '.maintenance' ) );
	}

	/**
	 * Test disabling maintenance mode.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::maintenance_mode
	 */
	public function test_maintenance_mode_disable() {

		$this->mock_filesystem();

		$this->mock_fs->mkdir_p( ABSPATH );
		$this->mock_fs->add_file( ABSPATH . '.maintenance' );

		$this->assertTrue( $this->mock_fs->exists( ABSPATH . '.maintenance' ) );

		$this->updater->maintenance_mode( false );

		$this->assertFalse( $this->mock_fs->exists( ABSPATH . '.maintenance' ) );
	}

	/**
	 * Test disabling maintenance mode when not connected to the filesystem.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::maintenance_mode
	 */
	public function test_maintenance_mode_disable_no_filesystem() {

		$this->mock_filesystem();

		$this->mock_fs->mkdir_p( ABSPATH );
		$this->mock_fs->add_file( ABSPATH . '.maintenance' );

		$this->assertTrue( $this->mock_fs->exists( ABSPATH . '.maintenance' ) );

		unset( $GLOBALS['wp_filesystem'] );

		$this->updater->maintenance_mode( false );

		$this->assertTrue( $this->mock_fs->exists( ABSPATH . '.maintenance' ) );
	}

	/**
	 * Test deactivating modules.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::deactivate_modules
	 */
	public function test_deactivate_modules() {

		update_option( 'wordpoints_active_modules', array( 'test1', 'test2' ) );

		$this->updater->deactivate_modules( array( 'test2' ) );

		$this->assertSame(
			array( 'test2' )
			, get_option( 'wordpoints_incompatible_modules' )
		);

		$this->assertSame(
			array( 'test1' )
			, get_option( 'wordpoints_active_modules' )
		);
	}

	/**
	 * Test deactivating network-wide modules.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::deactivate_modules
	 */
	public function test_deactivate_modules_network_wide() {

		$this->updater->context = 'network';

		update_site_option(
			'wordpoints_sitewide_active_modules'
			, array( 'test1' => 2343, 'test2' => 484983 )
		);

		$this->updater->deactivate_modules( array( 'test2' ) );

		$this->assertSame(
			array( 'test2' )
			, get_site_option( 'wordpoints_incompatible_modules' )
		);

		$this->assertSame(
			array( 'test1' => 2343 )
			, get_site_option( 'wordpoints_sitewide_active_modules' )
		);
	}

	/**
	 * Test checking a module.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::check_module
	 */
	public function test_check_module() {

		$this->http_responder = array( $this, 'check_module_success_response' );

		$this->assertFalse( get_option( 'wordpoints_module_check_rand_str' ) );
		$this->assertFalse( get_option( 'wordpoints_module_check_nonce' ) );

		$rand_str_update_filter = $this->mock_filter(
			'pre_update_option_wordpoints_module_check_rand_str'
		);

		$nonce_update_filter = $this->mock_filter(
			'pre_update_option_wordpoints_module_check_nonce'
		);

		$result = $this->updater->check_module( 'test' );

		$this->assertTrue( $result );

		$this->assertSame( 1, $rand_str_update_filter->call_count );
		$this->assertSame( 1, $nonce_update_filter->call_count );

		$this->assertFalse( get_option( 'wordpoints_module_check_rand_str' ) );
		$this->assertFalse( get_option( 'wordpoints_module_check_nonce' ) );

		$this->assertCount( 1, $this->http_requests );
		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=test&wordpoints_module_check=%s'
			, $this->http_requests[0]['url']
		);
	}

	/**
	 * Test checking a network-active module.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::check_module
	 */
	public function test_check_module_network_wide() {

		$this->updater->context = 'network';

		$this->http_responder = array( $this, 'check_module_success_response' );

		$this->assertFalse( get_site_option( 'wordpoints_module_check_rand_str' ) );
		$this->assertFalse( get_site_option( 'wordpoints_module_check_nonce' ) );

		$rand_str_update_filter = $this->mock_filter(
			'pre_update_site_option_wordpoints_module_check_rand_str'
		);

		$nonce_update_filter = $this->mock_filter(
			'pre_update_site_option_wordpoints_module_check_nonce'
		);

		$result = $this->updater->check_module( 'test' );

		$this->assertTrue( $result );

		$this->assertSame( 1, $rand_str_update_filter->call_count );
		$this->assertSame( 1, $nonce_update_filter->call_count );

		$this->assertFalse( get_site_option( 'wordpoints_module_check_rand_str' ) );
		$this->assertFalse( get_site_option( 'wordpoints_module_check_nonce' ) );

		$this->assertCount( 1, $this->http_requests );
		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=test&wordpoints_module_check=%s'
			, $this->http_requests[0]['url']
		);
	}

	/**
	 * Test checking a broken module.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::check_module
	 */
	public function test_check_broken_module() {

		$this->http_responder = array( $this, 'check_module_failure_response' );

		$this->assertFalse( get_option( 'wordpoints_module_check_rand_str' ) );
		$this->assertFalse( get_option( 'wordpoints_module_check_nonce' ) );

		$rand_str_update_filter = $this->mock_filter(
			'pre_update_option_wordpoints_module_check_rand_str'
		);

		$nonce_update_filter = $this->mock_filter(
			'pre_update_option_wordpoints_module_check_nonce'
		);

		$result = $this->updater->check_module( 'broken' );

		$this->assertFalse( $result );

		$this->assertSame( 1, $rand_str_update_filter->call_count );
		$this->assertSame( 1, $nonce_update_filter->call_count );

		$this->assertFalse( get_option( 'wordpoints_module_check_rand_str' ) );
		$this->assertFalse( get_option( 'wordpoints_module_check_nonce' ) );

		$this->assertCount( 1, $this->http_requests );
		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=broken&wordpoints_module_check=%s'
			, $this->http_requests[0]['url']
		);
	}

	/**
	 * Test a request failure when checking a module.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::check_module
	 */
	public function test_check_module_request_failure() {

		$filter = new WordPoints_PHPUnit_Mock_Filter( new WP_Error );
		$this->http_responder = array( $filter, 'filter' );

		$this->assertFalse( get_option( 'wordpoints_module_check_rand_str' ) );
		$this->assertFalse( get_option( 'wordpoints_module_check_nonce' ) );

		$rand_str_update_filter = $this->mock_filter(
			'pre_update_option_wordpoints_module_check_rand_str'
		);

		$nonce_update_filter = $this->mock_filter(
			'pre_update_option_wordpoints_module_check_nonce'
		);

		$result = $this->updater->check_module( 'test' );

		$this->assertWPError( $result );

		$this->assertSame( 1, $rand_str_update_filter->call_count );
		$this->assertSame( 1, $nonce_update_filter->call_count );

		$this->assertFalse( get_option( 'wordpoints_module_check_rand_str' ) );
		$this->assertFalse( get_option( 'wordpoints_module_check_nonce' ) );

		$this->assertCount( 1, $this->http_requests );
		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=test&wordpoints_module_check=%s'
			, $this->http_requests[0]['url']
		);
	}

	/**
	 * Test checking modules.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::check_modules
	 * @covers WordPoints_Breaking_Updater::validate_modules
	 */
	public function test_check_modules() {

		$this->http_responder = array( $this, 'check_module_success_response' );

		$this->updater->check_modules( array( 'test-3.php' ) );

		$this->assertCount( 1, $this->http_requests );
		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=test-3.php&wordpoints_module_check=%s'
			, $this->http_requests[0]['url']
		);
	}

	/**
	 * Test checking a module that has already been checked.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::check_modules
	 * @covers WordPoints_Breaking_Updater::validate_modules
	 */
	public function test_check_modules_already_checked() {

		$this->http_responder = array( $this, 'check_module_success_response' );

		$this->updater->check_modules( array( 'test-3.php' ) );

		$this->assertCount( 1, $this->http_requests );

		$this->updater->check_modules( array( 'test-3.php' ) );

		$this->assertCount( 1, $this->http_requests );
	}

	/**
	 * Test checking a module that has already been checked and is incompatible.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::check_modules
	 * @covers WordPoints_Breaking_Updater::validate_modules
	 */
	public function test_check_modules_already_checked_incompatible() {

		update_option( 'wordpoints_active_modules', array( 'test-3.php' ) );

		$this->http_responder = array( $this, 'check_module_failure_response' );

		$this->updater->check_modules( array( 'test-3.php' ) );

		$this->assertCount( 1, $this->http_requests );
		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=test-3.php&wordpoints_module_check=%s'
			, $this->http_requests[0]['url']
		);

		$this->assertSame(
			array( 'test-3.php' )
			, get_option( 'wordpoints_incompatible_modules' )
		);

		$this->assertSame(
			array()
			, get_option( 'wordpoints_active_modules' )
		);

		update_option( 'wordpoints_active_modules', array( 'test-3.php' ) );
		delete_option( 'wordpoints_incompatible_modules' );

		$this->http_responder = array( $this, 'check_module_failure_response' );

		$this->updater->check_modules( array( 'test-3.php' ) );

		// There shouldn't have been a second request made.
		$this->assertCount( 1, $this->http_requests );

		$this->assertSame(
			array()
			, get_option( 'wordpoints_active_modules' )
		);

		$this->assertSame(
			array( 'test-3.php' )
			, get_option( 'wordpoints_incompatible_modules' )
		);
	}

	/**
	 * Test checking a module that is invalid.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::check_modules
	 * @covers WordPoints_Breaking_Updater::validate_modules
	 */
	public function test_check_modules_invalid() {

		$this->http_responder = array( $this, 'check_module_success_response' );

		$this->updater->check_modules( array( 'invalid' ) );

		$this->assertCount( 0, $this->http_requests );
	}

	/**
	 * Test checking an incompatible module.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::check_modules
	 * @covers WordPoints_Breaking_Updater::validate_modules
	 */
	public function test_check_modules_incompatible() {

		$this->http_responder = array( $this, 'check_module_failure_response' );

		$this->updater->check_modules( array( 'test-3.php' ) );

		$this->assertCount( 1, $this->http_requests );
		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=test-3.php&wordpoints_module_check=%s'
			, $this->http_requests[0]['url']
		);

		$this->assertSame(
			array( 'test-3.php' )
			, get_option( 'wordpoints_incompatible_modules' )
		);
	}

	/**
	 * Test checking a multiple incompatible modules.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::check_modules
	 * @covers WordPoints_Breaking_Updater::validate_modules
	 */
	public function test_check_modules_multiple_incompatible() {

		$this->http_responder = array( $this, 'check_module_failure_response' );

		$this->updater->check_modules( array( 'test-3.php', 'test-4/test-4.php' ) );

		$this->assertCount( 3, $this->http_requests );

		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=test-3.php%2Ctest-4%2Ftest-4.php&wordpoints_module_check=%s'
			, $this->http_requests[0]['url']
		);

		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=test-3.php&wordpoints_module_check=%s'
			, $this->http_requests[1]['url']
		);

		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=test-4%2Ftest-4.php&wordpoints_module_check=%s'
			, $this->http_requests[2]['url']
		);

		$this->assertSame(
			array( 'test-3.php', 'test-4/test-4.php' )
			, get_option( 'wordpoints_incompatible_modules' )
		);
	}

	/**
	 * Test checking multiple modules.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::check_modules
	 * @covers WordPoints_Breaking_Updater::validate_modules
	 */
	public function test_check_modules_multiple() {

		$this->http_responder = array( $this, 'check_module_success_response' );

		$this->updater->check_modules( array( 'test-3.php', 'test-4/test-4.php' ) );

		$this->assertCount( 1, $this->http_requests );
		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=test-3.php%2Ctest-4%2Ftest-4.php&wordpoints_module_check=%s'
			, $this->http_requests[0]['url']
		);
	}

	/**
	 * Test checking network modules.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::update_network_to_breaking
	 */
	public function test_update_network_to_breaking() {

		update_site_option(
			'wordpoints_sitewide_active_modules'
			, array( 'test-3.php' => 2343, 'test-4/test-4.php' => 484983 )
		);

		$this->http_responder = array( $this, 'check_module_success_response' );

		$this->updater->network_wide = true;

		$this->updater->update_network_to_breaking();

		$this->assertSame(
			array( 'test-3.php' => 2343, 'test-4/test-4.php' => 484983 )
			, get_site_option( 'wordpoints_sitewide_active_modules' )
		);

		$this->assertCount( 1, $this->http_requests );
		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=test-3.php%2Ctest-4%2Ftest-4.php&wordpoints_module_check=%s'
			, $this->http_requests[0]['url']
		);
	}

	/**
	 * Test that network modules aren't checked when WordPoints isn't network-wide.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::update_network_to_breaking
	 */
	public function test_update_network_to_breaking_not_network_wide() {

		update_site_option(
			'wordpoints_sitewide_active_modules'
			, array( 'test-3.php' => 2343, 'test-4/test-4.php' => 484983 )
		);

		$this->http_responder = array( $this, 'check_module_success_response' );

		$this->updater->update_network_to_breaking();

		$this->assertSame(
			array( 'test-3.php' => 2343, 'test-4/test-4.php' => 484983 )
			, get_site_option( 'wordpoints_sitewide_active_modules' )
		);

		$this->assertCount( 0, $this->http_requests );
	}

	/**
	 * Test checking modules on multisite.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::update_site_to_breaking
	 */
	public function test_update_site_to_breaking() {

		update_option(
			'wordpoints_active_modules'
			, array( 'test-3.php', 'test-4/test-4.php' )
		);

		$this->http_responder = array( $this, 'check_module_success_response' );

		$this->updater->update_site_to_breaking();

		$this->assertSame(
			array( 'test-3.php', 'test-4/test-4.php' )
			, get_option( 'wordpoints_active_modules' )
		);

		$this->assertCount( 1, $this->http_requests );
		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=test-3.php%2Ctest-4%2Ftest-4.php&wordpoints_module_check=%s'
			, $this->http_requests[0]['url']
		);
	}

	/**
	 * Test checking modules on a single site.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Breaking_Updater::update_single_to_breaking
	 */
	public function test_update_single_to_breaking() {

		update_option(
			'wordpoints_active_modules'
			, array( 'test-3.php', 'test-4/test-4.php' )
		);

		$this->http_responder = array( $this, 'check_module_success_response' );

		$this->updater->update_single_to_breaking();

		$this->assertSame(
			array( 'test-3.php', 'test-4/test-4.php' )
			, get_option( 'wordpoints_active_modules' )
		);

		$this->assertCount( 1, $this->http_requests );
		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=test-3.php%2Ctest-4%2Ftest-4.php&wordpoints_module_check=%s'
			, $this->http_requests[0]['url']
		);
	}

	//
	// Helpers.
	//

	/**
	 * Simulates a success response to a module check request.
	 *
	 * @since 2.0.0
	 *
	 * @return array The mock response.
	 */
	protected function check_module_success_response() {

		if ( 'network' === $this->updater->context ) {
			$rand_str = get_site_option( 'wordpoints_module_check_rand_str' );
		} else {
			$rand_str = get_option( 'wordpoints_module_check_rand_str' );
		}

		return array(
			'body' => 'Modules screen.' . $rand_str,
		);
	}

	/**
	 * Simulates a failure response to a module check request.
	 *
	 * @since 2.0.0
	 *
	 * @return array The mock response.
	 */
	protected function check_module_failure_response() {

		return array(
			'body' => 'WSoD or half-loaded screen.',
		);
	}
}

// EOF
