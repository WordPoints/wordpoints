<?php

/**
 * Test case for WordPoints_Hook_Action.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Action.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Action
 */
class WordPoints_Hook_Action_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test getting the action slug.
	 *
	 * @since 2.1.0
	 */
	public function test_get_slug() {

		$action = new WordPoints_Hook_Action( 'test', array( 5 ) );

		$this->assertSame( 'test', $action->get_slug() );
	}

	/**
	 * Test checking if an action should fire.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_requirements_met() {

		$action = new WordPoints_Hook_Action(
			'test'
			, array( 5, 'a' )
			, array( 'requirements' => array( 1 => 'a' ) )
		);

		$this->assertTrue( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the requirements aren't met.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_requirements_not_met() {

		$action = new WordPoints_Hook_Action(
			'test'
			, array( 5, 'a' )
			, array( 'requirements' => array( 1 => 'b' ) )
		);

		$this->assertFalse( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the requirements aren't met.
	 *
	 * @since 2.1.4
	 */
	public function test_should_fire_requirements_not_met_not_set() {

		$action = new WordPoints_Hook_Action(
			'test'
			, array( 5 )
			, array( 'requirements' => array( 1 => 'a' ) )
		);

		$this->assertFalse( $action->should_fire() );
	}

	/**
	 * Test that actions should fire by default.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_true_by_default() {

		$action = new WordPoints_Hook_Action( 'test', array( 5, 'a' ) );

		$this->assertTrue( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when there are multiple requirements,
	 * and some aren't met.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_requirements_not_met_multiple() {

		$action = new WordPoints_Hook_Action(
			'test'
			, array( 5, 'a', true )
			, array( 'requirements' => array( 1 => 'a', 2 => false ) )
		);

		$this->assertFalse( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire.
	 *
	 * @since 2.1.4
	 */
	public function test_should_fire_requirements_met_comparator() {

		$action = new WordPoints_Hook_Action(
			'test'
			, array( 5, 'a' )
			, array(
				'requirements' => array(
					1 => array( 'comparator' => '=', 'value' => 'a' ),
				),
			)
		);

		$this->assertTrue( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the requirements aren't met.
	 *
	 * @since 2.1.4
	 */
	public function test_should_fire_requirements_not_met_comparator() {

		$action = new WordPoints_Hook_Action(
			'test'
			, array( 5, 'a' )
			, array(
				'requirements' => array(
					1 => array( 'comparator' => '=', 'value' => 'b' ),
				),
			)
		);

		$this->assertFalse( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the requirements aren't met.
	 *
	 * @since 2.1.4
	 */
	public function test_should_fire_requirements_not_met_comparator_value() {

		$action = new WordPoints_Hook_Action(
			'test'
			, array( 5, array( 'comparator' => '=', 'value' => 'b' ) )
			, array(
				'requirements' => array(
					1 => array( 'comparator' => '=', 'value' => 'b' ),
				),
			)
		);

		$this->assertFalse( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the requirements aren't met.
	 *
	 * @since 2.1.4
	 */
	public function test_should_fire_requirements_not_met_comparator_missing_value() {

		$action = new WordPoints_Hook_Action(
			'test'
			, array( 5, array( 'comparator' => '=' ) )
			, array(
				'requirements' => array(
					1 => array( 'comparator' => '=' ),
				),
			)
		);

		$this->assertTrue( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the requirements aren't met.
	 *
	 * @since 2.1.4
	 */
	public function test_should_fire_requirements_not_met_comparator_missing() {

		$action = new WordPoints_Hook_Action(
			'test'
			, array( 5, array( 'value' => 'b' ) )
			, array(
				'requirements' => array(
					1 => array( 'value' => 'b' ),
				),
			)
		);

		$this->assertTrue( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire.
	 *
	 * @since 2.1.4
	 */
	public function test_should_fire_requirements_met_not_comparator() {

		$action = new WordPoints_Hook_Action(
			'test'
			, array( 5, 'a' )
			, array(
				'requirements' => array(
					1 => array( 'comparator' => '!=', 'value' => 'b' ),
				),
			)
		);

		$this->assertTrue( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the requirements aren't met.
	 *
	 * @since 2.1.4
	 */
	public function test_should_fire_requirements_not_met_not_comparator() {

		$action = new WordPoints_Hook_Action(
			'test'
			, array( 5, 'a' )
			, array(
				'requirements' => array(
					1 => array( 'comparator' => '!=', 'value' => 'a' ),
				),
			)
		);

		$this->assertFalse( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the requirements aren't met.
	 *
	 * @since 2.1.4
	 */
	public function test_should_fire_requirements_met_not_comparator_not_set() {

		$action = new WordPoints_Hook_Action(
			'test'
			, array( 5 )
			, array(
				'requirements' => array(
					1 => array( 'comparator' => '!=', 'value' => 'a' ),
				),
			)
		);

		$this->assertTrue( $action->should_fire() );
	}

	/**
	 * Test getting an arg value.
	 *
	 * @since 2.1.0
	 */
	public function test_get_arg_value() {

		$this->factory->wordpoints->entity->create();

		$action = new WordPoints_Hook_Action(
			'test'
			, array( 5 )
			, array( 'arg_index' => array( 'test_entity' => 0 ) )
		);

		$this->assertSame( 5, $action->get_arg_value( 'test_entity' ) );
	}

	/**
	 * Test getting the entity ID when it is passed as an arg other than the first.
	 *
	 * @since 2.1.0
	 */
	public function test_get_entity_id_different_index() {

		$this->factory->wordpoints->entity->create();

		$action = new WordPoints_PHPUnit_Mock_Hook_Action(
			'test'
			, array( 'test', 5 )
			, array( 'arg_index' => array( 'test_entity' => 1 ) )
		);

		$this->assertSame( 5, $action->get_arg_value( 'test_entity' ) );
	}

	/**
	 * Test getting the value of an arg that doesn't exist.
	 *
	 * @since 2.1.0
	 */
	public function test_get_arg_value_not_found() {

		$this->factory->wordpoints->entity->create();

		$action = new WordPoints_Hook_Action(
			'test'
			, array( 5 )
		);

		$this->assertSame( null, $action->get_arg_value( 'test_entity' ) );
	}

	/**
	 * Test getting the value of an arg that exists but wasn't passed.
	 *
	 * @since 2.1.0
	 */
	public function test_get_arg_value_not_there() {

		$this->factory->wordpoints->entity->create();

		$action = new WordPoints_Hook_Action(
			'test'
			, array( 5 )
			, array( 'arg_index' => array( 'test_entity' => 1 ) )
		);

		$this->assertSame( null, $action->get_arg_value( 'test_entity' ) );
	}
}

// EOF
