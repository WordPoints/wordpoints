<?php

/**
 * Test case for WordPoints_Hook_Reaction_Store_Options_Network.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Reaction_Store_Options_Network.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Reaction_Store_Options_Network
 */
class WordPoints_Hook_Reaction_Store_Options_Network_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test that network options are used.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_network_options() {

		$reaction_store = $this->factory->wordpoints->hook_reaction_store->create_and_get(
			array( 'class' => 'WordPoints_Hook_Reaction_Store_Options_Network' )
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$this->assertEquals( 1, $reaction->get_id() );

		// Create another site.
		$site_id = $this->factory->blog->create();

		switch_to_blog( $site_id );

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$this->assertEquals( 2, $reaction->get_id() );

		$this->assertTrue( $reaction_store->delete_reaction( $reaction->get_id() ) );

		restore_current_blog();

		// The reaction on this site should still exist.
		$this->assertTrue( $reaction_store->reaction_exists( 1 ) );
	}
}

// EOF
