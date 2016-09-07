<?php

/**
 * Testcase for the profile points update functions.
 *
 * @package WordPoints\Tests
 * @since 1.9.2
 */

/**
 * Test that the profile points update functions behave properly.
 *
 * @since 1.9.2
 *
 * @covers ::wordpoints_points_profile_options_update
 */
class WordPoints_Profile_Points_Update_Admin_Test extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * The object for the user used in the test.
	 *
	 * @since 1.9.2
	 *
	 * @var WP_User
	 */
	protected $user;

	/**
	 * @since 1.9.2
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		/**
		 * @since 1.9.2
		 */
		require_once( WORDPOINTS_DIR . '/components/points/admin/admin.php' );
	}

	/**
	 * @since 1.9.2
	 */
	public function setUp() {

		parent::setUp();

		$this->user = $this->factory->user->create_and_get();
		$this->user->add_cap( 'set_wordpoints_points' );

		wp_set_current_user( $this->user->ID );

		$_POST['wordpoints_points_set_nonce'] = wp_create_nonce(
			'wordpoints_points_set_profile'
		);

		$_POST['wordpoints_points_set-points'] = '1';
		$_POST['wordpoints_points-points'] = '5';
		$_POST['wordpoints_points_old-points'] = '0';
		$_POST['wordpoints_set_reason'] = 'Testing.';
	}

	/**
	 * Test that the user's points are updated successfully.
	 *
	 * @since 1.9.2
	 */
	public function test_points_updated() {

		$this->assert_update_succeeds();
	}

	/**
	 * Test that current user must have set_wordpoints_points capability.
	 *
	 * @since 1.9.2
	 */
	public function test_current_user_must_have_required_caps() {

		$this->user = $this->factory->user->create_and_get();
		wp_set_current_user( $this->user->ID );

		$this->assert_update_fails();
	}

	/**
	 * Test that an nonce is required.
	 *
	 * @since 1.9.2
	 */
	public function test_nonce_required() {

		unset( $_POST['wordpoints_points_set_nonce'] );

		$this->assert_update_fails();
	}

	/**
	 * Test that a valid nonce is required.
	 *
	 * @since 1.9.2
	 */
	public function test_valid_nonce_required() {

		$_POST['wordpoints_points_set_nonce'] = 'invlaid';

		$this->assert_update_fails();
	}

	/**
	 * Test that the checkbox must be checked.
	 *
	 * @since 1.9.2
	 */
	public function test_checkbox_must_be_checked() {

		unset( $_POST['wordpoints_points_set-points'] );

		$this->assert_update_fails();
	}

	/**
	 * Test that the points value is required.
	 *
	 * @since 1.9.2
	 */
	public function test_points_value_required() {

		unset( $_POST['wordpoints_points-points'] );

		$this->assert_update_fails();
	}

	/**
	 * Test that the points value must be an integer.
	 *
	 * @since 1.9.2
	 */
	public function test_points_must_be_integer() {

		$_POST['wordpoints_points-points'] = 'foo';

		$this->assert_update_fails();
	}

	/**
	 * Test that the points value can be an integer.
	 *
	 * @since 1.9.2
	 */
	public function test_points_can_be_0() {

		$_POST['wordpoints_points-points'] = '0';
		$_POST['wordpoints_points_old-points'] = '5';

		wordpoints_set_points( $this->user->ID, 5, 'points', 'test' );

		wordpoints_points_profile_options_update( $this->user->ID );

		$this->assertEquals( 0, wordpoints_get_points( $this->user->ID, 'points' ) );
	}


	/**
	 * Test that the old points value is required.
	 *
	 * @since 1.9.2
	 */
	public function test_old_points_value_required() {

		unset( $_POST['wordpoints_points_old-points'] );

		$this->assert_update_fails();
	}

	/**
	 * Test that the old points value must be an integer.
	 *
	 * @since 1.9.2
	 */
	public function test_old_points_must_be_integer() {

		$_POST['wordpoints_points-points'] = 'foo';

		$this->assert_update_fails();
	}

	/**
	 * Test that the old points value can be an integer.
	 *
	 * @since 1.9.2
	 */
	public function test_old_points_can_be_0() {

		$_POST['wordpoints_points_old-points'] = '0';
		$_POST['wordpoints_points-points'] = '5';

		wordpoints_set_points( $this->user->ID, 0, 'points', 'test' );

		wordpoints_points_profile_options_update( $this->user->ID );

		$this->assertEquals( 5, wordpoints_get_points( $this->user->ID, 'points' ) );
	}

	/**
	 * Test that the reason is required.
	 *
	 * @since 1.9.2
	 */
	public function test_reason_required() {

		unset( $_POST['wordpoints_set_reason'] );

		$this->assert_update_fails();
	}

	/**
	 * Test that the reason is used in the log.
	 *
	 * @since 1.9.2
	 */
	public function test_reason_used_in_log() {

		wordpoints_points_profile_options_update( $this->user->ID );

		$query = new WordPoints_Points_Logs_Query();

		$this->assertStringMatchesFormat(
			'Points adjusted by %s. Reason: Testing.'
			, $query->get( 'row' )->text
		);
	}

	//
	// Helpers.
	//

	/**
	 * Assert that the update will fail.
	 *
	 * @since 1.9.2
	 */
	protected function assert_update_fails() {

		wordpoints_points_profile_options_update( $this->user->ID );

		$this->assertEquals( 0, wordpoints_get_points( $this->user->ID, 'points' ) );
	}

	/**
	 * Assert that the update will succeed.
	 *
	 * @since 1.9.2
	 */
	protected function assert_update_succeeds() {

		wordpoints_points_profile_options_update( $this->user->ID );

		$this->assertEquals( 5, wordpoints_get_points( $this->user->ID, 'points' ) );
	}
}

// EOF
