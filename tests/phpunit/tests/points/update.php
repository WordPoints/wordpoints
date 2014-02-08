<?php

/**
 * A test case for points component updates.
 *
 * @package WordPoints\Tests
 * @since 1.2.0
 */

/**
 * Test that the points component updates itself properly.
 *
 * @since 1.2.0
 *
 * @group points
 */
class WordPoints_Points_Update_Test extends WordPoints_Points_UnitTestCase {

	//
	// Helper Methods.
	//

	/**
	 * Set the version of the points component.
	 *
	 * @since 1.2.0
	 *
	 * @param string $version The version to set. Defaults to 1.0.0.
	 */
	protected function wordpoints_set_db_version( $version = '1.0.0' ) {

		$wordpoints_data = wordpoints_get_network_option( 'wordpoints_data' );
		$wordpoints_data['components']['points']['version'] = $version;
		wordpoints_update_network_option( 'wordpoints_data', $wordpoints_data );
	}

	/**
	 * Get the version of the points component.
	 *
	 * @since 1.2.0
	 *
	 * @return string The version of the points component.
	 */
	protected function wordpoints_get_db_version() {

		$wordpoints_data = wordpoints_get_network_option( 'wordpoints_data' );

		return ( isset( $wordpoints_data['components']['points']['version'] ) )
			? $wordpoints_data['components']['points']['version']
			: '';
	}

	//
	// Tests.
	//

	/**
	 * Test that the points component updates properly to 1.2.0.
	 *
	 * @since 1.2.0
	 */
	public function test_update_to_1_2_0() {

		// Unhook the clean-up funtions.
		remove_action( 'deleted_user', 'wordpoints_delete_points_logs_for_user' );

		$post_hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_post_points_hook' );
		remove_action( 'delete_post', array( $post_hook, 'clean_logs_on_post_deletion' ) );

		$comment_hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_comment_points_hook' );
		remove_action( 'delete_comment', array( $comment_hook, 'clean_logs_on_comment_deletion' ) );

		// Check that logs for deleted users are deleted.
		$user_ids = $this->factory->user->create_many( 2 );

		foreach ( $user_ids as $user_id ) {
			wordpoints_add_points( $user_id, 10, 'points', 'test', array( 'test' => $user_id ) );
		}

		if ( is_multisite() ) {
			wpmu_delete_user( $user_ids[0] );
		} else {
			wp_delete_user( $user_ids[0] );
		}

		$this->wordpoints_set_db_version();
		wordpoints_points_component_update();
		$this->assertEquals( WORDPOINTS_VERSION, $this->wordpoints_get_db_version() );

		$query = new WordPoints_Points_Logs_Query(
			array( 'user_id' => $user_ids[0] )
		);
		$this->assertEquals( 0, $query->count() );

		$query = new WordPoints_Points_Logs_Query(
			array( 'user_id' => $user_ids[1] )
		);
		$this->assertEquals( 1, $query->count() );

		// Check that the log meta was deleted also.
		$query = new WordPoints_Points_Logs_Query(
			array( 'meta_key' => 'test', 'meta_value' => $user_ids[0] )
		);
		$this->assertEquals( 0, $query->count() );

		$query = new WordPoints_Points_Logs_Query(
			array( 'meta_key' => 'test', 'meta_value' => $user_ids[1] )
		);
		$this->assertEquals( 1, $query->count() );

		// Check that the logs for deleted posts are cleaned up.
		wordpointstests_add_points_hook( 'wordpoints_post_points_hook', array( 'publish' => 20, 'trash' => 20, 'post_type' => 'ALL' ) );

		$post_ids = $this->factory->post->create_many( 2, array( 'post_author' => $this->factory->user->create() ) );

		wp_delete_post( $post_ids[0], true );

		$this->wordpoints_set_db_version();
		wordpoints_points_component_update();
		$this->assertEquals( WORDPOINTS_VERSION, $this->wordpoints_get_db_version() );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'post_publish',
				'meta_key'   => 'post_id',
				'meta_value' => $post_ids[0],
			)
		);
		$this->assertEquals( 0, $query->count() );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'post_publish',
				'meta_key'   => 'post_id',
				'meta_value' => $post_ids[1],
			)
		);
		$this->assertEquals( 1, $query->count() );

		// Check that the logs for deleted comments are cleaned up.
		wordpointstests_add_points_hook( 'wordpoints_comment_points_hook', array( 'approve' => 10, 'disapprove' => 10 ) );

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

		$comment = get_comment( $comment_ids[0] );

		wp_delete_comment( $comment_ids[0], true );

		$this->wordpoints_set_db_version();
		wordpoints_points_component_update();
		$this->assertEquals( WORDPOINTS_VERSION, $this->wordpoints_get_db_version() );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'comment_approve',
				'meta_key'   => 'comment_id',
				'meta_value' => $comment_ids[0],
			)
		);
		$this->assertEquals( 0, $query->count() );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'comment_approve',
				'meta_key'   => 'comment_id',
				'meta_value' => $comment_ids[1],
			)
		);
		$this->assertEquals( 1, $query->count() );

		// Check that it doesn't upgrade when we are already at 1.2.0.
		$user_id = $this->factory->user->create();

		wordpoints_add_points( $user_id, 10, 'points', 'test' );

		if ( is_multisite() ) {
			wpmu_delete_user( $user_id );
		} else {
			wp_delete_user( $user_id );
		}

		$this->wordpoints_set_db_version( '1.2.0' );
		wordpoints_points_component_update();
		$this->assertEquals( '1.2.0', $this->wordpoints_get_db_version() );

		$query = new WordPoints_Points_Logs_Query( array( 'user_id' => $user_id ) );
		$this->assertEquals( 1, $query->count() );

		// Check that it doesn't upgrade if we are at a higher version.
		$this->wordpoints_set_db_version( '1.3.0' );
		wordpoints_points_component_update();
		$this->assertEquals( '1.3.0', $this->wordpoints_get_db_version() );

		$query = new WordPoints_Points_Logs_Query( array( 'user_id' => $user_id ) );
		$this->assertEquals( 1, $query->count() );

		// Hook the clean-up funtions back up..
		add_action( 'deleted_user', 'wordpoints_delete_points_logs_for_user' );
		add_action( 'delete_post', array( $post_hook, 'clean_logs_on_post_deletion' ) );
		add_action( 'delete_comment', array( $comment_hook, 'clean_logs_on_comment_deletion' ) );

	} // public function test_update_to_1_2_0()
}
