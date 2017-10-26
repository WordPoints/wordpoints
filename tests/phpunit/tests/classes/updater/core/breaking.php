<?php

/**
 * Test case for WordPoints_Updater_Core_Breaking.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Updater_Core_Breaking.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Updater_Core_Breaking
 */
class WordPoints_Updater_Core_Breaking_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * The HTTP requests caught.
	 *
	 * Each of the requests has the following keys:
	 * {
	 *    var string $url     The URL for the request.
	 *    var array  $request The request arguments.
	 * }
	 *
	 * @since 2.4.0
	 *
	 * @var array $http_requests
	 */
	protected $http_requests;

	/**
	 * A function to simulate responses to requests.
	 *
	 * @since 2.4.0
	 *
	 * @var callable|false $http_responder
	 */
	protected $http_responder;

	/**
	 * Whether the network-wide extensions are being tested.
	 *
	 * @since 2.4.0
	 *
	 * @var bool
	 */
	protected $network_wide = false;

	/**
	 * @since 2.4.0
	 */
	public function setUp() {

		parent::setUp();

		$this->http_requests = array();

		add_filter( 'pre_http_request', array( $this, 'http_request_listener' ), 10, 3 );

		add_filter( 'wordpoints_extensions_dir', 'wordpoints_phpunit_extensions_dir' );
	}

	/**
	 * Mock responses to HTTP requests coming from WordPress.
	 *
	 * @since 2.4.0
	 *
	 * @WordPress\filter pre_http_request Added by self::setUp().
	 *
	 * @param mixed  $preempt Response to the request, or false to not preempt it.
	 * @param array  $request The request arguments.
	 * @param string $url     The URL the request is being made to.
	 *
	 * @return mixed A response, or false.
	 */
	public function http_request_listener( $preempt, $request, $url ) {

		$this->http_requests[] = array( 'url' => $url, 'request' => $request );

		if ( $this->http_responder ) {
			$preempt = call_user_func( $this->http_responder, $request, $url );
		}

		return $preempt;
	}

	/**
	 * Simulates a success response to an extension check request.
	 *
	 * @since 2.4.0
	 *
	 * @return array The mock response.
	 */
	protected function check_extension_success_response() {

		if ( $this->network_wide ) {
			$rand_str = get_site_option( 'wordpoints_module_check_rand_str' );
		} else {
			$rand_str = get_option( 'wordpoints_module_check_rand_str' );
		}

		return array(
			'body' => 'Extensions screen.' . $rand_str,
		);
	}

	/**
	 * Simulates a failure response to an extension check request.
	 *
	 * @since 2.4.0
	 *
	 * @return array The mock response.
	 */
	protected function check_extension_failure_response() {

		return array(
			'body' => 'WSoD or half-loaded screen.',
		);
	}

	//
	// Tests.
	//

	/**
	 * Tests that maintenance mode is enabled during the update.
	 *
	 * @since 2.4.0
	 */
	public function test_before_enables_maintenance_mode() {

		$this->mock_filesystem();

		$this->mock_fs->mkdir_p( ABSPATH );

		$this->assertFalse( $this->mock_fs->exists( ABSPATH . '.maintenance' ) );

		$updater = $this->createPartialMock(
			'WordPoints_Updater_Core_Breaking'
			, array( 'after' )
		);

		$updater->run();

		$this->assertTrue( $this->mock_fs->exists( ABSPATH . '.maintenance' ) );
	}

	/**
	 * Tests that before() doesn't throw errors when unable to access the filesystem.
	 *
	 * @since 2.4.0
	 */
	public function test_before_no_filesystem() {

		$this->mock_filesystem();

		$this->mock_fs->mkdir_p( ABSPATH );

		add_filter( 'filesystem_method', '__return_false' );

		$updater = $this->createPartialMock(
			'WordPoints_Updater_Core_Breaking'
			, array( 'after' )
		);

		$updater->run();

		$this->assertFalse( $this->mock_fs->exists( ABSPATH . '.maintenance' ) );
	}

	/**
	 * Tests that maintenance mode is disabled by after().
	 *
	 * @since 2.4.0
	 */
	public function test_after_disables_maintenance_mode() {

		$this->mock_filesystem();

		$this->mock_fs->mkdir_p( ABSPATH );

		$updater = new WordPoints_Updater_Core_Breaking();
		$updater->run();

		$this->assertFalse( $this->mock_fs->exists( ABSPATH . '.maintenance' ) );
	}

	/**
	 * Tests that the list of breaking extensions is saved by after().
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_after_saves_breaking_extensions() {

		$updater = $this->getMockBuilder( 'WordPoints_Updater_Core_Breaking' )
			->setMethods( array( 'check_extension' ) )
			->getMock();

		$updater->method( 'check_extension' )
			->willReturnOnConsecutiveCalls( false, true, false );

		update_site_option(
			'wordpoints_sitewide_active_modules'
			, array( 'test-3.php' => 1, 'test-6/main-file.php' => 1 )
		);

		$updater->run();

		$this->assertSame(
			array( 'test-6/main-file.php' )
			, get_site_option( 'wordpoints_breaking_deactivated_modules' )
		);
	}

	/**
	 * Tests that the list of breaking extensions isn't saved if not network wide.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPoints !network-active
	 */
	public function test_after_saves_breaking_extensions_not_network_wide() {

		$updater = $this->getMockBuilder( 'WordPoints_Updater_Core_Breaking' )
			->setMethods( array( 'check_extension' ) )
			->getMock();

		$updater->method( 'check_extension' )
			->willReturnOnConsecutiveCalls( false, true, false );

		update_option(
			'wordpoints_active_modules'
			, array( 'test-3.php', 'test-6/main-file.php' )
		);

		$updater->run();

		$this->assertFalse(
			get_site_option( 'wordpoints_breaking_deactivated_modules' )
		);
	}

	/**
	 * Tests deactivating the incompatible extensions.
	 *
	 * @since 2.4.0
	 */
	public function test_deactivate_extensions() {

		$updater = $this->getMockBuilder( 'WordPoints_Updater_Core_Breaking' )
			->setMethods( array( 'check_extension' ) )
			->getMock();

		$updater->method( 'check_extension' )
			->willReturnOnConsecutiveCalls( false, true, false );

		update_option(
			'wordpoints_active_modules'
			, array( 'test-3.php', 'test-6/main-file.php' )
		);

		$updater->run();

		$this->assertSame(
			array( 'test-6/main-file.php' )
			, get_option( 'wordpoints_incompatible_modules' )
		);

		$this->assertSame(
			array( 'test-3.php' )
			, get_option( 'wordpoints_active_modules' )
		);
	}

	/**
	 * Tests deactivating the incompatible extensions that are network active.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_deactivate_extensions_network_wide() {

		$updater = $this->getMockBuilder( 'WordPoints_Updater_Core_Breaking' )
			->setMethods( array( 'check_extension' ) )
			->getMock();

		$updater->method( 'check_extension' )
			->willReturnOnConsecutiveCalls( false, true, false );

		update_site_option(
			'wordpoints_sitewide_active_modules'
			, array( 'test-3.php' => 1, 'test-6/main-file.php' => 8 )
		);

		$updater->run();

		$this->assertSame(
			array( 'test-6/main-file.php' )
			, get_site_option( 'wordpoints_incompatible_modules' )
		);

		$this->assertSame(
			array( 'test-3.php' => 1 )
			, get_site_option( 'wordpoints_sitewide_active_modules' )
		);
	}

	/**
	 * Tests checking an extension.
	 *
	 * @since 2.4.0
	 */
	public function test_check_extension() {

		$this->http_responder = array( $this, 'check_extension_success_response' );

		$this->assertFalse( get_option( 'wordpoints_module_check_rand_str' ) );
		$this->assertFalse( get_option( 'wordpoints_module_check_nonce' ) );

		$rand_str_update_filter = $this->mock_filter(
			'pre_update_option_wordpoints_module_check_rand_str'
		);

		$nonce_update_filter = $this->mock_filter(
			'pre_update_option_wordpoints_module_check_nonce'
		);

		update_option( 'wordpoints_active_modules', array( 'test-3.php' ) );

		$updater = new WordPoints_Updater_Core_Breaking();
		$updater->run();

		$this->assertSame( 1, $rand_str_update_filter->call_count );
		$this->assertSame( 1, $nonce_update_filter->call_count );

		$this->assertFalse( get_option( 'wordpoints_module_check_rand_str' ) );
		$this->assertFalse( get_option( 'wordpoints_module_check_nonce' ) );

		$this->assertCount( 1, $this->http_requests );
		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=test-3.php&wordpoints_module_check=%s'
			, $this->http_requests[0]['url']
		);
	}

	/**
	 * Tests checking a network-active extension.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_check_extension_network_wide() {

		$this->network_wide = true;

		$this->http_responder = array( $this, 'check_extension_success_response' );

		$this->assertFalse( get_site_option( 'wordpoints_module_check_rand_str' ) );
		$this->assertFalse( get_site_option( 'wordpoints_module_check_nonce' ) );

		$rand_str_update_filter = $this->mock_filter(
			'pre_update_site_option_wordpoints_module_check_rand_str'
		);

		$nonce_update_filter = $this->mock_filter(
			'pre_update_site_option_wordpoints_module_check_nonce'
		);

		update_site_option(
			'wordpoints_sitewide_active_modules'
			, array( 'test-3.php' => 1 )
		);

		$updater = new WordPoints_Updater_Core_Breaking();
		$updater->run();

		$this->assertSame( 1, $rand_str_update_filter->call_count );
		$this->assertSame( 1, $nonce_update_filter->call_count );

		$this->assertFalse( get_site_option( 'wordpoints_module_check_rand_str' ) );
		$this->assertFalse( get_site_option( 'wordpoints_module_check_nonce' ) );

		$this->assertCount( 1, $this->http_requests );
		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=test-3.php&wordpoints_module_check=%s'
			, $this->http_requests[0]['url']
		);
	}

	/**
	 * Tests checking a broken extension.
	 *
	 * @since 2.4.0
	 */
	public function test_check_broken_extension() {

		$this->http_responder = array( $this, 'check_extension_failure_response' );

		$this->assertFalse( get_option( 'wordpoints_module_check_rand_str' ) );
		$this->assertFalse( get_option( 'wordpoints_module_check_nonce' ) );

		$rand_str_update_filter = $this->mock_filter(
			'pre_update_option_wordpoints_module_check_rand_str'
		);

		$nonce_update_filter = $this->mock_filter(
			'pre_update_option_wordpoints_module_check_nonce'
		);

		update_option( 'wordpoints_active_modules', array( 'test-3.php' ) );

		$updater = new WordPoints_Updater_Core_Breaking();
		$updater->run();

		$this->assertSame( 1, $rand_str_update_filter->call_count );
		$this->assertSame( 1, $nonce_update_filter->call_count );

		$this->assertFalse( get_option( 'wordpoints_module_check_rand_str' ) );
		$this->assertFalse( get_option( 'wordpoints_module_check_nonce' ) );

		$this->assertCount( 1, $this->http_requests );
		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=test-3.php&wordpoints_module_check=%s'
			, $this->http_requests[0]['url']
		);
	}

	/**
	 * Tests a request failure when checking an extension.
	 *
	 * @since 2.4.0
	 */
	public function test_check_extension_request_failure() {

		$filter               = new WordPoints_PHPUnit_Mock_Filter( new WP_Error() );
		$this->http_responder = array( $filter, 'filter' );

		$this->assertFalse( get_option( 'wordpoints_module_check_rand_str' ) );
		$this->assertFalse( get_option( 'wordpoints_module_check_nonce' ) );

		$rand_str_update_filter = $this->mock_filter(
			'pre_update_option_wordpoints_module_check_rand_str'
		);

		$nonce_update_filter = $this->mock_filter(
			'pre_update_option_wordpoints_module_check_nonce'
		);

		update_option( 'wordpoints_active_modules', array( 'test-3.php' ) );

		$updater = new WordPoints_Updater_Core_Breaking();
		$updater->run();

		$this->assertSame( 1, $rand_str_update_filter->call_count );
		$this->assertSame( 1, $nonce_update_filter->call_count );

		$this->assertFalse( get_option( 'wordpoints_module_check_rand_str' ) );
		$this->assertFalse( get_option( 'wordpoints_module_check_nonce' ) );

		$this->assertCount( 1, $this->http_requests );
		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=test-3.php&wordpoints_module_check=%s'
			, $this->http_requests[0]['url']
		);
	}

	/**
	 * Tests that each extension is only checked once.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_check_extensions_already_checked() {

		$this->http_responder = array( $this, 'check_extension_success_response' );

		update_option( 'wordpoints_active_modules', array( 'test-3.php' ) );

		update_site_option(
			'wordpoints_sitewide_active_modules'
			, array( 'test-3.php' => 1 )
		);

		$updater = new WordPoints_Updater_Core_Breaking();
		$updater->run();

		$this->assertCount( 1, $this->http_requests );
	}

	/**
	 * Tests that each extension is only checked once.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_check_extensions_already_checked_incompatible() {

		$this->http_responder = array( $this, 'check_extension_failure_response' );

		update_option( 'wordpoints_active_modules', array( 'test-3.php' ) );

		update_site_option(
			'wordpoints_sitewide_active_modules'
			, array( 'test-3.php' => 1 )
		);

		$updater = new WordPoints_Updater_Core_Breaking();
		$updater->run();

		$this->assertCount( 1, $this->http_requests );
	}

	/**
	 * Tests checking an extension that is invalid.
	 *
	 * @since 2.4.0
	 */
	public function test_check_extension_invalid() {

		$this->http_responder = array( $this, 'check_extension_success_response' );

		update_option( 'wordpoints_active_modules', array( 'invalid.php' ) );

		$updater = new WordPoints_Updater_Core_Breaking();
		$updater->run();

		$this->assertCount( 0, $this->http_requests );
	}

	/**
	 * Tests checking an incompatible extension.
	 *
	 * @since 2.4.0
	 */
	public function test_check_extension_incompatible() {

		$this->http_responder = array( $this, 'check_extension_failure_response' );

		update_option( 'wordpoints_active_modules', array( 'test-3.php' ) );

		$updater = new WordPoints_Updater_Core_Breaking();
		$updater->run();

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
	 * Tests checking a multiple incompatible extensions.
	 *
	 * @since 2.4.0
	 */
	public function test_check_extension_multiple_incompatible() {

		$this->http_responder = array( $this, 'check_extension_failure_response' );

		update_option( 'wordpoints_active_modules', array( 'test-3.php', 'test-4/test-4.php' ) );

		$updater = new WordPoints_Updater_Core_Breaking();
		$updater->run();

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
	 * Tests checking multiple extensions.
	 *
	 * @since 2.4.0
	 */
	public function test_check_extensions_multiple() {

		$this->http_responder = array( $this, 'check_extension_success_response' );

		update_option( 'wordpoints_active_modules', array( 'test-3.php', 'test-4/test-4.php' ) );

		$updater = new WordPoints_Updater_Core_Breaking();
		$updater->run();

		$this->assertCount( 1, $this->http_requests );
		$this->assertStringMatchesFormat(
			'%s/wp-admin/admin-ajax.php?action=wordpoints_breaking_module_check&check_module=test-3.php%2Ctest-4%2Ftest-4.php&wordpoints_module_check=%s'
			, $this->http_requests[0]['url']
		);
	}
}

// EOF
