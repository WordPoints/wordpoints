<?php

/**
 * Test case for objects implementing WordPoints_Class_RegistryI.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Tests class registry objects.
 *
 * @since 2.1.0
 */
abstract class WordPoints_PHPUnit_TestCase_Class_Registry extends PHPUnit_Framework_TestCase {

	/**
	 * Create a class registry.
	 *
	 * This method can be overridden in child test cases to test other
	 * implementations.
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_Class_RegistryI The registry.
	 */
	abstract protected function create_registry();

	/**
	 * Test registering a class.
	 *
	 * @since 2.1.0
	 */
	public function test_register() {

		$registry = $this->create_registry();

		$this->assertTrue(
			$registry->register( 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue( $registry->is_registered( 'test' ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $registry->get( 'test' )
		);

		$this->assertEquals( array( 'test' ), $registry->get_all_slugs() );
	}

	/**
	 * Test that register() will silently overwrite an existing registry.
	 *
	 * @since 2.1.0
	 */
	public function test_register_overwrite() {

		$registry = $this->create_registry();

		$this->assertTrue(
			$registry->register( 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue( $registry->is_registered( 'test' ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $registry->get( 'test' )
		);

		$this->assertTrue(
			$registry->register( 'test', 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$this->assertTrue( $registry->is_registered( 'test' ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object2'
			, $registry->get( 'test' )
		);
	}

	/**
	 * Test that is_registered() returns false if a class isn't registered.
	 *
	 * @since 2.1.0
	 */
	public function test_is_registered_not_registered() {

		$registry = $this->create_registry();

		$this->assertFalse( $registry->is_registered( 'test' ) );
	}

	/**
	 * Test getting all registered classes.
	 *
	 * @since 2.1.0
	 */
	public function test_get_all() {

		$registry = $this->create_registry();

		$this->assertTrue(
			$registry->register( 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue(
			$registry->register( 'test_2', 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$objects = $registry->get_all();

		$this->assertCount( 2, $objects );

		$this->assertArrayHasKey( 'test', $objects );
		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $objects['test']
		);

		$this->assertEquals( 'test', $objects['test']->calls[0]['arguments'][0] );

		$this->assertArrayHasKey( 'test_2', $objects );
		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object2'
			, $objects['test_2']
		);

		$this->assertEquals( 'test_2', $objects['test_2']->calls[0]['arguments'][0] );

		$this->assertEquals( array( 'test', 'test_2' ), $registry->get_all_slugs() );
	}

	/**
	 * Test getting a registered class.
	 *
	 * @since 2.1.0
	 */
	public function test_get() {

		$registry = $this->create_registry();

		$this->assertTrue(
			$registry->register( 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue(
			$registry->register( 'test_2', 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$object = $registry->get( 'test' );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $object
		);

		$this->assertEquals( 'test', $object->calls[0]['arguments'][0] );
	}

	/**
	 * Test getting an unregistered class.
	 *
	 * @since 2.1.0
	 */
	public function test_get_unregistered() {

		$registry = $this->create_registry();

		$this->assertFalse( $registry->get( 'test' ) );
	}

	/**
	 * Test getting a registered class a second time returns a new object.
	 *
	 * @since 2.1.0
	 */
	public function test_get_twice() {

		$registry = $this->create_registry();

		$this->assertTrue(
			$registry->register( 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$object = $registry->get( 'test' );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $object
		);

		$object_2 = $registry->get( 'test' );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $object
		);

		$this->assertFalse( $object === $object_2, 'Two objects are not equal.' );
	}

	/**
	 * Test getting a registered class constructed with some args.
	 *
	 * @since 2.1.0
	 */
	public function test_get_with_args() {

		$registry = $this->create_registry();

		$this->assertTrue(
			$registry->register( 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$args = array( 'one', 2 );

		$object = $registry->get( 'test', $args );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $object
		);

		array_unshift( $args, 'test' );

		$this->assertEquals(
			array( 'name' => '__construct', 'arguments' => $args )
			, $object->calls[0]
		);
	}

	/**
	 * Test getting all registered classes constructed with some args.
	 *
	 * @since 2.1.0
	 */
	public function test_get_all_with_args() {

		$registry = $this->create_registry();

		$this->assertTrue(
			$registry->register( 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue(
			$registry->register( 'test_2', 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$args = array( 'one', 2 );

		$objects = $registry->get_all( $args );

		$this->assertCount( 2, $objects );

		array_unshift( $args, 'test' );

		$this->assertEquals(
			array( 'name' => '__construct', 'arguments' => $args )
			, $objects['test']->calls[0]
		);

		$args[0] = 'test_2';

		$this->assertEquals(
			array( 'name' => '__construct', 'arguments' => $args )
			, $objects['test_2']->calls[0]
		);
	}

	/**
	 * Test deregistering a class.
	 *
	 * @since 2.1.0
	 */
	public function test_deregister() {

		$registry = $this->create_registry();

		$this->assertTrue(
			$registry->register( 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue( $registry->is_registered( 'test' ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $registry->get( 'test' )
		);

		$this->assertEquals( array( 'test' ), $registry->get_all_slugs() );

		$this->assertNull( $registry->deregister( 'test' ) );

		$this->assertFalse( $registry->is_registered( 'test' ) );

		$this->assertFalse( $registry->get( 'test' ) );

		$this->assertSame( array(), $registry->get_all_slugs() );
	}

	/**
	 * Test deregistering an unregistered class.
	 *
	 * @since 2.1.0
	 */
	public function test_deregister_unregistered() {

		$registry = $this->create_registry();

		$this->assertNull( $registry->deregister( 'test' ) );

		$this->assertFalse( $registry->is_registered( 'test' ) );

		$this->assertFalse( $registry->get( 'test' ) );

		$this->assertSame( array(), $registry->get_all_slugs() );
	}

	/**
	 * Test getting all slugs when no classed are registered.
	 *
	 * @since 2.1.0
	 */
	public function test_get_all_slugs_unregistered() {

		$registry = $this->create_registry();

		$this->assertSame( array(), $registry->get_all_slugs() );
	}
}

// EOF
