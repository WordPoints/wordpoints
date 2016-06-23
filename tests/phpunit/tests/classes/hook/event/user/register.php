<?php

/**
 * Test case for the User Register hook event.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests the User Register hook event.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Event_User_Register
 */
class WordPoints_Hook_Event_User_Register_Test extends WordPoints_PHPUnit_TestCase_Hook_Event {

	/**
	 * @since 2.1.0
	 */
	protected $event_class = 'WordPoints_Hook_Event_User_Register';

	/**
	 * @since 2.1.0
	 */
	protected $event_slug = 'user_register';

	/**
	 * @since 2.1.0
	 */
	protected $expected_targets = array(
		array( 'user' ),
	);

	/**
	 * @since 2.1.0
	 */
	protected function fire_event( $arg, $reactor_slug ) {
		return $this->factory->user->create();
	}

	/**
	 * @since 2.1.0
	 */
	protected function reverse_event( $arg_id, $index ) {
		$this->delete_user( $arg_id );
	}
}

// EOF
