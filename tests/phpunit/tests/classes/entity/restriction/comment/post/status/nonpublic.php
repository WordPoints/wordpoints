<?php

/**
 * Test case for WordPoints_Entity_Restriction_Comment_Post_Status_Nonpublic.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.2.0
 */

/**
 * Tests WordPoints_Entity_Restriction_Comment_Post_Status_Nonpublic.
 *
 * @since 2.2.0
 *
 * @covers WordPoints_Entity_Restriction_Comment_Post_Status_Nonpublic
 */
class WordPoints_Entity_Restriction_Comment_Post_Status_Nonpublic_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test that it doesn't apply when the comment post is public.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_public() {

		$restriction = new WordPoints_Entity_Restriction_Comment_Post_Status_Nonpublic(
			$this->factory->comment->create(
				array( 'comment_post_ID' => $this->factory->post->create() )
			)
			, array( 'test' )
		);

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it applies when the post is not public.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_not_public() {

		$restriction = new WordPoints_Entity_Restriction_Comment_Post_Status_Nonpublic(
			$this->factory->comment->create(
				array(
					'comment_post_ID' => $this->factory->post->create(
						array( 'post_status' => 'draft' )
					)
				)
			)
			, array( 'test' )
		);

		$this->assertTrue( $restriction->applies() );
	}

	/**
	 * Test that it applies when the comment is nonexistent.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_nonexistent() {

		$restriction = new WordPoints_Entity_Restriction_Comment_Post_Status_Nonpublic(
			0
			, array( 'test' )
		);

		$this->assertTrue( $restriction->applies() );
	}

	/**
	 * Test that it applies when the post is nonexistent.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_post_nonexistent() {

		$restriction = new WordPoints_Entity_Restriction_Comment_Post_Status_Nonpublic(
			$this->factory->comment->create( array( 'comment_post_ID' => 0 ) )
			, array( 'test' )
		);

		$this->assertTrue( $restriction->applies() );
	}

	/**
	 * Test that the user can when the post is public.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_public() {

		$restriction = new WordPoints_Entity_Restriction_Comment_Post_Status_Nonpublic(
			$this->factory->comment->create(
				array( 'comment_post_ID' => $this->factory->post->create() )
			)
			, array( 'test' )
		);

		$this->assertTrue(
			$restriction->user_can( $this->factory->user->create() )
		);
	}

	/**
	 * Test that the user can't when the post is not public.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_not_public() {

		$restriction = new WordPoints_Entity_Restriction_Comment_Post_Status_Nonpublic(
			$this->factory->comment->create(
				array(
					'comment_post_ID' => $this->factory->post->create(
						array( 'post_status' => 'draft' )
					)
				)
			)
			, array( 'test' )
		);

		$this->assertFalse(
			$restriction->user_can( $this->factory->user->create() )
		);
	}

	/**
	 * Test that the user can't when the comment is nonexistent.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_nonexistent() {

		$restriction = new WordPoints_Entity_Restriction_Comment_Post_Status_Nonpublic(
			0
			, array( 'test' )
		);

		$this->assertFalse(
			$restriction->user_can( $this->factory->user->create() )
		);
	}

	/**
	 * Test that the user can't when the post is nonexistent.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_post_nonexistent() {

		$restriction = new WordPoints_Entity_Restriction_Comment_Post_Status_Nonpublic(
			$this->factory->comment->create( array( 'comment_post_ID' => 0 ) )
			, array( 'test' )
		);

		$this->assertFalse(
			$restriction->user_can( $this->factory->user->create() )
		);
	}

	/**
	 * Test that the user can when the post is not public if they have the caps.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_not_public_has_cap() {

		$restriction = new WordPoints_Entity_Restriction_Comment_Post_Status_Nonpublic(
			$this->factory->comment->create(
				array(
					'comment_post_ID' => $this->factory->post->create(
						array( 'post_status' => 'draft' )
					)
				)
			)
			, array( 'test' )
		);

		$this->assertTrue(
			$restriction->user_can(
				$this->factory->user->create( array( 'role' => 'administrator' ) )
			)
		);
	}

	/**
	 * Test that the user can't when the comment is nonexistent.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_nonexistent_has_cap() {

		$restriction = new WordPoints_Entity_Restriction_Comment_Post_Status_Nonpublic(
			0
			, array( 'test' )
		);

		$this->assertFalse(
			$restriction->user_can(
				$this->factory->user->create( array( 'role' => 'administrator' ) )
			)
		);
	}

	/**
	 * Test that the user can't when the post is nonexistent.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_post_nonexistent_has_cap() {

		$restriction = new WordPoints_Entity_Restriction_Comment_Post_Status_Nonpublic(
			$this->factory->comment->create( array( 'comment_post_ID' => 0 ) )
			, array( 'test' )
		);

		$this->assertFalse(
			$restriction->user_can(
				$this->factory->user->create( array( 'role' => 'administrator' ) )
			)
		);
	}
}

// EOF
