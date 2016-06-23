<?php

/**
 * Test case for WordPoints_Hook_Fire.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Fire.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Fire
 */
class WordPoints_Hook_Fire_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test constructing the fire.
	 *
	 * @since 2.1.0
	 */
	public function test_construct() {

		$reaction = $this->factory->wordpoints->hook_reaction->create();
		$event_args = new WordPoints_Hook_Event_Args( array() );
		$action_type = 'test_fire';

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, $action_type );

		$this->assertEquals( $action_type, $fire->action_type );
		$this->assertEquals( $event_args, $fire->event_args );
		$this->assertEquals( $reaction, $fire->reaction );

		$this->assertInstanceOf( 'WordPoints_Hook_Hit_Logger', $fire->hit_logger );
	}

	/**
	 * Test marking the fire as a hit.
	 *
	 * @since 2.1.0
	 */
	public function test_hit() {

		$reaction = $this->factory->wordpoints->hook_reaction->create();
		$event_args = new WordPoints_Hook_Event_Args( array() );
		$action_type = 'test_fire';

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, $action_type );

		$hit_id = $fire->hit();

		$this->assertInternalType( 'integer', $hit_id );
		$this->assertEquals( $fire->hit_id, $hit_id );

		$this->assertHitsLogged( array( 'reaction_id' => $reaction->get_id() ) );
	}

	/**
	 * Test getting a matching hit logs query.
	 *
	 * @since 2.1.0
	 */
	public function test_get_matching_hit_logs_query() {

		$action_type = 'test_fire';
		$reaction = $this->factory->wordpoints->hook_reaction->create();
		$event_args = new WordPoints_Hook_Event_Args(
			array( new WordPoints_PHPUnit_Mock_Hook_Arg( 'test_entity' ) )
		);

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, $action_type );

		$query = $fire->get_matching_hits_query();

		$this->assertEquals( $action_type, $query->get_arg( 'action_type' ) );

		$this->assertEquals(
			wordpoints_hooks_get_event_primary_arg_guid_json( $event_args )
			, $query->get_arg( 'primary_arg_guid' )
		);

		$this->assertEquals(
			$reaction->get_event_slug()
			, $query->get_arg( 'event' )
		);

		$this->assertEquals(
			$reaction->get_reactor_slug()
			, $query->get_arg( 'reactor' )
		);

		$this->assertEquals(
			$reaction->get_store_slug()
			, $query->get_arg( 'reaction_store' )
		);

		$this->assertEquals(
			wp_json_encode( $reaction->get_context_id() )
			, $query->get_arg( 'reaction_context_id' )
		);

		$this->assertEquals( $reaction->get_id(), $query->get_arg( 'reaction_id' ) );
	}
}

// EOF
