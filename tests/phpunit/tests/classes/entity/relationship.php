<?php

/**
 * Test case for WordPoints_Entity_Relationship.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Entity_Relationship.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Entity_Relationship
 */
class WordPoints_Entity_Relationship_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test getting the related entity IDs.
	 *
	 * @since 2.1.0
	 */
	public function test_get_related_entity_ids() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set_the_value( array( 'id' => 1, 'related' => 'a' ) );

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_ids_field', 'related' );

		$args = array( $entity );

		$this->assertSame(
			'a'
			, $relationship->call( 'get_related_entity_ids', $args )
		);
	}

	/**
	 * Test getting the primary entity slug.
	 *
	 * @since 2.1.0
	 */
	public function test_get_primary_entity_slug() {

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'primary_entity_slug', 'post' );

		$this->assertSame( 'post', $relationship->get_primary_entity_slug() );
	}

	/**
	 * Test getting the related entity slug.
	 *
	 * @since 2.1.0
	 */
	public function test_get_related_entity_slug() {

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_entity_slug', 'post' );

		$this->assertSame( 'post', $relationship->get_related_entity_slug() );
	}

	/**
	 * Test setting the value from an entity.
	 *
	 * @since 2.1.0
	 */
	public function test_set_the_value_from_entity() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set_the_value( array( 'id' => 1, 'test_attr' => 'a' ) );

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_ids_field', 'test_attr' );
		$relationship->set( 'related_entity_slug', 'user' );

		$this->assertTrue( $relationship->set_the_value_from_entity( $entity ) );

		$this->assertSame( 'a', $relationship->get_the_value() );
	}

	/**
	 * Test setting the value from an entity whose value isn't set.
	 *
	 * @since 2.1.0
	 */
	public function test_set_the_value_from_entity_not_set() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_ids_field', 'test_attr' );
		$relationship->set( 'related_entity_slug', 'user' );

		$this->assertFalse( $relationship->set_the_value_from_entity( $entity ) );

		$this->assertNull( $relationship->get_the_value() );
	}

	/**
	 * Test setting the value from an entity that doesn't have related values.
	 *
	 * @since 2.1.0
	 */
	public function test_set_the_value_from_entity_invalid() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set_the_value( array( 'id' => 1 ) );

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_ids_field', 'test_attr' );
		$relationship->set( 'related_entity_slug', 'user' );

		$this->assertFalse( $relationship->set_the_value_from_entity( $entity ) );

		$this->assertNull( $relationship->get_the_value() );
	}

	/**
	 * Test setting the value from an entity twice.
	 *
	 * @since 2.1.0
	 */
	public function test_set_the_value_from_entity_twice() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set_the_value( array( 'id' => 1, 'test_attr' => 'a' ) );

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_ids_field', 'test_attr' );
		$relationship->set( 'related_entity_slug', 'user' );

		$this->assertTrue( $relationship->set_the_value_from_entity( $entity ) );

		$this->assertSame( 'a', $relationship->get_the_value() );

		$entity->set_the_value( array( 'id' => 1 ) );

		$this->assertFalse( $relationship->set_the_value_from_entity( $entity ) );

		$this->assertNull( $relationship->get_the_value() );
	}

	/**
	 * Test setting the value from a different context.
	 *
	 * @since 2.4.0
	 */
	public function test_set_the_value_from_entity_different_context() {

		$entity_slug = $this->factory->wordpoints->entity->create();

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

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_ids_field', 'test_attr' );
		$relationship->set( 'related_entity_slug', $entity_slug );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertTrue( $relationship->set_the_value_from_entity( $entity ) );
		$this->assertSame( 2, $relationship->get_the_value() );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test setting the value from the same context.
	 *
	 * @since 2.4.0
	 */
	public function test_set_the_value_from_entity_same_context() {

		$entity_slug = $this->factory->wordpoints->entity->create();

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

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_ids_field', 'test_attr' );
		$relationship->set( 'related_entity_slug', $entity_slug );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertTrue( $relationship->set_the_value_from_entity( $entity ) );
		$this->assertSame( 1, $relationship->get_the_value() );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test setting the value from an entity with no context set.
	 *
	 * @since 2.4.0
	 */
	public function test_set_the_value_from_entity_context_not_set() {

		$entity_slug = $this->factory->wordpoints->entity->create();

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

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_ids_field', 'test_attr' );
		$relationship->set( 'related_entity_slug', $entity_slug );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertFalse( $relationship->set_the_value_from_entity( $entity ) );
		$this->assertNull( $relationship->get_the_value() );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test setting the value from an entity with a nonexistent context.
	 *
	 * @since 2.4.0
	 */
	public function test_set_the_value_from_entity_context_nonexistent() {

		$entity_slug = $this->factory->wordpoints->entity->create();

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

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_ids_field', 'test_attr' );
		$relationship->set( 'related_entity_slug', $entity_slug );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertFalse( $relationship->set_the_value_from_entity( $entity ) );
		$this->assertNull( $relationship->get_the_value() );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test setting the value from an entity with a unset value.
	 *
	 * @since 2.4.0
	 */
	public function test_set_the_value_from_entity_value_not_set() {

		$entity_slug = $this->factory->wordpoints->entity->create();

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$entity = $this->getPartialMockForAbstractClass(
			'WordPoints_Entity'
			, array( 'get_the_attr_value', 'get_the_context' )
			, array( 'test' )
		);

		$entity->method( 'get_the_context' )
			->willReturn( array( 'test_context' => 2 ) );

		$entity->method( 'get_the_attr_value' )->willReturn( null );

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_ids_field', 'test_attr' );
		$relationship->set( 'related_entity_slug', $entity_slug );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertFalse( $relationship->set_the_value_from_entity( $entity ) );
		$this->assertNull( $relationship->get_the_value() );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test setting the value from a different context.
	 *
	 * @since 2.4.0
	 */
	public function test_set_the_value_from_entity_related_entity_slug_invalid() {

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

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_ids_field', 'test_attr' );
		$relationship->set( 'related_entity_slug', 'invalid' );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertFalse( $relationship->set_the_value_from_entity( $entity ) );
		$this->assertNull( $relationship->get_the_value() );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test setting the value from an entity from the global context.
	 *
	 * @since 2.4.0
	 */
	public function test_set_the_value_from_entity_global_context() {

		$entity_slug = $this->factory->wordpoints->entity->create();

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

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_ids_field', 'test_attr' );
		$relationship->set( 'related_entity_slug', $entity_slug );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertTrue( $relationship->set_the_value_from_entity( $entity ) );
		$this->assertSame( 1, $relationship->get_the_value() );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test setting the value for a related entity from the global context.
	 *
	 * @since 2.4.0
	 */
	public function test_set_the_value_from_entity_related_global_context() {

		$entity_slug = $this->factory->wordpoints->entity->create(
			array( 'class' => 'WordPoints_PHPUnit_Mock_Entity_Contexted_Global' )
		);

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

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_ids_field', 'test_attr' );
		$relationship->set( 'related_entity_slug', $entity_slug );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertTrue( $relationship->set_the_value_from_entity( $entity ) );
		$this->assertSame( 1, $relationship->get_the_value() );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test get_child().
	 *
	 * @since 2.1.0
	 */
	public function test_get_child() {

		$this->factory->wordpoints->entity->create();

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_entity_slug', 'test_entity' );

		$child = $relationship->get_child( 'test_entity' );

		$this->assertInstanceOf( 'WordPoints_PHPUnit_Mock_Entity', $child );
		$this->assertSame( 'test_entity', $child->get_slug() );
	}

	/**
	 * Test get_child() with a slug not related.
	 *
	 * @since 2.1.0
	 */
	public function test_get_child_not_related() {

		$this->factory->wordpoints->entity->create();

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_entity_slug', 'test_entity' );

		$this->assertFalse( $relationship->get_child( 'other_entity' ) );
	}

	/**
	 * Test get_child() when the child is an array.
	 *
	 * @since 2.1.0
	 */
	public function test_get_child_array() {

		$this->factory->wordpoints->entity->create();

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_entity_slug', 'test_entity{}' );

		$child = $relationship->get_child( 'test_entity{}' );

		$this->assertInstanceOf( 'WordPoints_Entity_Array', $child );
		$this->assertSame( 'test_entity', $child->get_entity_slug() );
	}

	/**
	 * Test get_child() sets the child value when the value is set.
	 *
	 * @since 2.1.0
	 */
	public function test_get_child_value_set() {

		$this->factory->wordpoints->entity->create();

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'primary_entity_slug', 'test_entity' );
		$relationship->set( 'related_entity_slug', 'test_entity' );
		$relationship->set_the_value( 1 );

		$child = $relationship->get_child( 'test_entity' );

		$this->assertSame( 1, $child->get_the_value() );
	}

	/**
	 * Test get_child() when the child is an array and the value is set.
	 *
	 * @since 2.1.0
	 */
	public function test_get_child_array_value_set() {

		$this->factory->wordpoints->entity->create();

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'primary_entity_slug', 'test_entity' );
		$relationship->set( 'related_entity_slug', 'test_entity{}' );
		$relationship->set_the_value( array( 1, 2 ) );

		$child = $relationship->get_child( 'test_entity{}' );

		$entities = $child->get_the_entities();

		$this->assertInternalType( 'array', $entities );
		$this->assertCount( 2, $entities );

		$this->assertSame( 1, $entities[0]->get_the_id() );
		$this->assertSame( 2, $entities[1]->get_the_id() );
	}

	/**
	 * Test getting the child from a different context.
	 *
	 * @since 2.4.0
	 */
	public function test_get_child_value_set_different_context() {

		$entity_slug = $this->factory->wordpoints->entity->create();

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

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_ids_field', 'test_attr' );
		$relationship->set( 'related_entity_slug', $entity_slug );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertTrue( $relationship->set_the_value_from_entity( $entity ) );

		$child = $relationship->get_child( 'test_entity' );

		$this->assertSame( 2, $child->get_the_value() );
		$this->assertSame(
			array( $context->get_slug() => 2 )
			, $child->get_the_context()
		);

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test getting the child from a different context and with the context switched.
	 *
	 * @since 2.4.0
	 */
	public function test_get_child_value_set_switched_context() {

		$entity_slug = $this->factory->wordpoints->entity->create();

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

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'related_ids_field', 'test_attr' );
		$relationship->set( 'related_entity_slug', $entity_slug );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertTrue( $relationship->set_the_value_from_entity( $entity ) );

		$context->switch_to( 3 );

		$child = $relationship->get_child( 'test_entity' );

		$context->switch_back();

		$this->assertSame( 2, $child->get_the_value() );
		$this->assertSame(
			array( $context->get_slug() => 2 )
			, $child->get_the_context()
		);

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Tests getting the child when the entities come from the global context.
	 *
	 * @since 2.4.0
	 */
	public function test_get_child_value_set_global_context() {

		$this->factory->wordpoints->entity->create(
			array( 'class' => 'WordPoints_PHPUnit_Mock_Entity_Contexted_Global' )
		);

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship( 'test' );
		$relationship->set( 'primary_entity_slug', 'test_entity' );
		$relationship->set( 'related_entity_slug', 'test_entity' );
		$relationship->set_the_value( 1 );

		$child = $relationship->get_child( 'test_entity' );

		$this->assertSame( 1, $child->get_the_value() );
		$this->assertSame( array( 'test_entity' => 1 ), $child->get_the_guid() );
	}
}

// EOF
