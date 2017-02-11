<?php

/**
 * Testcase for the [wordpoints_points_top] shortcode.
 *
 * @package WordPoints\Tests\Points
 * @since 1.4.0
 */

/**
 * Test the [wordpoints_points_top] shortcode.
 *
 * Since 1.0.0 this was a part of the WordPoints_Points_Shortcodes_Test, which was
 * split into a separate testcase for each shortcode.
 *
 * @since 1.4.0
 *
 * @group points
 * @group shortcodes
 *
 * @covers WordPoints_Points_Shortcode_Top_Users
 */
class WordPoints_Points_Top_Shortcode_Test extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * Test that the [wordpoints_points_top] shortcode exists.
	 *
	 * @since 1.4.0
	 *
	 * @coversNothing
	 */
	public function test_shortcode_exists() {

		$this->assertTrue( shortcode_exists( 'wordpoints_points_top' ) );
	}

	/**
	 * Test that the old version of the class is deprecated.
	 *
	 * @since 2.3.0
	 *
	 * @covers WordPoints_Points_Top_Shortcode
	 *
	 * @expectedDeprecated WordPoints_Points_Top_Shortcode::__construct
	 */
	public function test_deprecated_version() {

		new WordPoints_Points_Top_Shortcode( array(), '' );
	}

	/**
	 * Check that it displays a table of the top users correctly.
	 *
	 * @since 1.4.0
	 */
	public function test_parameters_work_properly() {

		// Create some data for the table.
		$this->factory->wordpoints->points_log->create_many( 4 );

		// Check output with valid parameters.
		$document = new DOMDocument;
		$document->loadHTML(
			$this->do_shortcode(
				'wordpoints_points_top'
				, array( 'points_type' => 'points', 'users' => 3 )
			)
		);
		$xpath = new DOMXPath( $document );

		$table_classes = $xpath->query( '//table' )
			->item( 0 )
			->attributes
			->getNamedItem( 'class' )
			->nodeValue;

		$this->assertContains( 'wordpoints-points-top-users', $table_classes );

		$this->assertSame( 3, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Test that it displays nothing for a subscriber when given an invalid arg.
	 *
	 * @since 1.4.0
	 */
	public function test_displays_nothing_to_normal_user_on_fail() {

		// Check failures with a normal user.
		$old_current_user = wp_get_current_user();
		$new_current_user = wp_set_current_user( $this->factory->user->create() );
		$new_current_user->set_role( 'subscriber' );

		$this->assertSame(
			''
			, $this->do_shortcode(
				'wordpoints_points_top'
				, array( 'points_type' => 'idontexist' )
			)
		);

		$this->assertSame(
			''
			, $this->do_shortcode(
				'wordpoints_points_top'
				, array( 'points_type' => 'points', 'users' => 'invalid' )
			)
		);

		wp_set_current_user( $old_current_user->ID );
	}

	/**
	 * Test that it displays an error for an administrator when given an invalid arg.
	 *
	 * @since 1.4.0
	 */
	public function test_displays_error_to_admins_on_fail() {

		// Check failures with an admin user - we're testing that they get an error.
		$old_current_user = wp_get_current_user();
		$new_current_user = wp_set_current_user( $this->factory->user->create() );
		$new_current_user->set_role( 'administrator' );

		$this->assertWordPointsShortcodeError(
			$this->do_shortcode(
				'wordpoints_points_top'
				, array( 'points_type' => 'idontexist' )
			)
		);

		$this->assertWordPointsShortcodeError(
			$this->do_shortcode(
				'wordpoints_points_top'
				, array( 'points_type' => 'points', 'users' => 'invalid' )
			)
		);

		wp_set_current_user( $old_current_user->ID );
	}

	/**
	 * Test that it displays users who haven't been awarded any points yet.
	 *
	 * @since 1.7.0
	 */
	public function test_displays_users_not_awarded_points() {

		$user_ids = $this->factory->user->create_many( 2 );

		wordpoints_set_points( $user_ids[0], 10, 'points', 'test' );

		// Check output with valid parameters.
		$document = new DOMDocument;
		$document->loadHTML(
			$this->do_shortcode(
				'wordpoints_points_top'
				, array( 'points_type' => 'points', 'users' => 3 )
			)
		);
		$xpath = new DOMXPath( $document );

		$this->assertSame( 3, $xpath->query( '//tbody/tr' )->length );
	}
}

// EOF
