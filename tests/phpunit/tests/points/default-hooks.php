<?php

/**
 * Tests for the WordPoints points hooks.
 *
 * @package WordPoints\Tests\Points\Hooks
 * @since 1.0.0
 */

/**
 * Test the default points hooks.
 *
 * @since 1.0.0
 *
 * @group points
 * @group points_hooks
 */
class WordPoints_Included_Points_Hooks_Test extends WordPoints_Points_UnitTestCase {

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

		// Check that points were aded when the post was created.
		$this->assertEquals( 20, wordpoints_get_points( $user_id, 'points' ) );

		// Now convert the post back to a draft.
		$post = get_post( $post_id );
		$post->post_status = 'draft';
		wp_update_post( $post );

		// Publish it again.
		wp_publish_post( $post->ID );

		// Check that points were not awarded a second time.
		$this->assertEquals( 20, wordpoints_get_points( $user_id, 'points' ) );

		wp_delete_post( $post_id, true );

		// Check that the points were removed when the post was deleted.
		$this->assertEquals( 0, wordpoints_get_points( $user_id, 'points' ) );

		// Check that the logs were cleaned up properly.
		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'post_publish',
				'meta_query' => array(
					array(
						'key'   => 'post_id',
						'value' => $post_id,
					),
				),
			)
		);

		$this->assertEquals( 0, $query->count() );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'post_publish',
				'meta_query' => array(
					array(
						'key'   => 'post_type',
						'value' => 'post',
					),
				),
			)
		);

		$this->assertEquals( 1, $query->count() );

		$log = $query->get( 'row' );

		$this->assertEquals( sprintf( _x( '%s published.', 'points log description', 'wordpoints' ), 'Post' ), $log->text );

		// Make sure points aren't deleted when auto-drafts are deleted.
		wordpoints_alter_points( $user_id, 20, 'points', 'test' );

		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
				'post_status' => 'auto-draft',
			)
		);

		wp_delete_post( $post_id, true );

		$this->assertEquals( 20, wordpoints_get_points( $user_id, 'points' ) );

		/*
		 * If an auto-draft is moved to the trash, it gets the 'trash' status, so
		 * in that case just checking the post status is insufficient.
		 *
		 * See https://core.trac.wordpress.org/ticket/16116
		 */
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
				'post_title' => __( 'Auto Draft', 'default' ),
			)
		);

		wp_delete_post( $post_id, true );

		$this->assertEquals( 40, wordpoints_get_points( $user_id, 'points' ) );

		// Test the non-public post types like revisions are ignored.
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
				'post_type'   => 'revision',
			)
		);

		$this->assertEquals( 40, wordpoints_get_points( $user_id, 'points' ) );

		wp_delete_post( $post_id, true );

		$this->assertEquals( 40, wordpoints_get_points( $user_id, 'points' ) );
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

		// Check that the logs are cleaned up when a comment is deleted.
		$comment = get_comment( $comment_id );

		wp_delete_comment( $comment_id, true );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'comment_approve',
				'meta_key'   => 'comment_id',
				'meta_value' => $comment_id,
			)
		);

		$this->assertEquals( 0, $query->count() );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'comment_approve',
				'meta_key'   => 'post_id',
				'meta_value' => $comment->comment_post_ID,
			)
		);

		$this->assertEquals( 4, $query->count() );

		$log = $query->get( 'row' );

		$this->assertEquals(
			'<span title="' . __( 'Comment removed...', 'wordpoints' ) . '">'
				. sprintf(
					_x( 'Comment on %s.', 'points log description', 'wordpoints' )
					, '<a href="' . get_permalink( $comment->comment_post_ID ) . '">'
						. get_the_title( $comment->comment_post_ID )
						. '</a>'
				)
				. '</span>'
			, $log->text
		);
	}

	/**
	 * Test the periodic points hook.
	 *
	 * @since 1.0.0
	 */
	function test_periodic_points_hook() {

		$hook = wordpointstests_add_points_hook(
			'wordpoints_periodic_points_hook'
			, array(
				'period' => DAY_IN_SECONDS,
				'points' => 10
			)
		);

		if ( ! $hook ) {
			$this->fail( 'Unable to create a periodic points hook.' );
		}

		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$hook->hook();
		$this->assertEquals( 10, wordpoints_get_points( $user_id, 'points' ) );

		$hook->hook();
		$this->assertEquals( 10, wordpoints_get_points( $user_id, 'points' ) );

		// Time machine!
		$global = ( ! is_multisite() || is_wordpoints_network_active() );
		update_user_option( $user_id, 'wordpoints_points_period_start', current_time( 'timestamp' ) - DAY_IN_SECONDS, $global );

		$hook->hook();
		$this->assertEquals( 20, wordpoints_get_points( $user_id, 'points' ) );
	}
}
