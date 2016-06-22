<?php

/**
 * Test case for WordPoints_Entity_Array.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Entity_Array.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Entity_Array
 */
class WordPoints_Entity_Array_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test getting the slug.
	 *
	 * @since 2.1.0
	 */
	public function test_get_slug() {

		$array = new WordPoints_Entity_Array( 'test_entity' );

		$this->assertEquals( 'test_entity{}', $array->get_slug() );
	}

	/**
	 * Test getting the entity slug.
	 *
	 * @since 2.1.0
	 */
	public function test_get_entity_slug() {

		$array = new WordPoints_Entity_Array( 'test_entity' );

		$this->assertEquals( 'test_entity', $array->get_entity_slug() );
	}

	/**
	 * Test setting the value.
	 *
	 * @since 2.1.0
	 */
	public function test_set_the_value() {

		$this->factory->wordpoints->entity->create();

		$array = new WordPoints_Entity_Array( 'test_entity' );

		$this->assertTrue( $array->set_the_value( array( 1, 2 ) ) );

		$entities = $array->get_the_entities();

		$this->assertInternalType( 'array', $entities );
		$this->assertCount( 2, $entities );

		$this->assertEquals( 1, $entities[0]->get_the_id() );
		$this->assertEquals( 2, $entities[1]->get_the_id() );

		$this->assertEquals( array( 1, 2 ), $array->get_the_value() );
	}

	/**
	 * Test setting the value when the entity type isn't registered.
	 *
	 * @since 2.1.0
	 */
	public function test_set_the_value_not_registered() {

		$array = new WordPoints_Entity_Array( 'test_entity' );

		$this->assertFalse( $array->set_the_value( array( 1, 2 ) ) );

		$entities = $array->get_the_entities();

		$this->assertInternalType( 'array', $entities );
		$this->assertCount( 0, $entities );
	}

	/**
	 * Test setting the value a second time replaces the former value.
	 *
	 * @since 2.1.0
	 */
	public function test_set_the_value_twice() {

		$this->factory->wordpoints->entity->create();

		$array = new WordPoints_Entity_Array( 'test_entity' );

		$this->assertTrue( $array->set_the_value( array( 1, 2 ) ) );

		$entities = $array->get_the_entities();

		$this->assertInternalType( 'array', $entities );
		$this->assertCount( 2, $entities );

		$this->assertEquals( 1, $entities[0]->get_the_id() );
		$this->assertEquals( 2, $entities[1]->get_the_id() );

		// The value should be updated, not appended.
		$this->assertTrue( $array->set_the_value( array( 3, 4 ) ) );

		$entities = $array->get_the_entities();

		$this->assertInternalType( 'array', $entities );
		$this->assertCount( 2, $entities );

		$this->assertEquals( 3, $entities[0]->get_the_id() );
		$this->assertEquals( 4, $entities[1]->get_the_id() );

		$this->assertTrue( $array->set_the_value( array() ) );

		$entities = $array->get_the_entities();

		$this->assertInternalType( 'array', $entities );
		$this->assertCount( 0, $entities );
	}

	/**
	 * Test setting the value with invalid values.
	 *
	 * @since 2.1.0
	 */
	public function test_set_the_value_not_valid() {

		$slug = $this->factory->wordpoints->entity->create(
			array( 'class' => 'WordPoints_PHPUnit_Mock_Entity_Unsettable' )
		);

		$array = new WordPoints_Entity_Array( $slug );

		$this->assertTrue( $array->set_the_value( array( 1, 2 ) ) );

		$entities = $array->get_the_entities();

		$this->assertInternalType( 'array', $entities );
		$this->assertCount( 0, $entities );
	}

	/**
	 * Test getting the title.
	 *
	 * @since 2.1.0
	 */
	public function test_get_title() {

		$entity = $this->factory->wordpoints->entity->create_and_get();

		$array = new WordPoints_Entity_Array( $entity->get_slug() );

		$this->assertStringMatchesFormat(
			'%S' . $entity->get_title() . '%S'
			, $array->get_title()
		);
	}

	/**
	 * Test getting the title when the entity isn't valid.
	 *
	 * @since 2.1.0
	 */
	public function test_get_title_unknown_entity() {

		$array = new WordPoints_Entity_Array( 'test_entity' );

		$this->assertNotEmpty( $array->get_title() );
	}
}

// EOF
