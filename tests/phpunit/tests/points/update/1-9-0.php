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
 */
class WordPoints_Points_1_9_0_Update_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * @since 1.9.0
	 */
	protected $previous_version = '1.8.0';

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
	 */
	public function test_matching_network_hooks_combined() {

		if ( ! is_wordpoints_network_active() ) {
			$this->markTestSkipped( 'WordPoints must be network active.' );
		}

		WordPoints_Points_Hooks::set_network_mode( true );

		$this->create_matching_points_hooks();
		$this->create_nonmatching_points_hooks();

		WordPoints_Points_Hooks::set_network_mode( false );

		$this->update_component();

		// These two hooks match each other, so they should be combined.
		$this->assertTrue( $this->is_auto_reverse_on( 0, 'network' ) );
		$this->assertFalse( $this->hook_exists( 1, 'network' ) );

		// These two hooks have different points values, so they don't get combined.
		$this->assertFalse( $this->is_auto_reverse_on( 2, 'network' ) );
		$this->assertTrue( $this->hook_exists( 3, 'network' ) );

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
	 */
	public function test_legacy_false_if_no_leftover_network_hooks() {

		if ( ! is_wordpoints_network_active() ) {
			$this->markTestSkipped( 'WordPoints must be network active.' );
		}

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
		$this->assertTrue( $this->is_auto_reverse_on( 0, 'network' ) );
		$this->assertFalse( $this->hook_exists( 1, 'network' ) );

		// The other hook should have auto-reversal turned off.
		$this->assertFalse( $this->is_auto_reverse_on( 2, 'network' ) );

		// No Comment Removed hooks were left over so this flag should not be set.
		$this->assertFalse(
			get_site_option( 'wordpoints_comment_removed_hook_legacy' )
		);

		// Auto-reversal had to be disabled for a hook, so this flag should be set.
		$this->assertTrue(
			get_site_option( 'wordpoints_comment_hook_legacy' )
		);
	}

	//
	// Helpers.
	//

	/**
	 * Create a pair of matching points hooks.
	 *
	 * @since 1.9.0
	 */
	protected function create_matching_points_hooks() {

		// Add a Comment points hook to award 20 points for comments on posts.
		wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'points' => 20, 'post_type' => 'posts' )
		);

		$this->hook_numbers[] = $this->comment_hook->get_number();

		// Add a matching Comment Removed hook.
		wordpointstests_add_points_hook(
			'wordpoints_comment_removed_points_hook'
			, array( 'points' => 20, 'post_type' => 'posts' )
		);

		$this->hook_numbers[] = $this->comment_removed_hook->get_number();
	}

	/**
	 * Create a pair of non-matching points hooks.
	 *
	 * @since 1.9.0
	 */
	protected function create_nonmatching_points_hooks() {

		// Add a Comment points hook to award 20 points for comments on pages.
		wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'points' => 20, 'post_type' => 'pages' )
		);

		$this->hook_numbers[] = $this->comment_hook->get_number();

		// Add a Comment Removed hook to remove *25* hooks for comments on pages.
		wordpointstests_add_points_hook(
			'wordpoints_comment_removed_points_hook'
			, array( 'points' => 25, 'post_type' => 'pages' )
		);

		$this->hook_numbers[] = $this->comment_removed_hook->get_number();
	}

	/**
	 * Check if auto-reversal is on for a Comment hook.
	 *
	 * @since 1.9.0
	 *
	 * @param int    $number The index number for this hook in self::$hook_numbers.
	 * @param string $type   The type of hook this is, 'standard' or 'network'.
	 */
	protected function is_auto_reverse_on( $number, $type = 'standard' ) {

		$instances = $this->comment_hook->get_instances( $type );

		return(
			! isset( $instances[ $this->hook_numbers[ $number ] ]['auto_reverse'] )
			|| 1 === $instances[ $this->hook_numbers[ $number ] ]['auto_reverse']
		);
	}

	/**
	 * Assert that a Comment Removed hook has been removed and no longer exists.
	 *
	 * @since 1.9.0
	 *
	 * @param int    $number The index number for this hook in self::$hook_numbers.
	 * @param string $type   The type of hook this is, 'standard' or 'network'.
	 */
	protected function hook_exists( $number, $type = 'standard' ) {

		$instances = $this->comment_removed_hook->get_instances( $type );

		return isset( $instances[ $this->hook_numbers[ $number ] ] );
	}
}

// EOF
