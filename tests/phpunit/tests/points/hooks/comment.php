<?php

/**
 * A test case for the comment points hook.
 *
 * @package WordPoints\Tests
 * @since 1.3.0
 */

/**
 * Test that the comment points hook functions as expected.
 *
 * Since 1.0.0 it was a part of WordPoints_Included_Points_Hooks_Test.
 *
 * @since 1.3.0
 *
 * @group points
 * @group points_hooks
 */
class WordPoints_Comment_Points_Hook_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test that points are awarded as expected.
	 *
	 * Since 1.3.0 it was called test_points_awarded_removed().
	 *
	 * @since 1.4.0
	 */
	public function test_points_awarded() {

		wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'points' => 10 )
		);

		$user_id = $this->factory->user->create();

		wordpoints_set_points( $user_id, 100, 'points', 'test' );
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		$post_id = $this->factory->post->create(
			array( 'post_author' => $user_id )
		);

		$comment_id = $this->factory->comment->create(
			array(
				'user_id'         => $user_id,
				'comment_post_ID' => $post_id,
			)
		);

		$this->assertEquals( 110, wordpoints_get_points( $user_id, 'points' ) );

		// Points should not be awarded twice in a row.
		do_action( 'transition_comment_status', 'approve', 'hold', get_comment( $comment_id ) );

		$this->assertEquals( 110, wordpoints_get_points( $user_id, 'points' ) );

		// Test that points are awarded on transition from hold.
		$comment_id = $this->factory->comment->create(
			array(
				'user_id'         => $user_id,
				'comment_post_ID' => $post_id,
				'comment_status'  => 'hold',
			)
		);

		wp_set_comment_status( $comment_id, 'approve' );
		$this->assertEquals( 120, wordpoints_get_points( $user_id, 'points' ) );

		// Test that points are awarded on transition from hold.
		$comment_id = $this->factory->comment->create(
			array(
				'user_id'         => $user_id,
				'comment_post_ID' => $post_id,
				'comment_status'  => 'spam',
			)
		);

		wp_set_comment_status( $comment_id, 'approve' );
		$this->assertEquals( 130, wordpoints_get_points( $user_id, 'points' ) );

		// Test that points are awarded on transition from hold.
		$comment_id = $this->factory->comment->create(
			array(
				'user_id'         => $user_id,
				'comment_post_ID' => $post_id,
				'comment_status'  => 'trash',
			)
		);

		wp_set_comment_status( $comment_id, 'approve' );
		$this->assertEquals( 140, wordpoints_get_points( $user_id, 'points' ) );

	} // public function test_points_awarded()

	/**
	 * Test that points are awarded again after the comment remove points hook runs.
	 *
	 * @since 1.4.0
	 */
	public function test_points_awarded_again_after_comment_remove_hook_runs() {

		$hook = wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'points' => 10 )
		);
		$this->assertInstanceOf( 'WordPoints_Comment_Points_Hook', $hook );

		$hook = wordpointstests_add_points_hook(
			'wordpoints_comment_removed_points_hook'
			, array( 'points' => 10 )
		);
		$this->assertInstanceOf( 'WordPoints_Comment_Removed_Points_Hook', $hook );

		$user_id = $this->factory->user->create();

		wordpoints_set_points( $user_id, 100, 'points', 'test' );
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		$comment_id = $this->factory->comment->create(
			array(
				'user_id'         => $user_id,
				'comment_post_ID' => $this->factory->post->create(
					array( 'post_author' => $user_id )
				),
			)
		);

		$this->assertEquals( 110, wordpoints_get_points( $user_id, 'points' ) );

		// Test that status transitions award/remove points correctly.
		wp_set_comment_status( $comment_id, 'hold' );
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		wp_set_comment_status( $comment_id, 'approve' );
		$this->assertEquals( 110, wordpoints_get_points( $user_id, 'points' ) );

		wp_set_comment_status( $comment_id, 'spam' );
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		wp_set_comment_status( $comment_id, 'approve' );
		$this->assertEquals( 110, wordpoints_get_points( $user_id, 'points' ) );

		wp_set_comment_status( $comment_id, 'trash' );
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		wp_set_comment_status( $comment_id, 'approve' );
		$this->assertEquals( 110, wordpoints_get_points( $user_id, 'points' ) );

	} // public function test_points_awarded_again_after_comment_remove_hook_runs()

	/**
	 * Test that points are only awarded for the specified post type.
	 *
	 * @since 1.5.0
	 */
	public function test_points_only_awarded_for_specified_post_type() {

		wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'points' => 20, 'post_type' => 'post' )
		);

		$user_id = $this->factory->user->create();

		// Create a comment on a post.
		$this->factory->comment->create(
			array(
				'user_id' => $user_id,
				'comment_post_ID' => $this->factory->post->create(
					array( 'post_type' => 'post' )
				),
			)
		);

		// Test that points were awarded for the comment.
		$this->assertEquals( 20, wordpoints_get_points( $user_id, 'points' ) );

		// Now create a comment on a page.
		$this->factory->comment->create(
			array(
				'user_id' => $user_id,
				'comment_post_ID' => $this->factory->post->create(
					array( 'post_type' => 'page' )
				),
			)
		);

		// Test that no points were awarded for the comment.
		$this->assertEquals( 20, wordpoints_get_points( $user_id, 'points' ) );

	} // public function test_points_only_awarded_for_specified_post_type()

	/**
	 * Test that the logs are cleaned properly when a comment is deleted.
	 *
	 * @since 1.4.0
	 */
	public function test_logs_cleaned_on_comment_deletion() {

		wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'points' => 10 )
		);

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $user_id ) );
		$comment_id = $this->factory->comment->create(
			array(
				'user_id'         => $user_id,
				'comment_post_ID' => $post_id,
			)
		);

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
				'meta_value' => $post_id,
			)
		);

		$this->assertEquals( 1, $query->count() );

		$log = $query->get( 'row' );

		$this->assertEquals(
			sprintf(
				_x( 'Comment on %s.', 'points log description', 'wordpoints' )
				, '<a href="' . get_permalink( $post_id ) . '">'
					. get_the_title( $post_id )
					. '</a>'
			)
			, $log->text
		);
	}

	/**
	 * Test that logs are cleaned properly when a post is deleted.
	 *
	 * @since 1.4.0
	 */
	public function test_logs_cleaned_on_post_deletion() {

		wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'points' => 10 )
		);

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $user_id ) );
		$comment_id = $this->factory->comment->create(
			array(
				'user_id'         => $user_id,
				'comment_post_ID' => $post_id,
			)
		);

		wp_delete_comment( $comment_id, true );
		wp_delete_post( $post_id, true );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'comment_approve',
				'meta_key'   => 'post_id',
				'meta_value' => $post_id,
			)
		);

		$this->assertEquals( 0, $query->count() );

		$query = new WordPoints_Points_Logs_Query(
			array( 'log_type' => 'comment_approve' )
		);
		$log = $query->get( 'row' );

		$this->assertEquals(
			_x( 'Comment', 'points log description', 'wordpoints' )
			, $log->text
		);
	}

	/**
	 * Test that logs are hidden for users who don't have the required capabilities.
	 *
	 * @since 1.3.0
	 */
	public function test_logs_hidden_for_insufficient_caps() {

		wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'approve' => 10, 'disapprove' => 10 )
		);

		$post = $this->factory->post->create_and_get(
			array( 'post_author' => $this->factory->user->create() )
		);

		$this->factory->comment->create(
			array(
				'user_id'         => $this->factory->user->create(),
				'comment_post_ID' => $post->ID,
			)
		);

		// Make the post private.
		$post->post_status = 'private';
		wp_update_post( $post );

		wp_set_current_user( $this->factory->user->create() );

		// The log shouldn't be displayed.
		$this->assertTag(
			array(
				'tag' => 'tbody',
				'content' => 'regexp:/[\r\t]*/',
			)
			, wordpoints_points_logs_shortcode( array( 'points_type' => 'points' ) )
		);
	}
}
