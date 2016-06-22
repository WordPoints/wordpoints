<?php

/**
 * Test case for WordPoints_Hook_Event.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Event.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Event
 */
class WordPoints_Hook_Event_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test that it provides the expected sub-apps.
	 *
	 * @since 2.1.0
	 */
	public function test_get_slug() {

		$event = new WordPoints_PHPUnit_Mock_Hook_Event( 'test' );

		$this->assertEquals( 'test', $event->get_slug() );
	}
}

// EOF
