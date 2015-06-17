<?php

/**
 * A parent test case class for the Ajax tests.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Parent testcase for testing Ajax callbacks.
 *
 * @since 1.7.0
 */
abstract class WordPoints_Ajax_UnitTestCase extends WP_Ajax_UnitTestCase {

	/**
	 * The Ajax action being tested.
	 *
	 * @since 1.7.0
	 *
	 * @type string $ajax_action
	 */
	protected $ajax_action;

	/**
	 * @since 2.0.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		/**
		 * Admin-side functions.
		 *
		 * @since 2.0.0
		 */
		require_once( WORDPOINTS_DIR . '/admin/admin.php' );
	}

	/**
	 * @since 2.0.0
	 */
	protected function checkRequirements() {

		parent::checkRequirements();

		$annotations = $this->getAnnotations();

		foreach ( array( 'class', 'method' ) as $depth ) {

			if ( empty( $annotations[ $depth ]['requires'] ) ) {
				continue;
			}

			$requires = array_flip( $annotations[ $depth ]['requires'] );

			if ( isset( $requires['WordPress multisite'] ) && ! is_multisite() ) {
				$this->markTestSkipped( 'Multisite must be enabled.' );
			} elseif ( isset( $requires['WordPress !multisite'] ) && is_multisite() ) {
				$this->markTestSkipped( 'Multisite must not be enabled.' );
			}

			if (
				isset( $requires['WordPoints network-active'] )
				&& ! is_wordpoints_network_active()
			) {
				$this->markTestSkipped( 'WordPoints must be network-activated.' );
			} elseif (
				isset( $requires['WordPoints !network-active'] )
				&& is_wordpoints_network_active()
			) {
				$this->markTestSkipped( 'WordPoints must not be network-activated.' );
			}
		}
	}

	/**
	 * Assert that there was a JSON response object with the success property false.
	 *
	 * @since 1.7.0
	 *
	 * @param string $action The Ajax action to fire. Defaults to self::$ajax_action.
	 *
	 * @return object The response JSON.
	 */
	protected function assertJSONErrorResponse( $action = '' ) {

		$response = $this->assertJSONResponse( $action );

		if ( false !== $response->success ) {
			$this->fail(
				sprintf(
					'Failed to detect an error response: %s'
					, wp_json_encode( $response )
				)
			);
		}

		return $response;
	}

	/**
	 * Assert that there was a JSON response object with the success property true.
	 *
	 * @since 1.7.0
	 *
	 * @param string $action The Ajax action to fire. Defaults to self::$ajax_action.
	 *
	 * @return object The response JSON.
	 */
	protected function assertJSONSuccessResponse( $action = '' ) {

		$response = $this->assertJSONResponse( $action );

		if ( true !== $response->success ) {
			$this->fail(
				sprintf(
					'Failed to detect a successful response: %s'
					, wp_json_encode( $response )
				)
			);
		}

		return $response;
	}

	/**
	 * Assert that there is a JSON response to an Ajax action.
	 *
	 * @since 1.7.0
	 *
	 * @param string $action The Ajax action to fire. Defaults to self::$ajax_action.
	 *
	 * @return object The response JSON.
	 */
	protected function assertJSONResponse( $action = '' ) {

		if ( empty( $action ) ) {
			$action = $this->ajax_action;
		}

		try {

			$this->_handleAjax( $action );

		} catch ( WPAjaxDieContinueException $e ) {

			$response = json_decode( $this->_last_response );

			$this->assertInternalType( 'object', $response );
			$this->assertObjectHasAttribute( 'success', $response );

			return $response;
		}

		$this->fail( 'Failed to detect a JSON response.' );
	}
}

// EOF
