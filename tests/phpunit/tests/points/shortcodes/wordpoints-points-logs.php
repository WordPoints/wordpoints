<?php

/**
 * Testcase for the [wordpoints_points_logs] shortcode.
 *
 * @package WordPoints\Tests\Points
 * @since 1.4.0
 */

/**
 * Test the [wordpoints_points_logs] shortcode.
 *
 * Since 1.0.0 this was a part of the WordPoints_Points_Shortcodes_Test, which was
 * split into a separate testcase for each shortcode.
 *
 * @since 1.4.0
 *
 * @group points
 * @group shortcodes
 */
class WordPoints_Points_Logs_Shortcode_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test that the [wordpoints_points_logs] shortcode exists.
	 *
	 * @since 1.4.0
	 */
	public function test_shortcode_exists() {

		$this->assertTrue( shortcode_exists( 'wordpoints_points_logs' ) );
	}

	/**
	 * Test the 'datatable' attribute.
	 *
	 * @since 1.4.0
	 */
	public function test_datatable_attribute() {

		// Create some data for the table to display.
		$user_id = $this->factory->user->create();

		for ( $i = 1; $i < 5; $i++ ) {

			wordpoints_add_points( $user_id, 10, 'points', 'test' );
		}

		// Default datatable.
		$this->assertTag(
			array(
				'tag'        => 'table',
				'attributes' => array(
					'class' => 'wordpoints-points-logs widefat datatables',
				),
				'child'      => array(
					'tag'      => 'tbody',
					'children' => array(
						'count' => 4,
						'only'  => array( 'tr' ),
					),
				),
			)
			, wordpointstests_do_shortcode_func(
				'wordpoints_points_logs'
				, array( 'points_type' => 'points' )
			)
		);

		// Non-datatable.
		$this->assertTag(
			array(
				'tag'        => 'table',
				'attributes' => array(
					'class' => 'wordpoints-points-logs widefat',
				),
			)
			, wordpointstests_do_shortcode_func(
				'wordpoints_points_logs'
				, array( 'points_type' => 'points', 'datatables' => 0 )
			)
		);

	} // public function test_datatable_attribute()

	/**
	 * Test the 'show_users' attribute.
	 *
	 * @since 1.4.0
	 */
	public function test_show_users_attribute() {

		// Create some data for the table to display.
		$user_id = $this->factory->user->create();

		for ( $i = 1; $i < 5; $i++ ) {

			wordpoints_add_points( $user_id, 10, 'points', 'test' );
		}

		// The user column should be displayed by default.
		$this->assertTag(
			array(
				'tag'   => 'table',
				'child' => array(
					'tag'   => 'thead',
					'child' => array(
						'tag'      => 'tr',
						'children' => array(
							'only'  => array( 'th' ),
							'count' => 4,
						),
					),
				),
			)
			, wordpointstests_do_shortcode_func(
				'wordpoints_points_logs'
				, array( 'points_type' => 'points' )
			)
		);

		// Check that it is hidden.
		$this->assertTag(
			array(
				'tag'   => 'table',
				'child' => array(
					'tag'   => 'thead',
					'child' => array(
						'tag'      => 'tr',
						'children' => array(
							'only'  => array( 'th' ),
							'count' => 3,
						),
					),
				),
			)
			, wordpointstests_do_shortcode_func(
				'wordpoints_points_logs'
				, array( 'points_type' => 'points', 'show_users' => 0 )
			)
		);

	} // public function test_show_users_attribute()

	/**
	 * Check failures with a normal user display nothing.
	 *
	 * @since 1.4.0
	 */
	public function test_displays_nothing_to_normal_user_on_fail() {

		$old_current_user = wp_get_current_user();
		$new_current_user = wp_set_current_user( $this->factory->user->create() );
		$new_current_user->set_role( 'subscriber' );

		$this->assertEmpty(
			wordpointstests_do_shortcode_func(
				'wordpoints_points_logs'
				, array( 'points_type' => 'idontexist' )
			)
		);

		$this->assertEmpty(
			wordpointstests_do_shortcode_func(
				'wordpoints_points_logs'
				, array( 'points_type' => 'points', 'query' => 'invalid' )
			)
		);

		wp_set_current_user( $old_current_user->ID );
	}

	/**
	 * Check failures with an admin user dispaly an error.
	 *
	 * @since 1.4.0
	 */
	public function test_displays_error_to_admin_user_on_fail() {

		$old_current_user = wp_get_current_user();
		$new_current_user = wp_set_current_user( $this->factory->user->create() );
		$new_current_user->set_role( 'administrator' );

		$shortcode_error = array(
			'tag' => 'p',
			'attributes' => array(
				'class' => 'wordpoints-shortcode-error',
			),
		);

		$this->assertTag(
			$shortcode_error
			, wordpointstests_do_shortcode_func(
				'wordpoints_points_logs'
				, array( 'points_type' => 'idontexist' )
			)
		);

		$this->assertTag(
			$shortcode_error
			, wordpointstests_do_shortcode_func(
				'wordpoints_points_logs'
				, array( 'points_type' => 'points', 'query' => 'invalid' )
			)
		);

		wp_set_current_user( $old_current_user->ID );
	}
}
