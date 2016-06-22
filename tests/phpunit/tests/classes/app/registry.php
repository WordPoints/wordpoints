<?php

/**
 * Test case for WordPoints_App_Registry.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_App_Registry.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_App_Registry
 */
class WordPoints_App_Registry_Test extends WordPoints_PHPUnit_TestCase_Class_Registry {

	/**
	 * @since 2.1.0
	 */
	protected function create_registry() {

		return new WordPoints_App_Registry( 'app' );
	}

	/**
	 * Test that it calls an action when it is constructed.
	 *
	 * @since 2.1.0
	 */
	public function test_does_action_on_construct() {

		$mock = new WordPoints_Mock_Filter;

		add_action( 'wordpoints_init_app-test', array( $mock, 'action' ) );

		$hooks = new WordPoints_App_Registry( 'test' );

		$this->assertEquals( 1, $mock->call_count );

		$this->assertTrue( $hooks === $mock->calls[0][0] );
	}
}

// EOF
