<?php

/**
 * Test case for WordPoints_Entity_Attr.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Entity_Attr.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Entity_Attr
 */
class WordPoints_Entity_Attr_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test getting the data type.
	 *
	 * @since 2.1.0
	 */
	public function test_get_data_type() {

		$attr = new WordPoints_PHPUnit_Mock_Entity_Attr( 'test' );
		$attr->set( 'data_type', 'int' );

		$this->assertSame( 'int', $attr->get_data_type() );
	}

	/**
	 * Test setting the value from an entity.
	 *
	 * @since 2.1.0
	 */
	public function test_set_the_value_from_entity() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set_the_value( array( 'id' => 1, 'test_attr' => 'a' ) );

		$attr = new WordPoints_PHPUnit_Mock_Entity_Attr( 'test' );
		$this->assertTrue( $attr->set_the_value_from_entity( $entity ) );

		$this->assertSame( 'a', $attr->get_the_value() );
	}

	/**
	 * Test setting the value from an entity whose value isn't set.
	 *
	 * @since 2.1.0
	 */
	public function test_set_the_value_from_entity_not_set() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$attr = new WordPoints_PHPUnit_Mock_Entity_Attr( 'test' );
		$this->assertFalse( $attr->set_the_value_from_entity( $entity ) );

		$this->assertNull( $attr->get_the_value() );
	}

	/**
	 * Test setting the value from an entity which doesn't have this attr.
	 *
	 * @since 2.1.0
	 */
	public function test_set_the_value_from_entity_attr_not_set() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set_the_value( array( 'id' => 1 ) );

		$attr = new WordPoints_PHPUnit_Mock_Entity_Attr( 'test' );
		$this->assertTrue( $attr->set_the_value_from_entity( $entity ) );

		$this->assertNull( $attr->get_the_value() );
	}

	/**
	 * Test setting the value from an entity twice.
	 *
	 * @since 2.1.0
	 */
	public function test_set_the_value_from_entity_twice() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set_the_value( array( 'id' => 1, 'test_attr' => 'a' ) );

		$attr = new WordPoints_PHPUnit_Mock_Entity_Attr( 'test' );
		$attr->set_the_value_from_entity( $entity );

		$this->assertSame( 'a', $attr->get_the_value() );

		$this->assertFalse(
			$attr->set_the_value_from_entity(
				new WordPoints_PHPUnit_Mock_Entity( 'test' )
			)
		);

		$this->assertNull( $attr->get_the_value() );
	}

	/**
	 * Test setting the value from a different context.
	 *
	 * @since 2.4.0
	 */
	public function test_set_the_value_from_entity_different_context() {

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$entity = $this->getPartialMockForAbstractClass(
			'WordPoints_Entity'
			, array( 'get_the_attr_value', 'get_the_context' )
			, array( 'test' )
		);

		$entity->method( 'get_the_context' )
			->willReturn( array( $context->get_slug() => 2 ) );

		$entity->method( 'get_the_attr_value' )
			->willReturnCallback( array( $context, 'get_current_id' ) );

		$attr = new WordPoints_PHPUnit_Mock_Entity_Attr( 'test' );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertTrue( $attr->set_the_value_from_entity( $entity ) );
		$this->assertSame( 2, $attr->get_the_value() );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test setting the value from the same context.
	 *
	 * @since 2.4.0
	 */
	public function test_set_the_value_from_entity_same_context() {

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$entity = $this->getPartialMockForAbstractClass(
			'WordPoints_Entity'
			, array( 'get_the_attr_value', 'get_the_context' )
			, array( 'test' )
		);

		$entity->method( 'get_the_context' )
			->willReturn( array( $context->get_slug() => 1 ) );

		$entity->method( 'get_the_attr_value' )
			->willReturnCallback( array( $context, 'get_current_id' ) );

		$attr = new WordPoints_PHPUnit_Mock_Entity_Attr( 'test' );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertTrue( $attr->set_the_value_from_entity( $entity ) );
		$this->assertSame( 1, $attr->get_the_value() );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test setting the value from an entity with no context set.
	 *
	 * @since 2.4.0
	 */
	public function test_set_the_value_from_entity_context_not_set() {

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$entity = $this->getPartialMockForAbstractClass(
			'WordPoints_Entity'
			, array( 'get_the_attr_value' )
			, array( 'test' )
		);

		$entity->method( 'get_the_attr_value' )
			->willReturnCallback( array( $context, 'get_current_id' ) );

		$attr = new WordPoints_PHPUnit_Mock_Entity_Attr( 'test' );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertFalse( $attr->set_the_value_from_entity( $entity ) );
		$this->assertNull( $attr->get_the_value() );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test setting the value from an entity with a nonexistent context.
	 *
	 * @since 2.4.0
	 */
	public function test_set_the_value_from_entity_context_nonexistent() {

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$entity = $this->getPartialMockForAbstractClass(
			'WordPoints_Entity'
			, array( 'get_the_attr_value', 'get_the_context' )
			, array( 'test' )
		);

		$entity->method( 'get_the_context' )
			->willReturn( array( 'nonexistent_context' => 5 ) );

		$entity->method( 'get_the_attr_value' )
			->willReturnCallback( array( $context, 'get_current_id' ) );

		$attr = new WordPoints_PHPUnit_Mock_Entity_Attr( 'test' );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertFalse( $attr->set_the_value_from_entity( $entity ) );
		$this->assertNull( $attr->get_the_value() );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test setting the value from an entity from the global context.
	 *
	 * @since 2.4.0
	 */
	public function test_set_the_value_from_entity_global_context() {

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$entity = $this->getPartialMockForAbstractClass(
			'WordPoints_Entity'
			, array( 'get_the_attr_value', 'get_the_context' )
			, array( 'test' )
		);

		$entity->method( 'get_the_context' )
			->willReturn( array() );

		$entity->method( 'get_the_attr_value' )
			->willReturnCallback( array( $context, 'get_current_id' ) );

		$attr = new WordPoints_PHPUnit_Mock_Entity_Attr( 'test' );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertTrue( $attr->set_the_value_from_entity( $entity ) );
		$this->assertSame( 1, $attr->get_the_value() );

		$this->assertSame( 1, $context->get_current_id() );
	}
}

// EOF
