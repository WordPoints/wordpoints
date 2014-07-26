<?php

/**
 * A test case for points component update to 1.2.0.
 *
 * @package WordPoints\Tests
 * @since 1.4.0
 */

/**
 * Test that the points component updates to 1.2.0 properly.
 *
 * Since 1.2.0 this was a part of the WordPoints_Points_Update_Test which was split
 * to provide a separate testcase for each update.
 *
 * @since 1.4.0
 *
 * @group points
 * @group update
 */
class WordPoints_Points_1_2_0_Update_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Set up before the tests run.
	 *
	 * @since 1.4.0
	 */
	public function setUpBeforeTests() {

		parent::setUpBeforeTests();

		// Unhook the clean-up funtions.
		remove_action( 'deleted_user', 'wordpoints_delete_points_logs_for_user' );

		$post_hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_post_points_hook' );
		remove_action( 'delete_post', array( $post_hook, 'clean_logs_on_post_deletion' ) );

		$comment_hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_comment_points_hook' );
		remove_action( 'delete_comment', array( $comment_hook, 'clean_logs_on_comment_deletion' ) );
	}

	/**
	 * Clean up after the tests.
	 *
	 * @since 1.4.0
	 */
	public static function tearDownAfterTests() {

		parent::tearDownAfterTests();

		add_action( 'deleted_user', 'wordpoints_delete_points_logs_for_user' );

		$post_hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_post_points_hook' );
		add_action( 'delete_post', array( $post_hook, 'clean_logs_on_post_deletion' ) );

		$comment_hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_comment_points_hook' );
		add_action( 'delete_comment', array( $comment_hook, 'clean_logs_on_comment_deletion' ) );
	}

	/**
	 * Test that logs for deleted users are deleted when the update takes place.
	 *
	 * @since 1.4.0
	 */
	public function test_logs_for_delete_users_deleted() {

		// Create two users and give them points to create some log entries.
		$user_ids = $this->factory->user->create_many( 2 );

		foreach ( $user_ids as $user_id ) {
			wordpoints_add_points( $user_id, 10, 'points', 'test', array( 'test' => $user_id ) );
		}

		// Make sure the logs and meta actually got created.
		$user_1_query = new WordPoints_Points_Logs_Query(
			array( 'user_id' => $user_ids[0] )
		);
		$this->assertEquals( 1, $user_1_query->count() );

		$user_1_meta_query = new WordPoints_Points_Logs_Query(
			array( 'meta_key' => 'test', 'meta_value' => $user_ids[0] )
		);
		$this->assertEquals( 1, $user_1_meta_query->count() );

		// Delete the first user.
		if ( is_multisite() ) {
			wpmu_delete_user( $user_ids[0] );
		} else {
			wp_delete_user( $user_ids[0] );
		}

		// Simulate the update.
		$this->set_points_db_version();
		wordpoints_points_component_update();
		$this->assertEquals( WORDPOINTS_VERSION, $this->get_points_db_version() );

		// Check that the log for the deleted user was deleted.
		$this->assertEquals( 0, $user_1_query->count( false ) );

		// Make sure the logs for the extant user are still there.
		$query = new WordPoints_Points_Logs_Query(
			array( 'user_id' => $user_ids[1] )
		);
		$this->assertEquals( 1, $query->count() );

		// Check that the log meta was deleted also.
		$this->assertEquals( 0, $user_1_meta_query->count( false ) );

		// But not for the existing user.
		$query = new WordPoints_Points_Logs_Query(
			array( 'meta_key' => 'test', 'meta_value' => $user_ids[1] )
		);
		$this->assertEquals( 1, $query->count() );

	} // public function test_logs_for_delete_users_deleted()


	/**
	 * Test that the logs for deleted posts are cleaned up.
	 *
	 * @since 1.4.0
	 */
	public function test_logs_for_deleted_posts_cleaned() {

		// Add an instance of the post points hook.
		wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'points' => 20, 'post_type' => 'ALL' )
		);

		// Create two posts, one of which we'll delete; the other is used as a control.
		$post_ids = $this->factory->post->create_many(
			2
			, array( 'post_author' => $this->factory->user->create() )
		);

		// Creating those posts should have awarded points, make sure some logs were created.
		$post_1_query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'post_publish',
				'meta_key'   => 'post_id',
				'meta_value' => $post_ids[0],
			)
		);
		$this->assertEquals( 1, $post_1_query->count() );

		// Delete the first post.
		wp_delete_post( $post_ids[0], true );

		// Simulate the update.
		$this->set_points_db_version();
		wordpoints_points_component_update();
		$this->assertEquals( WORDPOINTS_VERSION, $this->get_points_db_version() );

		// Check the logs for the deleted post where cleaned (meta post_id deleted).
		$this->assertEquals( 0, $post_1_query->count( false ) );

		// Make sure the data for the extant post was untouched.
		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'post_publish',
				'meta_key'   => 'post_id',
				'meta_value' => $post_ids[1],
			)
		);
		$this->assertEquals( 1, $query->count() );

	} // public function test_logs_for_deleted_posts_cleaned()

	/**
	 * Test that the logs for deleted comments are cleaned up.
	 *
	 * @since 1.4.0
	 */
	public function test_logs_for_deleted_comments_cleaned() {

		// Add an instance of the comments points hook.
		wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'points' => 10 )
		);

		// Create two comments, we'll delete one and use the other as a control.
		$user_id = $this->factory->user->create();
		$comment_ids = $this->factory->comment->create_many(
			2
			, array(
				'user_id'         => $user_id,
				'comment_post_ID' => $this->factory->post->create(
					array( 'post_author' => $user_id )
				),
			)
		);

		// Check that points were logged as expected when the comment was created.
		$comment_1_query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'comment_approve',
				'meta_key'   => 'comment_id',
				'meta_value' => $comment_ids[0],
			)
		);
		$this->assertEquals( 1, $comment_1_query->count() );

		// Get the data for the first comment before we delete it.
		$comment = get_comment( $comment_ids[0] );

		wp_delete_comment( $comment_ids[0], true );

		// Simulate the update.
		$this->set_points_db_version();
		wordpoints_points_component_update();
		$this->assertEquals( WORDPOINTS_VERSION, $this->get_points_db_version() );

		// The logs for the deleted comment should be cleaned (meta comment_id deleted).
		$this->assertEquals( 0, $comment_1_query->count( false ) );

		// Make sure the existing comment's logs weren't touched.
		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'comment_approve',
				'meta_key'   => 'comment_id',
				'meta_value' => $comment_ids[1],
			)
		);
		$this->assertEquals( 1, $query->count() );

	} // public function test_logs_for_deleted_comments_cleaned()
}
