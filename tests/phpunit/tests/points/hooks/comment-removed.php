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
 * @group legacy
 */
class WordPoints_Comment_Removed_Points_Hook_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * @since 1.9.0
	 *
	 * @var WordPoints_Comment_Points_Hook
	 */
	protected static $comment_hook;

	/**
	 * @since 1.9.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		self::$comment_hook = WordPoints_Points_Hooks::get_handler_by_id_base(
			'wordpoints_comment_points_hook'
		);

		self::$comment_hook->set_option( 'disable_auto_reverse_label', true );
	}

	/**
	 * @since 1.9.0
	 */
	public static function tearDownAfterClass() {

		self::$comment_hook->set_option( 'disable_auto_reverse_label', null );

		parent::tearDownAfterClass();
	}

	/**
	 * @since 1.9.0
	 */
	public function setUp() {

		parent::setUp();

		// Register the comment removed hook.
		WordPoints_Points_Hooks::register(
			'WordPoints_Comment_Removed_Points_Hook'
		);

		WordPoints_Points_Hooks::initialize_hooks();
	}

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

	/**
	 * Test that points are only awarded for the specified post type.
	 *
	 * @since 1.5.0
	 */
	public function test_points_only_removed_for_specified_post_type() {

		wordpointstests_add_points_hook(
			'wordpoints_comment_removed_points_hook'
			, array( 'points' => 20, 'post_type' => 'post' )
		);

		$user_id = $this->factory->user->create();
		wordpoints_set_points( $user_id, 100, 'points', 'test' );

		// Create a comment on a post.
		$comment_id = $this->factory->comment->create(
			array(
				'user_id' => $user_id,
				'comment_post_ID' => $this->factory->post->create(
					array( 'post_type' => 'post' )
				),
			)
		);

		wp_set_comment_status( $comment_id, 'spam' );

		// Test that points were removed for the comment.
		$this->assertEquals( 80, wordpoints_get_points( $user_id, 'points' ) );

		// Now create a comment on a page.
		$this->factory->comment->create(
			array(
				'user_id' => $user_id,
				'comment_post_ID' => $this->factory->post->create(
					array( 'post_type' => 'page' )
				),
			)
		);

		wp_set_comment_status( $comment_id, 'spam' );

		// Test that no points were removed for the comment.
		$this->assertEquals( 80, wordpoints_get_points( $user_id, 'points' ) );

	} // public function test_points_only_awarded_for_specified_post_type()

	/**
	 * Test that points are removed again after the comment points hook runs.
	 *
	 * Since 1.4.0 this had been a part of the Comment points hook's tests, but it
	 * was moved to here because these tests all need to be run separately, because
	 * of tests bleeding into eachother.
	 *
	 * @since 1.9.0
	 */
	public function test_points_removed_again_after_comment_hook_runs() {

		$hook = wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'points' => 10, 'auto_reverse' => 0 )
		);
		$this->assertInstanceOf( 'WordPoints_Comment_Points_Hook', $hook );

		$hook->set_option( 'disable_auto_reverse_label', true );

		$hook = wordpointstests_add_points_hook(
			'wordpoints_comment_removed_points_hook'
			, array( 'points' => 10 )
		);
		$this->assertInstanceOf( 'WordPoints_Comment_Removed_Points_Hook', $hook );

		$user_id = $this->factory->user->create();

		wordpoints_set_points( $user_id, 100, 'points', 'test' );
		$this->assertEquals( 100, wordpoints_get_points( $user_id, 'points' ) );

		$comment_id = $this->factory->comment->create(
			array(
				'user_id'         => $user_id,
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
}

// EOF
