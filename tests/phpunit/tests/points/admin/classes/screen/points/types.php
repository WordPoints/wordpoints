<?php

/**
 * Test case for WordPoints_Points_Admin_Screen_Points_Types.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests the WordPoints_Points_Admin_Screen_Points_Types class.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Points_Admin_Screen_Points_Types
 */
class WordPoints_Admin_Screen_Points_Types_Test
	extends WordPoints_PHPUnit_TestCase_Ajax_Points {

	/**
	 * Specs for a request to create a points type.
	 *
	 * @since 2.1.0
	 *
	 * @var string[]
	 */
	protected $create_request_spec = array(
		'can_manage_wordpoints_points_types',
		'posts_valid_create_nonce',
		'posts_valid_points-name',
		'posts_optional_points-prefix',
		'posts_optional_points-suffix',
		'posts_save-points-type',
	);

	/**
	 * Specs for a request to update a points type.
	 *
	 * @since 2.1.0
	 *
	 * @var string[]
	 */
	protected $update_request_spec = array(
		'can_manage_wordpoints_points_types',
		'posts_valid_update_nonce',
		'posts_valid_points-slug',
		'posts_valid_points-name',
		'posts_optional_points-prefix',
		'posts_optional_points-suffix',
		'posts_save-points-type',
	);

	/**
	 * Specs for a request to delete a points type.
	 *
	 * @since 2.1.0
	 *
	 * @var string[]
	 */
	protected $delete_request_spec = array(
		'can_manage_wordpoints_points_types',
		'posts_valid_delete_nonce',
		'posts_valid_points-slug',
		'posts_delete-points-type',
	);

	/**
	 * @since 2.1.0
	 */
	public function setUp() {

		parent::setUp();

		$GLOBALS['wp_settings_errors'] = array();

		// These tests expect no points types to exist.
		wordpoints_delete_points_type( 'points' );

		$GLOBALS['current_screen'] = WP_Screen::get( 'test' );
	}

	/**
	 * @since 2.1.0
	 */
	public function tearDown() {

		parent::tearDown();

		$GLOBALS['wp_settings_errors'] = array();

		unset( $GLOBALS['current_screen'] );
	}

	/**
	 * Test creating a points type.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_valid_create_requests
	 *
	 * @param array $request_spec The specs for a valid request.
	 */
	public function test_create_points_type( $request_spec ) {

		$this->generate_request( $request_spec );

		$screen = new WordPoints_Points_Admin_Screen_Points_Types();
		$screen->save_points_type();

		$this->assertSuccessSettingsError( 'wordpoints_points_type_create' );

		$this->assertTrue( wordpoints_is_points_type( 'points' ) );

		$points_type = wordpoints_get_points_type( 'points' );

		$this->assertEquals( 'Points', $points_type['name'] );

		if ( isset( $_POST['points-prefix'] ) ) {
			$this->assertEquals( '$', $points_type['prefix'] );
		}

		if ( isset( $_POST['points-suffix'] ) ) {
			$this->assertEquals( 'pts.', $points_type['suffix'] );
		}

		// We display the points type and not the add new form again.
		$this->assertEquals( 'points', $_GET['tab'] );
	}

	/**
	 * Provides specs for valid points type create requests.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] A list of valid create request specs.
	 */
	public function data_provider_valid_create_requests() {
		return $this->generate_valid_request_specs( $this->create_request_spec );
	}

	/**
	 * Test creating a points type requires valid requests.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_invalid_create_requests
	 *
	 * @param array $request_spec The specs for an invalid request.
	 */
	public function test_create_points_type_invalid_request( $request_spec ) {

		$this->generate_request( $request_spec );

		$screen = new WordPoints_Points_Admin_Screen_Points_Types();
		$screen->save_points_type();

		$this->assertFalse( wordpoints_is_points_type( 'points' ) );
	}

	/**
	 * Provides specs for invalid points type create requests.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] A list of invalid create request specs.
	 */
	public function data_provider_invalid_create_requests() {
		return $this->generate_invalid_request_specs( $this->create_request_spec );
	}

	/**
	 * Test creating a points type that already exists.
	 *
	 * @since 2.1.0
	 */
	public function test_create_points_type_already_exists() {

		wordpoints_add_points_type( array( 'name' => 'Points' ) );

		$this->assertTrue( wordpoints_is_points_type( 'points' ) );

		$this->generate_request( $this->create_request_spec );

		$screen = new WordPoints_Points_Admin_Screen_Points_Types();
		$screen->save_points_type();

		$this->assertSettingsError( 'wordpoints_points_type_create' );

		$this->assertTrue( wordpoints_is_points_type( 'points' ) );
	}

	/**
	 * Test updating a points type.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_valid_update_requests
	 *
	 * @param array $request_spec The specs for a valid request.
	 */
	public function test_update_points_type( $request_spec ) {

		wordpoints_add_points_type( array( 'name' => 'Points' ) );
		wordpoints_update_points_type(
			'points'
			, array( 'name' => 'Other', 'prefix' => 'o', 'suffix' => 'r' )
		);

		$this->generate_request( $request_spec );

		$screen = new WordPoints_Points_Admin_Screen_Points_Types();
		$screen->save_points_type();

		$this->assertSuccessSettingsError( 'wordpoints_points_type_update' );

		$this->assertTrue( wordpoints_is_points_type( 'points' ) );

		$points_type = wordpoints_get_points_type( 'points' );

		$this->assertEquals( 'Points', $points_type['name'] );

		if ( isset( $_POST['points-prefix'] ) ) {
			$this->assertEquals( '$', $points_type['prefix'] );
		} else {
			$this->assertEquals( 'o', $points_type['prefix'] );
		}

		if ( isset( $_POST['points-suffix'] ) ) {
			$this->assertEquals( 'pts.', $points_type['suffix'] );
		} else {
			$this->assertEquals( 'r', $points_type['suffix'] );
		}
	}

	/**
	 * Provides specs for valid points type update requests.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] A list of valid update request specs.
	 */
	public function data_provider_valid_update_requests() {
		return $this->generate_valid_request_specs( $this->update_request_spec );
	}

	/**
	 * Test updating a points type requires valid requests.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_invalid_update_requests
	 *
	 * @param array $request_spec The specs for an invalid request.
	 */
	public function test_update_points_type_invalid_request( $request_spec ) {

		$settings = array( 'name' => 'Other', 'prefix' => 'o', 'suffix' => 'r' );

		wordpoints_add_points_type( array( 'name' => 'Points' ) );
		wordpoints_update_points_type( 'points', $settings );

		$this->generate_request( $request_spec );

		$screen = new WordPoints_Points_Admin_Screen_Points_Types();
		$screen->save_points_type();

		$this->assertEquals( $settings, wordpoints_get_points_type( 'points' ) );
	}

	/**
	 * Provides specs for invalid points type update requests.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] A list of invalid update request specs.
	 */
	public function data_provider_invalid_update_requests() {
		return $this->generate_invalid_request_specs( $this->update_request_spec );
	}

	/**
	 * Test updating a points type that doesn't exist.
	 *
	 * @since 2.1.0
	 */
	public function test_update_points_type_nonexistent() {

		$this->assertFalse( wordpoints_is_points_type( 'points' ) );

		$this->generate_request( $this->update_request_spec );

		$screen = new WordPoints_Points_Admin_Screen_Points_Types();
		$screen->save_points_type();

		$this->assertSettingsError( 'wordpoints_points_type_update' );

		$this->assertFalse( wordpoints_is_points_type( 'points' ) );
	}

	/**
	 * Test deleting a points type.
	 *
	 * @since 2.1.0
	 */
	public function test_delete_points_type() {

		wordpoints_add_points_type( array( 'name' => 'Points' ) );

		$this->generate_request( $this->delete_request_spec );

		$screen = new WordPoints_Points_Admin_Screen_Points_Types();
		$screen->save_points_type();

		$this->assertSuccessSettingsError( 'wordpoints_points_type_delete' );

		$this->assertFalse( wordpoints_is_points_type( 'points' ) );
	}

	/**
	 * Test deleting a points type requires valid requests.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_invalid_delete_requests
	 *
	 * @param array $request_spec The specs for an invalid request.
	 */
	public function test_delete_points_type_invalid_request( $request_spec ) {

		wordpoints_add_points_type( array( 'name' => 'Points' ) );

		$this->generate_request( $request_spec );

		$screen = new WordPoints_Points_Admin_Screen_Points_Types();
		$screen->save_points_type();

		$this->assertTrue( wordpoints_is_points_type( 'points' ) );
	}

	/**
	 * Provides specs for invalid points type delete requests.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] A list of invalid delete request specs.
	 */
	public function data_provider_invalid_delete_requests() {
		return $this->generate_invalid_request_specs( $this->delete_request_spec );
	}

	/**
	 * Test deleting a points type that doesn't exist.
	 *
	 * @since 2.1.0
	 */
	public function test_delete_points_type_nonexistent() {

		$this->assertFalse( wordpoints_is_points_type( 'points' ) );

		$this->generate_request( $this->delete_request_spec );

		$screen = new WordPoints_Points_Admin_Screen_Points_Types();
		$screen->save_points_type();

		$this->assertSettingsError( 'wordpoints_points_type_delete' );

		$this->assertFalse( wordpoints_is_points_type( 'points' ) );
	}

	/**
	 * @since 2.1.0
	 */
	public function fulfill_posts_requirement( $requirement_parts ) {

		if ( isset( $requirement_parts[3] ) && 'nonce' === $requirement_parts[3] ) {

			if ( 'invalid' === $requirement_parts[1] ) {
				$_POST['nonce'] = 'invalid';

				return;
			}

			switch ( $requirement_parts[2] ) {

				case 'create':
					$_POST['add_new'] = wp_create_nonce(
						'wordpoints_add_new_points_type'
					);
				break;

				case 'update':
					$_POST['update_points_type'] = wp_create_nonce(
						'wordpoints_update_points_type-points'
					);
				break;

				case 'delete':
					$_POST['delete-points-type-nonce'] = wp_create_nonce(
						'wordpoints_delete_points_type-points'
					);
				break;
			}

		} elseif ( array( 1 => 'invalid', 2 => 'points-name' ) === $requirement_parts ) {

			$_POST['points-name'] = '';

		} elseif ( array( 1 => 'invalid', 2 => 'points-slug' ) === $requirement_parts ) {

			$_POST['points-slug'] = '';

		} else {

			parent::fulfill_posts_requirement( $requirement_parts );
		}
	}

	/**
	 * @since 2.1.0
	 */
	public function get_valid_posts_value( $query_arg ) {

		switch ( $query_arg ) {

			case 'points-name':
				return 'Points';

			case 'points-prefix':
				return '$';

			case 'points-suffix':
				return 'pts.';

			case 'save-points-type':
				return 'Save';

			case 'points-slug':
				return 'points';

			case 'delete-points-type':
				return 'Delete';
		}

		return parent::get_valid_posts_value( $query_arg );
	}

	/**
	 * Asserts that a settings error has occurred.
	 *
	 * @since 2.1.0
	 *
	 * @param string $code The message code.
	 * @param string $type The type of error message. Default is 'error'.
	 */
	protected function assertSettingsError( $code, $type = 'error' ) {

		global $wp_settings_errors;

		$this->assertCount( 1, (array) $wp_settings_errors );

		$this->assertEquals( $code, $wp_settings_errors[0]['code'] );
		$this->assertEquals( $type, $wp_settings_errors[0]['type'] );
	}

	/**
	 * Asserts that a success settings "error" has occurred.
	 *
	 * @since 2.1.0
	 *
	 * @param string $code The message code.
	 */
	protected function assertSuccessSettingsError( $code ) {
		$this->assertSettingsError( $code, 'updated' );
	}
}

// EOF
