<?php

/**
 * Test case for WordPoints_Class_Registry.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Class_Registry.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Class_Registry
 */
class WordPoints_Class_Registry_Test extends WordPoints_PHPUnit_TestCase_Class_Registry {

	/**
	 * @since 2.1.0
	 */
	protected function create_registry() {

		return new WordPoints_Class_Registry();
	}

	/**
	 * Test constructing classes without their slugs.
	 *
	 * @since 2.2.0
	 */
	public function test_construct_with_args_no_slugs() {

		$classes = array(
			'test'   => 'WordPoints_PHPUnit_Mock_Object',
			'test_2' => 'WordPoints_PHPUnit_Mock_Object2',
		);

		$objects = WordPoints_Class_Registry::construct_with_args(
			$classes
			, array()
			, array( 'pass_slugs' => false )
		);

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
	 * Test constructing classes with args but not their slugs.
	 *
	 * @since 2.2.0
	 */
	public function test_construct_with_args_no_slugs_args() {

		$classes = array(
			'test'   => 'WordPoints_PHPUnit_Mock_Object',
			'test_2' => 'WordPoints_PHPUnit_Mock_Object2',
		);

		$construct_with_args = array( 'one', 2 );

		$objects = WordPoints_Class_Registry::construct_with_args(
			$classes
			, $construct_with_args
			, array( 'pass_slugs' => false )
		);

		$this->assertCount( 2, $objects );

		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => $construct_with_args )
			, $objects['test']->calls[0]
		);

		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => $construct_with_args )
			, $objects['test_2']->calls[0]
		);
	}
}

// EOF
