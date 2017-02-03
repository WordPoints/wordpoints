<?php

/**
 * Test case for WordPoints_Entity_Attr_Stored_DB_Table_Meta.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.3.0
 */

/**
 * Tests WordPoints_Entity_Attr_Stored_DB_Table_Meta.
 *
 * @since 2.3.0
 *
 * @covers WordPoints_Entity_Attr_Stored_DB_Table_Meta
 */
class WordPoints_Entity_Attr_Stored_DB_Table_Meta_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test constructing the class sets up some attributes.
	 *
	 * @since 2.3.0
	 */
	public function test_construct() {

		$attr = new WordPoints_PHPUnit_Mock_Entity_Attr_Stored_DB_Table_Meta(
			'testing'
		);

		$this->assertEquals( 'testmeta', $attr->get( 'wpdb_table_name' ) );
		$this->assertEquals( 'test_id', $attr->get( 'entity_id_field' ) );
	}

	/**
	 * Test setting the value from an entity.
	 *
	 * @since 2.3.0
	 */
	public function test_set_the_value_from_entity() {

		update_post_meta( 1, 'test_attr', 'a' );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set_the_value( array( 'id' => 1, 'test_attr' => 'b' ) );

		$attr = new WordPoints_PHPUnit_Mock_Entity_Attr_Stored_DB_Table_Meta(
			'test'
			, 'post'
		);

		$this->assertTrue( $attr->set_the_value_from_entity( $entity ) );

		$this->assertEquals( 'a', $attr->get_the_value() );
	}

	/**
	 * Test setting the value from an entity whose value isn't set.
	 *
	 * @since 2.3.0
	 */
	public function test_set_the_value_from_entity_not_set() {

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set_the_value( array( 'id' => 1, 'test_attr' => 'b' ) );

		$attr = new WordPoints_PHPUnit_Mock_Entity_Attr_Stored_DB_Table_Meta(
			'test'
			, 'post'
		);

		$this->assertNull( $attr->get_the_value() );
	}

	/**
	 * Test setting the value from an entity twice.
	 *
	 * @since 2.3.0
	 */
	public function test_set_the_value_from_entity_twice() {

		update_post_meta( 1, 'test_attr', 'a' );

		$entity = new WordPoints_PHPUnit_Mock_Entity( 'test' );
		$entity->set_the_value( array( 'id' => 1, 'test_attr' => 'b' ) );

		$attr = new WordPoints_PHPUnit_Mock_Entity_Attr_Stored_DB_Table_Meta(
			'test'
			, 'post'
		);

		$this->assertTrue( $attr->set_the_value_from_entity( $entity ) );

		$this->assertEquals( 'a', $attr->get_the_value() );

		$this->assertTrue(
			$attr->set_the_value_from_entity(
				new WordPoints_PHPUnit_Mock_Entity( 'test' )
			)
		);

		$this->assertNull( $attr->get_the_value() );
	}

	/**
	 * Test getting the storage info.
	 *
	 * @since 2.3.0
	 */
	public function test_get_storage_info() {

		global $wpdb;

		$attr = new WordPoints_PHPUnit_Mock_Entity_Attr_Stored_DB_Table_Meta(
			'test'
			, 'post'
		);

		$this->assertEquals(
			array(
				'type' => 'db',
				'info' => array(
					'type'             => 'meta_table',
					'table_name'       => $wpdb->postmeta,
					'meta_key'         => 'test_attr',
					'meta_key_field'   => 'meta_key',
					'meta_value_field' => 'meta_value',
					'entity_id_field'  => 'post_id',
				),
			)
			, $attr->get_storage_info()
		);
	}
}

// EOF
