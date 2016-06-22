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

		$this->assertEquals( 'int', $attr->get_data_type() );
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

		$this->assertEquals( 'a', $attr->get_the_value() );
	}

	/**
	 * Test setting the value from an entity whose value isn't set.
	 *
	 * @since 2.1.0
	 */
	public function test_set_the_value_from_entity_not_set() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$attr = new WordPoints_PHPUnit_Mock_Entity_Attr( 'test' );
		$this->assertTrue( $attr->set_the_value_from_entity( $entity ) );

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

		$this->assertEquals( 'a', $attr->get_the_value() );

		$this->assertTrue(
			$attr->set_the_value_from_entity(
				new WordPoints_PHPUnit_Mock_Entity( 'test' )
			)
		);

		$this->assertNull( $attr->get_the_value() );
	}
}

// EOF
