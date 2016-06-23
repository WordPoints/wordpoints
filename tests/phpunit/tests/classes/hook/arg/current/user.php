<?php

/**
 * Test case for WordPoints_Hook_Arg_Current_User.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Arg_Current_User.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Arg_Current_User
 */
class WordPoints_Hook_Arg_Current_User_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test getting the arg value.
	 *
	 * @since 2.1.0
	 */
	public function test_get_value() {

		$this->mock_apps();

		wordpoints_entities()->register(
			'test_entity'
			, 'WordPoints_Entity_User'
		);

		$user_id = $this->factory->user->create();

		wp_set_current_user( $user_id );

		$action = new WordPoints_PHPUnit_Mock_Hook_Action( 'test_action', array() );
		$arg = new WordPoints_Hook_Arg_Current_User( 'test_entity', $action );

		$this->assertEquals( $user_id, $arg->get_value()->ID );

		$entity = $arg->get_entity();

		$this->assertInstanceOf( 'WordPoints_Entity_User', $entity );

		$this->assertEquals( $user_id, $entity->get_the_id() );
		$this->assertNotEmpty( $arg->get_title() );
	}

	/**
	 * Test checking if the arg is stateful.
	 *
	 * @since 2.1.0
	 */
	public function test_is_stateful() {

		$arg = new WordPoints_Hook_Arg_Current_User( 'test_entity' );

		$this->assertTrue( $arg->is_stateful() );
	}
}

// EOF
