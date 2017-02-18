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
	 * The class being tested.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	protected $class_name = 'WordPoints_Hook_Action_Post_Type';

	/**
	 * The slug of the base entity for the action.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	protected $base_entity_slug = 'post\\post';

	/**
	 * Test checking if an action should fire.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_correct_post_type() {

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
	 * Test checking if an action should fire.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_other_post_type() {

		$entity = $this->get_entity( array( 'post_type' => 'page' ) );

		$slug = str_replace( '\\post', '\\page', $this->base_entity_slug );

		/** @var WordPoints_Hook_Action_Post_Type $action */
		$action = new $this->class_name(
			'test\\page'
			, array( $entity )
			, array( 'arg_index' => array( $slug => 0 ) )
		);

		$this->assertTrue( $action->should_fire() );
	}

	/**
	 * Test checking if an action should fire when the requirements aren't met.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_incorrect_post_type() {

		$entity = $this->get_entity( array( 'post_type' => 'page' ) );

		/** @var WordPoints_Hook_Action_Post_Type $action */
		$action = new $this->class_name(
			'test\\post'
			, array( $entity )
			, array( 'arg_index' => array( $this->base_entity_slug => 0 ) )
		);

		$this->assertFalse( $action->should_fire() );
	}

	/**
	 * Test checking if the action should fire when there is no dynamic slug.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_no_dynamic_slug() {

		$entity = $this->get_entity();

		$slug = str_replace( '\\post', '', $this->base_entity_slug );

		/** @var WordPoints_Hook_Action_Post_Type $action */
		$action = new $this->class_name(
			'test'
			, array( $entity )
			, array( 'arg_index' => array( $slug => 0 ) )
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

		$entity = $this->get_entity();

		/** @var WordPoints_Hook_Action_Post_Type $action */
		$action = new $this->class_name(
			'test\\post'
			, array( $entity )
			, array( 'arg_index' => array( $this->base_entity_slug => 0 ) )
		);

		$this->assertFalse( $action->should_fire() );
	}

	/**
	 * Test checking if the action should fire when the arg for the entity isn't set.
	 *
	 * @since 2.1.0
	 */
	public function test_should_fire_no_entity_arg() {

		/** @var WordPoints_Hook_Action_Post_Type $action */
		$action = new $this->class_name(
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

		$entity = $this->get_entity();

		/** @var WordPoints_Hook_Action_Post_Type $action */
		$action = new $this->class_name(
			'test\\post'
			, array( $entity, 'a' )
			, array(
				'arg_index'    => array( $this->base_entity_slug => 0 ),
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

		$entity = $this->get_entity();

		/** @var WordPoints_Hook_Action_Post_Type $action */
		$action = new $this->class_name(
			'test\\post'
			, array( $entity, 'b' )
			, array(
				'arg_index'    => array( $this->base_entity_slug => 0 ),
				'requirements' => array( 1 => 'a' ),
			)
		);

		$this->assertFalse( $action->should_fire() );
	}

	/**
	 * Get an entity object.
	 *
	 * @since 2.3.0
	 *
	 * @param array $post_data The data for the post relating to the entity.
	 *
	 * @return object The entity object.
	 */
	protected function get_entity( array $post_data = null ) {

		if ( ! isset( $post_data ) ) {
			$post_data = array( 'post_type' => 'post' );
		}

		return $this->factory->post->create_and_get( $post_data );
	}
}

// EOF
