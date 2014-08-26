<?php

/**
 * A test case for the post delete points hook.
 *
 * @package WordPoints\Tests
 * @since 1.4.0
 */

/**
 * Test that the post delete points hook functions as expected.
 *
 * Since 1.3.0 this had been part of the WordPoints_Post_Points_Hook_Test. Now the
 * hook has been split into the post publish and post delete hooks.
 *
 * @since 1.4.0
 *
 * @group points
 * @group points_hooks
 */
class WordPoints_Post_Delete_Points_Hook_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test that points are removed when a post is permanently deleted.
	 *
	 * @since 1.4.0
	 */
	public function test_points_removed_when_post_deleted() {

		wordpointstests_add_points_hook(
			'wordpoints_post_delete_points_hook'
			, array( 'points' => 20, 'post_type' => 'ALL' )
		);

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $user_id ) );

		wordpoints_set_points( $user_id, 100, 'points', 'test' );

		// Check that points were set correctly.
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		// Now delete the post.
		wp_delete_post( $post_id, true );

		// Check that the points were removed when the post was deleted.
		$this->assertEquals( 80, wordpoints_get_points( $user_id, 'points' ) );
	}

	/**
	 * Make sure points aren't deleted when auto-drafts are deleted.
	 *
	 * @since 1.4.0
	 */
	public function test_points_not_deleted_for_auto_drafts() {

		wordpointstests_add_points_hook(
			'wordpoints_post_delete_points_hook'
			, array( 'points' => 20, 'post_type' => 'ALL' )
		);

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
				'post_status' => 'auto-draft',
			)
		);

		// Give the user some points.
		wordpoints_set_points( $user_id, 100, 'points', 'test' );

		// Check that points were set correctly.
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		// Now delete the post.
		wp_delete_post( $post_id, true );

		// No points should have been removed.
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

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

		// No points should have been deleted.
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );
	}

	/**
	 * Test the non-public post types like revisions are ignored.
	 *
	 * @since 1.4.0
	 */
	public function test_non_public_post_types_ignored() {

		wordpointstests_add_points_hook(
			'wordpoints_post_delete_points_hook'
			, array( 'points' => 20, 'post_type' => 'ALL' )
		);

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
				'post_type'   => 'revision',
			)
		);

		// Give the user some points.
		wordpoints_set_points( $user_id, 100, 'points', 'test' );

		// Check that points were set correctly.
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		// Delete the post.
		wp_delete_post( $post_id, true );

		// No points should have been deleted.
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );
	}

	/**
	 * Test that points are only removed for the specified post type.
	 *
	 * @since 1.4.0
	 */
	public function test_points_only_awarded_for_specified_post_type() {

		wordpointstests_add_points_hook(
			'wordpoints_post_delete_points_hook'
			, array( 'points' => 20, 'post_type' => 'post' )
		);

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
				'post_type'   => 'post',
			)
		);

		// Give the user some points.
		wordpoints_set_points( $user_id, 100, 'points', 'test' );

		// Check that points were set correctly.
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		// Delete the post.
		wp_delete_post( $post_id, true );

		// The points should have been removed.
		$this->assertEquals( 80, wordpoints_get_points( $user_id, 'points' ) );

		// Now create a page.
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
				'post_type'   => 'page',
			)
		);

		// Delete the page.
		wp_delete_post( $post_id, true );

		// This time, no points should have been deleted.
		$this->assertEquals( 80, wordpoints_get_points( $user_id, 'points' ) );
	}
}

// EOF
