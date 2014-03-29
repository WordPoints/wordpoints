<?php

/**
 * A test case for the comment removed points hook.
 *
 * @package WordPoints\Tests
 * @since 1.4.0
 */

/**
 * Test that the comment removed points hook functions as expected.
 *
 * @since 1.4.0
 *
 * @group points
 * @group points_hooks
 */
class WordPoints_Comment_Removed_Points_Hook_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test that points are removed as expected.
	 *
	 * @since 1.4.0
	 */
	public function test_points_removed() {

		$hook = wordpointstests_add_points_hook(
			'wordpoints_comment_removed_points_hook'
			, array( 'points' => 10 )
		);
		$this->assertInstanceOf( 'WordPoints_Comment_Removed_Points_Hook', $hook );

		$user_id     = $this->factory->user->create();
		$comment_ids = $this->factory->comment->create_many(
			3
			, array(
				'user_id'         => $user_id,
				'comment_post_ID' => $this->factory->post->create(
					array( 'post_author' => $user_id )
				),
			)
		);

		wordpoints_set_points( $user_id, 100, 'points', 'test' );
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		// Test that status transitions remove points correctly.
		wp_set_comment_status( array_pop( $comment_ids ), 'hold' );
		$this->assertEquals( 90, wordpoints_get_points( $user_id, 'points' ) );

		wp_set_comment_status( array_pop( $comment_ids ), 'spam' );
		$this->assertEquals( 80, wordpoints_get_points( $user_id, 'points' ) );

		wp_set_comment_status( array_pop( $comment_ids ), 'trash' );
		$this->assertEquals( 70, wordpoints_get_points( $user_id, 'points' ) );
	}
}
