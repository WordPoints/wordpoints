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

		$this->assertTrue( $relationship->set_the_value_from_entity( $entity ) );

		$this->assertSame( 'a', $relationship->get_the_value() );

		$entity->set_the_value( array( 'id' => 1 ) );

		$this->assertFalse( $relationship->set_the_value_from_entity( $entity ) );

		$this->assertNull( $relationship->get_the_value() );
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
		$relationship->set( 'related_entity_slug', 'test_entity{}' );
		$relationship->set_the_value( array( 1, 2 ) );

		$child = $relationship->get_child( 'test_entity{}' );

		$entities = $child->get_the_entities();

		$this->assertInternalType( 'array', $entities );
		$this->assertCount( 2, $entities );

		$this->assertSame( 1, $entities[0]->get_the_id() );
		$this->assertSame( 2, $entities[1]->get_the_id() );
	}
}

// EOF
