<?php

/**
 * A test case for AJAX saving points hooks.
 *
 * @package WordPoints\Tests
 * @since 1.3.0
 */

/**
 * Test that points hooks are saved properly via AJAX.
 *
 * @since 1.3.0
 *
 * @group ajax
 */
class WordPoints_Points_Hooks_AJAX_Test extends WordPoints_Points_AJAX_UnitTestCase {

	/**
	 * Test that it fails for subscribers.
	 *
	 * @since 1.3.0
	 */
	public function test_as_subscriber() {

		$this->_setRole( 'subscriber' );

		$_POST['savehooks']    = wp_create_nonce( 'save-wordpoints-points-hooks' );
		$_POST['id_base']      = 'wordpoints_registration_points_hook';
		$_POST['hook-id']      = '';
		$_POST['points_type']  = 'points';
		$_POST['hook_number']  = '';
		$_POST['multi_number'] = 1;
		$_POST['add_new']      = 1;

		$_POST['hook-wordpoints_registration_points_hook'] = array(
			'points' => '10',
		);

		$this->setExpectedException( 'WPAjaxDieStopException', '-1' );
		$this->_handleAjax( 'save-wordpoints-points-hook' );
	}

	/**
	 * Test creating a new hook.
	 *
	 * @since 1.3.0
	 */
	public function test_add_new_hook() {

		$this->_setRole( 'administrator' );

		$_POST['savehooks']    = wp_create_nonce( 'save-wordpoints-points-hooks' );
		$_POST['id_base']      = 'wordpoints_registration_points_hook';
		$_POST['hook-id']      = '';
		$_POST['points_type']  = 'points';
		$_POST['hook_number']  = '';
		$_POST['multi_number'] = 1;
		$_POST['add_new']      = 1;

		$_POST['hook-wordpoints_registration_points_hook'] = array(
			array( 'points' => '15' ),
		);

		try {

			$this->_handleAjax( 'save-wordpoints-points-hook' );

		} catch ( WPAjaxDieStopException $e ) {

			if ( $e->getMessage() !== '' ) {
				$this->fail( 'Unexpected exception message: "' . $e->getMessage() . '"' );
			}
		}

		$hooks = WordPoints_Points_Hooks::get_points_type_hooks( 'points' );
		$this->assertCount( 1, $hooks );
		$this->assertEquals( 'wordpoints_registration_points_hook-1', $hooks[0] );

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_registration_points_hook' );
		$this->assertInstanceOf( 'WordPoints_Registration_Points_Hook', $hook );

		$instances = $hook->get_instances();
		$this->assertCount( 1, $instances );
		$this->assertEquals( array( 'points' => '15' ), $instances[1] );
	}

	/**
	 * Test updating an existing hook.
	 *
	 * @since 1.3.0
	 */
	public function test_update_hook() {

		$this->_setRole( 'administrator' );

		// Add a hook.
		$hook = wordpointstests_add_points_hook(
			'wordpoints_registration_points_hook'
			, array( 'points' => '20' )
		);

		$_POST['savehooks']    = wp_create_nonce( 'save-wordpoints-points-hooks' );
		$_POST['id_base']      = 'wordpoints_registration_points_hook';
		$_POST['hook-id']      = 'wordpoints_registration_points_hook-1';
		$_POST['points_type']  = 'points';
		$_POST['hook_number']  = 1;
		$_POST['multi_number'] = 2;
		$_POST['add_new']      = 0;

		$_POST['hook-wordpoints_registration_points_hook'] = array(
			array( 'points' => '15' ),
		);

		try {
			$this->_handleAjax( 'save-wordpoints-points-hook' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$this->assertTag(
			array(
				'tag' => 'p',
				'child' => array(
					'tag'  => 'input',
					'attributes' => array( 'value' => '15' ),
				),
			)
			, $this->_last_response
		);

		$hooks = WordPoints_Points_Hooks::get_points_type_hooks( 'points' );

		$this->assertCount( 1, $hooks );
		$this->assertEquals( 'wordpoints_registration_points_hook-1', $hooks[0] );

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_registration_points_hook' );
		$this->assertInstanceOf( 'WordPoints_Registration_Points_Hook', $hook );

		$instances = $hook->get_instances();
		$this->assertCount( 1, $instances );
		$this->assertEquals( array( 'points' => '15' ), $instances[1] );
	}

	/**
	 * Test deleting an existing hook.
	 *
	 * @since 1.3.0
	 */
	public function test_delete_hook() {

		$this->_setRole( 'administrator' );

		// Add a hook.
		$hook = wordpointstests_add_points_hook(
			'wordpoints_registration_points_hook'
			, array( 'points' => '20' )
		);

		$_POST['savehooks']    = wp_create_nonce( 'save-wordpoints-points-hooks' );
		$_POST['id_base']      = 'wordpoints_registration_points_hook';
		$_POST['hook-id']      = 'wordpoints_registration_points_hook-1';
		$_POST['points_type']  = 'points';
		$_POST['hook_number']  = 1;
		$_POST['multi_number'] = 2;
		$_POST['add_new']      = 0;
		$_POST['delete_hook']  = 1;

		$_POST['hook-wordpoints_registration_points_hook'] = array(
			array( 'points' => '15' ),
		);

		try {
			$this->_handleAjax( 'save-wordpoints-points-hook' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$this->assertEquals( 'deleted:wordpoints_registration_points_hook-1', $this->_last_response );

		$hooks = WordPoints_Points_Hooks::get_points_type_hooks( 'points' );
		$this->assertCount( 0, $hooks );
	}

	/**
	 * Test that only super-admins can save network-wide hooks.
	 *
	 * @since 1.3.0
	 */
	public function test_network_as_admin() {

		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Multisite is required.' );
		}

		$this->_setRole( 'administrator' );

		$_POST['savehooks']    = wp_create_nonce( 'save-network-wordpoints-points-hooks' );
		$_POST['id_base']      = 'wordpoints_registration_points_hook';
		$_POST['hook-id']      = '';
		$_POST['points_type']  = 'points';
		$_POST['hook_number']  = '';
		$_POST['multi_number'] = 1;
		$_POST['add_new']      = 1;

		$_POST['hook-wordpoints_registration_points_hook'] = array(
			'points' => '10',
		);

		$this->setExpectedException( 'WPAjaxDieStopException', '-1' );
		$this->_handleAjax( 'save-wordpoints-points-hook' );
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

		$_POST['savehooks']    = wp_create_nonce( 'save-network-wordpoints-points-hooks' );
		$_POST['id_base']      = 'wordpoints_registration_points_hook';
		$_POST['hook-id']      = '';
		$_POST['points_type']  = 'points';
		$_POST['hook_number']  = '';
		$_POST['multi_number'] = 1;
		$_POST['add_new']      = 1;

		$_POST['hook-wordpoints_registration_points_hook'] = array(
			array( 'points' => '15' ),
		);

		try {

			$this->_handleAjax( 'save-wordpoints-points-hook' );

		} catch ( WPAjaxDieStopException $e ) {

			if ( $e->getMessage() !== '' ) {
				$this->fail( 'Unexpected exception message: "' . $e->getMessage() . '"' );
			}
		}

		$hooks = WordPoints_Points_Hooks::get_points_type_hooks( 'points' );
		$this->assertCount( 1, $hooks );
		$this->assertEquals( 'wordpoints_registration_points_hook-1', $hooks[0] );

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_registration_points_hook' );
		$this->assertInstanceOf( 'WordPoints_Registration_Points_Hook', $hook );

		$instances = $hook->get_instances();
		$this->assertCount( 1, $instances );
		$this->assertEquals( array( 'points' => '15' ), $instances['network_1'] );
	}
}
