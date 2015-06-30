<?php

/**
 * A test case for miscellaneous points functions.
 *
 * @package WordPoints\Tests
 * @since 1.2.0
 */

/**
 * Test miscellaneous points functions.
 *
 * @since 1.2.0
 *
 * @group points
 */
class WordPoints_Points_Misc_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test that points logs and log meta tables are cleaned up on user deletion.
	 *
	 * @since 1.2.0
	 *
	 * @covers ::wordpoints_delete_points_logs_for_user
	 */
	public function test_logs_tables_cleaned_on_user_deletion() {

		// Create a user and give them some points.
		$user_id = $this->factory->user->create();

		wp_set_current_user( $user_id );

		wordpoints_alter_points( $user_id, 10, 'points', 'test', array( 'test' => 10 ) );

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Make sure that was a success.
		$query = new WordPoints_Points_Logs_Query( array( 'user_id' => $user_id ) );
		$this->assertEquals( 1, $query->count() );

		$query = new WordPoints_Points_Logs_Query( array( 'meta_key' => 'test' ) );
		$this->assertEquals( 1, $query->count() );

		// Run this query so we can check caches are deleted.
		wordpoints_get_points_logs_query( 'points', 'current_user' )->get();

		$this->assertEquals( 3, $this->filter_was_called( 'query' ) );

		$log_id = $query->get( 'row' )->id;

		// Now delete the user.
		wp_delete_user( $user_id );

		// Here is the first real test. The logs for the user should be gone.
		$this->assertEquals( array(), wordpoints_get_points_log_meta( $log_id, 'user_id' ) );
		$this->assertEquals( array(), wordpoints_get_points_log_meta( $log_id, 'test' ) );

		$this->assertEquals( 6, $this->filter_was_called( 'query' ) );

		// Check that the cache was cleared as well.
		wordpoints_get_points_logs_query( 'points', 'current_user' )->get();
		$this->assertEquals( 7, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Test that points logs and log meta tables are cleaned up on user deletion.
	 *
	 * @since 1.2.0
	 *
	 * @covers ::wordpoints_delete_points_logs_for_user
	 *
	 * @requires WordPress multisite
	 */
	public function test_logs_tables_cleaned_on_user_deletion_multisite() {

		// Same as above, create a user and give them some points.
		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test', array( 'test' => 10 ) );

		// Now create a second blog and give them some points there too.
		$blog_id = $this->factory->blog->create();

		switch_to_blog( $blog_id );

		if ( ! is_wordpoints_network_active() ) {
			wordpoints_add_points_type( array( 'name' => 'points' ) );
		}

		wordpoints_alter_points( $user_id, 10, 'points', 'test', array( 'test' => 10 ) );

		restore_current_blog();

		// Now we'll do do the same for a third blog.
		$blog_id_2 = $this->factory->blog->create();

		switch_to_blog( $blog_id_2 );

		if ( ! is_wordpoints_network_active() ) {
			wordpoints_add_points_type( array( 'name' => 'points' ) );
		}

		wordpoints_alter_points( $user_id, 10, 'points', 'test', array( 'test' => 10 ) );

		// While we're here in blog 3, let's test that all of the points got awarded.
		$query = new WordPoints_Points_Logs_Query( array( 'user_id' => $user_id, 'blog_id' => false ) );
		$this->assertEquals( 3, $query->count() );

		$query = new WordPoints_Points_Logs_Query( array( 'meta_key' => 'test', 'blog_id' => false ) );
		$this->assertEquals( 3, $query->count() );

		// Get the ID of the log on this site.
		$query = new WordPoints_Points_Logs_Query( array( 'meta_key' => 'test' ) );
		$log_id = $query->get( 'row' )->id;

		// Now we'll delete the user, but just from this blog.
		wp_delete_user( $user_id );

		restore_current_blog();

		// Test to make sure only the logs and meta for the third blog were deleted.
		$query = new WordPoints_Points_Logs_Query( array( 'user_id' => $user_id, 'blog_id' => false ) );
		$this->assertEquals( 2, $query->count() );

		$query = new WordPoints_Points_Logs_Query( array( 'meta_key' => 'test', 'blog_id' => false ) );
		$this->assertEquals( 2, $query->count() );

		$this->assertEquals( array(), wordpoints_get_points_log_meta( $log_id, 'user_id' ) );
		$this->assertEquals( array(), wordpoints_get_points_log_meta( $log_id, 'test' ) );

		// Get the IDs of the logs on the remaining blogs.
		$log_ids = wp_list_pluck( $query->get(), 'user_id' );

		// Good, now lets completely delete the user from the whole network.
		wpmu_delete_user( $user_id );

		// All of their logs and meta should now be gone.
		$this->assertEquals( array(), wordpoints_get_points_log_meta( $log_ids[0], 'user_id' ) );
		$this->assertEquals( array(), wordpoints_get_points_log_meta( $log_ids[0], 'test' ) );
		$this->assertEquals( array(), wordpoints_get_points_log_meta( $log_ids[1], 'user_id' ) );
		$this->assertEquals( array(), wordpoints_get_points_log_meta( $log_ids[1], 'test' ) );
	}

	/**
	 * Test that the logs and meta for a blog are deleted when the blog is deleted.
	 *
	 * @since 1.2.0
	 *
	 * @covers ::wordpoints_delete_points_logs_for_blog
	 *
	 * @requires WordPress multisite
	 */
	public function test_logs_tables_cleaned_on_blog_deletion() {

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Create a blog, and switch to it.
		$blog_id = $this->factory->blog->create();

		switch_to_blog( $blog_id );

		// Now create a user and give them some points.
		$user_id = $this->factory->user->create();

		if ( ! is_wordpoints_network_active() ) {
			wordpoints_add_points_type( array( 'name' => 'points' ) );
		}

		wordpoints_alter_points( $user_id, 10, 'points', 'test', array( 'test' => 10 ) );

		// Make sure that was a success.
		$query = new WordPoints_Points_Logs_Query( array( 'user_id' => $user_id ) );
		$this->assertEquals( 1, $query->count() );

		$query = new WordPoints_Points_Logs_Query( array( 'meta_key' => 'test' ) );
		$this->assertEquals( 1, $query->count() );

		// Run this query so we can check caches are deleted.
		wordpoints_get_points_logs_query( 'points', 'network' )->get();

		$this->assertEquals( 3, $this->filter_was_called( 'query' ) );

		$log_id = $query->get( 'row' )->id;

		// Back to Kansas.
		restore_current_blog();

		// Now delete the blog.
		wpmu_delete_blog( $blog_id );

		// Here is the real test. The logs for the blog should be gone.
		$this->assertEquals( array(), wordpoints_get_points_log_meta( $log_id, 'blog_id' ) );
		$this->assertEquals( array(), wordpoints_get_points_log_meta( $log_id, 'test' ) );

		$this->assertEquals( 6, $this->filter_was_called( 'query' ) );

		// The caches should be deleted.
		wordpoints_get_points_logs_query( 'points', 'network' )->get();

		$this->assertEquals( 7, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Test that the correct key is returned for user points metadata.
	 *
	 * @since 1.2.0
	 *
	 * @covers ::wordpoints_get_points_user_meta_key
	 */
	public function test_wordpoints_get_points_user_meta_key() {

		global $wpdb;

		$meta_key = wordpoints_get_points_user_meta_key( 'points' );

		if ( ! is_multisite() ) {
			$this->assertEquals( 'wordpoints_points-points', $meta_key );
		} elseif ( is_wordpoints_network_active() ) {
			$this->assertEquals( 'wordpoints_points-points', $meta_key );
		} else {
			$this->assertEquals( $wpdb->get_blog_prefix() . 'wordpoints_points-points', $meta_key );
		}

		// Test that the meta_key points type setting takes precendence when set.
		$settings = wordpoints_get_points_type( 'points' );
		$settings['meta_key'] = 'credits';
		wordpoints_update_points_type( 'points', $settings );

		$this->assertEquals( 'credits', wordpoints_get_points_user_meta_key( 'points' ) );
	}

	/**
	 * Test points log regeneration.
	 *
	 * @since 1.2.0
	 *
	 * @covers ::wordpoints_regenerate_points_logs
	 *
	 * @expectedDeprecated wordpoints_regenerate_points_logs
	 */
	public function test_wordpoints_regenerate_points_logs() {

		// We do this so we can check that the caches are flushed.
		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		// Create a user and add a points log.
		$user_id = $this->factory->user->create();

		wordpoints_add_points( $user_id, 10, 'points', 'register' );

		// Get the log from the database.
		$log = wordpoints_get_points_logs_query( 'points' )->get( 'row' );

		// Check that all is as expected.
		$this->assertInternalType( 'object', $log );
		$this->assertEquals( __( 'Registration.', 'wordpoints' ), $log->text );

		// Now, modify the log text.
		global $wpdb;

		$wpdb->update(
			$wpdb->wordpoints_points_logs
			, array( 'text' => 'Test' )
			, array( 'id' => $log->id )
			, array( '%s' )
			, array( '%d' )
		);

		// Check that the log was updated.
		$log = new WordPoints_Points_Logs_Query;
		$log = $log->get( 'row' );

		$this->assertInternalType( 'object', $log );
		$this->assertEquals( 'Test', $log->text );

		// Now, regenerate it.
		wordpoints_regenerate_points_logs( array( $log ) );

		$this->assertEquals( 2, $this->filter_was_called( 'query' ) );

		// Check that the log was regenerated.
		$log = wordpoints_get_points_logs_query( 'points' )->get( 'row' );

		// Check that the cache was cleared and a new query was made.
		$this->assertEquals( 3, $this->filter_was_called( 'query' ) );

		$this->assertInternalType( 'object', $log );
		$this->assertEquals( __( 'Registration.', 'wordpoints' ), $log->text );

		// Modify the log and test again, to check that it also works with the log
		// IDs, as this was expected since 1.2.0 but was deprecated in 1.6.0.
		$wpdb->update(
			$wpdb->wordpoints_points_logs
			, array( 'text' => 'Test' )
			, array( 'id' => $log->id )
			, array( '%s' )
			, array( '%d' )
		);

		// Check that the log was updated.
		$log = new WordPoints_Points_Logs_Query;
		$log = $log->get( 'row' );

		$this->assertInternalType( 'object', $log );
		$this->assertEquals( 'Test', $log->text );

		// Now, regenerate it.
		wordpoints_regenerate_points_logs( array( $log->id ) );

		$this->assertEquals( 5, $this->filter_was_called( 'query' ) );

		// Check that the log was regenerated.
		$log = wordpoints_get_points_logs_query( 'points' )->get( 'row' );

		// Check that the cache was cleared and a new query was made.
		$this->assertEquals( 6, $this->filter_was_called( 'query' ) );

		$this->assertInternalType( 'object', $log );
		$this->assertEquals( __( 'Registration.', 'wordpoints' ), $log->text );
	}

	/**
	 * Test that that emojis work in logs wen they are regenerated.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_regenerate_points_logs
	 */
	public function test_emoji_in_log() {

		global $wpdb;

		if ( 'utf8mb4' !== $wpdb->charset ) {
			$this->markTestSkipped( 'wpdb database charset must be utf8mb4.' );
		}

		$log = $this->factory->wordpoints_points_log->create_and_get();

		$log_text = "You've got Points! \xf0\x9f\x98\x8e";

		$filter = new WordPoints_Mock_Filter( $log_text );
		add_filter( 'wordpoints_points_log-test', array( $filter, 'filter' ) );

		wordpoints_regenerate_points_logs( array( $log ) );

		$query = new WordPoints_Points_Logs_Query(
			array( 'fields' => 'text', 'id__in' => array( $log->id ) )
		);

		$this->assertEquals( $log_text, $query->get( 'var' ) );
	}

	/**
	 * Test that that emojis in logs are encoded if needed.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_regenerate_points_logs
	 */
	public function test_emoji_in_log_utf8() {

		$log = $this->factory->wordpoints_points_log->create_and_get();

		$filter = new WordPoints_Mock_Filter( 'utf8' );
		add_filter( 'pre_get_col_charset', array( $filter, 'filter' ) );

		$log_text = "You've got Points! \xf0\x9f\x98\x8e";

		$filter = new WordPoints_Mock_Filter( $log_text );
		add_filter( 'wordpoints_points_log-test', array( $filter, 'filter' ) );

		wordpoints_regenerate_points_logs( array( $log ) );

		$query = new WordPoints_Points_Logs_Query(
			array( 'fields' => 'text', 'id__in' => array( $log->id ) )
		);

		$this->assertEquals( "You've got Points! &#x1f60e;", $query->get( 'var' ) );
	}
}

// EOF
