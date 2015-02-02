<?php

/**
 * A test case for the comment received points hook.
 *
 * @package WordPoints\Tests
 * @since 1.8.0
 */

/**
 * Test that the comment points hook functions as expected.
 *
 * @since 1.8.0
 *
 * @group points
 * @group points_hooks
 */
class WordPoints_Comment_Received_Points_Hook_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * @since 1.9.0
	 */
	public function setUp() {

		parent::setUp();

		// Back-compat WP 3.9 and below.
		remove_action( 'transition_post_status', '_update_blog_date_on_post_publish' );
	}

	/**
	 * Test that points are awarded as expected.
	 *
	 * @since 1.8.0
	 */
	public function test_points_awarded() {

		wordpointstests_add_points_hook(
			'wordpoints_comment_received_points_hook'
			, array( 'points' => 10 )
		);

		$user_id = $this->factory->user->create();

		wordpoints_set_points( $user_id, 100, 'points', 'test' );
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		$post_id = $this->factory->post->create(
			array( 'post_author' => $user_id )
		);

		$comment_id = $this->factory->comment->create(
			array( 'comment_post_ID' => $post_id )
		);

		$this->assertEquals( 110, wordpoints_get_points( $user_id, 'points' ) );

		// Points should not be awarded twice in a row.
		do_action( 'transition_comment_status', 'approve', 'hold', get_comment( $comment_id ) );

		$this->assertEquals( 110, wordpoints_get_points( $user_id, 'points' ) );

		// Test that points are awarded on transition from hold.
		$comment_id = $this->factory->comment->create(
			array(
				'comment_post_ID' => $post_id,
				'comment_status'  => 'hold',
			)
		);

		wp_set_comment_status( $comment_id, 'approve' );
		$this->assertEquals( 120, wordpoints_get_points( $user_id, 'points' ) );

		// Test that points are awarded on transition from spam.
		$comment_id = $this->factory->comment->create(
			array(
				'comment_post_ID' => $post_id,
				'comment_status'  => 'spam',
			)
		);

		wp_set_comment_status( $comment_id, 'approve' );
		$this->assertEquals( 130, wordpoints_get_points( $user_id, 'points' ) );

		// Test that points are awarded on transition from trash.
		$comment_id = $this->factory->comment->create(
			array(
				'comment_post_ID' => $post_id,
				'comment_status'  => 'trash',
			)
		);

		wp_set_comment_status( $comment_id, 'approve' );
		$this->assertEquals( 140, wordpoints_get_points( $user_id, 'points' ) );

	} // public function test_points_awarded()

	/**
	 * Test automatic reversal of the hook when the comment's status is toggled.
	 *
	 * @since 1.8.0
	 */
	public function test_points_auto_reversal() {

		$hook = wordpointstests_add_points_hook(
			'wordpoints_comment_received_points_hook'
			, array( 'points' => 10 )
		);
		$this->assertInstanceOf( 'WordPoints_Comment_Received_Points_Hook', $hook );

		$user_id = $this->factory->user->create();

		wordpoints_set_points( $user_id, 100, 'points', 'test' );
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		$comment_id = $this->factory->comment->create(
			array(
				'comment_post_ID' => $this->factory->post->create(
					array( 'post_author' => $user_id )
				),
			)
		);

		$this->assertEquals( 110, wordpoints_get_points( $user_id, 'points' ) );

		// Test that status transitions award/remove points correctly.
		wp_set_comment_status( $comment_id, 'hold' );
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		wp_set_comment_status( $comment_id, 'approve' );
		$this->assertEquals( 110, wordpoints_get_points( $user_id, 'points' ) );

		wp_set_comment_status( $comment_id, 'spam' );
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		wp_set_comment_status( $comment_id, 'approve' );
		$this->assertEquals( 110, wordpoints_get_points( $user_id, 'points' ) );

		wp_set_comment_status( $comment_id, 'trash' );
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		wp_set_comment_status( $comment_id, 'approve' );
		$this->assertEquals( 110, wordpoints_get_points( $user_id, 'points' ) );

	} // public function test_points_awarded_again_after_comment_remove_hook_runs()

	/**
	 * Test that points are only awarded for the specified post type.
	 *
	 * @since 1.8.0
	 */
	public function test_points_only_awarded_for_specified_post_type() {

		wordpointstests_add_points_hook(
			'wordpoints_comment_received_points_hook'
			, array( 'points' => 20, 'post_type' => 'post' )
		);

		$user_id = $this->factory->user->create();

		// Create a comment on a post.
		$this->factory->comment->create(
			array(
				'comment_post_ID' => $this->factory->post->create(
					array(
						'post_type'   => 'post',
						'post_author' => $user_id,
					)
				),
			)
		);

		// Test that points were awarded for the comment.
		$this->assertEquals( 20, wordpoints_get_points( $user_id, 'points' ) );

		// Now create a comment on a page.
		$this->factory->comment->create(
			array(
				'comment_post_ID' => $this->factory->post->create(
					array(
						'post_type'   => 'page',
						'post_author' => $user_id,
					)
				),
			)
		);

		// Test that no points were awarded for the comment.
		$this->assertEquals( 20, wordpoints_get_points( $user_id, 'points' ) );

	} // public function test_points_only_awarded_for_specified_post_type()

	/**
	 * Test that the logs are cleaned properly when a comment is deleted.
	 *
	 * @since 1.8.0
	 */
	public function test_logs_cleaned_on_comment_deletion() {

		wordpointstests_add_points_hook(
			'wordpoints_comment_received_points_hook'
			, array( 'points' => 10 )
		);

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $user_id ) );
		$comment_id = $this->factory->comment->create(
			array( 'comment_post_ID' => $post_id )
		);

		wp_delete_comment( $comment_id, true );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'comment_received',
				'meta_key'   => 'comment_id',
				'meta_value' => $comment_id,
			)
		);

		$this->assertEquals( 0, $query->count() );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'comment_received',
				'meta_key'   => 'post_id',
				'meta_value' => $post_id,
			)
		);

		$this->assertEquals( 1, $query->count() );

		$log = $query->get( 'row' );

		$link = '<a href="' . get_permalink( $post_id ) . '">'
			. get_the_title( $post_id )
			. '</a>';

		$this->assertEquals(
			sprintf(
				_x( 'Received a comment on %s.', 'points log description', 'wordpoints' )
				, $link
			)
			, $log->text
		);
	}

	/**
	 * Test that logs are cleaned properly when a post is deleted.
	 *
	 * @since 1.8.0
	 */
	public function test_logs_cleaned_on_post_deletion() {

		wordpointstests_add_points_hook(
			'wordpoints_comment_received_points_hook'
			, array( 'points' => 10 )
		);

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $user_id ) );
		$comment_id = $this->factory->comment->create(
			array( 'comment_post_ID' => $post_id )
		);

		wp_delete_comment( $comment_id, true );
		wp_delete_post( $post_id, true );

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'comment_received',
				'meta_key'   => 'post_id',
				'meta_value' => $post_id,
			)
		);

		$this->assertEquals( 0, $query->count() );

		$query = new WordPoints_Points_Logs_Query(
			array( 'log_type' => 'comment_received' )
		);
		$log = $query->get( 'row' );

		$this->assertEquals(
			_x( 'Received a comment on a Post.', 'points log description', 'wordpoints' )
			, $log->text
		);
	}

	/**
	 * Test that logs are hidden for users who don't have the required capabilities.
	 *
	 * @since 1.8.0
	 */
	public function test_logs_hidden_for_insufficient_caps() {

		wordpointstests_add_points_hook(
			'wordpoints_comment_received_points_hook'
			, array( 'points' => 10 )
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
	 * Test that an author doesn't receive points for their own comments.
	 *
	 * @since 1.8.0
	 */
	public function test_authors_comments_ignored() {

		wordpointstests_add_points_hook(
			'wordpoints_comment_received_points_hook'
			, array( 'points' => 10 )
		);

		$author_id = $this->factory->user->create();

		wordpoints_set_points( $author_id, 100, 'points', 'test' );
		$this->assertEquals( 100, wordpoints_get_points( $author_id, 'points' ) );

		$post_id = $this->factory->post->create(
			array( 'post_author' => $author_id )
		);

		$this->factory->comment->create(
			array(
				'user_id'         => $author_id,
				'comment_post_ID' => $post_id,
			)
		);

		$this->assertEquals( 100, wordpoints_get_points( $author_id, 'points' ) );
	}

	/**
	 * Test that it uses a message with a link to the comment if available.
	 *
	 * @since 1.9.0
	 */
	public function test_log_text_with_comment_link() {

		$this->assertStringMatchesFormat(
			'Received a comment on <a href="%s#comment-%d">Post title 1</a>.'
			, $this->render_log_text(
				array( 'post_type' => 'not', 'comment' => true )
			)
		);
	}

	/**
	 * Test that it uses just the post link if the comment is unavailable.
	 *
	 * @since 1.9.0
	 */
	public function test_log_text_with_post_title() {

		$this->assertStringMatchesFormat(
			'Received a comment on <a href="%s">Post title 1</a>.'
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
			'Received a comment on <a href="%s">(no title)</a>.'
			, $this->render_log_text(
				array( 'post_type' => 'page', 'post_title' => '' )
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
			'Received a comment.'
			, $this->render_log_text( null, array( 'post_id' => $post_id ) )
		);
	}

	/**
	 * Test that it will use the post type if supplied as meta.
	 *
	 * @since 1.9.0
	 */
	public function test_log_text_with_no_post_and_post_type_meta() {

		$this->assertEquals(
			'Received a comment on a Page.'
			, $this->render_log_text( null, array( 'post_type' => 'page' ) )
		);
	}

	/**
	 * Test that it will use the generic message bad post type if supplied as meta.
	 *
	 * @since 1.9.0
	 */
	public function test_log_text_with_no_post_and_bad_post_type_meta() {

		$this->assertEquals(
			'Received a comment.'
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
			'Comment received removed.'
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
			'Comment received on Test title removed.'
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
			'Comment received on a Page removed.'
			, $this->render_log_text( false, array( 'post_type' => 'page' ) )
		);
	}

	/**
	 * Test that it will use the generic message if bad post type supplied as meta.
	 *
	 * @since 1.9.0
	 */
	public function test_reverse_log_text_with_bad_post_type_meta() {

		$this->assertEquals(
			'Comment received removed.'
			, $this->render_log_text( false, array( 'post_type' => 'not' ) )
		);
	}

	//
	// Helpers.
	//

	/**
	 * Render the text for a points log.
	 *
	 * @since 1.9.0
	 *
	 * @param array|null|false $post_args The attributes for a post, or false not to
	 *                                    create one.
	 * @param array            $meta      The metadata for the transaction.
	 *
	 * @return string The log text generated for this pseudo-transaction.
	 */
	protected function render_log_text( $post_args, $meta = array() ) {

		if ( ! is_null( $post_args ) && false !== $post_args ) {

			$post_id = $this->factory->post->create( $post_args );

			if ( ! empty( $post_args['comment'] ) ) {
				$meta['comment_id'] = $this->factory->comment->create(
					array( 'comment_post_ID' => $post_id )
				);
			} else {
				$meta['post_id'] = $post_id;
			}
		}

		$log_type = ( false === $post_args ) ? 'reverse_comment_received' : 'comment_received';

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
