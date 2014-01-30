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
 * @group dev
 */
class WordPoints_Points_Misc_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test that points logs and log meta tables are cleaned up on user deletion.
	 *
	 * @since 1.2.0
	 */
	public function test_logs_tables_cleaned_on_user_deletion() {

		// Create a user and give them some points.
		$user_id = $this->factory->user->create();

		wordpoints_alter_points( $user_id, 10, 'points', 'test', array( 'test' => 10 ) );

		// Make sure that was a success.
		$query = new WordPoints_Points_Logs_Query( array( 'user_id' => $user_id ) );
		$this->assertEquals( 1, $query->count() );

		$query = new WordPoints_Points_Logs_Query( array( 'meta_key' => 'test' ) );
		$this->assertEquals( 1, $query->count() );

		// Now delete the user.
		wp_delete_user( $user_id );

		// Here is the first real test. The logs for the user should be gone.
		$query = new WordPoints_Points_Logs_Query( array( 'user_id' => $user_id ) );
		$this->assertEquals( 0, $query->count() );

		$query = new WordPoints_Points_Logs_Query( array( 'meta_key' => 'test' ) );
		$this->assertEquals( 0, $query->count() );

		// If we aren't on multisite, we've completed our mission.
		if ( ! is_multisite() ) {
			$this->markTestIncomplete( 'Unable to test multisite network user deletion.' );
			return;
		}

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

		// Now we'll delete the user, but just from this blog.
		wp_delete_user( $user_id );

		restore_current_blog();

		// Test to make sure only the logs and meta for the third blog were deleted.
		$query = new WordPoints_Points_Logs_Query( array( 'user_id' => $user_id, 'blog_id' => false ) );
		$this->assertEquals( 2, $query->count() );

		$query = new WordPoints_Points_Logs_Query( array( 'meta_key' => 'test', 'blog_id' => false ) );
		$this->assertEquals( 2, $query->count() );

		$query = new WordPoints_Points_Logs_Query( array( 'user_id' => $user_id, 'blog_id' => $blog_id_2 ) );
		$this->assertEquals( 0, $query->count() );

		$query = new WordPoints_Points_Logs_Query( array( 'meta_key' => 'test', 'blog_id' => $blog_id_2 ) );
		$this->assertEquals( 0, $query->count() );

		// Good, now lets completely delete the user from the whole network.
		wpmu_delete_user( $user_id );

		// All of their logs and meta should now be gone.
		$query = new WordPoints_Points_Logs_Query( array( 'user_id' => $user_id, 'blog_id' => false ) );
		$this->assertEquals( 0, $query->count() );

		$query = new WordPoints_Points_Logs_Query( array( 'meta_key' => 'test', 'blog_id' => false ) );
		$this->assertEquals( 0, $query->count() );
	}
}
