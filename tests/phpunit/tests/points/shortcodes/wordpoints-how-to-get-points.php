<?php

/**
 * Testcase for the [wordpoints_how_to_get_points] shortcode.
 *
 * @package WordPoints\Tests\Points
 * @since 1.4.0
 */

/**
 * Test the [wordpoints_how_to_get_points] shortcode.
 *
 * @since 1.4.0
 *
 * @group points
 * @group shortcodes
 */
class WordPoints_How_To_Get_Points_Shortcode_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test that the [wordpoints_how_to_get_points] shortcode exists.
	 *
	 * @since 1.4.0
	 */
	public function test_shortcode_exists() {

		$this->assertTrue( shortcode_exists( 'wordpoints_how_to_get_points' ) );
	}

	/**
	 * Test that it displays a table of points hooks.
	 *
	 * @since 1.4.0
	 */
	public function test_displays_table_of_hooks() {

		// Create some points hooks for the table to display.
		wordpointstests_add_points_hook(
			'wordpoints_registration_points_hook'
			, array( 'points' => 10 )
		);

		wordpointstests_add_points_hook(
			'wordpoints_post_delete_points_hook'
			, array( 'points' => 20, 'post_type' => 'ALL' )
		);

		// Test that the hooks are displayed in the table.
		$this->assertTag(
			array(
				'tag'        => 'tbody',
				'children'   => array(
					'only'  => array( 'tag' => 'tr' ),
					'count' => 2,
				),
			)
			, wordpointstests_do_shortcode_func(
				'wordpoints_how_to_get_points'
				, array( 'points_type' => 'points' )
			)
		);
	}

	/**
	 * Test that it displays network hooks when network active on multisite.
	 *
	 * @since 1.4.0
	 */
	public function test_displays_network_hooks() {

		if ( ! is_wordpoints_network_active() ) {
			$this->markTestSkipped( 'WordPoints must be network active.' );
		}

		// Create some points hooks for the table to display.
		wordpointstests_add_points_hook(
			'wordpoints_registration_points_hook'
			, array( 'points' => 10 )
		);

		WordPoints_Points_Hooks::set_network_mode( true );
		wordpointstests_add_points_hook(
			'wordpoints_post_delete_points_hook'
			, array( 'points' => 20, 'post_type' => 'ALL' )
		);
		WordPoints_Points_Hooks::set_network_mode( false );

		// Test that both hooks are displayed in the table.
		$this->assertTag(
			array(
				'tag'        => 'tbody',
				'children'   => array(
					'only'  => array( 'tag' => 'tr' ),
					'count' => 2,
				),
			)
			, wordpointstests_do_shortcode_func(
				'wordpoints_how_to_get_points'
				, array( 'points_type' => 'points' )
			)
		);
	}

	/**
	 * Test that nothing is displayed to a normal user on failure.
	 *
	 * @since 1.4.0
	 */
	public function test_nothing_displayed_to_normal_user_on_failure() {

		$user_id = $this->factory->user->create();

		$old_current_user = wp_get_current_user();
		wp_set_current_user( $user_id );

		// There should be no error with an invalid points type.
		$this->assertEquals( null, wordpointstests_do_shortcode_func( 'wordpoints_how_to_get_points' ) );

		wp_set_current_user( $old_current_user->ID );
	}

	/**
	 * Test that an error is displayed to an admin user on failure.
	 *
	 * @since 1.4.0
	 */
	public function test_error_displayed_to_admin_user_on_failure() {

		// Create a user and assign them admin-like capabilities.
		$user = $this->factory->user->create_and_get();
		$user->add_cap( 'manage_options' );

		$old_current_user = wp_get_current_user();
		wp_set_current_user( $user->ID );

		// Check for an error when no points type is provided.
		$this->assertTag(
			array(
				'tag' => 'p',
				'attributes' => array(
					'class' => 'wordpoints-shortcode-error',
				),
			)
			, wordpointstests_do_shortcode_func( 'wordpoints_how_to_get_points' )
		);

		wp_set_current_user( $old_current_user->ID );
	}
}
