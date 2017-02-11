<?php

/**
 * Test case for WordPoints_Class_Registry_Children.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Class_Registry_Children.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Class_Registry_Children
 */
class WordPoints_Class_Registry_Children_Test extends PHPUnit_Framework_TestCase {

	/**
	 * Test registering a class.
	 *
	 * @since 2.1.0
	 */
	public function test_register() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertTrue(
			$registry->register( 'parent', 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue( $registry->is_registered( 'parent', 'test' ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $registry->get( 'parent', 'test' )
		);

		$this->assertSame(
			array( 'test' )
			, $registry->get_children_slugs( 'parent' )
		);

		$this->assertSame(
			array( 'parent' => array( 'test' ) )
			, $registry->get_all_slugs()
		);
	}

	/**
	 * Test that register() will silently overwrite an existing registry.
	 *
	 * @since 2.1.0
	 */
	public function test_register_overwrite() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertTrue(
			$registry->register( 'parent', 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue( $registry->is_registered( 'parent', 'test' ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $registry->get( 'parent', 'test' )
		);

		$this->assertTrue(
			$registry->register( 'parent', 'test', 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$this->assertTrue( $registry->is_registered( 'parent', 'test' ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object2'
			, $registry->get( 'parent', 'test' )
		);
	}

	/**
	 * Test that is_registered() returns false if a class isn't registered.
	 *
	 * @since 2.1.0
	 */
	public function test_is_registered_not_registered() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertFalse( $registry->is_registered( 'parent', 'test' ) );
	}

	/**
	 * Test checking if any children are registered for a parent.
	 *
	 * @since 2.1.0
	 */
	public function test_is_registered_any() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertFalse( $registry->is_registered( 'parent' ) );

		$this->assertTrue(
			$registry->register( 'parent', 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue( $registry->is_registered( 'parent' ) );
	}

	/**
	 * Test getting all registered children of given parent.
	 *
	 * @since 2.1.0
	 */
	public function test_get_all_children() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertTrue(
			$registry->register( 'parent', 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue(
			$registry->register( 'parent', 'test_2', 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$objects = $registry->get_children( 'parent' );

		$this->assertCount( 2, $objects );

		$this->assertArrayHasKey( 'test', $objects );
		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $objects['test']
		);

		$this->assertSame(
			array( 'test', 'parent' )
			, $objects['test']->calls[0]['arguments']
		);

		$this->assertArrayHasKey( 'test_2', $objects );
		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object2'
			, $objects['test_2']
		);

		$this->assertSame(
			array( 'test_2', 'parent' )
			, $objects['test_2']->calls[0]['arguments']
		);

		$this->assertSame(
			array( 'test', 'test_2' )
			, $registry->get_children_slugs( 'parent' )
		);

		$this->assertSame(
			array( 'parent' => array( 'test', 'test_2' ) )
			, $registry->get_all_slugs()
		);
	}

	/**
	 * Test getting a registered class.
	 *
	 * @since 2.1.0
	 */
	public function test_get() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertTrue(
			$registry->register( 'parent', 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue(
			$registry->register( 'parent', 'test_2', 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$object = $registry->get( 'parent', 'test' );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $object
		);

		$this->assertSame(
			array( 'test', 'parent' )
			, $object->calls[0]['arguments']
		);

		$this->assertSame(
			array( 'test', 'test_2' )
			, $registry->get_children_slugs( 'parent' )
		);
	}

	/**
	 * Test getting all registered classes.
	 *
	 * @since 2.1.0
	 */
	public function test_get_all() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertTrue(
			$registry->register( 'parent', 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue(
			$registry->register( 'parent', 'test_2', 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$this->assertTrue(
			$registry->register( 'parent_2', 'test', 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$objects = $registry->get_all();

		$this->assertCount( 2, $objects );

		$this->assertArrayHasKey( 'parent', $objects );

		$this->assertCount( 2, $objects['parent'] );

		$this->assertArrayHasKey( 'test', $objects['parent'] );
		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $objects['parent']['test']
		);

		$this->assertSame(
			array( 'test', 'parent' )
			, $objects['parent']['test']->calls[0]['arguments']
		);

		$this->assertArrayHasKey( 'test_2', $objects['parent'] );
		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object2'
			, $objects['parent']['test_2']
		);

		$this->assertSame(
			array( 'test_2', 'parent' )
			, $objects['parent']['test_2']->calls[0]['arguments']
		);

		$this->assertArrayHasKey( 'parent_2', $objects );

		$this->assertCount( 1, $objects['parent_2'] );

		$this->assertArrayHasKey( 'test', $objects['parent_2'] );
		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $objects['parent_2']['test']
		);

		$this->assertSame(
			array( 'test', 'parent_2' )
			, $objects['parent_2']['test']->calls[0]['arguments']
		);

		$this->assertSame(
			array(
				'parent' => array( 'test', 'test_2' ),
				'parent_2' => array( 'test' ),
			)
			, $registry->get_all_slugs()
		);
	}

	/**
	 * Test getting a registered class constructed with some args.
	 *
	 * @since 2.1.0
	 */
	public function test_get_with_args() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertTrue(
			$registry->register( 'parent', 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$args = array( 'one', 2 );

		$object = $registry->get( 'parent', 'test', $args );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $object
		);

		array_unshift( $args, 'test', 'parent' );

		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => $args )
			, $object->calls[0]
		);
	}

	/**
	 * Test getting all children of a parent constructed with some args.
	 *
	 * @since 2.1.0
	 */
	public function test_get_all_children_with_args() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertTrue(
			$registry->register( 'parent', 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue(
			$registry->register( 'parent', 'test_2', 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$args = array( 'one', 2 );

		$objects = $registry->get_children( 'parent', $args );

		$this->assertCount( 2, $objects );

		array_unshift( $args, 'test', 'parent' );

		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => $args )
			, $objects['test']->calls[0]
		);

		$args[0] = 'test_2';

		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => $args )
			, $objects['test_2']->calls[0]
		);
	}

	/**
	 * Test getting all registered classes.
	 *
	 * @since 2.1.0
	 */
	public function test_get_all_with_args() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertTrue(
			$registry->register( 'parent', 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue(
			$registry->register( 'parent', 'test_2', 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$this->assertTrue(
			$registry->register( 'parent_2', 'test', 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$args = array( 'one', 2 );

		$objects = $registry->get_all( $args );

		$this->assertCount( 2, $objects );

		array_unshift( $args, 'test', 'parent' );

		$this->assertArrayHasKey( 'parent', $objects );

		$this->assertCount( 2, $objects['parent'] );

		$this->assertArrayHasKey( 'test', $objects['parent'] );

		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => $args )
			, $objects['parent']['test']->calls[0]
		);

		$args[0] = 'test_2';

		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => $args )
			, $objects['parent']['test_2']->calls[0]
		);

		$this->assertArrayHasKey( 'parent_2', $objects );

		$this->assertCount( 1, $objects['parent_2'] );

		$this->assertArrayHasKey( 'test', $objects['parent_2'] );

		$args[0] = 'test';
		$args[1] = 'parent_2';

		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => $args )
			, $objects['parent_2']['test']->calls[0]
		);
	}

	/**
	 * Test getting an unregistered class.
	 *
	 * @since 2.1.0
	 */
	public function test_get_unregistered() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertFalse( $registry->get( 'parent', 'test' ) );
	}

	/**
	 * Test getting children when none are registered.
	 *
	 * @since 2.1.0
	 */
	public function test_get_children_unregistered() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertSame( array(), $registry->get_children( 'parent' ) );
	}

	/**
	 * Test getting all classes when none are registered.
	 *
	 * @since 2.1.0
	 */
	public function test_get_all_unregistered() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertSame( array(), $registry->get_all() );
	}


	/**
	 * Test getting children slugs when none are registered.
	 *
	 * @since 2.1.0
	 */
	public function test_get_children_slugs_unregistered() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertSame( array(), $registry->get_children_slugs( 'parent' ) );
	}

	/**
	 * Test getting all slugs when none are registered.
	 *
	 * @since 2.1.0
	 */
	public function test_get_all_slugs_unregistered() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertSame( array(), $registry->get_all_slugs() );
	}

	/**
	 * Test getting a registered class a second time returns a new object.
	 *
	 * @since 2.1.0
	 */
	public function test_get_twice() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertTrue(
			$registry->register( 'parent', 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$object = $registry->get( 'parent', 'test' );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $object
		);

		$object_2 = $registry->get( 'parent', 'test' );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $object
		);

		$this->assertFalse( $object === $object_2, 'Two objects are not equal.' );
	}

	/**
	 * Test deregistering a class.
	 *
	 * @since 2.1.0
	 */
	public function test_deregister() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertTrue(
			$registry->register( 'parent', 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue( $registry->is_registered( 'parent', 'test' ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $registry->get( 'parent', 'test' )
		);

		$this->assertNull( $registry->deregister( 'parent', 'test' ) );

		$this->assertFalse( $registry->is_registered( 'parent', 'test' ) );

		$this->assertFalse( $registry->get( 'parent', 'test' ) );
	}

	/**
	 * Test deregistering an unregistered class.
	 *
	 * @since 2.1.0
	 */
	public function test_deregister_unregistered() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertNull( $registry->deregister( 'parent', 'test' ) );

		$this->assertFalse( $registry->is_registered( 'parent', 'test' ) );

		$this->assertFalse( $registry->get( 'parent', 'test' ) );
	}

	/**
	 * Test deregistering all classes for a given parent.
	 *
	 * @since 2.1.0
	 */
	public function test_deregister_all() {

		$registry = new WordPoints_Class_Registry_Children;

		$this->assertTrue(
			$registry->register( 'parent', 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue( $registry->is_registered( 'parent', 'test' ) );

		$this->assertTrue(
			$registry->register( 'parent', 'test_2', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue( $registry->is_registered( 'parent', 'test_2' ) );

		$this->assertTrue( $registry->is_registered( 'parent' ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $registry->get( 'parent', 'test' )
		);

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $registry->get( 'parent', 'test_2' )
		);

		$this->assertNull( $registry->deregister_children( 'parent' ) );

		$this->assertFalse( $registry->is_registered( 'parent', 'test' ) );
		$this->assertFalse( $registry->is_registered( 'parent', 'test_2' ) );
		$this->assertFalse( $registry->is_registered( 'parent' ) );

		$this->assertFalse( $registry->get( 'parent', 'test' ) );
		$this->assertFalse( $registry->get( 'parent', 'test_2' ) );
		$this->assertSame( array(), $registry->get_children( 'parent' ) );
	}
}

// EOF
