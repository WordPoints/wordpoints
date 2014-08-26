<?php

/**
 * A test case for points hooks AJAX.
 *
 * @package WordPoints\Tests
 * @since 1.3.0
 */

/**
 * Test that the points hooks AJAx functions work correctly.
 *
 * @since 1.3.0
 *
 * @group ajax
 */
class WordPoints_Save_Points_Type_AJAX_Test extends WordPoints_Points_AJAX_UnitTestCase {

	/**
	 * Test that it fails for subscribers.
	 *
	 * @since 1.3.0
	 */
	public function test_as_subscriber() {

		$this->_setRole( 'subscriber' );

		$_POST['savehooks'] = wp_create_nonce( 'save-wordpoints-points-hooks' );
		$_POST['points-slug'] = 'points';

		$this->setExpectedException( 'WPAjaxDieStopException', '-1' );
		$this->_handleAjax( 'save-wordpoints-points-hook' );
	}

	/**
	 * Test that it succeeds for an administrator.
	 *
	 * @since 1.3.0
	 */
	public function test_as_admin() {

		$this->_setRole( 'administrator' );

		$_POST['savehooks']     = wp_create_nonce( 'save-wordpoints-points-hooks' );
		$_POST['points-slug']   = 'points';
		$_POST['points-name']   = 'SuperPoints';
		$_POST['points-prefix'] = 'SP:';
		$_POST['points-suffix'] = '';

		try {

			$this->_handleAjax( 'save-wordpoints-points-hook' );

		} catch ( WPAjaxDieStopException $e ) {

			if ( is_wordpoints_network_active() ) {

				if ( $e->getMessage() !== '-1' ) {
					$this->fail( 'Unexpected exception message: "' . $e->getMessage() . '"' );
				}

				return;

			} else {

				if ( $e->getMessage() !== '' ) {
					$this->fail( 'Unexpected exception message: "' . $e->getMessage() . '"' );
				}
			}
		}

		$settings = wordpoints_get_points_type( 'points' );

		$this->assertEquals( 'SuperPoints', $settings['name'] );
		$this->assertEquals( 'SP:', $settings['prefix'] );
		$this->assertEquals( '', $settings['suffix'] );
	}

	/**
	 * Test that it fails for non-existant points types.
	 *
	 * @since 1.3.0
	 */
	public function test_bad_points_type() {

		$this->_setRole( 'administrator' );

		$_POST['savehooks'] = wp_create_nonce( 'save-wordpoints-points-hooks' );
		$_POST['points-slug'] = 'invalid';

		$this->setExpectedException( 'WPAjaxDieStopException', '-1' );
		$this->_handleAjax( 'save-wordpoints-points-hook' );
	}

	/**
	 * Test that it fails for a bad nonce.
	 *
	 * @since 1.3.0
	 */
	public function test_bad_nonce() {

		$this->_setRole( 'administrator' );

		$_POST['savehooks'] = 'invalid';
		$_POST['points-slug'] = 'points';

		$this->setExpectedException( 'WPAjaxDieStopException', '-1' );
		$this->_handleAjax( 'save-wordpoints-points-hook' );
	}
}

// EOF
