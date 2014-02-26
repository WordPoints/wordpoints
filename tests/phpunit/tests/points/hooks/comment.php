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
	 * Test that points are awarded and removed as expected.
	 *
	 * @since 1.3.0
	 */
	public function test_points_awarded_removed() {

		wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'approve' => 10, 'disapprove' => 10 )
		);

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

		// Points should not be awarded twice in a row.
		do_action( 'transition_comment_status', 'approve', 'hold', get_comment( $comment_id ) );

		$this->assertEquals( 10, wordpoints_get_points( $user_id, 'points' ) );

		// Test that status transitions award/remove points correctly.
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

	} // public function test_points_awarded_removed()

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
