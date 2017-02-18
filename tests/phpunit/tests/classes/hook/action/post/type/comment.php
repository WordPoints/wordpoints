<?php

/**
 * Test case for WordPoints_Hook_Action_Post_Type_Comment.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.3.0
 */

/**
 * Tests WordPoints_Hook_Action_Post_Type_Comment.
 *
 * @since 2.3.0
 *
 * @covers WordPoints_Hook_Action_Post_Type_Comment
 */
class WordPoints_Hook_Action_Post_Type_Comment_Test
	extends WordPoints_Hook_Action_Post_Type_Test {

	/**
	 * @since 2.3.0
	 */
	protected $class_name = 'WordPoints_Hook_Action_Post_Type_Comment';

	/**
	 * @since 2.3.0
	 */
	protected $base_entity_slug = 'comment\\post';

	/**
	 * Test checking if an action should fire.
	 *
	 * @since 2.3.0
	 */
	public function test_should_fire_correct_comment_type() {

		$entity = $this->get_entity();

		/** @var WordPoints_Hook_Action_Post_Type $action */
		$action = new $this->class_name(
			'test\\post'
			, array( $entity )
			, array( 'arg_index' => array( $this->base_entity_slug => 0 ) )
		);

		$this->assertTrue( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the comment type is empty.
	 *
	 * @since 2.3.0
	 */
	public function test_should_fire_empty_comment_type() {

		$entity = $this->get_entity( null, array( 'comment_type' => '' ) );

		/** @var WordPoints_Hook_Action_Post_Type $action */
		$action = new $this->class_name(
			'test\\post'
			, array( $entity )
			, array( 'arg_index' => array( $this->base_entity_slug => 0 ) )
		);

		$this->assertTrue( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire for another comment type.
	 *
	 * @since 2.3.0
	 */
	public function test_should_fire_other_comment_type() {

		$entity = $this->get_entity( null, array( 'comment_type' => 'trackback' ) );

		/** @var WordPoints_Hook_Action_Post_Type $action */
		$action = new $this->class_name(
			'test\\post'
			, array( $entity )
			, array(
				'arg_index'    => array( $this->base_entity_slug => 0 ),
				'comment_type' => 'trackback',
			)
		);

		$this->assertTrue( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the comment type is incorrect.
	 *
	 * @since 2.3.0
	 */
	public function test_should_fire_incorrect_comment_type() {

		$entity = $this->get_entity( null, array( 'comment_type' => 'trackback' ) );

		/** @var WordPoints_Hook_Action_Post_Type $action */
		$action = new $this->class_name(
			'test\\post'
			, array( $entity )
			, array( 'arg_index' => array( $this->base_entity_slug => 0 ) )
		);

		$this->assertFalse( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the comment type is incorrect.
	 *
	 * @since 2.3.0
	 */
	public function test_should_fire_comment_type_incorrect() {

		$entity = $this->get_entity();

		/** @var WordPoints_Hook_Action_Post_Type $action */
		$action = new $this->class_name(
			'test\\post'
			, array( $entity )
			, array(
				'arg_index'    => array( $this->base_entity_slug => 0 ),
				'comment_type' => 'trackback',
			)
		);

		$this->assertFalse( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the comment type is not set.
	 *
	 * @since 2.3.0
	 */
	public function test_should_fire_no_comment_type() {

		$entity = $this->get_entity();

		/** @var WordPoints_Hook_Action_Post_Type $action */
		$action = new $this->class_name(
			'test\\post'
			, array( $entity )
			, array(
				'arg_index'    => array( $this->base_entity_slug => 0 ),
				'comment_type' => false,
			)
		);

		$this->assertTrue( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the comment type is not set.
	 *
	 * @since 2.3.0
	 */
	public function test_should_fire_no_comment_type_other() {

		$entity = $this->get_entity( null, array( 'comment_type' => 'trackback' ) );

		/** @var WordPoints_Hook_Action_Post_Type $action */
		$action = new $this->class_name(
			'test\\post'
			, array( $entity )
			, array(
				'arg_index'    => array( $this->base_entity_slug => 0 ),
				'comment_type' => false,
			)
		);

		$this->assertTrue( $action->should_fire() );
	}

	/**
	 * @since 2.3.0
	 */
	protected function get_entity(
		array $post_data = null,
		array $comment_data = null
	) {

		if ( ! isset( $comment_data ) ) {
			$comment_data = array( 'comment_type' => 'comment' );
		}

		$comment_data['comment_post_ID'] = parent::get_entity( $post_data )->ID;

		return $this->factory->comment->create_and_get( $comment_data );
	}
}

// EOF
