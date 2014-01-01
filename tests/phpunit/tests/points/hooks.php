<?php

/**
 * Tests for the WordPoints points hooks.
 *
 * @package WordPoints\Tests\Points\Hooks
 * @since 1.0.0
 */

/**
 * Test the points hooks and related API.
 *
 * @since 1.0.0
 *
 * @group points
 * @group points_hooks
 */
class WordPoints_Included_Points_Hooks_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test the get and save hooks functions.
	 *
	 * @since 1.2.0
	 */
	function test_get_and_save() {

		wordpointstests_add_points_hook( 'wordpoints_registration_points_hook', array( 'points' => 10 ) );

		$points_types_hooks = WordPoints_Points_Hooks::get_points_types_hooks();

		$this->assertEquals(
			array( 'points' => array( 0 => 'wordpoints_registration_points_hook-1' ) )
			, $points_types_hooks
		);

		$this->assertEquals( $points_types_hooks, get_option( 'wordpoints_points_types_hooks' ) );

		WordPoints_Points_Hooks::save_points_types_hooks( array() );

		$this->assertEquals( array(), get_option( 'wordpoints_points_types_hooks' ) );

		// Network mode.
		WordPoints_Points_Hooks::set_network_mode( true );

		wordpointstests_add_points_hook( 'wordpoints_registration_points_hook', array( 'points' => 10 ) );

		$points_types_hooks = WordPoints_Points_Hooks::get_points_types_hooks();

		$this->assertEquals(
			array( 'points' => array( 0 => 'wordpoints_registration_points_hook-1' ) )
			, $points_types_hooks
		);

		$this->assertEquals( $points_types_hooks, get_site_option( 'wordpoints_points_types_hooks' ) );

		WordPoints_Points_Hooks::save_points_types_hooks( array() );

		$this->assertEquals( array(), get_site_option( 'wordpoints_points_types_hooks' ) );

		WordPoints_Points_Hooks::set_network_mode( false );

		WordPoints_Points_Hooks::save_points_types_hooks( $points_types_hooks );

		$this->assertEquals( $points_types_hooks, WordPoints_Points_Hooks::get_points_types_hooks() );
	}

	/**
	 * Test the register points hook.
	 *
	 * @since 1.0.0
	 */
	function test_registration_points_hook() {

		wordpointstests_add_points_hook( 'wordpoints_registration_points_hook', array( 'points' => 10 ) );

		$user_id = $this->factory->user->create();

		$this->assertEquals( 10, wordpoints_get_points( $user_id, 'points' ) );
	}

	/**
	 * Test the post points hook.
	 *
	 * @since 1.0.0
	 */
	function test_post_points_hook() {

		wordpointstests_add_points_hook( 'wordpoints_post_points_hook', array( 'publish' => 20, 'trash' => 20, 'post_type' => 'ALL' ) );

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $user_id ) );

		$this->assertEquals( 20, wordpoints_get_points( $user_id, 'points' ) );

		wp_delete_post( $post_id, true );

		$this->assertEquals( 0, wordpoints_get_points( $user_id, 'points' ) );
	}

	/**
	 * Test the comments points hook.
	 *
	 * @since 1.0.0
	 */
	function test_comment_points_hook() {

		wordpointstests_add_points_hook( 'wordpoints_comment_points_hook', array( 'approve' => 10, 'disapprove' => 10 ) );

		$user_id    = $this->factory->user->create();
		$comment_id = $this->factory->comment->create(
			array(
				'user_id'         => $user_id,
				'comment_post_ID' => $this->factory->post->create(
					array( 'post_author' => $user_id )
				),
			)
		);

		$this->assertEquals( 10, wordpoints_get_points( $user_id, 'points' ) );

		wp_set_comment_status( $comment_id, 'hold' );
		$this->assertEquals( 0, wordpoints_get_points( $user_id, 'points' ) );

		wp_set_comment_status( $comment_id, 'approve' );
		$this->assertEquals( 10, wordpoints_get_points( $user_id, 'points' ) );

		wp_set_comment_status( $comment_id, 'spam' );
		$this->assertEquals( 0, wordpoints_get_points( $user_id, 'points' ) );

		wp_set_comment_status( $comment_id, 'approve' );
		$this->assertEquals( 10, wordpoints_get_points( $user_id, 'points' ) );

		wp_set_comment_status( $comment_id, 'trash' );
		$this->assertEquals( 0, wordpoints_get_points( $user_id, 'points' ) );

		wp_set_comment_status( $comment_id, 'approve' );
		$this->assertEquals( 10, wordpoints_get_points( $user_id, 'points' ) );
	}

	/**
	 * Test the periodic points hook.
	 *
	 * @since 1.0.0
	 */
	function test_periodic_points_hook() {

		wordpointstests_add_points_hook( 'wordpoints_periodic_points_hook', array( 'period' => DAY_IN_SECONDS, 'points' => 10 ) );

		$user_id = $this->factory->user->create();

		wp_set_current_user( $user_id );
		$this->assertEquals( 10, wordpoints_get_points( $user_id, 'points' ) );

		unset( $GLOBALS['current_user'] );

		wp_set_current_user( $user_id );
		$this->assertEquals( 10, wordpoints_get_points( $user_id, 'points' ) );

		// Time machine!
		update_user_meta( $user_id, 'wordpoints_points_period_start', current_time( 'timestamp' ) - DAY_IN_SECONDS );

		unset( $GLOBALS['current_user'] );

		wp_set_current_user( $user_id );
		$this->assertEquals( 20, wordpoints_get_points( $user_id, 'points' ) );
	}
}
