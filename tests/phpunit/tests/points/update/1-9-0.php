<?php

/**
 * A test case for the points component update to 1.9.0.
 *
 * @package WordPoints\Tests
 * @since 1.9.0
 */

/**
 * Test that the points component updates to 1.9.0 properly.
 *
 * @since 1.9.0
 *
 * @group points
 * @group update
 *
 * @covers WordPoints_Points_Un_Installer::update_single_to_1_9_0
 * @covers WordPoints_Points_Un_Installer::update_site_to_1_9_0
 * @covers WordPoints_Points_Un_Installer::update_network_to_1_9_0
 * @covers WordPoints_Points_Un_Installer::_1_9_0_combine_hooks
 *
 * @expectedDeprecated WordPoints_Comment_Removed_Points_Hook::__construct
 * @expectedDeprecated WordPoints_Post_Delete_Points_Hook::__construct
 */
class WordPoints_Points_1_9_0_Update_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * @since 1.9.0
	 */
	protected $previous_version = '1.8.0';

	/**
	 * The ID numbers of the hooks created during the test.
	 *
	 * @since 1.9.0
	 *
	 * @var array
	 */
	protected $hook_numbers;

	/**
	 * The handler class for the Comment hook type.
	 *
	 * @since 1.9.0
	 *
	 * @var WordPoints_Comment_Points_Hook
	 */
	protected $comment_hook;

	/**
	 * The handler class for the Comment Removed hook type.
	 *
	 * @since 1.9.0
	 *
	 * @var WordPoints_Comment_Removed_Points_Hook
	 */
	protected $comment_removed_hook;

	/**
	 * The handler class for the Post hook type.
	 *
	 * @since 1.9.0
	 *
	 * @var WordPoints_Post_Points_Hook
	 */
	protected $post_hook;

	/**
	 * The handler class for the Post Delete hook type.
	 *
	 * @since 1.9.0
	 *
	 * @var WordPoints_Post_Delete_Points_Hook
	 */
	protected $post_delete_hook;

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

		$this->comment_hook = WordPoints_Points_Hooks::get_handler_by_id_base(
			'wordpoints_comment_points_hook'
		);

		$this->comment_removed_hook = WordPoints_Points_Hooks::get_handler_by_id_base(
			'wordpoints_comment_removed_points_hook'
		);

		$this->post_hook = WordPoints_Points_Hooks::get_handler_by_id_base(
			'wordpoints_post_points_hook'
		);

		$this->post_delete_hook = WordPoints_Points_Hooks::get_handler_by_id_base(
			'wordpoints_post_delete_points_hook'
		);
	}

	//
	// Tests.
	//

	/**
	 * Test that matching Comment and Comment Removed hooks are combined.
	 *
	 * @since 1.9.0
	 */
	public function test_matching_hooks_combined() {

		$this->create_matching_points_hooks();
		$this->create_nonmatching_points_hooks();

		$this->update_component();

		// These two hooks match each other, so they should be combined.
		$this->assertTrue( $this->is_auto_reverse_on( 0 ) );
		$this->assertFalse( $this->hook_exists( 1 ) );

		// These two hooks have different points values, so they don't get combined.
		$this->assertFalse( $this->is_auto_reverse_on( 2 ) );
		$this->assertTrue( $this->hook_exists( 3 ) );

		// There was a leftover Comment Removed hook, so we set this flag.
		$this->assertTrue(
			get_site_option( 'wordpoints_comment_removed_hook_legacy' )
		);

		// Auto-reversal had to be disabled for a hook, so this flag should be set.
		$this->assertTrue(
			get_site_option( 'wordpoints_comment_hook_legacy' )
		);
	}

	/**
	 * Test that if there are no leftover hooks the legacy option isn't added.
	 *
	 * @since 1.9.0
	 */
	public function test_legacy_false_if_no_leftover_removed_hooks() {

		$this->create_matching_points_hooks();

		// Add a Comment points hook to award 20 points for comments on pages.
		wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'points' => 20, 'post_type' => 'pages' )
		);

		$this->hook_numbers[] = $this->comment_hook->get_number();

		$this->update_component();

		// These two hooks match each other, so they should be combined.
		$this->assertTrue( $this->is_auto_reverse_on( 0 ) );
		$this->assertFalse( $this->hook_exists( 1 ) );

		// The other hook should have auto-reversal turned off.
		$this->assertFalse( $this->is_auto_reverse_on( 2 ) );

		// No Comment Removed hooks were left over so this flag should not be set.
		$this->assertFalse(
			get_site_option( 'wordpoints_comment_removed_hook_legacy' )
		);

		// Auto-reversal had to be disabled for a hook, so this flag should be set.
		$this->assertTrue(
			get_site_option( 'wordpoints_comment_hook_legacy' )
		);
	}

	/**
	 * Test that if there are no leftover hooks the legacy option isn't added.
	 *
	 * @since 1.9.0
	 */
	public function test_legacy_false_if_no_leftover_hooks() {

		$this->create_matching_points_hooks();

		// Add a Comment points hook to award 20 points for comments on pages.
		wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'points' => 20, 'post_type' => 'pages' )
		);

		$this->hook_numbers[] = $this->comment_hook->get_number();

		$this->update_component();

		// These two hooks match each other, so they should be combined.
		$this->assertTrue( $this->is_auto_reverse_on( 0 ) );
		$this->assertFalse( $this->hook_exists( 1 ) );

		// No Comment Removed hooks were left over so this flag should not be set.
		$this->assertFalse(
			get_site_option( 'wordpoints_comment_removed_hook_legacy' )
		);

		// Auto-reversal had to be disabled for a hook, so this flag should be set.
		$this->assertTrue(
			get_site_option( 'wordpoints_comment_hook_legacy' )
		);
	}

	/**
	 * Test that network hooks are updated on multisite.
	 *
	 * @since 1.9.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_matching_network_hooks_combined() {

		WordPoints_Points_Hooks::set_network_mode( true );

		$this->create_matching_points_hooks();
		$this->create_nonmatching_points_hooks();

		WordPoints_Points_Hooks::set_network_mode( false );

		$this->update_component();

		// These two hooks match each other, so they should be combined.
		$this->assertTrue( $this->is_auto_reverse_on( 0, 'comment', 'network' ) );
		$this->assertFalse( $this->hook_exists( 1, 'comment_removed', 'network' ) );

		// These two hooks have different points values, so they don't get combined.
		$this->assertFalse( $this->is_auto_reverse_on( 2, 'comment', 'network' ) );
		$this->assertTrue( $this->hook_exists( 3, 'comment_removed', 'network' ) );

		// There was a leftover Comment Removed hook, so we set this flag.
		$this->assertTrue(
			get_site_option( 'wordpoints_comment_removed_hook_legacy' )
		);

		// Auto-reversal had to be disabled for a hook, so this flag should be set.
		$this->assertTrue(
			get_site_option( 'wordpoints_comment_hook_legacy' )
		);
	}

	/**
	 * Test that if there are no leftover network hooks the legacy option isn't added.
	 *
	 * @since 1.9.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_legacy_false_if_no_leftover_network_hooks() {

		WordPoints_Points_Hooks::set_network_mode( true );

		$this->create_matching_points_hooks();

		// Add a Comment points hook to award 20 points for comments on pages.
		wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'points' => 20, 'post_type' => 'pages' )
		);

		$this->hook_numbers[] = $this->comment_hook->get_number();

		WordPoints_Points_Hooks::set_network_mode( false );

		$this->update_component();

		// These two hooks match each other, so they should be combined.
		$this->assertTrue( $this->is_auto_reverse_on( 0, 'comment', 'network' ) );
		$this->assertFalse( $this->hook_exists( 1, 'comment_removed', 'network' ) );

		// The other hook should have auto-reversal turned off.
		$this->assertFalse( $this->is_auto_reverse_on( 2, 'comment', 'network' ) );

		// No Comment Removed hooks were left over so this flag should not be set.
		$this->assertFalse(
			get_site_option( 'wordpoints_comment_removed_hook_legacy' )
		);

		// Auto-reversal had to be disabled for a hook, so this flag should be set.
		$this->assertTrue(
			get_site_option( 'wordpoints_comment_hook_legacy' )
		);
	}

	/**
	 * Test that matching Post and Post Delete hooks are combined.
	 *
	 * @since 1.9.0
	 */
	public function test_matching_post_hooks_combined() {

		$this->create_matching_points_hooks( 'post' );
		$this->create_nonmatching_points_hooks( 'post' );

		$this->update_component();

		// These two hooks match each other, so they should be combined.
		$this->assertTrue( $this->is_auto_reverse_on( 0, 'post' ) );
		$this->assertFalse( $this->hook_exists( 1, 'post_delete' ) );

		// These two hooks have different points values, so they don't get combined.
		$this->assertFalse( $this->is_auto_reverse_on( 2, 'post' ) );
		$this->assertTrue( $this->hook_exists( 3, 'post_delete' ) );

		// There was a leftover Post Delete hook, so we set this flag.
		$this->assertTrue(
			get_site_option( 'wordpoints_post_delete_hook_legacy' )
		);

		// Auto-reversal had to be disabled for a hook, so this flag should be set.
		$this->assertTrue(
			get_site_option( 'wordpoints_post_hook_legacy' )
		);
	}

	/**
	 * Test that if there are no leftover hooks the legacy option isn't added.
	 *
	 * @since 1.9.0
	 */
	public function test_legacy_false_if_no_leftover_post_delete_hooks() {

		$this->create_matching_points_hooks( 'post' );

		// Add a hook to award 20 points for pages.
		wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'points' => 20, 'post_type' => 'pages' )
		);

		$this->hook_numbers[] = $this->post_hook->get_number();

		$this->update_component();

		// These two hooks match each other, so they should be combined.
		$this->assertTrue( $this->is_auto_reverse_on( 0, 'post' ) );
		$this->assertFalse( $this->hook_exists( 1, 'post_delete' ) );

		// The other hook should have auto-reversal turned off.
		$this->assertFalse( $this->is_auto_reverse_on( 2, 'post' ) );

		// No Post Delete hooks were left over so this flag should not be set.
		$this->assertFalse(
			get_site_option( 'wordpoints_post_delete_hook_legacy' )
		);

		// Auto-reversal had to be disabled for a hook, so this flag should be set.
		$this->assertTrue(
			get_site_option( 'wordpoints_post_hook_legacy' )
		);
	}

	/**
	 * Test that if there are no leftover hooks the legacy option isn't added.
	 *
	 * @since 1.9.0
	 */
	public function test_legacy_false_if_no_leftover_post_hooks() {

		$this->create_matching_points_hooks( 'post' );

		// Add a Post points hook to award 20 points for pages.
		wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'points' => 20, 'post_type' => 'pages' )
		);

		$this->hook_numbers[] = $this->post_hook->get_number();

		$this->update_component();

		// These two hooks match each other, so they should be combined.
		$this->assertTrue( $this->is_auto_reverse_on( 0, 'post' ) );
		$this->assertFalse( $this->hook_exists( 1, 'post_delete' ) );

		// No Post Delete hooks were left over so this flag should not be set.
		$this->assertFalse(
			get_site_option( 'wordpoints_post_delete_hook_legacy' )
		);

		// Auto-reversal had to be disabled for a hook, so this flag should be set.
		$this->assertTrue(
			get_site_option( 'wordpoints_post_hook_legacy' )
		);
	}

	/**
	 * Test that network hooks are updated on multisite.
	 *
	 * @since 1.9.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_matching_network_post_hooks_combined() {

		WordPoints_Points_Hooks::set_network_mode( true );

		$this->create_matching_points_hooks( 'post' );
		$this->create_nonmatching_points_hooks( 'post' );

		WordPoints_Points_Hooks::set_network_mode( false );

		$this->update_component();

		// These two hooks match each other, so they should be combined.
		$this->assertTrue( $this->is_auto_reverse_on( 0, 'post', 'network' ) );
		$this->assertFalse( $this->hook_exists( 1, 'post_delete', 'network' ) );

		// These two hooks have different points values, so they don't get combined.
		$this->assertFalse( $this->is_auto_reverse_on( 2, 'post', 'network' ) );
		$this->assertTrue( $this->hook_exists( 3, 'post_delete', 'network' ) );

		// There was a leftover Post Delete hook, so we set this flag.
		$this->assertTrue(
			get_site_option( 'wordpoints_post_delete_hook_legacy' )
		);

		// Auto-reversal had to be disabled for a hook, so this flag should be set.
		$this->assertTrue(
			get_site_option( 'wordpoints_post_hook_legacy' )
		);
	}

	/**
	 * Test that if there are no leftover network hooks the legacy option isn't added.
	 *
	 * @since 1.9.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_legacy_false_if_no_leftover_network_post_hooks() {

		WordPoints_Points_Hooks::set_network_mode( true );

		$this->create_matching_points_hooks( 'post' );

		// Add a Post points hook to award 20 points for pages.
		wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'points' => 20, 'post_type' => 'pages' )
		);

		$this->hook_numbers[] = $this->post_hook->get_number();

		WordPoints_Points_Hooks::set_network_mode( false );

		$this->update_component();

		// These two hooks match each other, so they should be combined.
		$this->assertTrue( $this->is_auto_reverse_on( 0, 'post', 'network' ) );
		$this->assertFalse( $this->hook_exists( 1, 'post_delete', 'network' ) );

		// The other hook should have auto-reversal turned off.
		$this->assertFalse( $this->is_auto_reverse_on( 2, 'post', 'network' ) );

		// No Post Delete hooks were left over so this flag should not be set.
		$this->assertFalse(
			get_site_option( 'wordpoints_post_delete_hook_legacy' )
		);

		// Auto-reversal had to be disabled for a hook, so this flag should be set.
		$this->assertTrue(
			get_site_option( 'wordpoints_post_hook_legacy' )
		);
	}

	//
	// Helpers.
	//

	/**
	 * Create a pair of matching points hooks.
	 *
	 * @since 1.9.0
	 *
	 * @param string $type The type of hooks to create.
	 */
	protected function create_matching_points_hooks( $type = 'comment' ) {

		// Add a points hook to award 20 points for posts.
		wordpointstests_add_points_hook(
			"wordpoints_{$type}_points_hook"
			, array( 'points' => 20, 'post_type' => 'posts' )
		);

		$this->hook_numbers[] = $this->{"{$type}_hook"}->get_number();

		$delete_type = ( 'comment' === $type ) ? 'comment_removed' : 'post_delete';

		// Add a matching reverse hook.
		wordpointstests_add_points_hook(
			"wordpoints_{$delete_type}_points_hook"
			, array( 'points' => 20, 'post_type' => 'posts' )
		);

		$this->hook_numbers[] = $this->{"{$delete_type}_hook"}->get_number();
	}

	/**
	 * Create a pair of non-matching points hooks.
	 *
	 * @since 1.9.0
	 *
	 * @param string $type The type of hooks to create.
	 */
	protected function create_nonmatching_points_hooks( $type = 'comment' ) {

		// Add a points hook to award 20 points for pages.
		wordpointstests_add_points_hook(
			"wordpoints_{$type}_points_hook"
			, array( 'points' => 20, 'post_type' => 'pages' )
		);

		$this->hook_numbers[] = $this->{"{$type}_hook"}->get_number();

		$delete_type = ( 'comment' === $type ) ? 'comment_removed' : 'post_delete';

		// Add a hook to remove *25* points for comments on pages.
		wordpointstests_add_points_hook(
			"wordpoints_{$delete_type}_points_hook"
			, array( 'points' => 25, 'post_type' => 'pages' )
		);

		$this->hook_numbers[] = $this->{"{$delete_type}_hook"}->get_number();
	}

	/**
	 * Check if auto-reversal is on for a hook.
	 *
	 * @since 1.9.0
	 *
	 * @param int    $number The index number for this hook in self::$hook_numbers.
	 * @param string $type   The type of hook, 'comment' or 'post'.
	 * @param string $mode   The type of hook this is, 'standard' or 'network'.
	 *
	 * @return bool Whether auto-reversal is on for a hook.
	 */
	protected function is_auto_reverse_on( $number, $type = 'comment', $mode = 'standard' ) {

		$instances = $this->{"{$type}_hook"}->get_instances( $mode );

		return(
			! isset( $instances[ $this->hook_numbers[ $number ] ]['auto_reverse'] )
			|| 1 === $instances[ $this->hook_numbers[ $number ] ]['auto_reverse']
		);
	}

	/**
	 * Assert that a reverse hook has been removed and no longer exists.
	 *
	 * @since 1.9.0
	 *
	 * @param int    $number The index number for this hook in self::$hook_numbers.
	 * @param string $type   The type of hook, 'comment_removed' or 'post_delete'.
	 * @param string $mode   The type of hook this is, 'standard' or 'network'.
	 *
	 * @return bool Whether the hook exists.
	 */
	protected function hook_exists( $number, $type = 'comment_removed', $mode = 'standard' ) {

		$instances = $this->{"{$type}_hook"}->get_instances( $mode );

		return isset( $instances[ $this->hook_numbers[ $number ] ] );
	}
}

// EOF
