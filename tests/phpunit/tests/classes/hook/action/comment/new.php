<?php

/**
 * Test case for WordPoints_Hook_Action_Comment_New.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Action_Comment_New.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Action_Comment_New
 */
class WordPoints_Hook_Action_Comment_New_Test
	extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test checking if an action should fire.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_requirements_met() {

		$comment = $this->factory->comment->create_and_get(
			array(
				'comment_approved' => '1',
				// We supply a post because some code always expects one.
				'comment_post_ID' => $this->factory->post->create(),
			)
		);

		$action = new WordPoints_Hook_Action_Comment_New(
			'test\\post'
			, array( 'a', $comment )
			, array( 'arg_index' => array( 'comment\\post' => 1 ) )
		);

		$this->assertTrue( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the requirements aren't met.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_requirements_not_met() {

		$comment = $this->factory->comment->create_and_get(
			array( 'comment_approved' => '0' )
		);

		$action = new WordPoints_Hook_Action_Comment_New(
			'test\\post'
			, array( 'a', $comment )
			, array( 'arg_index' => array( 'comment\\post' => 1 ) )
		);

		$this->assertFalse( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the post type is wrong.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_wrong_post_type() {

		$comment = $this->factory->comment->create_and_get(
			array(
				'comment_approved' => '1',
				// We supply a post because some code always expects one.
				'comment_post_ID' => $this->factory->post->create(),
			)
		);

		$action = new WordPoints_Hook_Action_Comment_New(
			'test\\page'
			, array( 'a', $comment )
			, array( 'arg_index' => array( 'comment\\post' => 1 ) )
		);

		$this->assertFalse( $action->should_fire() );
	}

	/**
	 * Test checking if the action should fire when there is no comment.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_no_comment() {

		$action = new WordPoints_Hook_Action_Comment_New(
			'test\\post'
			, array( 'a' )
		);

		$this->assertFalse( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the requirements are met.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_other_requirements_met() {

		$comment = $this->factory->comment->create_and_get(
			array(
				'comment_approved' => '1',
				'comment_post_ID' => $this->factory->post->create(),
			)
		);

		$action = new WordPoints_Hook_Action_Comment_New(
			'test\\post'
			, array( 'a', $comment )
			, array(
				'requirements' => array( 0 => 'a' ),
				'arg_index' => array( 'comment\\post' => 1 ),
			)
		);

		$this->assertTrue( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the requirements aren't met.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_other_requirements_not_met() {

		$comment = $this->factory->comment->create_and_get(
			array(
				'comment_approved' => '1',
				'comment_post_ID' => $this->factory->post->create(),
			)
		);

		$action = new WordPoints_Hook_Action_Comment_New(
			'test\\post'
			, array( 'a', $comment )
			, array(
				'requirements' => array( 0 => 'b' ),
				'arg_index' => array( 'comment\\post' => 1 ),
			)
		);

		$this->assertFalse( $action->should_fire() );
	}
}

// EOF
