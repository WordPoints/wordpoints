<?php

/**
 * Test case for WordPoints_Entityish.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Entityish.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Entityish
 */
class WordPoints_Entityish_Test extends WP_UnitTestCase {

	/**
	 * Test getting the slug
	 *
	 * @since 2.1.0
	 */
	public function test_get_slug() {

		$entityish = new WordPoints_PHPUnit_Mock_Entityish( 'test' );

		$this->assertEquals( 'test', $entityish->get_slug() );
	}

	/**
	 * Test getting and setting the value
	 *
	 * @since 2.1.0
	 */
	public function test_get_value() {

		$entityish = new WordPoints_PHPUnit_Mock_Entityish( 'test' );

		$entityish->set_the_value( 'a' );

		$this->assertEquals( 'a', $entityish->get_the_value() );
	}
}

// EOF
