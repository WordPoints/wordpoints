<?php

/**
 * Test case for WordPoints_Class_Registry_Persistent.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Class_Registry_Persistent.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Class_Registry_Persistent
 */
class WordPoints_Class_Registry_Persistent_Test extends PHPUnit_Framework_TestCase {

	/**
	 * Test registering a class.
	 *
	 * @since 2.1.0
	 */
	public function test_register() {

		$registry = new WordPoints_Class_Registry_Persistent;

		$this->assertTrue(
			$registry->register( 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue( $registry->is_registered( 'test' ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $registry->get( 'test' )
		);
	}

	/**
	 * Test that register() will silently overwrite an existing registry.
	 *
	 * @since 2.1.0
	 */
	public function test_register_overwrite() {

		$registry = new WordPoints_Class_Registry_Persistent;

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

		$registry = new WordPoints_Class_Registry_Persistent;

		$this->assertFalse( $registry->is_registered( 'test' ) );
	}

	/**
	 * Test getting all registered classes.
	 *
	 * @since 2.1.0
	 */
	public function test_get_all() {

		$registry = new WordPoints_Class_Registry_Persistent;

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

		$this->assertSame( 'test', $objects['test']->calls[0]['arguments'][0] );

		$this->assertArrayHasKey( 'test_2', $objects );
		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object2'
			, $objects['test_2']
		);

		$this->assertSame( 'test_2', $objects['test_2']->calls[0]['arguments'][0] );
	}


	/**
	 * Test getting all registered classes after a new one has been registered.
	 *
	 * @since 2.1.0
	 */
	public function test_get_all_new_registered() {

		$registry = new WordPoints_Class_Registry_Persistent;

		$this->assertTrue(
			$registry->register( 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$objects = $registry->get_all();

		$this->assertCount( 1, $objects );

		$this->assertArrayHasKey( 'test', $objects );
		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $objects['test']
		);

		$this->assertSame( 'test', $objects['test']->calls[0]['arguments'][0] );

		// Register another class.
		$this->assertTrue(
			$registry->register( 'test_2', 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$objects = $registry->get_all();

		$this->assertCount( 2, $objects );

		$this->assertArrayHasKey( 'test_2', $objects );
		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object2'
			, $objects['test_2']
		);

		$this->assertSame( 'test_2', $objects['test_2']->calls[0]['arguments'][0] );
	}

	/**
	 * Test getting a registered class.
	 *
	 * @since 2.1.0
	 */
	public function test_get() {

		$registry = new WordPoints_Class_Registry_Persistent;

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

		$this->assertSame( 'test', $object->calls[0]['arguments'][0] );
	}

	/**
	 * Test getting an unregistered class.
	 *
	 * @since 2.1.0
	 */
	public function test_get_unregistered() {

		$registry = new WordPoints_Class_Registry_Persistent;

		$this->assertFalse( $registry->get( 'test' ) );
	}

	/**
	 * Test getting a registered class a second time returns the same object.
	 *
	 * @since 2.1.0
	 */
	public function test_get_twice() {

		$registry = new WordPoints_Class_Registry_Persistent;

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

		$this->assertTrue( $object === $object_2, 'Two objects are equal.' );
	}

	/**
	 * Test getting a registered class constructed with some args.
	 *
	 * @since 2.1.0
	 */
	public function test_get_with_args() {

		$registry = new WordPoints_Class_Registry_Persistent;

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

		$this->assertSame(
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

		$registry = new WordPoints_Class_Registry_Persistent;

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
	 * Test getting all registered classes constructed with some args when some
	 * classes are already constructed.
	 *
	 * @since 2.1.0
	 */
	public function test_get_all_with_args_already_constructed() {

		$registry = new WordPoints_Class_Registry_Persistent;

		$this->assertTrue(
			$registry->register( 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue(
			$registry->register( 'test_2', 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$registry->get( 'test' );

		$args = array( 'one', 2 );

		$objects = $registry->get_all( $args );

		$this->assertCount( 2, $objects );

		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => array( 'test' ) )
			, $objects['test']->calls[0]
		);

		array_unshift( $args, 'test_2' );

		$this->assertSame(
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

		$registry = new WordPoints_Class_Registry_Persistent;

		$this->assertTrue(
			$registry->register( 'test', 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue( $registry->is_registered( 'test' ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $registry->get( 'test' )
		);

		$this->assertNull( $registry->deregister( 'test' ) );

		$this->assertFalse( $registry->is_registered( 'test' ) );

		$this->assertFalse( $registry->get( 'test' ) );
	}

	/**
	 * Test deregistering an unregistered class.
	 *
	 * @since 2.1.0
	 */
	public function test_deregister_unregistered() {

		$registry = new WordPoints_Class_Registry_Persistent;

		$this->assertNull( $registry->deregister( 'test' ) );

		$this->assertFalse( $registry->is_registered( 'test' ) );

		$this->assertFalse( $registry->get( 'test' ) );
	}
}

// EOF
