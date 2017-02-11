<?php

/**
 * Test case for WordPoints_Entity_Post.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Entity_Post.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Entity_Post
 */
class WordPoints_Entity_Post_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test getting the title when the post type is known.
	 *
	 * @since 2.1.0
	 */
	public function test_get_title() {

		$entity = new WordPoints_Entity_Post( 'post\page' );

		$this->assertSame( __( 'Page' ), $entity->get_title() );
	}

	/**
	 * Test getting the title when the post type is unknown.
	 *
	 * @since 2.1.0
	 */
	public function test_get_title_nonexistent_post_type() {

		$entity = new WordPoints_Entity_Post( 'post\invalid' );

		$this->assertSame( 'post\invalid', $entity->get_title() );
	}
}

// EOF
