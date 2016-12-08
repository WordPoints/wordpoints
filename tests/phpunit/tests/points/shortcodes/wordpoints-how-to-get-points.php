<?php

/**
 * Testcase for the [wordpoints_how_to_get_points] shortcode.
 *
 * @package WordPoints\Tests\Points
 * @since 1.4.0
 */

/**
 * Test the [wordpoints_how_to_get_points] shortcode.
 *
 * @since 1.4.0
 *
 * @group points
 * @group shortcodes
 *
 * @covers WordPoints_How_To_Get_Points_Shortcode
 */
class WordPoints_How_To_Get_Points_Shortcode_Test extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * Test that the [wordpoints_how_to_get_points] shortcode exists.
	 *
	 * @since 1.4.0
	 *
	 * @coversNothing
	 */
	public function test_shortcode_exists() {

		$this->assertTrue( shortcode_exists( 'wordpoints_how_to_get_points' ) );
	}

	/**
	 * Test that it displays a table of points hooks.
	 *
	 * @since 1.4.0
	 */
	public function test_displays_table_of_hooks() {

		// Create some points hooks for the table to display.
		wordpointstests_add_points_hook(
			'wordpoints_registration_points_hook'
			, array( 'points' => 10 )
		);

		wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'points' => 20, 'post_type' => 'ALL' )
		);

		// Test that the hooks are displayed in the table.
		$document = new DOMDocument;
		$document->preserveWhiteSpace = false;
		$document->loadHTML(
			wordpointstests_do_shortcode_func(
				'wordpoints_how_to_get_points'
				, array( 'points_type' => 'points' )
			)
		);
		$xpath = new DOMXPath( $document );
		$this->assertEquals( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Test that it displays network hooks when network active on multisite.
	 *
	 * @since 1.4.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_displays_network_hooks() {

		// Create some points hooks for the table to display.
		wordpointstests_add_points_hook(
			'wordpoints_registration_points_hook'
			, array( 'points' => 10 )
		);

		WordPoints_Points_Hooks::set_network_mode( true );
		wordpointstests_add_points_hook(
			'wordpoints_comment_points_hook'
			, array( 'points' => 20, 'post_type' => 'ALL' )
		);
		WordPoints_Points_Hooks::set_network_mode( false );

		// Test that both hooks are displayed in the table.
		$document = new DOMDocument;
		$document->preserveWhiteSpace = false;
		$document->loadHTML(
			wordpointstests_do_shortcode_func(
				'wordpoints_how_to_get_points'
				, array( 'points_type' => 'points' )
			)
		);
		$xpath = new DOMXPath( $document );
		$this->assertEquals( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Test that nothing is displayed to a normal user on failure.
	 *
	 * @since 1.4.0
	 */
	public function test_nothing_displayed_to_normal_user_on_failure() {

		$user_id = $this->factory->user->create();

		$old_current_user = wp_get_current_user();
		wp_set_current_user( $user_id );

		// There should be no error with an invalid points type.
		$this->assertEquals( null, wordpointstests_do_shortcode_func( 'wordpoints_how_to_get_points' ) );

		wp_set_current_user( $old_current_user->ID );
	}

	/**
	 * Test that an error is displayed to an admin user on failure.
	 *
	 * @since 1.4.0
	 */
	public function test_error_displayed_to_admin_user_on_failure() {

		// Create a user and assign them admin-like capabilities.
		$user = $this->factory->user->create_and_get();
		$user->add_cap( 'manage_options' );

		$old_current_user = wp_get_current_user();
		wp_set_current_user( $user->ID );

		// Check for an error when no points type is provided.
		$this->assertWordPointsShortcodeError(
			wordpointstests_do_shortcode_func( 'wordpoints_how_to_get_points' )
		);

		wp_set_current_user( $old_current_user->ID );
	}

	/**
	 * Test that it displays the reactions in the table.
	 *
	 * @since 2.1.0
	 */
	public function test_displays_reactions() {

		$hooks = wordpoints_hooks();
		$reaction_store = $hooks->get_reaction_store( 'points' );
		$reaction_1 = $reaction_store->create_reaction(
			array(
				'event' => 'user_register',
				'target' => array( 'user' ),
				'reactor' => 'points',
				'points' => 100,
				'points_type' => 'points',
				'description' => 'Registration.',
				'log_text' => 'Registration.',
			)
		);

		$this->assertIsReaction( $reaction_1 );

		$reaction_2 = $reaction_store->create_reaction(
			array(
				'event' => 'comment_leave\post',
				'target' => array( 'comment\post', 'author', 'user' ),
				'reactor' => 'points',
				'points' => 20,
				'points_type' => 'points',
				'description' => 'Leaving a comment.',
				'log_text' => 'Left a comment on a post.',
			)
		);

		$this->assertIsReaction( $reaction_2 );

		// Test that the hooks are displayed in the table.
		$document = new DOMDocument;
		$document->preserveWhiteSpace = false;
		$document->loadHTML(
			wordpointstests_do_shortcode_func(
				'wordpoints_how_to_get_points'
				, array( 'points_type' => 'points' )
			)
		);

		$xpath = new DOMXPath( $document );
		$table_rows = $xpath->query( '//tbody/tr' );
		$this->assertEquals( 2, $table_rows->length );

		$columns = $table_rows->item( 0 )->childNodes;
		$this->assertEquals(
			'$100pts.'
			, $columns->item( 0 )->textContent
		);
		$this->assertEquals(
			$reaction_1->get_meta( 'description' )
			, $columns->item( 2 )->textContent
		);

		$columns = $table_rows->item( 1 )->childNodes;
		$this->assertEquals(
			'$20pts.'
			, $columns->item( 0 )->textContent
		);
		$this->assertEquals(
			$reaction_2->get_meta( 'description' )
			, $columns->item( 2 )->textContent
		);
	}

	/**
	 * Test that it displays network reactions when network active on multisite.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_displays_network_reactions() {

		$hooks = wordpoints_hooks();
		$reaction_store = $hooks->get_reaction_store( 'points' );
		$reaction_1 = $reaction_store->create_reaction(
			array(
				'event' => 'user_register',
				'target' => array( 'user' ),
				'reactor' => 'points',
				'points' => 100,
				'points_type' => 'points',
				'description' => 'Registration.',
				'log_text' => 'Registration.',
			)
		);

		$this->assertIsReaction( $reaction_1 );

		$hooks->set_current_mode( 'network' );
		$reaction_store = $hooks->get_reaction_store( 'points' );
		$reaction_2 = $reaction_store->create_reaction(
			array(
				'event' => 'comment_leave\post',
				'target' => array( 'comment\post', 'author', 'user' ),
				'reactor' => 'points',
				'points' => 20,
				'points_type' => 'points',
				'description' => 'Leaving a comment.',
				'log_text' => 'Left a comment on a post.',
			)
		);

		$this->assertIsReaction( $reaction_2 );
		$hooks->set_current_mode( 'standard' );

		// Test that the hooks are displayed in the table.
		$document = new DOMDocument;
		$document->preserveWhiteSpace = false;
		$document->loadHTML(
			wordpointstests_do_shortcode_func(
				'wordpoints_how_to_get_points'
				, array( 'points_type' => 'points' )
			)
		);

		$xpath = new DOMXPath( $document );
		$table_rows = $xpath->query( '//tbody/tr' );
		$this->assertEquals( 2, $table_rows->length );

		$columns = $table_rows->item( 0 )->childNodes;
		$this->assertEquals(
			'$100pts.'
			, $columns->item( 0 )->textContent
		);
		$this->assertEquals(
			$reaction_1->get_meta( 'description' )
			, $columns->item( 2 )->textContent
		);

		$columns = $table_rows->item( 1 )->childNodes;
		$this->assertEquals(
			'$20pts.'
			, $columns->item( 0 )->textContent
		);
		$this->assertEquals(
			$reaction_2->get_meta( 'description' )
			, $columns->item( 2 )->textContent
		);
	}
}

// EOF
