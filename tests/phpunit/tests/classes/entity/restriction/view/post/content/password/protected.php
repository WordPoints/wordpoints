<?php

/**
 * Test case for WordPoints_Entity_Restriction_View_Post_Content_Password_Protected.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.2.0
 */

/**
 * Tests WordPoints_Entity_Restriction_View_Post_Content_Password_Protected.
 *
 * @since 2.2.0
 *
 * @covers WordPoints_Entity_Restriction_View_Post_Content_Password_Protected
 */
class WordPoints_Entity_Restriction_View_Post_Content_Password_Protected_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test that it doesn't apply when the post has no password.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_no_password() {

		$restriction = new WordPoints_Entity_Restriction_View_Post_Content_Password_Protected(
			$this->factory->post->create()
			, array( 'test' )
		);

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it applies when the post has a password.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_password() {

		$restriction = new WordPoints_Entity_Restriction_View_Post_Content_Password_Protected(
			$this->factory->post->create( array( 'post_password' => 'password' ) )
			, array( 'test' )
		);

		$this->assertTrue( $restriction->applies() );
	}

	/**
	 * Test that it doesn't apply when the post is nonexistent.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_nonexistent() {

		$restriction = new WordPoints_Entity_Restriction_View_Post_Content_Password_Protected(
			0
			, array( 'test' )
		);

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that the user can when the post has no password.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_no_password() {

		$restriction = new WordPoints_Entity_Restriction_View_Post_Content_Password_Protected(
			$this->factory->post->create()
			, array( 'test' )
		);

		$this->assertTrue(
			$restriction->user_can( $this->factory->user->create() )
		);
	}

	/**
	 * Test that the user can't when the post has a password.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_password() {

		$restriction = new WordPoints_Entity_Restriction_View_Post_Content_Password_Protected(
			$this->factory->post->create( array( 'post_password' => 'password' ) )
			, array( 'test' )
		);

		$this->assertFalse(
			$restriction->user_can( $this->factory->user->create() )
		);
	}

	/**
	 * Test that the user can when the post is nonexistent.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_nonexistent() {

		$restriction = new WordPoints_Entity_Restriction_View_Post_Content_Password_Protected(
			0
			, array( 'test' )
		);

		$this->assertTrue(
			$restriction->user_can( $this->factory->user->create() )
		);
	}
}

// EOF
