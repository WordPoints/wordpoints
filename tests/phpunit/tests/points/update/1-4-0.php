<?php

/**
 * A test case for the points component update to 1.4.0.
 *
 * @package WordPoints\Tests
 * @since 1.4.0
 */

/**
 * Test that the points component updates to 1.4.0 properly.
 *
 * @since 1.4.0
 *
 * @group points
 * @group update
 */
class WordPoints_Points_1_4_0_Update_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test that the custom capabailities that were'nt added in 1.3.0 are added.
	 *
	 * @since 1.4.0
	 */
	public function test_custom_caps_added_when_network_active() {

		if ( ! is_wordpoints_network_active() ) {
			$this->markTestSkipped( 'WordPoints must be network-active.' );
			return;
		}

		// Create a second site on the network.
		$this->factory->blog->create();

		// Remove the caps on each site for the test.
		global $wpdb;

		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

		foreach ( $blog_ids as $blog_id ) {

			switch_to_blog( $blog_id );
			wordpoints_remove_custom_caps( array_keys( wordpoints_points_get_custom_caps() ) );
			restore_current_blog();
		}

		// Simulate the update.
		$this->set_points_db_version();
		wordpoints_points_component_update();

		// Check that the custom capabilties were added.
		foreach ( $blog_ids as $blog_id ) {

			switch_to_blog( $blog_id );

			$administrator = get_role( 'administrator' );
			$this->assertTrue( $administrator->has_cap( 'set_wordpoints_points' ) );
			$this->assertFalse( $administrator->has_cap( 'manage_wordpoints_points_types' ) );

			restore_current_blog();
		}
	}

	/**
	 * Test that the post points hooks are split properly on a single site.
	 *
	 * @since 1.4.0
	 */
	public function test_post_points_hooks_split() {

		// Create a post points hook with the old type of settings.
		$hook = wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'publish' => 20, 'trash' => 20, 'post_type' => 'ALL' )
		);

		// Now simulate the update.
		$this->set_points_db_version();
		wordpoints_points_component_update();

		// Test that the post hook instance was updated.
		$this->assertEquals(
			array( $hook->get_number() => array( 'points' => 20, 'post_type' => 'ALL' ) )
			, $hook->get_instances()
		);

		// Check that a post delete points hook was created.
		$delete_hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_post_delete_points_hook' );

		$this->assertEquals(
			array( $delete_hook->get_number() => array( 'points' => 20, 'post_type' => 'ALL' ) )
			, $delete_hook->get_instances()
		);

		// Check that the points-types-hooks list was updated.
		$this->assertEquals(
			array(
				'points' => array(
					$hook->get_id(),
					$delete_hook->get_id(),
				)
			),
			WordPoints_Points_Hooks::get_points_types_hooks()
		);
	}

	/**
	 * Test that the post points hooks are split properly when network-active.
	 *
	 * @since 1.4.0
	 */
	public function test_standard_post_points_hooks_split_when_network_active() {

		if ( ! is_wordpoints_network_active() ) {
			$this->markTestSkipped( 'WordPoints must be network-active.' );
			return;
		}

		// Create a second site on the network.
		$this->factory->blog->create();

		// Create an old-style post points hook on each site.
		global $wpdb;

		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

		foreach ( $blog_ids as $blog_id ) {

			switch_to_blog( $blog_id );
			wordpointstests_add_points_hook(
				'wordpoints_post_points_hook'
				, array( 'publish' => 20, 'trash' => 20, 'post_type' => 'ALL' )
			);
			restore_current_blog();
		}

		// Simulate the update.
		$this->set_points_db_version();
		wordpoints_points_component_update();

		// Check that the hooks for each site were updated.
		foreach ( $blog_ids as $blog_id ) {

			switch_to_blog( $blog_id );

			// Test that the post hook instance was updated.
			$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_post_points_hook' );
			$hook_number = $hook->get_number();

			$this->assertEquals(
				array( $hook_number => array( 'points' => 20, 'post_type' => 'ALL' ) )
				, $hook->get_instances( 'standard' )
			);

			// Check that a post delete points hook was created.
			$delete_hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_post_delete_points_hook' );
			$delete_hook_number = $delete_hook->get_number();

			$this->assertEquals(
				array( $delete_hook_number => array( 'points' => 20, 'post_type' => 'ALL' ) )
				, $delete_hook->get_instances( 'standard' )
			);

			// Check that the points-types-hooks list was updated.
			$this->assertEquals(
				array(
					'points' => array(
						$hook->get_id( $hook_number ),
						$delete_hook->get_id( $delete_hook_number ),
					)
				),
				WordPoints_Points_Hooks::get_points_types_hooks()
			);

			restore_current_blog();
		}

	} // public function test_standard_post_points_hooks_split_when_network_active()

	/**
	 * Test that network post points hooks are split when network-active.
	 *
	 * @since 1.4.0
	 */
	public function test_network_post_points_hooks_split() {

		if ( ! is_wordpoints_network_active() ) {
			$this->markTestSkipped( 'WordPoints must be network-active.' );
			return;
		}

		// Create a network-wide hook.
		WordPoints_Points_Hooks::set_network_mode( true );
		$hook = wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'publish' => 20, 'trash' => 20, 'post_type' => 'ALL' )
		);
		$network_hook_id = $hook->get_id();
		WordPoints_Points_Hooks::set_network_mode( false );

		// Simulate the update.
		$this->set_points_db_version();
		wordpoints_points_component_update();

		// Test that the network-hook was updated.
		WordPoints_Points_Hooks::set_network_mode( true );

		// Test that the post hook instance was updated.
		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_post_points_hook' );
		$hook_number = $hook->get_number_by_id( $network_hook_id );

		$this->assertEquals(
			array( $hook_number => array( 'points' => 20, 'post_type' => 'ALL' ) )
			, $hook->get_instances( 'network' )
		);

		// Check that a post delete points hook was created.
		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_post_delete_points_hook' );

		$this->assertEquals(
			array( $hook_number => array( 'points' => 20, 'post_type' => 'ALL' ) )
			, $hook->get_instances( 'network' )
		);

		// Check that the points-types-hooks list was updated.
		$this->assertEquals(
			array(
				'points' => array(
					$network_hook_id,
					$hook->get_id( $hook_number ),
				)
			),
			WordPoints_Points_Hooks::get_points_types_hooks()
		);

		WordPoints_Points_Hooks::set_network_mode( false );

	} // public function test_network_post_points_hooks_split()

	/**
	 * Test that the comment points hooks are split properly on a single site.
	 *
	 * @since 1.4.0
	 */
	public function test_comment_points_hooks_split() {

		// Create a comment points hook with the old type of settings.
		$hook = wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'approve' => 20, 'disapprove' => 20 )
		);

		// Now simulate the update.
		$this->set_points_db_version();
		wordpoints_points_component_update();

		// Test that the comment hook instance was updated.
		$this->assertEquals(
			array( $hook->get_number() => array( 'points' => 20 ) )
			, $hook->get_instances()
		);

		// Check that a comment remove points hook was created.
		$removed_hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_comment_removed_points_hook' );

		$this->assertEquals(
			array( $removed_hook->get_number() => array( 'points' => 20 ) )
			, $removed_hook->get_instances()
		);

		// Check that the points-types-hooks list was updated.
		$this->assertEquals(
			array(
				'points' => array(
					$hook->get_id(),
					$removed_hook->get_id(),
				)
			),
			WordPoints_Points_Hooks::get_points_types_hooks()
		);
	}

	/**
	 * Test that the comment points hooks are split properly when network-active.
	 *
	 * @since 1.4.0
	 */
	public function test_standard_comment_points_hooks_split_when_network_active() {

		if ( ! is_wordpoints_network_active() ) {
			$this->markTestSkipped( 'WordPoints must be network-active.' );
			return;
		}

		// Create a second site on the network.
		$this->factory->blog->create();

		// Create an old-style comment points hook on each site.
		global $wpdb;

		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

		foreach ( $blog_ids as $blog_id ) {

			switch_to_blog( $blog_id );
			wordpointstests_add_points_hook(
				'wordpoints_comment_points_hook'
				, array( 'approve' => 20, 'disapprove' => 20 )
			);
			restore_current_blog();
		}

		// Simulate the update.
		$this->set_points_db_version();
		wordpoints_points_component_update();

		// Check that the hooks for each site were updated.
		foreach ( $blog_ids as $blog_id ) {

			switch_to_blog( $blog_id );

			// Test that the post hook instance was updated.
			$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_comment_points_hook' );
			$hook_number = $hook->get_number();

			$this->assertEquals(
				array( $hook_number => array( 'points' => 20 ) )
				, $hook->get_instances( 'standard' )
			);

			// Check that a comment remove points hook was created.
			$remove_hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_comment_removed_points_hook' );
			$remove_hook_number = $remove_hook->get_number();

			$this->assertEquals(
				array( $remove_hook_number => array( 'points' => 20 ) )
				, $remove_hook->get_instances( 'standard' )
			);

			// Check that the points-types-hooks list was updated.
			$this->assertEquals(
				array(
					'points' => array(
						$hook->get_id( $hook_number ),
						$remove_hook->get_id( $remove_hook_number ),
					)
				),
				WordPoints_Points_Hooks::get_points_types_hooks()
			);

			restore_current_blog();
		}

	} // public function test_standard_comment_points_hooks_split_when_network_active()

	/**
	 * Test that network comment points hooks are split when network-active.
	 *
	 * @since 1.4.0
	 */
	public function test_network_comment_points_hooks_split() {

		if ( ! is_wordpoints_network_active() ) {
			$this->markTestSkipped( 'WordPoints must be network-active.' );
			return;
		}

		// Create a network-wide hook.
		WordPoints_Points_Hooks::set_network_mode( true );
		$hook = wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'approve' => 20, 'disapprove' => 20 )
		);
		$network_hook_id = $hook->get_id();
		WordPoints_Points_Hooks::set_network_mode( false );

		// Simulate the update.
		$this->set_points_db_version();
		wordpoints_points_component_update();

		// Test that the network-hook was updated.
		WordPoints_Points_Hooks::set_network_mode( true );

		// Test that the comment hook instance was updated.
		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_comment_points_hook' );
		$hook_number = $hook->get_number_by_id( $network_hook_id );

		$this->assertEquals(
			array( $hook_number => array( 'points' => 20 ) )
			, $hook->get_instances( 'network' )
		);

		// Check that a comment remove points hook was created.
		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_comment_removed_points_hook' );

		$this->assertEquals(
			array( $hook_number => array( 'points' => 20 ) )
			, $hook->get_instances( 'network' )
		);

		// Check that the points-types-hooks list was updated.
		$this->assertEquals(
			array(
				'points' => array(
					$network_hook_id,
					$hook->get_id( $hook_number ),
				)
			),
			WordPoints_Points_Hooks::get_points_types_hooks()
		);

		WordPoints_Points_Hooks::set_network_mode( false );

	} // public function test_network_comment_points_hooks_split()

	/**
	 * Test comment approve points logs for deleted posts cleaned.
	 *
	 * @since 1.4.0
	 */
	public function test_comment_approve_points_logs_cleaned() {

		$hook = wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'points' => 10 )
		);

		remove_action( 'delete_post', array( $hook, 'clean_logs_on_post_deletion' ) );

		// Create a comment, then delete it.
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

		// Simulate the update.
		$this->set_points_db_version();
		wordpoints_points_component_update();

		// Check that the log meta was deleted.
		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'comment_approve',
				'meta_key'   => 'post_id',
				'meta_value' => $post_id,
			)
		);

		$this->assertEquals( 0, $query->count() );

		// Make sure the log text was regenerated.
		$query = new WordPoints_Points_Logs_Query(
			array( 'log_type' => 'comment_approve' )
		);

		$log = $query->get( 'row' );

		$this->assertEquals(
			_x( 'Comment', 'points log description', 'wordpoints' )
			, $log->text
		);

		add_action( 'delete_post', array( $hook, 'clean_logs_on_post_deletion' ) );

	} // public function test_comment_approve_points_logs_cleaned()
}

// EOF
