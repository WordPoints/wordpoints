<?php

/**
 * Test case for the User Visit hook event.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests the User Visit hook event.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Event_User_Visit
 */
class WordPoints_Hook_Event_User_Visit_Test extends WordPoints_PHPUnit_TestCase_Hook_Event {

	/**
	 * @since 2.1.0
	 */
	protected $event_class = 'WordPoints_Hook_Event_User_Visit';

	/**
	 * @since 2.1.0
	 */
	protected $event_slug = 'user_visit';

	/**
	 * @since 2.1.0
	 */
	protected $expected_targets = array(
		array( 'current:user' ),
	);

	/**
	 * @since 2.1.0
	 */
	protected $is_reversible = false;

	/**
	 * @since 2.1.0
	 */
	protected function fire_event( $arg, $reactor_slug ) {

		$user_id = $this->factory->user->create();

		wp_set_current_user( $user_id );

		do_action_ref_array( 'wp', array( &$GLOBALS['wp'] ) );

		return $user_id;
	}
}

// EOF
