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
 *
 * @covers WordPoints_Post_Points_Hook
 */
class WordPoints_Post_Points_Hook_Test extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * @since 1.9.0
	 */
	public function tearDown() {

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base(
			'wordpoints_post_points_hook'
		);

		$hook->set_option( 'disable_auto_reverse_label', null );

		parent::tearDown();
	}

	/**
	 * Test that points are added as expected.
	 *
	 * Since 1.3.0 it was called test_points_awarded_removed(). Now the post delete
	 * hook has been split from the post hook, so we only test that points are
	 * awarded here.
	 *
	 * @since 1.4.0
	 */
	public function test_points_awarded() {

		wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'points' => 20, 'post_type' => 'ALL' )
		);

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $user_id ) );

		// Check that points were added when the post was created.
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

	} // public function test_points_awarded()

	/**
	 * Test automatic reversal of the hook when the post is deleted.
	 *
	 * @since 1.9.0
	 */
	public function test_points_auto_reversal() {

		$hook = wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'points' => 20, 'post_type' => 'ALL' )
		);

		$user_id = $this->factory->user->create();

		wordpoints_set_points( $user_id, 100, 'points', 'test' );

		$post_id = $this->factory->post->create(
			array( 'post_author' => $user_id )
		);

		// Check that points were added when the post was created.
		$this->assertEquals( 120, wordpoints_get_points( $user_id, 'points' ) );

		wp_delete_post( $post_id, true );

		// Check that points were removed when the post was deleted.
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		// Check that the log is marked as reversed.
		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'post_publish',
				'meta_query' => array(
					array(
						'key'   => 'auto_reversed',
						'value' => true,
					),
				),
			)
		);

		$this->assertEquals( 1, $query->count() );

		// Check that it doesn't happen twice.
		$hook->reverse_hook( $post_id );

		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

	} // public function test_points_auto_reversal()

	/**
	 * Test that logs aren't reversed for posts that haven't had points awarded.
	 *
	 * @since 1.9.0
	 */
	public function test_no_points_auto_reversal_if_none_awarded() {

		$user_id = $this->factory->user->create();

		wordpoints_set_points( $user_id, 100, 'points', 'test' );

		$post_id = $this->factory->post->create(
			array( 'post_author' => $user_id )
		);

		// Check that points were added when the post was created.
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'points' => 20, 'post_type' => 'ALL' )
		);

		wp_delete_post( $post_id, true );

		// Check that no points were removed when the post was deleted.
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );
	}

	/**
	 * Test that the auto-reversal setting of the instance for the post's type has priority.
	 *
	 * @since 1.9.0
	 */
	public function test_auto_reversal_post_type_priority_off() {

		// Create a hook for pages.
		$page_hook = wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'points' => 10, 'post_type' => 'page' )
		);
		$this->assertInstanceOf( 'WordPoints_Post_Points_Hook', $page_hook );

		// Create another for the 'post' post type only.
		$post_hook = wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'points' => 15, 'post_type' => 'post', 'auto_reverse' => 0 )
		);
		$this->assertInstanceOf( 'WordPoints_Post_Points_Hook', $post_hook );

		$post_hook->set_option( 'disable_auto_reverse_label', true );

		$user_id = $this->factory->user->create();

		wordpoints_set_points( $user_id, 100, 'points', 'test' );
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		// Create a post.
		$post_id = $this->factory->post->create(
			array( 'post_author' => $user_id, 'post_type' => 'post' )
		);

		// Only the 'post' hook will award the user.
		$this->assertEquals( 115, wordpoints_get_points( $user_id, 'points' ) );

		wp_delete_post( $post_id, true );

		// No reversals will take place.
		$this->assertEquals( 115, wordpoints_get_points( $user_id, 'points' ) );
	}

	/**
	 * Test that auto-reversal can only be disabled on legacy installs.
	 *
	 * @since 1.9.0
	 */
	public function test_auto_reversal_cannot_be_disabled() {

		// Create a hook for pages.
		$page_hook = wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'points' => 10, 'auto_reverse' => 0 )
		);
		$this->assertInstanceOf( 'WordPoints_Post_Points_Hook', $page_hook );

		$user_id = $this->factory->user->create();

		wordpoints_set_points( $user_id, 100, 'points', 'test' );
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		// Create a post.
		$post_id = $this->factory->post->create(
			array( 'post_author' => $user_id, 'post_type' => 'post' )
		);

		// Only the 'post' hook will award the user.
		$this->assertEquals( 110, wordpoints_get_points( $user_id, 'points' ) );

		wp_delete_post( $post_id, true );

		// No reversals will take place.
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );
	}

	/**
	 * Test that the auto-reversal setting of the instance for the post's type has priority.
	 *
	 * @since 1.9.0
	 */
	public function test_auto_reversal_post_type_priority_on() {

		// Create a hook for pages.
		$page_hook = wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'points' => 10, 'post_type' => 'page', 'auto_reverse' => 0 )
		);
		$this->assertInstanceOf( 'WordPoints_Post_Points_Hook', $page_hook );

		// Create another for the 'post' post type only.
		$post_hook = wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'points' => 15, 'post_type' => 'post', 'auto_reverse' => 1 )
		);
		$this->assertInstanceOf( 'WordPoints_Post_Points_Hook', $post_hook );

		$post_hook->set_option( 'disable_auto_reverse_label', true );

		$user_id = $this->factory->user->create();

		wordpoints_set_points( $user_id, 100, 'points', 'test' );
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		$post_id = $this->factory->post->create(
			array( 'post_author' => $user_id, 'post_type' => 'post' )
		);

		// Only the post hook will award the user.
		$this->assertEquals( 115, wordpoints_get_points( $user_id, 'points' ) );

		wp_delete_post( $post_id, true );

		// Reversal of the post hook will take place.
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );
	}

	/**
	 * Test the non-public post types like revisions are ignored.
	 *
	 * @since 1.3.0
	 */
	public function test_non_public_post_types_ignored() {

		wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'points' => 20, 'post_type' => 'ALL' )
		);

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
				'post_type'   => 'revision',
			)
		);

		// Test that no points were awarded.
		$this->assertEquals( 0, wordpoints_get_points( $user_id, 'points' ) );
	}

	/**
	 * Test that points are only awarded for the specified post type.
	 *
	 * @since 1.3.0
	 */
	public function test_points_only_awarded_for_specified_post_type() {

		wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'points' => 20, 'post_type' => 'post' )
		);

		$user_id = $this->factory->user->create();

		// Create a post.
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
				'post_type'   => 'post',
			)
		);

		// Test that points were awarded for the post.
		$this->assertEquals( 20, wordpoints_get_points( $user_id, 'points' ) );

		// Now create a page.
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
				'post_type'   => 'page',
			)
		);

		// Test that no points were awarded for the page.
		$this->assertEquals( 20, wordpoints_get_points( $user_id, 'points' ) );
	}

	/**
	 * Test that the logs are only displayed to users with the correct caps.
	 *
	 * @since 1.3.0
	 */
	public function test_logs_hidden_for_insufficient_caps() {

		wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'points' => 20, 'post_type' => 'ALL' )
		);

		$post = $this->factory->post->create_and_get(
			array( 'post_author' => $this->factory->user->create() )
		);

		// Make the post private.
		$post->post_status = 'private';
		wp_update_post( $post );

		wp_set_current_user( $this->factory->user->create() );

		// The log shouldn't be displayed.
		$document = new DOMDocument;
		$document->preserveWhiteSpace = false;
		$document->loadHTML(
			WordPoints_Shortcodes::do_shortcode(
				array( 'points_type' => 'points' )
				, null
				, 'wordpoints_points_logs'
			)
		);
		$xpath = new DOMXPath( $document );
		$this->assertEquals( 1, $xpath->query( '//tbody[. = ""]' )->length );
	}

	/**
	 * Test that log messages are generated with the post title and post type when available.
	 *
	 * @since 1.9.0
	 */
	public function test_log_text_with_post_title_and_type() {

		$this->assertStringMatchesFormat(
			'Page <a href="%s">Post title %d</a> published.'
			, $this->render_log_text( array( 'post_type' => 'page' ) )
		);
	}

	/**
	 * Test that it uses a message with just the title if the type is unavailble.
	 *
	 * @since 1.9.0
	 */
	public function test_log_text_with_post_title() {

		$this->assertStringMatchesFormat(
			'Post <a href="%s">Post title %d</a> published.'
			, $this->render_log_text( array( 'post_type' => 'not' ) )
		);
	}

	/**
	 * Test that it uses a placeholder is supplied if no title is available.
	 *
	 * @since 1.9.0
	 */
	public function test_log_text_with_no_post_title() {

		$this->assertStringMatchesFormat(
			'Page <a href="%s">(no title)</a> published.'
			, $this->render_log_text(
				array( 'post_type' => 'page', 'post_title' => '' )
			)
		);
	}

	/**
	 * Test that it uses a message with just the title if the type is unavailble.
	 *
	 * @since 1.9.0
	 */
	public function test_log_text_with_post_type() {

		$this->assertStringMatchesFormat(
			'Post <a href="%s">(no title)</a> published.'
			, $this->render_log_text(
				array( 'post_type' => 'not', 'post_title' => '' )
			)
		);
	}

	/**
	 * Test that it uses a generic message if the post doesn't exist.
	 *
	 * @since 1.9.0
	 */
	public function test_log_text_with_no_post() {

		$post_id = $this->factory->post->create();

		wp_delete_post( $post_id, true );

		$this->assertEquals(
			'Post published.'
			, $this->render_log_text( null, array( 'post_id' => $post_id ) )
		);
	}

	/**
	 * Test that it will use the post title if supplied as meta.
	 *
	 * @since 1.9.0
	 */
	public function test_log_text_with_no_post_and_post_title_meta() {

		$this->assertEquals(
			'Post Test title published.'
			, $this->render_log_text( null, array( 'post_title' => 'Test title' ) )
		);
	}

	/**
	 * Test that it will use the post type if supplied as meta.
	 *
	 * @since 1.9.0
	 */
	public function test_log_text_with_no_post_and_post_type_meta() {

		$this->assertEquals(
			'Page published.'
			, $this->render_log_text( null, array( 'post_type' => 'page' ) )
		);
	}

	/**
	 * Test that it will use the generic messsage bad post type if supplied as meta.
	 *
	 * @since 1.9.0
	 */
	public function test_log_text_with_no_post_and_bad_post_type_meta() {

		$this->assertEquals(
			'Post published.'
			, $this->render_log_text( null, array( 'post_type' => 'not' ) )
		);
	}

	/**
	 * Test that it uses a generic message for the reversals by default.
	 *
	 * @since 1.9.0
	 */
	public function test_reverse_log_text() {

		$this->assertEquals(
			'Post deleted.'
			, $this->render_log_text( false )
		);
	}

	/**
	 * Test that it will use the post title if supplied as meta.
	 *
	 * @since 1.9.0
	 */
	public function test_reverse_log_text_with_post_title_meta() {

		$this->assertEquals(
			'Post &#8220;Test title&#8221; deleted.'
			, $this->render_log_text( false, array( 'post_title' => 'Test title' ) )
		);
	}

	/**
	 * Test that it will use the post type if supplied as meta.
	 *
	 * @since 1.9.0
	 */
	public function test_reverse_log_text_with_post_type_meta() {

		$this->assertEquals(
			'Page deleted.'
			, $this->render_log_text( false, array( 'post_type' => 'page' ) )
		);
	}

	/**
	 * Test that it will use the generic message bad post type if supplied as meta.
	 *
	 * @since 1.9.0
	 */
	public function test_reverse_log_text_with_bad_post_type_meta() {

		$this->assertEquals(
			'Post deleted.'
			, $this->render_log_text( false, array( 'post_type' => 'not' ) )
		);
	}

	/**
	 * Test that post_delete logs are still rendered properly.
	 *
	 * @since 1.9.0
	 *
	 * @covers ::wordpoints_points_logs_post_delete
	 */
	public function test_delete_post_log_text() {

		$text = wordpoints_render_points_log_text(
			$this->factory->user->create()
			, 10
			, 'points'
			, 'post_delete'
			, array()
		);

		$this->assertEquals( 'Post deleted.', $text );
	}

	/**
	 * Test that post_delete logs are rendered with the post type if available.
	 *
	 * @since 1.9.0
	 *
	 * @covers ::wordpoints_points_logs_post_delete
	 */
	public function test_delete_post_log_text_with_post_type() {

		$text = wordpoints_render_points_log_text(
			$this->factory->user->create()
			, 10
			, 'points'
			, 'post_delete'
			, array( 'post_type' => 'page' )
		);

		$this->assertEquals( 'Page deleted.', $text );
	}

	/**
	 * Test that post_delete logs are rendered generically with a bad post type.
	 *
	 * @since 1.9.0
	 *
	 * @covers ::wordpoints_points_logs_post_delete
	 */
	public function test_delete_post_log_text_with_bad_post_type() {

		$text = wordpoints_render_points_log_text(
			$this->factory->user->create()
			, 10
			, 'points'
			, 'post_delete'
			, array( 'post_type' => 'not' )
		);

		$this->assertEquals( 'Post deleted.', $text );
	}

	//
	// Helpers.
	//

	/**
	 * Render the text for a points log.
	 *
	 * @since 1.9.0
	 *
	 * @param array $post_args The arguments to create a post with.
	 * @param array $meta      The log meta.
	 *
	 * @return string The log text.
	 */
	protected function render_log_text( $post_args, $meta = array() ) {

		if ( ! is_null( $post_args ) && false !== $post_args ) {
			$meta['post_id'] = $this->factory->post->create( $post_args );
		}

		$log_type = ( false === $post_args ) ? 'reverse_post_publish' : 'post_publish';

		return wordpoints_render_points_log_text(
			$this->factory->user->create()
			, 10
			, 'points'
			, $log_type
			, $meta
		);
	}
}

// EOF
