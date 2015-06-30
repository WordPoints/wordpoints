<?php

/**
 * Test case for the WordPoints notices functions.
 *
 * @package WordPoints\Tests
 * @since 2.0.0
 */

/**
 * Tests the admin notices functions.
 *
 * @since 2.0.0
 */
class WordPoints_Admin_Notices_Test extends WordPoints_UnitTestCase {

	/**
	 * Whether the Ajax callback functions have been included yet.
	 *
	 * @since 2.0.0
	 *
	 * @var bool
	 */
	private static $included_functions = false;

	/**
	 * @since 2.0.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		if ( ! self::$included_functions ) {

			/**
			 * Admin-side functions.
			 *
			 * @since 2.0.0
			 */
			require_once( WORDPOINTS_DIR . '/admin/admin.php' );

			self::$included_functions = true;
		}
	}

	/**
	 * @since 2.0.0
	 */
	public function setUp() {

		parent::setUp();

		unset( $GLOBALS['current_screen'] );
	}

	/**
	 * Test that it displays the notice message.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_show_admin_message
	 */
	public function test_displays_notice() {

		$message = 'Testing.';

		ob_start();
		wordpoints_show_admin_message( $message );
		$notice = ob_get_clean();

		$this->assertStringMatchesFormat( "%a{$message}%a", $notice );

		$this->assertWordPointsAdminNotice( $notice );
	}

	/**
	 * Test that it displays an update notice by default.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_show_admin_message
	 */
	public function test_displays_update_notice() {

		$message = 'Testing.';

		ob_start();
		wordpoints_show_admin_message( $message );
		$notice = ob_get_clean();

		$this->assertStringMatchesFormat( "%a{$message}%a", $notice );

		$this->assertWordPointsAdminNotice( $notice, array( 'type' => 'updated' ) );
	}

	/**
	 * Test that it can display an error notice.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_show_admin_message
	 */
	public function test_displays_error_notice() {

		$message = 'Testing.';

		ob_start();
		wordpoints_show_admin_message( $message, 'error' );
		$notice = ob_get_clean();

		$this->assertStringMatchesFormat( "%a{$message}%a", $notice );

		$this->assertWordPointsAdminNotice( $notice, array( 'type' => 'error' ) );
	}

	/**
	 * Test that it can display a dismissible notice.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_show_admin_message
	 */
	public function test_displays_notice_dismissible() {

		$message = 'Testing.';

		ob_start();
		wordpoints_show_admin_message(
			$message
			, 'updated'
			, array( 'dismissible' => true )
		);
		$notice = ob_get_clean();

		$this->assertStringMatchesFormat( "%a{$message}%a", $notice );

		$this->assertWordPointsAdminNotice( $notice, array( 'dismissible' => true ) );
	}

	/**
	 * Test that it can display a dismissible notice for a particular option.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_show_admin_message
	 */
	public function test_displays_notice_dismissible_with_option() {

		$message = 'Testing.';

		ob_start();
		wordpoints_show_admin_message(
			$message
			, 'updated'
			, array( 'dismissible' => true, 'option' => 'test' )
		);
		$notice = ob_get_clean();

		$this->assertStringMatchesFormat( "%a{$message}%a", $notice );

		$this->assertWordPointsAdminNotice(
			$notice
			, array( 'dismissible' => true, 'option' => 'test' )
		);
	}

	/**
	 * Test that it displays a dismissible notice when the "dismissable" arg is used.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_show_admin_message
	 *
	 * @expectedDeprecated wordpoints_show_admin_message
	 */
	public function test_displays_notice_dismissable() {

		$message = 'Testing.';

		ob_start();
		wordpoints_show_admin_message(
			$message
			, 'updated'
			, array( 'dismissable' => true )
		);
		$notice = ob_get_clean();

		$this->assertStringMatchesFormat( "%a{$message}%a", $notice );

		$this->assertWordPointsAdminNotice( $notice, array( 'dismissible' => true ) );
	}

	/**
	 * Test that it displays an error notice message.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_show_admin_error
	 */
	public function test_error_displays_notice() {

		$message = 'Testing.';

		ob_start();
		wordpoints_show_admin_error( $message );
		$notice = ob_get_clean();

		$this->assertStringMatchesFormat( "%a{$message}%a", $notice );

		$this->assertWordPointsAdminNotice( $notice, array( 'type' => 'error' ) );
	}

	/**
	 * Test that it can display a dismissible notice.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_show_admin_error
	 */
	public function test_error_displays_notice_dismissible() {

		$message = 'Testing.';

		ob_start();
		wordpoints_show_admin_error(
			$message
			, array( 'dismissible' => true )
		);
		$notice = ob_get_clean();

		$this->assertStringMatchesFormat( "%a{$message}%a", $notice );

		$this->assertWordPointsAdminNotice(
			$notice
			, array( 'dismissible' => true, 'type' => 'error' )
		);
	}

	/**
	 * Test that it deletes the option when the dismiss form has been submitted.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_admin_notices
	 *
	 * @requires WordPoints !network-active
	 */
	public function test_option_deleted() {

		update_option( 'test', 'test' );

		$_POST['wordpoints_notice'] = 'test';
		$_POST['_wpnonce'] = wp_create_nonce(
			"wordpoints_dismiss_notice-{$_POST['wordpoints_notice']}"
		);

		wordpoints_admin_notices();

		$this->assertFalse( get_option( 'test' ) );
	}

	/**
	 * Test that it deletes the a network option when WordPoints is network-active.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_admin_notices
	 *
	 * @requires WordPoints network-active
	 */
	public function test_option_deleted_network_wide() {

		update_site_option( 'test', 'test' );

		$_POST['wordpoints_notice'] = 'test';
		$_POST['_wpnonce'] = wp_create_nonce(
			"wordpoints_dismiss_notice-{$_POST['wordpoints_notice']}"
		);

		wordpoints_admin_notices();

		$this->assertFalse( get_site_option( 'test' ) );
	}

	/**
	 * Test that a valid nonce must be present for the option to be deleted.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_admin_notices
	 *
	 * @requires WordPoints !network-active
	 */
	public function test_option_not_deleted_without_nonce() {

		update_option( 'test', 'test' );

		$_POST['wordpoints_notice'] = 'test';

		wordpoints_admin_notices();

		$this->assertEquals( 'test', get_option( 'test' ) );
	}

	/**
	 * Test that it deletes the a network option when WordPoints is network-active.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_admin_notices
	 *
	 * @requires WordPoints network-active
	 */
	public function test_incompatible_modules_option_deleted_network_wide() {

		update_option( 'wordpoints_incompatible_modules', 'test' );

		$_POST['wordpoints_notice'] = 'wordpoints_incompatible_modules';
		$_POST['_wpnonce'] = wp_create_nonce(
			"wordpoints_dismiss_notice-{$_POST['wordpoints_notice']}"
		);

		wordpoints_admin_notices();

		$this->assertFalse( get_option( 'wordpoints_incompatible_modules' ) );
	}

	/**
	 * Test that the breaking deactivated modules notice is displayed.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_admin_notices
	 *
	 * @requires WordPoints network-active
	 */
	public function test_breaking_deactivated_modules_notice() {

		update_site_option(
			'wordpoints_breaking_deactivated_modules'
			, array( 'test-1.php', 'test-2/test-2.php' )
		);

		$this->give_current_user_caps( 'activate_wordpoints_modules' );

		$GLOBALS['current_screen'] = WP_Screen::get( 'test-network' );

		$this->assertAdminNoticeDisplayedForOption(
			'wordpoints_breaking_deactivated_modules'
		);
	}

	/**
	 * Test that it isn't displayed if the user doesn't have the required caps.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_admin_notices
	 *
	 * @requires WordPoints network-active
	 */
	public function test_breaking_deactivated_modules_notice_user_caps() {

		update_site_option(
			'wordpoints_breaking_deactivated_modules'
			, array( 'test-1.php', 'test-2/test-2.php' )
		);

		$GLOBALS['current_screen'] = WP_Screen::get( 'test-network' );

		$this->assertNoAdminNoticesDisplayed();
	}

	/**
	 * Test that it isn't displayed if not in the network admin.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_admin_notices
	 *
	 * @requires WordPoints network-active
	 */
	public function test_breaking_deactivated_modules_notice_not_network_admin() {

		update_site_option(
			'wordpoints_breaking_deactivated_modules'
			, array( 'test-1.php', 'test-2/test-2.php' )
		);

		$this->give_current_user_caps( 'activate_wordpoints_modules' );

		$this->assertNoAdminNoticesDisplayed();
	}

	/**
	 * Test that the incompatible modules notice is displayed.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_admin_notices
	 *
	 * @requires WordPoints network-active
	 */
	public function test_incompatible_modules_notice() {

		update_site_option(
			'wordpoints_incompatible_modules'
			, array( 'test-1.php', 'test-2/test-2.php' )
		);

		$this->give_current_user_caps( 'activate_wordpoints_modules' );

		$GLOBALS['current_screen'] = WP_Screen::get( 'test-network' );

		$this->assertAdminNoticeDisplayedForOption(
			'wordpoints_incompatible_modules'
		);
	}

	/**
	 * Test that it isn't displayed if the user doesn't have the required caps.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_admin_notices
	 *
	 * @requires WordPoints network-active
	 */
	public function test_incompatible_modules_notice_user_caps() {

		update_site_option(
			'wordpoints_incompatible_modules'
			, array( 'test-1.php', 'test-2/test-2.php' )
		);

		$GLOBALS['current_screen'] = WP_Screen::get( 'test-network' );

		$this->assertNoAdminNoticesDisplayed();
	}

	/**
	 * Test that it isn't displayed if not in the network admin.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_admin_notices
	 *
	 * @requires WordPoints network-active
	 */
	public function test_incompatible_modules_notice_not_network_admin() {

		update_site_option(
			'wordpoints_incompatible_modules'
			, array( 'test-1.php', 'test-2/test-2.php' )
		);

		$this->give_current_user_caps( 'activate_wordpoints_modules' );

		$this->assertNoAdminNoticesDisplayed();
	}

	/**
	 * Test that the incompatible modules notice is displayed.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_admin_notices
	 */
	public function test_incompatible_modules_notice_non_network() {

		update_option(
			'wordpoints_incompatible_modules'
			, array( 'test-1.php', 'test-2/test-2.php' )
		);

		$this->give_current_user_caps( 'activate_wordpoints_modules' );

		$this->assertAdminNoticeDisplayedForOption(
			'wordpoints_incompatible_modules'
		);
	}

	/**
	 * Test that it isn't displayed if the user doesn't have the required caps.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_admin_notices
	 *
	 * @requires WordPoints network-active
	 */
	public function test_incompatible_modules_notice_user_caps_non_network() {

		update_option(
			'wordpoints_incompatible_modules'
			, array( 'test-1.php', 'test-2/test-2.php' )
		);

		$this->assertNoAdminNoticesDisplayed();
	}

	/**
	 * Test that it isn't displayed if in the network admin.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_admin_notices
	 *
	 * @requires WordPoints network-active
	 */
	public function test_incompatible_modules_notice_network_admin_non_network() {

		update_option(
			'wordpoints_incompatible_modules'
			, array( 'test-1.php', 'test-2/test-2.php' )
		);

		$this->give_current_user_caps( 'activate_wordpoints_modules' );

		$GLOBALS['current_screen'] = WP_Screen::get( 'test-network' );

		$this->assertNoAdminNoticesDisplayed();
	}

	//
	// Helpers.
	//

	/**
	 * Asserts that no admin notices are displayed.
	 *
	 * @since 2.0.0
	 */
	public function assertNoAdminNoticesDisplayed() {

		ob_start();
		wordpoints_admin_notices();
		$notice = ob_get_clean();

		$this->assertEmpty( $notice );
	}

	/**
	 * Asserts that an admin notice is displayed for an option.
	 *
	 * @since 2.0.0
	 *
	 * @param string $option The option.
	 */
	public function assertAdminNoticeDisplayedForOption( $option ) {

		ob_start();
		wordpoints_admin_notices();
		$notice = ob_get_clean();

		$this->assertWordPointsAdminNotice(
			$notice
			, array(
				'type' => 'error',
				'dismissible' => true,
				'option' => $option,
			)
		);
	}
}

// EOF
