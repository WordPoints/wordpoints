<?php

/**
 * Test set points profile fields.
 *
 * @package WordPoints\Tests\UI
 * @since 1.0.1
 */

if ( ! class_exists( 'WordPoints_Selenium2TestCase' ) ) {
	return;
}

/**
 * Profile points update test case.
 *
 * @since 1.0.1
 *
 * @group ui
 */
class WordPoints_Admin_Profile_Points_Update_Test extends WordPoints_Selenium2TestCase {

	/**
	 * The user requires the manage_options and set_wordpoints_points capabilities.
	 *
	 * @since 1.0.1
	 *
	 * @type array $user_capabilities
	 */
	protected $user_capabilities = array(
		'manage_options'        => true,
		'set_wordpoints_points' => true,
	);

	/**
	 * Set up for the test by creating a points type.
	 *
	 * @since 1.0.1
	 */
	public function setUp() {

		parent::setUp();

		wordpoints_add_points_type(
			array(
				'name'   => 'Points',
				'prefix' => '$',
				'suffix' => 'pts.',
			)
		);

		wordpoints_add_points_type(
			array(
				'name'   => 'Credits',
				'prefix' => '',
				'suffix' => 'c',
			)
		);
	}

	/**
	 * Test that the points may be altered by admins from the admin profile.
	 *
	 * @since 1.0.1
	 */
	public function test_admin_profile_points_set() {

		$this->url( admin_url( 'profile.php' ) );

		$points = $this->byName( 'wordpoints_points-points' );
		$points->clear();
		$points->value( 5 );
		$this->byName( 'wordpoints_points_set-points' )->click();

		$credits = $this->byName( 'wordpoints_points-credits' );
		$credits->clear();
		$credits->value( 10 );
		// We don't check the checkbox.

		$this->byName( 'wordpoints_set_reason' )->value( 'Testing.' );

		$this->clickOnElement( 'submit' );

		$this->flush_cache();

		$test_user = wordpointstests_ui_user();

		$this->assertEquals( 5, wordpoints_get_points( $test_user->ID, 'points' ) );
		$this->assertEquals( 0, wordpoints_get_points( $test_user->ID, 'credits' ) );
	}

	/**
	 * Tear down afterward by deleting the points type.
	 *
	 * @since 1.0.1
	 */
	public function tearDown() {

		wordpoints_delete_points_type( 'points' );
		wordpoints_delete_points_type( 'credits' );

		parent::tearDown();
	}
}

// end of file /tests/phpunit/tests/points/profile-alter.php
