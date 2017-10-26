<?php

/**
 * Test case for WordPoints_Hook_Reaction_Store_Options.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Reaction_Store_Options.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Reaction_Store_Options
 */
class WordPoints_Hook_Reaction_Store_Options_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test checking if a nonexistent reaction exists.
	 *
	 * @since 2.1.0
	 */
	public function test_nonexistent_reaction_exists() {

		$store = new WordPoints_Hook_Reaction_Store_Options(
			'test_store'
			, 'standard'
		);

		$this->assertFalse( $store->reaction_exists( 1 ) );
	}

	/**
	 * Test checking if a reaction exists.
	 *
	 * @since 2.1.0
	 */
	public function test_reaction_exists() {

		$reaction_store = $this->factory->wordpoints->hook_reaction_store->create_and_get(
			array( 'class' => 'WordPoints_Hook_Reaction_Store_Options' )
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$this->assertTrue( $reaction_store->reaction_exists( $reaction->get_id() ) );
	}

	/**
	 * Test getting all reactions when there are none.
	 *
	 * @since 2.1.0
	 */
	public function test_get_reactions_none() {

		$store = new WordPoints_Hook_Reaction_Store_Options(
			'test_store'
			, 'standard'
		);

		$this->assertSame( array(), $store->get_reactions() );
	}

	/**
	 * Test getting all reactions.
	 *
	 * @since 2.1.0
	 */
	public function test_get_reactions() {

		$reaction_store = $this->factory->wordpoints->hook_reaction_store->create_and_get(
			array( 'class' => 'WordPoints_Hook_Reaction_Store_Options' )
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$reactions = $reaction_store->get_reactions();

		$this->assertCount( 1, $reactions );
		$this->assertSame( $reaction->get_id(), $reactions[0]->get_id() );
		$this->assertSame( $reaction->get_all_meta(), $reactions[0]->get_all_meta() );
	}

	/**
	 * Test getting all reactions when the hook mode has changed.
	 *
	 * @since 2.1.0
	 *
	 * @link https://github.com/WordPoints/wordpoints/issues/411
	 */
	public function test_get_reactions_switched_modes() {

		$reaction_store = $this->factory->wordpoints->hook_reaction_store->create_and_get(
			array( 'class' => 'WordPoints_Hook_Reaction_Store_Options' )
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$hooks = wordpoints_hooks();
		$hooks->set_current_mode( 'test' );
		$reactions = $reaction_store->get_reactions();

		$this->assertCount( 1, $reactions );
		$this->assertSame( $reaction->get_id(), $reactions[0]->get_id() );
		$this->assertSame( $reaction->get_all_meta(), $reactions[0]->get_all_meta() );
	}

	/**
	 * Test getting all reactions to an event when there are no reactions.
	 *
	 * @since 2.1.0
	 */
	public function test_get_reactions_to_event_no_reactions() {

		$store = new WordPoints_Hook_Reaction_Store_Options(
			'test_store'
			, 'standard'
		);

		$this->assertSame(
			array()
			, $store->get_reactions_to_event( 'test_event' )
		);
	}

	/**
	 * Test getting all reactions to an event.
	 *
	 * @since 2.1.0
	 */
	public function test_get_reactions_to_event() {

		$reaction_store = $this->factory->wordpoints->hook_reaction_store->create_and_get(
			array( 'class' => 'WordPoints_Hook_Reaction_Store_Options' )
		);

		$this->factory->wordpoints->hook_reaction->create(
			array( 'event' => 'another' )
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$reactions = $reaction_store->get_reactions_to_event( 'test_event' );

		$this->assertCount( 1, $reactions );
		$this->assertSame( $reaction->get_id(), $reactions[0]->get_id() );
		$this->assertSame( $reaction->get_all_meta(), $reactions[0]->get_all_meta() );
	}

	/**
	 * Test getting all reactions to an event when there are none.
	 *
	 * @since 2.1.0
	 */
	public function test_get_reactions_to_event_none() {

		$reaction_store = $this->factory->wordpoints->hook_reaction_store->create_and_get(
			array( 'class' => 'WordPoints_Hook_Reaction_Store_Options' )
		);

		$this->factory->wordpoints->hook_reaction->create(
			array( 'event' => 'another' )
		);

		$reactions = $reaction_store->get_reactions_to_event( 'test_event' );

		$this->assertSame( array(), $reactions );
	}

	/**
	 * Test getting the event for a nonexistent reaction from the index.
	 *
	 * @since 2.1.0
	 */
	public function test_get_reaction_event_from_index_nonexistent() {

		$store = new WordPoints_Hook_Reaction_Store_Options(
			'test_store'
			, 'standard'
		);

		$this->assertFalse( $store->get_reaction_event_from_index( 1 ) );
	}

	/**
	 * Test getting the event for a reaction from the index.
	 *
	 * @since 2.1.0
	 */
	public function test_get_reaction_event_from_index() {

		/** @var WordPoints_Hook_Reaction_Store_Options $reaction_store */
		$reaction_store = $this->factory->wordpoints->hook_reaction_store->create_and_get(
			array( 'class' => 'WordPoints_Hook_Reaction_Store_Options' )
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$this->assertSame(
			$reaction->get_event_slug()
			, $reaction_store->get_reaction_event_from_index( $reaction->get_id() )
		);
	}

	/**
	 * Test updating the event for a nonexistent reaction in the index.
	 *
	 * @since 2.1.0
	 */
	public function test_update_reaction_event_from_index_nonexistent() {

		$store = new WordPoints_Hook_Reaction_Store_Options(
			'test_store'
			, 'standard'
		);

		$this->assertFalse(
			$store->update_reaction_event_in_index( 1, 'test_event' )
		);
	}

	/**
	 * Test getting the event for a reaction in the index.
	 *
	 * @since 2.1.0
	 */
	public function test_update_reaction_event_in_index() {

		/** @var WordPoints_Hook_Reaction_Store_Options $reaction_store */
		$reaction_store = $this->factory->wordpoints->hook_reaction_store->create_and_get(
			array( 'class' => 'WordPoints_Hook_Reaction_Store_Options' )
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$this->assertTrue(
			$reaction_store->update_reaction_event_in_index( 1, 'another_event' )
		);

		$this->assertSame(
			'another_event'
			, $reaction_store->get_reaction_event_from_index( $reaction->get_id() )
		);
	}

	/**
	 * Test deleting a reaction that doesn't exist.
	 *
	 * @since 2.1.0
	 */
	public function test_delete_reaction_nonexistent() {

		$store = new WordPoints_Hook_Reaction_Store_Options(
			'test_store'
			, 'standard'
		);

		$this->assertFalse( $store->delete_reaction( 1 ) );
	}

	/**
	 * Test deleting a reaction.
	 *
	 * @since 2.1.0
	 */
	public function test_delete_reaction() {

		/** @var WordPoints_Hook_Reaction_Store_Options $reaction_store */
		$reaction_store = $this->factory->wordpoints->hook_reaction_store->create_and_get(
			array( 'class' => 'WordPoints_Hook_Reaction_Store_Options' )
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$this->assertTrue( $reaction_store->delete_reaction( $reaction->get_id() ) );

		$this->assertFalse( $reaction_store->reaction_exists( $reaction->get_id() ) );
		$this->assertSame( array(), $reaction_store->get_reactions() );
		$this->assertSame(
			array()
			, $reaction_store->get_reactions_to_event( 'test_event' )
		);

		$this->assertFalse(
			$reaction_store->get_reaction_event_from_index( $reaction->get_id() )
		);
	}

	/**
	 * Test creating a reaction.
	 *
	 * @since 2.1.0
	 */
	public function test_create_reaction() {

		/** @var WordPoints_Hook_Reaction_Store_Options $reaction_store */
		$reaction_store = $this->factory->wordpoints->hook_reaction_store->create_and_get(
			array( 'class' => 'WordPoints_Hook_Reaction_Store_Options' )
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$this->assertSame( 1, $reaction->get_id() );

		$this->assertTrue( $reaction_store->reaction_exists( $reaction->get_id() ) );

		$reactions = $reaction_store->get_reactions();
		$this->assertCount( 1, $reactions );
		$this->assertSame( $reaction->get_id(), $reactions[0]->get_id() );
		$this->assertSame( $reaction->get_all_meta(), $reactions[0]->get_all_meta() );

		$reactions = $reaction_store->get_reactions_to_event( 'test_event' );
		$this->assertCount( 1, $reactions );
		$this->assertSame( $reaction->get_id(), $reactions[0]->get_id() );
		$this->assertSame( $reaction->get_all_meta(), $reactions[0]->get_all_meta() );

		$this->assertSame(
			'test_event'
			, $reaction_store->get_reaction_event_from_index( $reaction->get_id() )
		);
	}

	/**
	 * Test creating a reaction increments the IDs.
	 *
	 * @since 2.1.0
	 */
	public function test_create_reaction_increments_id() {

		/** @var WordPoints_Hook_Reaction_Store_Options $reaction_store */
		$reaction_store = $this->factory->wordpoints->hook_reaction_store->create_and_get(
			array( 'class' => 'WordPoints_Hook_Reaction_Store_Options' )
		);

		$reactions = $this->factory->wordpoints->hook_reaction->create_many( 3 );

		$this->assertSame( 1, $reactions[0]->get_id() );
		$this->assertSame( 2, $reactions[1]->get_id() );
		$this->assertSame( 3, $reactions[2]->get_id() );

		$this->assertTrue( $reaction_store->delete_reaction( 1 ) );

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$this->assertSame( 4, $reaction->get_id() );

		// When the newest reaction is deleted, the ID shouldn't be reused.
		$this->assertTrue( $reaction_store->delete_reaction( 4 ) );

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$this->assertSame( 5, $reaction->get_id() );
	}

	/**
	 * Test creating a reaction increments the IDs even when the index is off.
	 *
	 * @since 2.1.0
	 */
	public function test_create_reaction_increments_id_index() {

		/** @var WordPoints_Hook_Reaction_Store_Options $reaction_store */
		$reaction_store = $this->factory->wordpoints->hook_reaction_store->create_and_get(
			array( 'class' => 'WordPoints_Hook_Reaction_Store_Options' )
		);

		$reactions = $this->factory->wordpoints->hook_reaction->create_many( 3 );

		$this->assertSame( 1, $reactions[0]->get_id() );
		$this->assertSame( 2, $reactions[1]->get_id() );
		$this->assertSame( 3, $reactions[2]->get_id() );

		$slug        = $reaction_store->get_slug();
		$option_name = "wordpoints_hook_reaction_last_id-{$slug}-standard";

		// When the index max is equal to the next ID as calculated from the option.
		$reaction_store->update_option( $option_name, 2 );

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$this->assertSame( 4, $reaction->get_id() );

		// When the index max is greater than the next ID as calculated from option.
		$reaction_store->update_option( $option_name, 1 );

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$this->assertSame( 5, $reaction->get_id() );
	}

	/**
	 * Test that regular options are used (not network-wide).
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_options() {

		$reaction_store = $this->factory->wordpoints->hook_reaction_store->create_and_get(
			array( 'class' => 'WordPoints_Hook_Reaction_Store_Options' )
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$this->assertSame( 1, $reaction->get_id() );

		// Create another site.
		$site_id = $this->factory->blog->create();

		switch_to_blog( $site_id );

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$this->assertSame( 1, $reaction->get_id() );

		$this->assertTrue( $reaction_store->delete_reaction( $reaction->get_id() ) );

		restore_current_blog();

		// The reaction on this site should still exist.
		$this->assertTrue( $reaction_store->reaction_exists( 1 ) );
	}
}

// EOF
