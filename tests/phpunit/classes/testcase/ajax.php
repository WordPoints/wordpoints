<?php

/**
 * Base Ajax test case class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Parent test case for Ajax tests.
 *
 * @since 2.1.0
 *
 * @property WordPoints_PHPUnit_Factory_Stub $factory The factory.
 */
abstract class WordPoints_PHPUnit_TestCase_Ajax extends WP_Ajax_UnitTestCase {

	/**
	 * The Ajax action being tested.
	 *
	 * @since 1.7.0
	 *
	 * @type string $ajax_action
	 */
	protected $ajax_action;

	/**
	 * A backup of the main app.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_App
	 */
	protected $backup_app;

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

			self::backup_hooks();
		}
	}

	/**
	 * @since 2.1.0
	 */
	public function setUp() {

		parent::setUp();

		if ( ! isset( $this->factory->wordpoints ) ) {
			$this->factory->wordpoints = WordPoints_PHPUnit_Factory::$factory;
		}
	}

	/**
	 * @since 2.1.0
	 */
	public function tearDown() {

		parent::tearDown();

		if ( isset( $this->backup_app ) ) {
			WordPoints_App::$main = $this->backup_app;
			$this->backup_app = null;
		}

		WordPoints_PHPUnit_Mock_Entity_Context::$current_id = 1;
	}

	/**
	 * Back up the hooks.
	 *
	 * @since 2.0.0
	 */
	protected static function backup_hooks() {

		$globals = array( 'merged_filters', 'wp_actions', 'wp_current_filter', 'wp_filter' );

		foreach ( $globals as $key ) {
			// merged_filters no longer used in 4.7.
			if ( isset( $GLOBALS[ $key ] ) ) {
				WP_UnitTestCase::$hooks_saved[ $key ] = $GLOBALS[ $key ];
			}
		}
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

	/**
	 * Set up the global apps object as a mock.
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_App The mock app.
	 */
	public function mock_apps() {

		$this->backup_app = WordPoints_App::$main;

		return WordPoints_App::$main = new WordPoints_PHPUnit_Mock_App_Silent(
			'apps'
		);
	}

	/**
	 * Generate a request from specifications.
	 *
	 * @since 2.1.0
	 *
	 * @param string[] $specs The request specifications.
	 */
	public function generate_request( $specs ) {

		foreach ( $specs as $spec ) {

			$parts = explode( '_', $spec );

			$type = $parts[0];

			unset( $parts[0] );

			switch ( $type ) {

				case 'am':
					$this->fulfill_am_requirement( $parts );
				break;

				case 'can':
					$this->fulfill_can_requirement( $parts );
				break;

				case 'posts':
					$this->fulfill_posts_requirement( $parts );
				break;

				default:
					$this->fulfill_other_requirement( $type, $parts );
			}
		}
	}

	/**
	 * Create the specs for valid requests based on the specs for a valid request.
	 *
	 * Because some things can be optional.
	 *
	 * @since 2.1.0
	 *
	 * @param string[] $specs The specs for a valid request.
	 *
	 * @return array[] The valid requests, ready to be returned by a data provider.
	 */
	public function generate_valid_request_specs( $specs ) {

		$valid_requests = array( 'basic' => array( $specs ) );

		foreach ( $specs as $index => $spec ) {

			if ( 'posts_optional_' === substr( $spec, 0, 15 ) ) {
				$request = $specs;
				unset( $request[ $index ] );
				$valid_requests[ 'no' . substr( $spec, 14 ) ] = array( $request );
			}
		}

		return $valid_requests;
	}

	/**
	 * Create the specs for invalid requests based on the specs for a valid request.
	 *
	 * @since 2.1.0
	 *
	 * @param string[] $specs The specs for a valid request.
	 *
	 * @return array[] The invalid requests, ready to be returned by a data provider.
	 */
	public function generate_invalid_request_specs( $specs ) {

		$invalid_requests = array();

		foreach ( $specs as $index => $spec ) {

			$parts = explode( '_', $spec );

			$request = $specs;

			unset( $request[ $index ] );

			$type = $parts[0];

			unset( $parts[0] );

			$rest = implode( '_', $parts );

			switch ( $type ) {

				case 'am':
					$invalid_requests[ 'not_' . $rest ] = array( $request );
				break;

				case 'can':
					$invalid_requests[ 'cant_' . $rest ] = array( $request );
				break;

				case 'posts':
					$next_part = '';

					switch ( $parts[1] ) {

						case 'valid':
							$invalid_requests[ 'missing_' . $rest ] = array( $request );
							$next_part = $parts[1];
						break;

						case 'optional':
							$next_part = $parts[2];
							$rest = substr( $rest, 0, 9 /* optional_ */ );
						break;
					}

					if ( 'valid' === $next_part ) {
						// The 'in' makes 'valid' become 'invalid'.
						$request[ $index ] = 'posts_in' . $rest;

						$invalid_requests[ 'invalid' . ltrim( $rest, 'valid' ) ] = array( $request );
					}
				break;
			}

		} // End foreach ( $specs ).

		return $invalid_requests;
	}

	/**
	 * Fulfill the requirements for an "am" request specification.
	 *
	 * An "am" request specification dictates that the current user must have a
	 * certain role.
	 *
	 * @since 2.1.0
	 *
	 * @param string[] $requirement_parts The requirement parts.
	 */
	public function fulfill_am_requirement( $requirement_parts ) {

		$this->_setRole( implode( '_', $requirement_parts ) );
	}

	/**
	 * Fulfill the requirements for an "can" request specification.
	 *
	 * A "can" request specification dictates that the current user must have a
	 * certain capability.
	 *
	 * @since 2.1.0
	 *
	 * @param string[] $requirement_parts The requirement parts.
	 */
	public function fulfill_can_requirement( $requirement_parts ) {

		$post = $_POST;

		/** @var WP_User $user */
		$user = $this->factory->user->create_and_get();
		$user->add_cap( implode( '_', $requirement_parts ) );

		wp_set_current_user( $user->ID );

		$_POST = array_merge( $_POST, $post );
	}

	/**
	 * Fulfill the requirements for a "posts" request specification.
	 *
	 * A "posts" request spec dictates that a certain value should be posted. The
	 * value can be requested to be valid or invalid, and will be supplied
	 * accordingly.
	 *
	 * @since 2.1.0
	 *
	 * @param string[] $requirement_parts The requirement parts.
	 */
	public function fulfill_posts_requirement( $requirement_parts ) {

		$type = $requirement_parts[1];

		$parts = $requirement_parts;

		unset( $parts[1] );

		$rest = implode( '_', $parts );

		switch ( $type ) {

			case 'invalid':
				$_POST[ $rest ] = 'invalid';
			break;

			case 'valid':
			case 'optional':
				$_POST[ $rest ] = $this->get_valid_posts_value( $rest );
			break;

			default:
				$parts = implode( '_', $requirement_parts );

				$_POST[ $parts ] = $this->get_valid_posts_value( $parts );
		}
	}

	/**
	 * Get a valid value for a POST query arg.
	 *
	 * @since 2.1.0
	 *
	 * @param string $query_arg The name of the POST query arg to get the valid data
	 *                          for.
	 *
	 * @return mixed The valid data for this query arg.
	 */
	public function get_valid_posts_value( $query_arg ) {
		return null;
	}

	/**
	 * Fulfill the requirements for a non-standard request specification.
	 *
	 * If you want to use a request spec of a type other than those provided, you
	 * can override this method to provide the logic to fulfill such requirements.
	 *
	 * @since 2.1.0
	 *
	 * @param string   $type              The type of requirement.
	 * @param string[] $requirement_parts The requirement parts.
	 */
	public function fulfill_other_requirement( $type, $requirement_parts ) {}
}

// EOF
