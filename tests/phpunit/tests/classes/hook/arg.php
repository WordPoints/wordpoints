<?php

/**
 * Test case for WordPoints_Hook_Arg.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Arg.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Arg
 */
class WordPoints_Hook_Arg_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test getting the arg slug.
	 *
	 * @since 2.1.0
	 */
	public function test_get_slug() {

		$arg = new WordPoints_Hook_Arg( 'test' );

		$this->assertSame( 'test', $arg->get_slug() );
		$this->assertSame( 'test', $arg->get_entity_slug() );
	}

	/**
	 * Test getting the arg slug when the slug is an alias.
	 *
	 * @since 2.1.0
	 */
	public function test_get_slug_alias() {

		$arg = new WordPoints_Hook_Arg( 'alias:test' );

		$this->assertSame( 'alias:test', $arg->get_slug() );
		$this->assertSame( 'test', $arg->get_entity_slug() );
	}

	/**
	 * Test getting the arg value.
	 *
	 * @since 2.1.0
	 */
	public function test_get_value() {

		$this->factory->wordpoints->entity->create();

		$entity_id = 13;

		$action = new WordPoints_PHPUnit_Mock_Hook_Action(
			'test_action'
			, array( $entity_id )
		);

		$arg = new WordPoints_Hook_Arg( 'test_entity', 'test_action', $action );

		$this->assertSame( $entity_id, $arg->get_value() );

		$entity = $arg->get_entity();

		$this->assertInstanceOf( 'WordPoints_PHPUnit_Mock_Entity', $entity );

		$this->assertSame( $entity_id, $entity->get_the_id() );
		$this->assertSame( $entity->get_title(), $arg->get_title() );
	}

	/**
	 * Test getting the arg value when the slug is an alias.
	 *
	 * @since 2.1.0
	 */
	public function test_get_value_alias() {

		$this->factory->wordpoints->entity->create();

		$entity_id = 13;

		$action = new WordPoints_PHPUnit_Mock_Hook_Action(
			'test_action'
			, array( $entity_id )
			, array( 'arg_index' => array( 'current:test_entity' => 0 ) )
		);

		$arg = new WordPoints_Hook_Arg( 'current:test_entity', 'test_action', $action );
		$this->assertSame( $entity_id, $arg->get_value() );

		$entity = $arg->get_entity();

		$this->assertInstanceOf( 'WordPoints_PHPUnit_Mock_Entity', $entity );

		$this->assertSame( $entity_id, $entity->get_the_id() );
		$this->assertSame( $entity->get_title(), $arg->get_title() );
	}

	/**
	 * Test getting the arg value when the entity doesn't exist.
	 *
	 * @since 2.1.0
	 */
	public function test_get_value_invalid_entity() {

		$entity_id = 13;

		$action = new WordPoints_PHPUnit_Mock_Hook_Action(
			'test_action'
			, array( $entity_id )
		);

		$arg = new WordPoints_Hook_Arg( 'test_entity', 'test_action', $action );
		$this->assertSame( $entity_id, $arg->get_value() );

		$this->assertFalse( $arg->get_entity() );
		$this->assertSame( $arg->get_slug(), $arg->get_title() );
	}

	/**
	 * Test getting the arg value when no action is passed.
	 *
	 * @since 2.1.0
	 */
	public function test_get_value_no_action() {

		$this->factory->wordpoints->entity->create();

		$arg = new WordPoints_Hook_Arg( 'test_entity' );

		$this->assertNull( $arg->get_value() );

		$entity = $arg->get_entity();

		$this->assertInstanceOf( 'WordPoints_PHPUnit_Mock_Entity', $entity );

		$this->assertNull( $entity->get_the_id() );
		$this->assertSame( $entity->get_title(), $arg->get_title() );
	}

	/**
	 * Test checking if an arg is stateful.
	 *
	 * @since 2.1.0
	 */
	public function test_is_stateful() {

		$arg = new WordPoints_PHPUnit_Mock_Hook_Arg( 'test_entity' );

		$this->assertFalse( $arg->is_stateful() );

		$arg->is_stateful = true;

		$this->assertTrue( $arg->is_stateful );
	}
}

// EOF
