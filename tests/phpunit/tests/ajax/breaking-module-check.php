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
class WordPoints_Breaking_Module_Check_Ajax_Test extends WordPoints_PHPUnit_TestCase_Ajax {

	/**
	 * The nonce value used in the tests.
	 *
	 * @since 2.0.1
	 *
	 * @var string
	 */
	protected $nonce;

	/**
	 * @since 2.0.1
	 */
	public function setUp() {

		parent::setUp();

		$this->nonce = sanitize_key( __METHOD__ );

		$_GET['wordpoints_module_check'] = $this->nonce;

		update_option( 'wordpoints_module_check_nonce', $this->nonce );
	}

	/**
	 * That the modules screen is displayed.
	 *
	 * @since 2.0.0
	 */
	public function test_displays_modules_screen() {

		try {
			$this->_handleAjax( 'nopriv_wordpoints_breaking_module_check' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$document = new DOMDocument;
		$document->loadHTML( $this->_last_response );
		$xpath = new DOMXPath( $document );
		$this->assertSame(
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

		delete_option( 'wordpoints_module_check_nonce' );
		update_site_option( 'wordpoints_module_check_nonce', $this->nonce );

		$GLOBALS['current_screen'] = WP_Screen::get( 'test-network' );

		try {
			$this->_handleAjax( 'nopriv_wordpoints_breaking_module_check' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$document = new DOMDocument;
		$document->loadHTML( $this->_last_response );
		$xpath = new DOMXPath( $document );
		$this->assertSame(
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

		unset( $_GET['wordpoints_module_check'] );

		$this->setExpectedException( 'WPAjaxDieStopException', '' );
		$this->_handleAjax( 'nopriv_wordpoints_breaking_module_check' );
	}
}

// EOF
