<?php

/**
 * Test case for WordPoints_Class_Registry_Deep_Multilevel.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.2.0
 */

/**
 * Tests WordPoints_Class_Registry_Deep_Multilevel.
 *
 * @since 2.2.0
 *
 * @covers WordPoints_Class_Registry_Deep_Multilevel
 * @covers WordPoints_Class_Registry_Deep_Multilevel_Slugless
 */
class WordPoints_Class_Registry_Deep_Multilevel_Test
	extends PHPUnit_Framework_TestCase {

	/**
	 * Test registering a class.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_valid_parents
	 *
	 * @param string|string[] $parent The parent slug(s).
	 */
	public function test_register( $parent ) {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel;

		$this->assertTrue(
			$registry->register( 'test', $parent, 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue( $registry->is_registered( 'test', $parent ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $registry->get( 'test', $parent )
		);

		$this->assertSame(
			array( 'test' )
			, $registry->get_children_slugs( $parent )
		);
	}

	/**
	 * Data provider for valid class parents.
	 *
	 * @since 2.2.0
	 *
	 * @return array[] List of valid class parents.
	 */
	public function data_provider_valid_parents() {
		return array(
			'none' => array( array() ),
			'one' => array( array( 'parent' ) ),
			'two' => array( array( 'parent', 'child' ) ),
			'deep' => array(
				array( 'parent', 'child', 'grandchild', 'great', 'great-2' ),
			),
		);
	}

	/**
	 * Test registering a class at the top level of the hierarchy.
	 *
	 * @since 2.2.0
	 */
	public function test_register_no_parent() {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel;

		$this->assertTrue(
			$registry->register( 'test', array(), 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue( $registry->is_registered( 'test' ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $registry->get( 'test' )
		);

		$this->assertSame(
			array( 'test' )
			, $registry->get_children_slugs()
		);
	}

	/**
	 * Test that register() will silently overwrite an existing class.
	 *
	 * @since 2.2.0
	 */
	public function test_register_overwrite() {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel;

		$this->assertTrue(
			$registry->register(
				'test'
				, array( 'parent' )
				, 'WordPoints_PHPUnit_Mock_Object'
			)
		);

		$this->assertTrue( $registry->is_registered( 'test', array( 'parent' ) ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $registry->get( 'test', array( 'parent' ) )
		);

		$this->assertTrue(
			$registry->register(
				'test'
				, array( 'parent' )
				, 'WordPoints_PHPUnit_Mock_Object2'
			)
		);

		$this->assertTrue( $registry->is_registered( 'test', array( 'parent' ) ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object2'
			, $registry->get( 'test', array( 'parent' ) )
		);
	}

	/**
	 * Test registering classes at multiple levels within the hierarchy.
	 *
	 * @since 2.2.0
	 */
	public function test_register_multilevel() {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel;

		$this->assertTrue(
			$registry->register( 'parent', array(), 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue( $registry->is_registered( 'parent' ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $registry->get( 'parent' )
		);

		$this->assertTrue(
			$registry->register(
				'test'
				, array( 'parent' )
				, 'WordPoints_PHPUnit_Mock_Object2'
			)
		);

		$this->assertTrue( $registry->is_registered( 'test', array( 'parent' ) ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object2'
			, $registry->get( 'test', array( 'parent' ) )
		);
	}

	/**
	 * Test that is_registered() returns false if a class isn't registered.
	 *
	 * @since 2.2.0
	 */
	public function test_is_registered_not_registered() {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel;

		$this->assertFalse( $registry->is_registered( 'test', array( 'parent' ) ) );
	}

	/**
	 * Test checking if any children are registered for a parent.
	 *
	 * @since 2.2.0
	 */
	public function test_is_registered_any() {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel;

		$this->assertFalse( $registry->is_registered( null, array( 'parent' ) ) );

		$this->assertTrue(
			$registry->register(
				'test'
				, array( 'parent' )
				, 'WordPoints_PHPUnit_Mock_Object'
			)
		);

		$this->assertTrue( $registry->is_registered( null, array( 'parent' ) ) );
	}

	/**
	 * Test getting all registered children of given parent.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_valid_parents
	 *
	 * @param string|string[] $parent The parent slug(s).
	 */
	public function test_get_all_children( $parent ) {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel;

		$this->assertTrue(
			$registry->register( 'test', $parent, 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue(
			$registry->register( 'test_2', $parent, 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$objects = $registry->get_children( $parent );

		$this->assertCount( 2, $objects );

		$this->assertArrayHasKey( 'test', $objects );
		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $objects['test']
		);

		$this->assertSame(
			array( 'test', $parent )
			, $objects['test']->calls[0]['arguments']
		);

		$this->assertArrayHasKey( 'test_2', $objects );
		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object2'
			, $objects['test_2']
		);

		$this->assertSame(
			array( 'test_2', $parent )
			, $objects['test_2']->calls[0]['arguments']
		);

		$this->assertSame(
			array( 'test', 'test_2' )
			, $registry->get_children_slugs( $parent )
		);
	}

	/**
	 * Test getting a registered class.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_valid_parents
	 *
	 * @param string|string[] $parent The parent slug(s).
	 */
	public function test_get( $parent ) {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel;

		$this->assertTrue(
			$registry->register( 'test', $parent, 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue(
			$registry->register( 'test_2', $parent, 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$object = $registry->get( 'test', $parent );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $object
		);

		$this->assertSame(
			array( 'test', $parent )
			, $object->calls[0]['arguments']
		);

		$this->assertSame(
			array( 'test', 'test_2' )
			, $registry->get_children_slugs( $parent )
		);
	}

	/**
	 * Test getting a registered class constructed with some args.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_valid_parents
	 *
	 * @param string|string[] $parent The parent slug(s).
	 */
	public function test_get_with_args( $parent ) {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel;

		$this->assertTrue(
			$registry->register( 'test', $parent, 'WordPoints_PHPUnit_Mock_Object' )
		);

		$args = array( 'one', 2 );

		$object = $registry->get( 'test', $parent, $args );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $object
		);

		array_unshift( $args, 'test', $parent );

		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => $args )
			, $object->calls[0]
		);
	}

	/**
	 * Test getting all children of a parent constructed with some args.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_valid_parents
	 *
	 * @param string|string[] $parent The parent slug(s).
	 */
	public function test_get_all_children_with_args( $parent ) {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel;

		$this->assertTrue(
			$registry->register( 'test', $parent, 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue(
			$registry->register( 'test_2', $parent, 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$args = array( 'one', 2 );

		$objects = $registry->get_children( $parent, $args );

		$this->assertCount( 2, $objects );

		array_unshift( $args, 'test', $parent );

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
	 * Test getting a registered class constructed with some args.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_valid_parents
	 *
	 * @param string|string[] $parent The parent slug(s).
	 */
	public function test_get_with_args_no_slugs( $parent ) {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel_Slugless;

		$this->assertTrue(
			$registry->register( 'test', $parent, 'WordPoints_PHPUnit_Mock_Object' )
		);

		$args = array( 'one', 2 );

		$object = $registry->get( 'test', $parent, $args );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $object
		);

		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => $args )
			, $object->calls[0]
		);
	}

	/**
	 * Test getting all children of a parent constructed with some args.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_valid_parents
	 *
	 * @param string|string[] $parent The parent slug(s).
	 */
	public function test_get_all_children_with_args_no_slugs( $parent ) {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel_Slugless;

		$this->assertTrue(
			$registry->register( 'test', $parent, 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue(
			$registry->register( 'test_2', $parent, 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$args = array( 'one', 2 );

		$objects = $registry->get_children( $parent, $args );

		$this->assertCount( 2, $objects );

		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => $args )
			, $objects['test']->calls[0]
		);

		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => $args )
			, $objects['test_2']->calls[0]
		);
	}

	/**
	 * Test getting a registered class constructed with some args.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_valid_parents
	 *
	 * @param string|string[] $parent The parent slug(s).
	 */
	public function test_get_no_slugs( $parent ) {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel_Slugless;

		$this->assertTrue(
			$registry->register( 'test', $parent, 'WordPoints_PHPUnit_Mock_Object' )
		);

		$object = $registry->get( 'test', $parent );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $object
		);

		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => array() )
			, $object->calls[0]
		);
	}

	/**
	 * Test getting all children of a parent constructed with some args.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_valid_parents
	 *
	 * @param string|string[] $parent The parent slug(s).
	 */
	public function test_get_all_children_no_slugs( $parent ) {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel_Slugless;

		$this->assertTrue(
			$registry->register( 'test', $parent, 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue(
			$registry->register( 'test_2', $parent, 'WordPoints_PHPUnit_Mock_Object2' )
		);

		$objects = $registry->get_children( $parent );

		$this->assertCount( 2, $objects );

		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => array() )
			, $objects['test']->calls[0]
		);

		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => array() )
			, $objects['test_2']->calls[0]
		);
	}

	/**
	 * Test getting an unregistered class.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_valid_parents
	 *
	 * @param string|string[] $parent The parent slug(s).
	 */
	public function test_get_unregistered( $parent ) {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel;

		$this->assertFalse( $registry->get( 'test', $parent ) );
	}

	/**
	 * Test getting children when none are registered.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_valid_parents
	 *
	 * @param string|string[] $parent The parent slug(s).
	 */
	public function test_get_children_unregistered( $parent ) {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel;

		$this->assertSame( array(), $registry->get_children( $parent ) );
	}

	/**
	 * Test getting children slugs when none are registered.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_valid_parents
	 *
	 * @param string|string[] $parent The parent slug(s).
	 */
	public function test_get_children_slugs_unregistered( $parent ) {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel;

		$this->assertSame( array(), $registry->get_children_slugs( $parent ) );
	}

	/**
	 * Test getting a registered class a second time returns a new object.
	 *
	 * @since 2.2.0
	 */
	public function test_get_twice() {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel;

		$this->assertTrue(
			$registry->register(
				'test'
				, array( 'parent' )
				, 'WordPoints_PHPUnit_Mock_Object'
			)
		);

		$object = $registry->get( 'test', array( 'parent' ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $object
		);

		$object_2 = $registry->get( 'test', array( 'parent' ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $object
		);

		$this->assertFalse( $object === $object_2, 'Two objects are not equal.' );
	}

	/**
	 * Test deregistering a class.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_valid_parents
	 *
	 * @param string|string[] $parent The parent slug(s).
	 */
	public function test_deregister( $parent ) {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel;

		$this->assertTrue(
			$registry->register( 'test', $parent, 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue( $registry->is_registered( 'test', $parent ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $registry->get( 'test', $parent )
		);

		$this->assertNull( $registry->deregister( 'test', $parent ) );

		$this->assertFalse( $registry->is_registered( 'test', $parent ) );

		$this->assertFalse( $registry->get( 'test', $parent ) );
	}

	/**
	 * Test deregistering an unregistered class.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_valid_parents
	 *
	 * @param string|string[] $parent The parent slug(s).
	 */
	public function test_deregister_unregistered( $parent ) {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel;

		$this->assertNull( $registry->deregister( 'test', $parent ) );

		$this->assertFalse( $registry->is_registered( 'test', $parent ) );

		$this->assertFalse( $registry->get( 'test', $parent ) );
	}

	/**
	 * Test deregistering all classes for a given parent.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_valid_parents
	 *
	 * @param string|string[] $parent The parent slug(s).
	 */
	public function test_deregister_all( $parent ) {

		$registry = new WordPoints_Class_Registry_Deep_Multilevel;

		$this->assertTrue(
			$registry->register( 'test', $parent, 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue( $registry->is_registered( 'test', $parent ) );

		$this->assertTrue(
			$registry->register( 'test_2', $parent, 'WordPoints_PHPUnit_Mock_Object' )
		);

		$this->assertTrue( $registry->is_registered( 'test_2', $parent ) );

		$this->assertTrue( $registry->is_registered( null, $parent ) );

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $registry->get( 'test', $parent )
		);

		$this->assertInstanceOf(
			'WordPoints_PHPUnit_Mock_Object'
			, $registry->get( 'test_2', $parent )
		);

		$this->assertNull( $registry->deregister_children( $parent ) );

		$this->assertFalse( $registry->is_registered( 'test', $parent ) );
		$this->assertFalse( $registry->is_registered( 'test_2', $parent ) );
		$this->assertFalse( $registry->is_registered( null, $parent ) );

		$this->assertFalse( $registry->get( 'test', $parent ) );
		$this->assertFalse( $registry->get( 'test_2', $parent ) );
		$this->assertEmpty( $registry->get_children( $parent ) );
	}
}

// EOF
