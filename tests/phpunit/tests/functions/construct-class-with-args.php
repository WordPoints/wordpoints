<?php

/**
 * Test case for wordpoints_construct_class_with_args().
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests wordpoints_construct_class_with_args().
 *
 * @since 2.1.0
 *
 * @covers ::wordpoints_construct_class_with_args
 */
class WordPoints_Construct_Class_With_Args_Test extends PHPUnit_Framework_TestCase {

	/**
	 * Test constructing a class with args.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider args_provider
	 */
	public function test_args( $args ) {

		/** @var WordPoints_PHPUnit_Mock_Object $object */
		$object = wordpoints_construct_class_with_args(
			'WordPoints_PHPUnit_Mock_Object'
			, $args
		);

		$this->assertInstanceOf( 'WordPoints_PHPUnit_Mock_Object', $object );

		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => $args )
			, $object->calls[0]
		);
	}

	/**
	 * Data provider for the test_args() test.
	 *
	 * @since 2.1.0
	 *
	 * @return array[]
	 */
	public function args_provider() {

		return array(
			'0 args' => array( array() ),
			'1 arg'  => array( array( 'arg 1' ) ),
			'2 args' => array( array( 'arg 1', 2 ) ),
			'3 args' => array( array( 'arg 1', 2, 3.0 ) ),
			'4 args' => array( array( 'arg 1', 2, 3.0, '4' ) ),
		);
	}

	/**
	 * Test constructing a class with 5 args.
	 *
	 * @since 2.1.0
	 */
	public function test_5_args() {

		$args = array( 'arg 1', 2, 3.0, '4', 'five' );

		$result = wordpoints_construct_class_with_args(
			'WordPoints_PHPUnit_Mock_Object'
			, $args
		);

		$this->assertFalse( $result );
	}
}

// EOF
