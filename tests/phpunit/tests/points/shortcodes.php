<?php

/**
 * Test the shortcodes of the points component.
 *
 * @package WordPoints\Tests\Points
 * @since 1.0.0
 */

/**
 * Points shortcodes test case.
 *
 * @since 1.0.0
 *
 * @group points
 * @group shortcodes
 */
class WordPoints_Points_Shortcodes_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test the wordpoints_points_top shortcode.
	 *
	 * @since 1.0.0
	 */
	function test_wordpoints_points_top() {

		$this->assertTrue( shortcode_exists( 'wordpoints_points_top' ) );

		// Create some data for the table.
		$user_ids = $this->factory->user->create_many( 4 );

		foreach ( $user_ids as $user_id ) {

			wordpoints_add_points( $user_id, 10, 'points', 'tests' );
		}

		// Check output with valid parameters.
		$output_3 =
	 	$matches =

		$this->assertTag(
			array(
				'tag'        => 'table',
				'attributes' => array(
					'class' => 'wordpoints-points-top-users',
				),
				'children'   => array(
					'only'         => array( 'tag' => 'tr' ),
					'less_than'    => 4,
					'greater_than' => 2,
				),
			)
			, wordpointstests_do_shortcode_func(
				  'wordpoints_points_top'
				, array( 'points_type' => 'points', 'users' => 3 )
			)
	 	);

		// Check failures with a normal user.
		$old_current_user = wp_get_current_user();
		$new_current_user = wp_set_current_user( $user_ids[0] );
		$new_current_user->set_role( 'subscriber' );

 		$this->assertEmpty(
			wordpointstests_do_shortcode_func(
				  'wordpoints_points_top'
				, array( 'points_type' => 'idontexist' )
			)
		);


		$this->assertEmpty(
			wordpointstests_do_shortcode_func(
				  'wordpoints_points_top'
				, array( 'points_type' => 'points', 'users' => 'invalid' )
			)
		);

		// Check failures with an admin user - we're testing that they get an error.
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
				'wordpoints_points_top'
				, array( 'points_type' => 'idontexist' )
			)
		);

		$this->assertTag(
			$shortcode_error
			, wordpointstests_do_shortcode_func(
				'wordpoints_points_top'
				, array( 'points_type' => 'points', 'users' => 'invalid' )
			)
		);

		wp_set_current_user( $old_current_user->ID );
	}

	/**
	 * Test the wordpoints_points_logs shortcode.
	 *
	 * @since 1.0.0
	 */
	function test_wordpoints_points_logs_shortcode() {

		$this->assertTrue( shortcode_exists( 'wordpoints_points_logs' ) );

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
						'less_than'    => 5,
						'greater_than' => 3,
						'only'         => array( 'tr' ),
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
					'class' => 'wordpoints-points-logs widefat'
				)
			)
			, wordpointstests_do_shortcode_func(
				'wordpoints_points_logs'
				, array( 'points_type' => 'points', 'datatables' => 0 )
			)
		);

		// Hide user column.
		$hidden_user_column = wordpointstests_do_shortcode_func(
			'wordpoints_points_logs'
			, array( 'points_type' => 'points', 'show_users' => 0 )
		);

		$this->assertTag(
			array(
				'tag'        => 'table',
				'attributes' => array(
					'class' => 'wordpoints-points-logs widefat datatables hide-user-column'
				)
			)
			, $hidden_user_column
		);

		$this->assertTag( array( 'tag' => 'style' ), $hidden_user_column );

		// Check failures with a normal user.
		$old_current_user = wp_get_current_user();
		$new_current_user = wp_set_current_user( $user_id );
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

		// Check failures with an admin user - we're testing that they get an error.
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

// end of file /tests/phpunit/tests/points/shortcodes.php
