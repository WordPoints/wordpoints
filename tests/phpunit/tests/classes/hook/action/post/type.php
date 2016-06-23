<?php

/**
 * Test case for WordPoints_Hook_Action_Post_Type.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Action_Post_Type.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Action_Post_Type
 */
class WordPoints_Hook_Action_Post_Type_Test
	extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test checking if an action should fire.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_correct_post_type() {

		$post = $this->factory->post->create_and_get(
			array( 'post_type' => 'post' )
		);

		$action = new WordPoints_Hook_Action_Post_Type(
			'test\\post'
			, array( $post )
			, array( 'arg_index' => array( 'post\\post' => 0 ) )
		);

		$this->assertTrue( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_other_post_type() {

		$post = $this->factory->post->create_and_get(
			array( 'post_type' => 'page' )
		);

		$action = new WordPoints_Hook_Action_Post_Type(
			'test\\page'
			, array( $post )
			, array( 'arg_index' => array( 'post\\page' => 0 ) )
		);

		$this->assertTrue( $action->should_fire() );
	}
	/**
	 * Test checking if an action should fire when the requirements aren't met.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_incorrect_post_type() {

		$post = $this->factory->post->create_and_get(
			array( 'post_type' => 'page' )
		);

		$action = new WordPoints_Hook_Action_Post_Type(
			'test\\post'
			, array( $post )
			, array( 'arg_index' => array( 'post\\post' => 0 ) )
		);

		$this->assertFalse( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_correct_post_type_hierarchy() {

		$post = $this->factory->post->create_and_get(
			array( 'post_type' => 'post' )
		);

		$comment = $this->factory->comment->create_and_get(
			array( 'comment_post_ID' => $post->ID )
		);

		$action = new WordPoints_PHPUnit_Mock_Hook_Action_Post_Type(
			'test\\post'
			, array( $comment )
			, array( 'arg_index' => array( 'comment\\post' => 0 ) )
		);

		$action->set(
			'post_hierarchy'
			, array( 'comment\\post', 'post\\post', 'post\\post' )
		);

		$this->assertTrue( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_other_post_type_hierarchy() {

		$post = $this->factory->post->create_and_get(
			array( 'post_type' => 'page' )
		);

		$comment = $this->factory->comment->create_and_get(
			array( 'comment_post_ID' => $post->ID )
		);

		$action = new WordPoints_PHPUnit_Mock_Hook_Action_Post_Type(
			'test\\page'
			, array( $comment )
			, array( 'arg_index' => array( 'comment\\page' => 0 ) )
		);

		$action->set(
			'post_hierarchy'
			, array( 'comment\\post', 'post\\post', 'post\\post' )
		);

		$this->assertTrue( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the post type is incorrect.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_incorrect_post_type_hierarchy() {

		$post = $this->factory->post->create_and_get(
			array( 'post_type' => 'page' )
		);

		$comment = $this->factory->comment->create_and_get(
			array( 'comment_post_ID' => $post->ID )
		);

		$action = new WordPoints_PHPUnit_Mock_Hook_Action_Post_Type(
			'test\\post'
			, array( $comment )
			, array( 'arg_index' => array( 'comment\\post' => 0 ) )
		);

		$action->set(
			'post_hierarchy'
			, array( 'comment\\post', 'post\\post', 'post\\post' )
		);

		$this->assertFalse( $action->should_fire() );
	}

	/**
	 * Test checking if the action should fire when there is no dynamic slug.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_no_dynamic_slug() {

		$post = $this->factory->post->create_and_get(
			array( 'post_type' => 'post' )
		);

		$action = new WordPoints_Hook_Action_Post_Type(
			'test'
			, array( $post )
			, array( 'arg_index' => array( 'post' => 0 ) )
		);

		$this->assertFalse( $action->should_fire() );
	}

	/**
	 * Test checking if the action should fire when there is no entity registered.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_no_entity() {

		// Clears the registries.
		$this->mock_apps();

		$post = $this->factory->post->create_and_get(
			array( 'post_type' => 'post' )
		);

		$action = new WordPoints_Hook_Action_Post_Type(
			'test\\post'
			, array( $post )
			, array( 'arg_index' => array( 'post\\post' => 0 ) )
		);

		$this->assertFalse( $action->should_fire() );
	}

	/**
	 * Test checking if the action should fire when there is no post.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_no_post() {

		$action = new WordPoints_Hook_Action_Post_Type(
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

		$post = $this->factory->post->create_and_get(
			array( 'post_type' => 'post' )
		);

		$action = new WordPoints_Hook_Action_Post_Type(
			'test\\post'
			, array( $post, 'a' )
			, array(
				'arg_index' => array( 'post\\post' => 0 ),
				'requirements' => array( 1 => 'a' ),
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

		$post = $this->factory->post->create_and_get(
			array( 'post_type' => 'post' )
		);

		$action = new WordPoints_Hook_Action_Post_Type(
			'test\\post'
			, array( $post, 'b' )
			, array(
				'arg_index' => array( 'post\\post' => 0 ),
				'requirements' => array( 1 => 'a' ),
			)
		);

		$this->assertFalse( $action->should_fire() );
	}
}

// EOF
