<?php

/**
 * A test case for the module compatibility check Ajax callback.
 *
 * @package WordPoints\Tests
 * @since 2.0.0
 */

/**
 * Tests the module compatibility check Ajax callback.
 *
 * @since 2.0.0
 *
 * @group ajax
 *
 * @covers ::wordpoints_admin_ajax_breaking_module_check
 */
class WordPoints_Breaking_Module_Check_Ajax_Test extends WordPoints_AJAX_UnitTestCase {

	/**
	 * That that the modules screen is displayed.
	 *
	 * @since 2.0.0
	 */
	public function test_displays_modules_screen() {

		update_option( 'wordpoints_module_check_nonce', __METHOD__ );

		$_GET['wordpoints_module_check'] = __METHOD__;

		try {
			$this->_handleAjax( 'nopriv_wordpoints_breaking_module_check' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$document = new DOMDocument;
		$document->loadHTML( $this->_last_response );
		$xpath = new DOMXPath( $document );
		$this->assertEquals(
			1
			, $xpath->query( '//div[@class = "tablenav bottom"]' )->length
		);
	}

	/**
	 * Test running when the request is network-wide.
	 *
	 * @since 2.0.0
	 */
	public function test_network_wide() {

		update_site_option( 'wordpoints_module_check_nonce', __METHOD__ );

		$GLOBALS['current_screen'] = WP_Screen::get( 'test-network' );

		$_GET['wordpoints_module_check'] = __METHOD__;

		try {
			$this->_handleAjax( 'nopriv_wordpoints_breaking_module_check' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$document = new DOMDocument;
		$document->loadHTML( $this->_last_response );
		$xpath = new DOMXPath( $document );
		$this->assertEquals(
			1
			, $xpath->query( '//div[@class = "tablenav bottom"]' )->length
		);
	}

	/**
	 * Test that a valid nonce is required.
	 *
	 * @since 2.0.0
	 */
	public function test_valid_nonce_required() {

		update_option( 'wordpoints_module_check_nonce', __METHOD__ );

		$_GET['wordpoints_module_check'] = 'invalid';

		$this->setExpectedException( 'WPAjaxDieStopException', '' );
		$this->_handleAjax( 'nopriv_wordpoints_breaking_module_check' );
	}

	/**
	 * Test that a valid nonce is required.
	 *
	 * @since 2.0.0
	 */
	public function test_nonce_required() {

		update_option( 'wordpoints_module_check_nonce', __METHOD__ );

		$this->setExpectedException( 'WPAjaxDieStopException', '' );
		$this->_handleAjax( 'nopriv_wordpoints_breaking_module_check' );
	}
}

// EOF
