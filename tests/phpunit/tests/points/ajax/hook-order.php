<?php

/**
 * A test case for the points hooks order AJAX callback.
 *
 * @package WordPoints\Tests
 * @since 1.3.0
 */

/**
 * Test the points hooks order AJAX callback.
 *
 * @since 1.3.0
 *
 * @group ajax
 */
class WordPoints_Points_Hooks_Order_AJAX_Test extends WordPoints_Points_Ajax_UnitTestCase {

	/**
	 * Test that subscribers can't change the order.
	 *
	 * @since 1.3.0
	 */
	public function test_as_subscriber() {

		$this->_setRole( 'subscriber' );

		// Create some points hooks.
		wordpointstests_add_points_hook( 'wordpoints_registration_points_hook' );
		wordpointstests_add_points_hook( 'wordpoints_comments_points_hook' );

		$_POST['savehooks'] = wp_create_nonce( 'save-wordpoints-points-hooks' );
		$_POST['points_types'] = array(
			'points' => 'wordpoints_registration_points_hook-1,wordpoints_comments_points_hook-1',
		);

		$this->setExpectedException( 'WPAjaxDieStopException', '-1' );
		$this->_handleAjax( 'wordpoints-points-hooks-order' );
	}

	/**
	 * Test as an administrator
	 *
	 * @since 1.3.0
	 */
	public function test_as_admin() {

		$this->_setRole( 'administrator' );

		// Create some points hooks.
		wordpointstests_add_points_hook( 'wordpoints_registration_points_hook' );
		wordpointstests_add_points_hook( 'wordpoints_comments_points_hook' );

		$_POST['savehooks'] = wp_create_nonce( 'save-wordpoints-points-hooks' );
		$_POST['points_types'] = array(
			'points' => 'wordpoints_registration_points_hook-1,wordpoints_comments_points_hook-1',
		);

		try {

			$this->_handleAjax( 'wordpoints-points-hooks-order' );

		} catch ( WPAjaxDieStopException $e ) {

			if ( $e->getMessage() != '1' ) {
				$this->fail( 'Unexpected exception message: "' . $e->getMessage() . '"' );
			}
		}

		$this->assertEquals(
			array(
				'points' => array(
					'registration_points_hook-1',
					'comments_points_hook-1',
				)
			)
			, WordPoints_Points_Hooks::get_points_types_hooks()
		);
	}

	/**
	 * Test that it fails with a bad nonce.
	 *
	 * @since 1.3.0
	 */
	public function test_bad_nonce() {

		$this->_setRole( 'administrator' );

		// Create some points hooks.
		wordpointstests_add_points_hook( 'wordpoints_registration_points_hook' );
		wordpointstests_add_points_hook( 'wordpoints_comments_points_hook' );

		$_POST['savehooks'] = 'invalid';
		$_POST['points_types'] = array(
			'points' => 'wordpoints_registration_points_hook-1,wordpoints_comments_points_hook-1',
		);

		$this->setExpectedException( 'WPAjaxDieStopException', '-1' );
		$this->_handleAjax( 'wordpoints-points-hooks-order' );
	}

	/**
	 * Test that only super admins can save network-wide hooks.
	 *
	 * @since 1.3.0
	 */
	public function test_network_as_admin() {

		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Multisite is required.' );
		}

		$this->_setRole( 'administrator' );

		// Create some points hooks.
		wordpointstests_add_points_hook( 'wordpoints_registration_points_hook' );
		wordpointstests_add_points_hook( 'wordpoints_comments_points_hook' );

		$_POST['savehooks'] = 'invalid';
		$_POST['points_types'] = array(
			'points' => 'wordpoints_registration_points_hook-1,wordpoints_comments_points_hook-1',
		);

		$this->setExpectedException( 'WPAjaxDieStopException', '-1' );
		$this->_handleAjax( 'wordpoints-points-hooks-order' );
	}

	/**
	 * Test that super admins can save network-wide hooks.
	 *
	 * @since 1.3.0
	 */
	public function test_network_as_super_admin() {

		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Multisite is required.' );
		}

		$this->_setRole( 'administrator' );
		grant_super_admin( get_current_user_id() );

		// Create some points hooks.
		wordpointstests_add_points_hook( 'wordpoints_registration_points_hook' );
		wordpointstests_add_points_hook( 'wordpoints_comments_points_hook' );

		$_POST['savehooks'] = wp_create_nonce( 'save-wordpoints-points-hooks' );
		$_POST['points_types'] = array(
			'points' => 'wordpoints_registration_points_hook-1,wordpoints_comments_points_hook-1',
		);

		try {

			$this->_handleAjax( 'wordpoints-points-hooks-order' );

		} catch ( WPAjaxDieStopException $e ) {

			if ( $e->getMessage() != '1' ) {
				$this->fail( 'Unexpected exception message: "' . $e->getMessage() . '"' );
			}
		}

		$this->assertEquals(
			array(
				'points' => array(
					'registration_points_hook-1',
					'comments_points_hook-1',
				)
			)
			, WordPoints_Points_Hooks::get_points_types_hooks()
		);
	}
}
