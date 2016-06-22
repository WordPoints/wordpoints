<?php

/**
 * Test case for WordPoints_Hook_Hit_Logger.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Hit_Logger.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Hit_Logger
 */
class WordPoints_Hook_Hit_Logger_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test logging a hit.
	 *
	 * @since 2.1.0
	 */
	public function test_log_hit() {

		$reaction = $this->factory->wordpoints->hook_reaction->create();
		$event_args = new WordPoints_Hook_Event_Args( array() );
		$action_type = 'test_fire';

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, $action_type );

		$hit_id = $fire->hit_logger->log_hit();

		$this->assertInternalType( 'integer', $hit_id );

		$this->assertHitsLogged( array( 'reaction_id' => $reaction->get_id() ) );
	}
}

// EOF
