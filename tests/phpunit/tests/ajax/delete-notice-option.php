<?php

/**
 * A test case for the notice option delete Ajax callback.
 *
 * @package WordPoints\Tests
 * @since 2.1.0
 */

/**
 * Tests the notice option delete Ajax callback.
 *
 * @since 2.1.0
 *
 * @group ajax
 *
 * @covers ::wordpoints_delete_admin_notice_option
 */
class WordPoints_Delete_Notice_Option_Ajax_Test extends WordPoints_PHPUnit_TestCase_Ajax {

	/**
	 * Test that it deletes the option when the dismiss form has been submitted.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPoints !network-active
	 */
	public function test_option_deleted() {

		update_option( 'test', 'test' );

		$_POST['wordpoints_notice'] = 'test';
		$_POST['_wpnonce'] = wp_create_nonce(
			"wordpoints_dismiss_notice-{$_POST['wordpoints_notice']}"
		);

		try {
			$this->_handleAjax( 'wordpoints-delete-admin-notice-option' );
		} catch ( WPAjaxDieStopException $e ) {
			unset( $e );
		}

		$this->assertFalse( get_option( 'test' ) );
	}

	/**
	 * Test that it deletes the a network option when WordPoints is network-active.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_option_deleted_network_wide() {

		update_site_option( 'test', 'test' );

		$_POST['wordpoints_notice'] = 'test';
		$_POST['_wpnonce'] = wp_create_nonce(
			"wordpoints_dismiss_notice-{$_POST['wordpoints_notice']}"
		);

		try {
			$this->_handleAjax( 'wordpoints-delete-admin-notice-option' );
		} catch ( WPAjaxDieStopException $e ) {
			unset( $e );
		}

		$this->assertFalse( get_site_option( 'test' ) );
	}

	/**
	 * Test that a valid nonce must be present for the option to be deleted.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPoints !network-active
	 */
	public function test_option_not_deleted_without_nonce() {

		update_option( 'test', 'test' );

		$_POST['wordpoints_notice'] = 'test';

		try {
			$this->_handleAjax( 'wordpoints-delete-admin-notice-option' );
		} catch ( WPAjaxDieStopException $e ) {
			unset( $e );
		}

		$this->assertSame( 'test', get_option( 'test' ) );
	}

	/**
	 * Test that it deletes the a network option when WordPoints is network-active.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_incompatible_modules_option_deleted_network_wide() {

		update_option( 'wordpoints_incompatible_modules', 'test' );

		$_POST['wordpoints_notice'] = 'wordpoints_incompatible_modules';
		$_POST['_wpnonce'] = wp_create_nonce(
			"wordpoints_dismiss_notice-{$_POST['wordpoints_notice']}"
		);

		try {
			$this->_handleAjax( 'wordpoints-delete-admin-notice-option' );
		} catch ( WPAjaxDieStopException $e ) {
			unset( $e );
		}

		$this->assertFalse( get_option( 'wordpoints_incompatible_modules' ) );
	}

	/**
	 * Test that a valid nonce is required.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPoints !network-active
	 */
	public function test_requires_valid_nonce() {

		update_option( 'test', 'test' );

		$_POST['wordpoints_notice'] = 'test';
		$_POST['_wpnonce'] = 'invalid';

		try {
			$this->_handleAjax( 'wordpoints-delete-admin-notice-option' );
		} catch ( WPAjaxDieStopException $e ) {
			unset( $e );
		}

		$this->assertSame( 'test', get_option( 'test' ) );
	}

	/**
	 * Test that a valid nonce is required.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPoints !network-active
	 */
	public function test_nonce_required() {

		update_option( 'test', 'test' );

		$_POST['wordpoints_notice'] = 'test';

		try {
			$this->_handleAjax( 'wordpoints-delete-admin-notice-option' );
		} catch ( WPAjaxDieStopException $e ) {
			unset( $e );
		}

		$this->assertSame( 'test', get_option( 'test' ) );
	}


	/**
	 * Test that it requires the option name.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPoints !network-active
	 */
	public function test_requires_option() {

		update_option( 'test', 'test' );

		$_POST['_wpnonce'] = wp_create_nonce( 'wordpoints_dismiss_notice-' );

		try {
			$this->_handleAjax( 'wordpoints-delete-admin-notice-option' );
		} catch ( WPAjaxDieStopException $e ) {
			unset( $e );
		}

		$this->assertSame( 'test', get_option( 'test' ) );
	}
}

// EOF
