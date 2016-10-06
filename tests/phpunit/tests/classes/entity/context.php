<?php

/**
 * Test case for WordPoints_Entity_Context.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Entity_Context.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Entity_Context
 */
class WordPoints_Entity_Context_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test getting the slug.
	 *
	 * @since 2.1.0
	 */
	public function test_get_slug() {

		$context = new WordPoints_PHPUnit_Mock_Entity_Context( 'test' );

		$this->assertEquals( 'test', $context->get_slug() );
	}

	/**
	 * Test getting the parent slug.
	 *
	 * @since 2.1.0
	 */
	public function test_get_parent_slug() {

		$context = new WordPoints_PHPUnit_Mock_Entity_Context( 'test' );

		$this->assertNull( $context->get_parent_slug() );

		$context->set( 'parent_slug', 'test_parent' );

		$this->assertEquals( 'test_parent', $context->get_parent_slug() );
	}

	/**
	 * Test switching to a different context.
	 *
	 * @since 2.2.0
	 */
	public function test_switch_to() {

		$context = new WordPoints_PHPUnit_Mock_Entity_Context( 'test' );

		$this->assertFalse( $context->switch_to( 1 ) );
	}

	/**
	 * Test switching back to the previous context.
	 *
	 * @since 2.2.0
	 */
	public function test_switch_back() {

		$context = new WordPoints_PHPUnit_Mock_Entity_Context( 'test' );

		$this->assertFalse( $context->switch_back() );
	}
}

// EOF
