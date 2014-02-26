<?php

/**
 * A test case for the post points hook.
 *
 * @package WordPoints\Tests
 * @since 1.3.0
 */

/**
 * Test that post points hook functions as expected.
 *
 * Since 1.0.0 it was a part of WordPoints_Included_Points_Hooks_Test.
 *
 * @since 1.3.0
 *
 * @group points
 * @group points_hooks
 */
class WordPoints_Post_Points_Hook_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test that points are added and removed as expected.
	 *
	 * @since 1.3.0
	 */
	public function test_points_awarded_removed() {

		wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'publish' => 20, 'trash' => 20, 'post_type' => 'ALL' )
		);

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

	} // public function test_points_awarded_removed()

	/**
	 * Make sure points aren't deleted when auto-drafts are deleted.
	 *
	 * @since 1.3.0
	 */
	public function test_points_not_deleted_for_auto_drafts() {

		wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'publish' => 20, 'trash' => 20, 'post_type' => 'ALL' )
		);

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
				'post_status' => 'auto-draft',
			)
		);

		wp_delete_post( $post_id, true );

		$this->assertEquals( 0, wordpoints_get_points( $user_id, 'points' ) );

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

		$this->assertEquals( 20, wordpoints_get_points( $user_id, 'points' ) );
	}

	/**
	 * Test the non-public post types like revisions are ignored.
	 *
	 * @since 1.3.0
	 */
	public function test_non_public_post_types_ignored() {

		wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'publish' => 20, 'trash' => 20, 'post_type' => 'ALL' )
		);

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
				'post_type'   => 'revision',
			)
		);

		$this->assertEquals( 0, wordpoints_get_points( $user_id, 'points' ) );

		// Set the user's points so that there will be some to delete.
		wordpoints_set_points( $user_id, 30, 'points', 'test' );

		wp_delete_post( $post_id, true );

		$this->assertEquals( 30, wordpoints_get_points( $user_id, 'points' ) );
	}

	/**
	 * Test that points are only awarded for the specified post type.
	 *
	 * @since 1.3.0
	 */
	public function test_points_only_awarded_for_specified_post_type() {

		wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'publish' => 20, 'trash' => 20, 'post_type' => 'post' )
		);

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
				'post_type'   => 'post',
			)
		);

		$this->assertEquals( 20, wordpoints_get_points( $user_id, 'points' ) );

		wp_delete_post( $post_id, true );

		$this->assertEquals( 0, wordpoints_get_points( $user_id, 'points' ) );

		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
				'post_type'   => 'page',
			)
		);

		$this->assertEquals( 0, wordpoints_get_points( $user_id, 'points' ) );

		// Set the user's points so that there will be some to delete.
		wordpoints_set_points( $user_id, 30, 'points', 'test' );

		wp_delete_post( $post_id, true );

		$this->assertEquals( 30, wordpoints_get_points( $user_id, 'points' ) );
	}

	/**
	 * Test that the logs are only displayed to users with the correct caps.
	 *
	 * @since 1.3.0
	 */
	public function test_logs_hidden_for_insufficient_caps() {

		wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'publish' => 20, 'trash' => 20, 'post_type' => 'ALL' )
		);

		$post = $this->factory->post->create_and_get(
			array( 'post_author' => $this->factory->user->create() )
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
