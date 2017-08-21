<?php

/**
 * Test case for WordPoints_Hook_Actions.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Actions.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Actions
 */
class WordPoints_Hook_Actions_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test registering an action registers it with the router.
	 *
	 * @since 2.1.0
	 */
	public function test_register() {

		$actions = new WordPoints_Hook_Actions();

		$actions->register(
			'test'
			, 'WordPoints_PHPUnit_Mock_Hook_Action'
			, array( 'action' => __METHOD__ )
		);

		$this->assertSame(
			10
			, has_action(
				__METHOD__
				, array( wordpoints_hooks()->get_sub_app( 'router' ), __METHOD__ . ',10' )
			)
		);
	}

	/**
	 * Test deregistering an action deregisters it with the router.
	 *
	 * @since 2.1.0
	 */
	public function test_deregister() {

		$actions = new WordPoints_Hook_Actions();

		$actions->register(
			'test'
			, 'WordPoints_PHPUnit_Mock_Hook_Action'
			, array( 'action' => __METHOD__ )
		);

		$router = wordpoints_hooks()->get_sub_app( 'router' );

		$this->assertSame(
			10
			, has_action( __METHOD__, array( $router, __METHOD__ . ',10' ) )
		);

		$actions->deregister( 'test' );

		$this->assertFalse(
			has_action( __METHOD__, array( $router, __METHOD__ . ',10' ) )
		);
	}

	/**
	 * Test getting an action instantiates with the passed args.
	 *
	 * @since 2.1.0
	 */
	public function test_get() {

		$this->mock_apps();

		$actions = new WordPoints_Hook_Actions();

		$actions->register(
			'test'
			, 'WordPoints_PHPUnit_Mock_Hook_Action'
			, array( 'action' => __METHOD__ )
		);

		$this->factory->wordpoints->entity->create();

		/** @var WordPoints_Hook_ActionI $action */
		$action = $actions->get(
			'test'
			, array( 5 )
			, array( 'arg_index' => array( 'test_entity' => 0 ) )
		);

		$this->assertSame( 5, $action->get_arg_value( 'test_entity' ) );
	}

	/**
	 * Test getting an unregistered action.
	 *
	 * @since 2.1.0
	 */
	public function test_get_unregistered() {

		$actions = new WordPoints_Hook_Actions();

		$action = $actions->get(
			'test'
			, array( 5 )
			, array( 'arg_index' => array( 'test_entity' => 0 ) )
		);

		$this->assertFalse( $action );
	}
}

// EOF
