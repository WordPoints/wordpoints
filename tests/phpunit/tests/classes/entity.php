<?php

/**
 * Test case for WordPoints_Entity.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Entity.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Entity
 */
class WordPoints_Entity_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test getting the entity.
	 *
	 * @since 2.1.0
	 */
	public function test_get_entity() {

		$return_value = array( 'id' => 1 );

		$mock = new WordPoints_PHPUnit_Mock_Filter( $return_value );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'getter', array( $mock, 'filter' ) );

		$args = array( 1 );

		$this->assertSame( $return_value, $entity->call( 'get_entity', $args ) );

		$this->assertSame( $args, $mock->calls[0] );
	}

	/**
	 * Test getting a nonexistent entity.
	 *
	 * @since 2.1.0
	 */
	public function test_get_nonexistent_entity() {

		$return_value = new WP_Error();

		$mock = new WordPoints_PHPUnit_Mock_Filter( $return_value );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'getter', array( $mock, 'filter' ) );

		$args = array( 1 );

		// The return value should be normalized to false.
		$this->assertFalse( $entity->call( 'get_entity', $args ) );

		$this->assertSame( $args, $mock->calls[0] );
	}

	/**
	 * Test is_entity() with an object.
	 *
	 * @since 2.1.0
	 */
	public function test_is_entity_object() {

		$object = (object) array( 'id' => 1 );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertTrue( $entity->call( 'is_entity', array( $object ) ) );
	}

	/**
	 * Test is_entity() with an object that isn't an entity.
	 *
	 * @since 2.1.0
	 */
	public function test_is_entity_object_not() {

		$object = (object) array( 'not' => 1 );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertFalse( $entity->call( 'is_entity', array( $object ) ) );
	}

	/**
	 * Test is_entity() with an array.
	 *
	 * @since 2.1.0
	 */
	public function test_is_entity_array() {

		$array = array( 'id' => 1 );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertTrue( $entity->call( 'is_entity', array( $array ) ) );
	}

	/**
	 * Test is_entity() with an array that isn't an entity.
	 *
	 * @since 2.1.0
	 */
	public function test_is_entity_array_not() {

		$array = array( 'not' => 1 );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertFalse( $entity->call( 'is_entity', array( $array ) ) );
	}

	/**
	 * Test is_entity() with an non-entity value.
	 *
	 * @since 2.1.0
	 */
	public function test_is_entity_not() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertFalse( $entity->call( 'is_entity', array( 'not' ) ) );
	}

	/**
	 * Test getting the entity from a different context.
	 *
	 * @since 2.2.0
	 */
	public function test_get_entity_from_context() {

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'getter', array( $this, 'get_entity_from_context' ) );
		$entity->set( 'context', $context->get_slug() );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertSame(
			array( 'id' => 2, 'context' => 2 )
			, $entity->call(
				'get_entity_from_context'
				, array( 2, array( 'test_context' => 2 ) )
			)
		);

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Get an entity from a mock storage with contexts.
	 *
	 * @since 2.2.0
	 *
	 * @param int $id The entity ID.
	 *
	 * @return array|false The entity or false.
	 */
	public function get_entity_from_context( $id ) {

		$table = array(
			1 => array(
				1 => array( 'id' => 1, 'context' => 1 ),
			),
			2 => array(
				2 => array( 'id' => 2, 'context' => 2 ),
			),
		);

		$context_id = wordpoints_entities()
			->get_sub_app( 'contexts' )
			->get( 'test_context' )
			->get_current_id();

		if ( isset( $table[ $context_id ][ $id ] ) ) {
			return $table[ $context_id ][ $id ];
		} else {
			return false;
		}
	}

	/**
	 * Test getting the entity from a the same context.
	 *
	 * @since 2.2.0
	 */
	public function test_get_entity_from_same_context() {

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'getter', array( $this, 'get_entity_from_context' ) );
		$entity->set( 'context', $context->get_slug() );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertSame(
			array( 'id' => 1, 'context' => 1 )
			, $entity->call(
				'get_entity_from_context'
				, array( 1, array( 'test_context' => 1 ) )
			)
		);

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test getting a nonexistent entity from a different context.
	 *
	 * @since 2.2.0
	 */
	public function test_get_nonexistent_entity_from_context() {

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'getter', array( $this, 'get_entity_from_context' ) );
		$entity->set( 'context', $context->get_slug() );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertFalse(
			$entity->call(
				'get_entity_from_context'
				, array( 3, array( 'test_context' => 2 ) )
			)
		);

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test getting a an entity from a nonexistent context.
	 *
	 * @since 2.2.0
	 */
	public function test_get_entity_from_nonexistent_context() {

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'getter', array( $this, 'get_entity_from_context' ) );
		$entity->set( 'context', $context->get_slug() );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertFalse(
			$entity->call(
				'get_entity_from_context'
				, array( 2, array( 'test_context' => 3 ) )
			)
		);

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test is_guid().
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_guids
	 *
	 * @param mixed $value   A value that may be a GUID.
	 * @param bool  $is_guid Whether the value is a GUID.
	 */
	public function test_is_guid( $value, $is_guid ) {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'context', 'test_context' );

		$this->assertSame( $is_guid, $entity->call( 'is_guid', array( $value ) ) );
	}

	/**
	 * Values that may or may not be entity GUIDs, and whether they are or not.
	 *
	 * @since 2.2.0
	 *
	 * @return array[] The values and whether they are GUIDs.
	 */
	public function data_provider_guids() {

		return array(
			'id'           => array( 1, false ),
			'entity'       => array( array( 'id' => 1, 'attr' => 'value' ), false ),
			'entity_obj'   => array( (object) array( 'id' => 1, 'attr' => 'value' ), false ),
			'guid'         => array( array( 'test' => 1, 'test_context' => 2 ), true ),
			'with_parent'  => array( array( 'test' => 1, 'test_context' => 2, 'parent_context' => 5 ), true ),
			'id_only'      => array( array( 'test' => 1 ), false ),
			'context_only' => array( array( 'test_context' => 2 ), false ),
		);
	}

	/**
	 * Test is_guid() with an entity from the global context.
	 *
	 * @since 2.4.0
	 */
	public function test_is_guid_global_context() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'context', '' );

		$this->assertTrue(
			$entity->call( 'is_guid', array( array( 'test' => 1 ) ) )
		);
	}

	/**
	 * Test split_guid().
	 *
	 * @since 2.2.0
	 */
	public function test_split_guid() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertSame(
			array( 'id' => 5, 'context' => array( 'child' => 1, 'parent' => 2 ) )
			, $entity->call(
				'split_guid'
				, array( array( 'test' => 5, 'child' => 1, 'parent' => 2 ) )
			)
		);
	}

	/**
	 * Test split_guid() with global context.
	 *
	 * @since 2.4.0
	 */
	public function test_split_guid_global_context() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertSame(
			array( 'id' => 5, 'context' => array() )
			, $entity->call( 'split_guid', array( array( 'test' => 5 ) ) )
		);
	}

	/**
	 * Test get_attr_value() with an object.
	 *
	 * @since 2.1.0
	 */
	public function test_get_attr_value_object() {

		$object = (object) array( 'id' => 1, 'a' => 'b' );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertSame(
			'b'
			, $entity->call( 'get_attr_value', array( $object, 'a' ) )
		);
	}

	/**
	 * Test get_attr_value() with an object and an attr that isn't set.
	 *
	 * @since 2.1.0
	 */
	public function test_get_attr_value_not_set() {

		$object = (object) array( 'not' => 1 );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertNull(
			$entity->call( 'get_attr_value', array( $object, 'a' ) )
		);
	}

	/**
	 * Test get_attr_value() with an array.
	 *
	 * @since 2.1.0
	 */
	public function test_get_attr_value_array() {

		$array = array( 'id' => 1, 'a' => 'b' );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertSame(
			'b'
			, $entity->call( 'get_attr_value', array( $array, 'a' ) )
		);
	}

	/**
	 * Test get_attr_value() with an array and an attr that isn't set.
	 *
	 * @since 2.1.0
	 */
	public function test_get_attr_value_array_not_set() {

		$array = array( 'not' => 1 );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertNull( $entity->call( 'get_attr_value', array( $array, 'a' ) ) );
	}

	/**
	 * Test get_entity_id() with an object.
	 *
	 * @since 2.1.0
	 */
	public function test_get_entity_id_object() {

		$object = (object) array( 'id' => 1 );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertSame( 1, $entity->call( 'get_entity_id', array( $object ) ) );
	}

	/**
	 * Test get_entity_id() with an array.
	 *
	 * @since 2.1.0
	 */
	public function test_get_entity_id_array() {

		$array = array( 'id' => 1 );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertSame( 1, $entity->call( 'get_entity_id', array( $array ) ) );
	}

	/**
	 * Test get_entity_id() with an ID that is a string.
	 *
	 * @since 2.4.0
	 */
	public function test_get_entity_id_string() {

		$array = array( 'id' => '1' );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertSame( '1', $entity->call( 'get_entity_id', array( $array ) ) );
	}

	/**
	 * Test get_entity_id() with an ID that is a string when $id_is_int is true.
	 *
	 * @since 2.1.0
	 */
	public function test_get_entity_id_is_int() {

		$array = array( 'id' => '1' );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'id_is_int', true );

		$this->assertSame( 1, $entity->call( 'get_entity_id', array( $array ) ) );
	}

	/**
	 * Test get_entity_id() with an entity that isn't valid when $id_is_int is true.
	 *
	 * @since 2.4.0
	 */
	public function test_get_entity_id_is_int_invalid() {

		$array = array( 'invalid' => '1' );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'id_is_int', true );

		$this->assertNull( $entity->call( 'get_entity_id', array( $array ) ) );
	}

	/**
	 * Test get_id_field().
	 *
	 * @since 2.1.0
	 */
	public function test_get_id_field() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertSame( 'id', $entity->get_id_field() );
	}

	/**
	 * Test get_human_id() with an object.
	 *
	 * @since 2.1.0
	 */
	public function test_get_human_id_object() {

		$object = (object) array( 'id' => 1, 'title' => 'Title' );

		$mock = new WordPoints_PHPUnit_Mock_Filter( $object );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'getter', array( $mock, 'filter' ) );
		$entity->set( 'human_id_field', 'title' );

		$this->assertSame( 'Title', $entity->get_human_id( 1 ) );
	}

	/**
	 * Test get_human_id() with an array.
	 *
	 * @since 2.1.0
	 */
	public function test_get_human_id_array() {

		$object = array( 'id' => 1, 'title' => 'Title' );

		$mock = new WordPoints_PHPUnit_Mock_Filter( $object );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'getter', array( $mock, 'filter' ) );
		$entity->set( 'human_id_field', 'title' );

		$this->assertSame( 'Title', $entity->get_human_id( 1 ) );
	}

	/**
	 * Test get_human_id() with an invalid ID.
	 *
	 * @since 2.1.0
	 */
	public function test_get_human_id_invalid() {

		$mock = new WordPoints_PHPUnit_Mock_Filter( false );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'getter', array( $mock, 'filter' ) );
		$entity->set( 'human_id_field', 'title' );

		$this->assertFalse( $entity->get_human_id( 1 ) );
	}

	/**
	 * Test exists().
	 *
	 * @since 2.1.0
	 */
	public function test_exists() {

		$object = array( 'id' => 1, 'title' => 'Title' );

		$mock = new WordPoints_PHPUnit_Mock_Filter( $object );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'getter', array( $mock, 'filter' ) );

		$this->assertTrue( $entity->exists( 1 ) );
	}

	/**
	 * Test exists() with an ID that doesn't exist.
	 *
	 * @since 2.1.0
	 */
	public function test_exists_not() {

		$mock = new WordPoints_PHPUnit_Mock_Filter( false );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'getter', array( $mock, 'filter' ) );

		$this->assertFalse( $entity->exists( 1 ) );
	}

	/**
	 * Test get_child().
	 *
	 * @since 2.1.0
	 */
	public function test_get_child() {

		$entity = $this->factory->wordpoints->entity->create_and_get();

		wordpoints_entities()->get_sub_app( 'children' )->register(
			'test_entity'
			, 'child'
			, 'WordPoints_PHPUnit_Mock_Entity_Child'
		);

		$child = $entity->get_child( 'child' );

		$this->assertInstanceOf( 'WordPoints_PHPUnit_Mock_Entity_Child', $child );
		$this->assertSame( 'child', $child->get_slug() );
	}

	/**
	 * Test get_child() not registered.
	 *
	 * @since 2.1.0
	 */
	public function test_get_child_unregistered() {

		$entity = $this->factory->wordpoints->entity->create_and_get();

		$child = $entity->get_child( 'child' );

		$this->assertFalse( $child );
	}

	/**
	 * Test get_child() with the value set.
	 *
	 * @since 2.1.0
	 */
	public function test_get_child_with_value() {

		$entity = $this->factory->wordpoints->entity->create_and_get();

		wordpoints_entities()->get_sub_app( 'children' )->register(
			'test_entity'
			, 'child'
			, 'WordPoints_PHPUnit_Mock_Entity_Child'
		);

		$value = array( 'id' => 1 );

		$entity->set_the_value( $value );

		$child = $entity->get_child( 'child' );

		$this->assertInstanceOf( 'WordPoints_PHPUnit_Mock_Entity_Child', $child );
		$this->assertSame( 'child', $child->get_slug() );
		$this->assertSame( $value['id'], $child->get_the_value() );
	}

	/**
	 * Test get_child() with the value set but child doesn't implement the interface.
	 *
	 * @since 2.1.0
	 */
	public function test_get_child_with_value_not_child() {

		$entity = $this->factory->wordpoints->entity->create_and_get();

		wordpoints_entities()->get_sub_app( 'children' )->register(
			'test_entity'
			, 'child'
			, 'WordPoints_PHPUnit_Mock_Entity'
		);

		$value = array( 'id' => 1 );

		$entity->set_the_value( $value );

		$child = $entity->get_child( 'child' );

		$this->assertInstanceOf( 'WordPoints_PHPUnit_Mock_Entity', $child );
		$this->assertSame( 'child', $child->get_slug() );
		$this->assertNull( $child->get_the_value() );
	}

	/**
	 * Test set_the_value() with an ID.
	 *
	 * @since 2.1.0
	 */
	public function test_set_the_value_from_id() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertTrue( $entity->set_the_value( 1 ) );

		$this->assertSame( 1, $entity->get_the_value() );
		$this->assertSame( 1, $entity->get_the_id() );
		$this->assertSame( 'test', $entity->get_the_attr_value( 'type' ) );
		$this->assertSame( array( 'site' => 1, 'network' => 1 ), $entity->get_the_context() );
		$this->assertSame( array( 'test' => 1, 'site' => 1, 'network' => 1 ), $entity->get_the_guid() );
	}

	/**
	 * Test set_the_value() for an entity from the global context.
	 *
	 * @since 2.4.0
	 */
	public function test_set_the_value_from_id_global_context() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'context', '' );

		$this->assertTrue( $entity->set_the_value( 1 ) );

		$this->assertSame( 1, $entity->get_the_value() );
		$this->assertSame( 1, $entity->get_the_id() );
		$this->assertSame( 'test', $entity->get_the_attr_value( 'type' ) );
		$this->assertSame( array(), $entity->get_the_context() );
		$this->assertSame( array( 'test' => 1 ), $entity->get_the_guid() );
	}

	/**
	 * Test set_the_value() with an entity.
	 *
	 * @since 2.1.0
	 */
	public function test_set_the_value_from_entity() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertTrue(
			$entity->set_the_value( array( 'id' => 1, 'type' => 'test' ) )
		);

		$this->assertSame( 1, $entity->get_the_value() );
		$this->assertSame( 1, $entity->get_the_id() );
		$this->assertSame( 'test', $entity->get_the_attr_value( 'type' ) );
		$this->assertSame( array( 'site' => 1, 'network' => 1 ), $entity->get_the_context() );
		$this->assertSame( array( 'test' => 1, 'site' => 1, 'network' => 1 ), $entity->get_the_guid() );
	}

	/**
	 * Test set_the_value() with a GUID.
	 *
	 * @since 2.2.0
	 */
	public function test_set_the_value_from_guid() {

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'getter', array( $this, 'get_entity_from_context' ) );
		$entity->set( 'context', 'test_context' );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertTrue(
			$entity->set_the_value(
				array( 'test' => 2, 'test_context' => 2 )
			)
		);

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertSame( 2, $entity->get_the_value() );
		$this->assertSame( 2, $entity->get_the_id() );
		$this->assertSame( 2, $entity->get_the_attr_value( 'context' ) );
		$this->assertSame( array( 'test_context' => 2 ), $entity->get_the_context() );
		$this->assertSame( array( 'test' => 2, 'test_context' => 2 ), $entity->get_the_guid() );
	}

	/**
	 * Test set_the_value() with an invalid ID.
	 *
	 * @since 2.1.0
	 */
	public function test_set_the_value_from_id_invalid() {

		$mock = new WordPoints_PHPUnit_Mock_Filter( false );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'getter', array( $mock, 'filter' ) );

		$this->assertFalse( $entity->set_the_value( 1 ) );

		$this->assertNull( $entity->get_the_value() );
		$this->assertNull( $entity->get_the_id() );
		$this->assertNull( $entity->get_the_attr_value( 'type' ) );
		$this->assertNull( $entity->get_the_context() );
		$this->assertNull( $entity->get_the_guid() );
	}

	/**
	 * Test set_the_value() with an invalid entity.
	 *
	 * @since 2.1.0
	 */
	public function test_set_the_value_from_entity_invalid() {

		$mock = new WordPoints_PHPUnit_Mock_Filter( false );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'getter', array( $mock, 'filter' ) );

		$this->assertFalse( $entity->set_the_value( array( 'type' => 'test' ) ) );

		$this->assertNull( $entity->get_the_value() );
		$this->assertNull( $entity->get_the_id() );
		$this->assertNull( $entity->get_the_attr_value( 'type' ) );
		$this->assertNull( $entity->get_the_context() );
		$this->assertNull( $entity->get_the_guid() );
	}

	/**
	 * Test set_the_value() with an invalid GUID.
	 *
	 * @since 2.2.0
	 */
	public function test_set_the_value_from_guid_invalid() {

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'getter', array( $this, 'get_entity_from_context' ) );
		$entity->set( 'context', 'test_context' );

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertFalse(
			$entity->set_the_value(
				array( 'test' => 3, 'test_context' => 3 )
			)
		);

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertNull( $entity->get_the_value() );
		$this->assertNull( $entity->get_the_id() );
		$this->assertNull( $entity->get_the_attr_value( 'type' ) );
		$this->assertNull( $entity->get_the_context() );
		$this->assertNull( $entity->get_the_guid() );
	}

	/**
	 * Test set_the_value() twice.
	 *
	 * @since 2.1.0
	 */
	public function test_set_the_value_twice() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertTrue( $entity->set_the_value( 1 ) );

		$this->assertSame( 1, $entity->get_the_value() );
		$this->assertSame( 1, $entity->get_the_id() );
		$this->assertSame( 'test', $entity->get_the_attr_value( 'type' ) );
		$this->assertSame( array( 'site' => 1, 'network' => 1 ), $entity->get_the_context() );
		$this->assertSame( array( 'test' => 1, 'site' => 1, 'network' => 1 ), $entity->get_the_guid() );

		$mock = new WordPoints_PHPUnit_Mock_Filter( false );

		$entity->set( 'getter', array( $mock, 'filter' ) );

		$this->assertFalse( $entity->set_the_value( array( 'type' => 'test' ) ) );

		$this->assertNull( $entity->get_the_value() );
		$this->assertNull( $entity->get_the_id() );
		$this->assertNull( $entity->get_the_attr_value( 'type' ) );
		$this->assertNull( $entity->get_the_context() );
		$this->assertNull( $entity->get_the_guid() );
	}

	/**
	 * Test get_the_id() when the ID is a string.
	 *
	 * @since 2.4.0
	 */
	public function test_get_the_id_string() {

		$object = (object) array( 'id' => '1' );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set_the_value( $object );

		$this->assertSame( '1', $entity->get_the_id() );
	}

	/**
	 * Test get_the_id() when the ID is a string and $id_is_int is true.
	 *
	 * @since 2.4.0
	 */
	public function test_get_the_id_is_int() {

		$object = (object) array( 'id' => '1' );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set_the_value( $object );
		$entity->set( 'id_is_int', true );

		$this->assertSame( 1, $entity->get_the_id() );
	}

	/**
	 * Test get_the_id() when the ID isn't set and $id_is_int is true.
	 *
	 * @since 2.4.0
	 */
	public function test_get_the_id_is_int_not_set() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'id_is_int', true );

		$this->assertNull( $entity->get_the_id() );
	}

	/**
	 * Test get_the_guid() when the ID is a string.
	 *
	 * @since 2.4.0
	 */
	public function test_get_the_guid_string() {

		$object = (object) array( 'id' => '1' );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set_the_value( $object );

		$this->assertSame(
			array( 'test' => '1', 'site' => 1, 'network' => 1 )
			, $entity->get_the_guid()
		);
	}

	/**
	 * Test get_the_guid() when the ID is a string and $id_is_int is true.
	 *
	 * @since 2.4.0
	 */
	public function test_get_the_guid_is_int() {

		$object = (object) array( 'id' => '1' );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set_the_value( $object );
		$entity->set( 'id_is_int', true );

		$this->assertSame(
			array( 'test' => 1, 'site' => 1, 'network' => 1 )
			, $entity->get_the_guid()
		);
	}

	/**
	 * Test get_the_human_id().
	 *
	 * @since 2.1.0
	 */
	public function test_get_the_human_id() {

		$object = (object) array( 'id' => 1, 'title' => 'Title' );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'human_id_field', 'title' );
		$entity->set_the_value( $object );

		$this->assertSame( 'Title', $entity->get_the_human_id() );
	}

	/**
	 * Test get_the_human_id() when the value isn't set.
	 *
	 * @since 2.1.0
	 */
	public function test_get_the_human_id_not_set() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'human_id_field', 'title' );

		$this->assertNull( $entity->get_the_human_id() );
	}

	/**
	 * Test get_context()'s default value.
	 *
	 * @since 2.1.0
	 */
	public function test_get_context_default() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );

		$this->assertSame( 'site', $entity->get_context() );
	}

	/**
	 * Test get_context()'s default value.
	 *
	 * @since 2.1.0
	 */
	public function test_get_context() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set( 'context', 'test_context' );

		$this->assertSame( 'test_context', $entity->get_context() );
	}
}

// EOF
