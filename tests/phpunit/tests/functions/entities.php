<?php

/**
 * Test case for the entities functions.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests the entities functions.
 *
 * @since 2.1.0
 */
class WordPoints_Entities_Functions_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test initializing the API.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_entities_app_init
	 */
	public function test_init() {

		$entities = new WordPoints_App_Registry( 'entities' );

		wordpoints_entities_app_init( $entities );

		$sub_apps = $entities->sub_apps();

		$this->assertTrue( $sub_apps->is_registered( 'children' ) );
		$this->assertTrue( $sub_apps->is_registered( 'contexts' ) );
	}

	/**
	 * Test getting the app.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_entities
	 */
	public function test_get_app() {

		$this->mock_apps();

		$this->assertInstanceOf( 'WordPoints_App_Registry', wordpoints_entities() );
	}

	/**
	 * Test getting the app when the apps haven't been initialized.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_entities
	 */
	public function test_get_app_not_initialized() {

		$this->mock_apps();

		WordPoints_App::$main = null;

		$this->assertInstanceOf( 'WordPoints_App_Registry', wordpoints_entities() );
	}

	/**
	 * Test the entity registration function.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_entities_init
	 */
	public function test_entities() {

		$this->mock_apps();

		$entities = wordpoints_entities();

		$filter = 'wordpoints_register_entities_for_post_types';
		$this->listen_for_filter( $filter );

		wordpoints_entities_init( $entities );

		$this->assertEquals( 1, $this->filter_was_called( $filter ) );

		$children = $entities->get_sub_app( 'children' );

		$this->assertTrue( $entities->is_registered( 'post\post' ) );
		$this->assertTrue( $children->is_registered( 'post\post', 'content' ) );
		$this->assertTrue( $children->is_registered( 'post\post', 'author' ) );

		$this->assertTrue( $entities->is_registered( 'comment\post' ) );
		$this->assertTrue( $children->is_registered( 'comment\post', 'post\post' ) );
		$this->assertTrue( $children->is_registered( 'comment\post', 'author' ) );

		$this->assertTrue( $entities->is_registered( 'post\page' ) );
		$this->assertTrue( $children->is_registered( 'post\page', 'content' ) );
		$this->assertTrue( $children->is_registered( 'post\page', 'author' ) );

		$this->assertTrue( $entities->is_registered( 'comment\page' ) );
		$this->assertTrue( $children->is_registered( 'comment\page', 'post\page' ) );
		$this->assertTrue( $children->is_registered( 'comment\page', 'author' ) );

		$this->assertTrue( $entities->is_registered( 'post\attachment' ) );
		$this->assertTrue( $children->is_registered( 'post\attachment', 'author' ) );

		$this->assertTrue( $entities->is_registered( 'comment\attachment' ) );
		$this->assertTrue( $children->is_registered( 'comment\attachment', 'post\attachment' ) );
		$this->assertTrue( $children->is_registered( 'comment\attachment', 'author' ) );

		$this->assertTrue( $entities->is_registered( 'user' ) );
		$this->assertTrue( $children->is_registered( 'user', 'roles' ) );

		$this->assertTrue( $entities->is_registered( 'user_role' ) );
	}

	/**
	 * Test the get post types for entities function.
	 *
	 * @since 2.2.0
	 *
	 * @covers ::wordpoints_get_post_types_for_entities
	 */
	public function test_get_post_types_for_entities() {

		$filter = 'wordpoints_register_entities_for_post_types';
		$this->listen_for_filter( $filter );

		$this->assertEquals(
			get_post_types( array( 'public' => true ) )
			, wordpoints_get_post_types_for_entities()
		);

		$this->assertEquals( 1, $this->filter_was_called( $filter ) );
	}

	/**
	 * Test the entity user capability check function.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_entity_user_can_view
	 */
	public function test_user_can_view() {

		$user_id = $this->factory->user->create();

		$entity_slug = $this->factory->wordpoints->entity->create();

		$this->listen_for_filter( 'wordpoints_entity_user_can_view' );

		$this->assertTrue(
			wordpoints_entity_user_can_view( $user_id, $entity_slug, 1 )
		);

		$this->assertEquals(
			1
			, $this->filter_was_called( 'wordpoints_entity_user_can_view' )
		);

		add_filter( 'wordpoints_entity_user_can_view', '__return_false' );

		$this->assertFalse(
			wordpoints_entity_user_can_view( $user_id, $entity_slug, 1 )
		);
	}

	/**
	 * Test checking if an unregistered entity can be viewed.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_entity_user_can_view
	 */
	public function test_user_can_view_not_registered() {

		$this->mock_apps();

		$user_id = $this->factory->user->create();

		$this->listen_for_filter( 'wordpoints_entity_user_can_view' );

		$this->assertFalse(
			wordpoints_entity_user_can_view( $user_id, 'test_entity', 1 )
		);

		$this->assertEquals(
			0
			, $this->filter_was_called( 'wordpoints_entity_user_can_view' )
		);
	}

	/**
	 * Test an entity that isn't an entity can be viewed.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_entity_user_can_view
	 */
	public function test_user_can_view_not_entity() {

		$this->mock_apps();

		$user_id = $this->factory->user->create();

		wordpoints_entities()->register(
			'test_entity'
			, 'WordPoints_PHPUnit_Mock_Entityish'
		);

		$this->listen_for_filter( 'wordpoints_entity_user_can_view' );

		$this->assertFalse(
			wordpoints_entity_user_can_view( $user_id, 'test_entity', 1 )
		);

		$this->assertEquals(
			0
			, $this->filter_was_called( 'wordpoints_entity_user_can_view' )
		);
	}

	/**
	 * Test checking if a restricted entity can be viewed.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_entity_user_can_view
	 */
	public function test_user_can_view_restricted() {

		$this->mock_apps();

		$user_id = $this->factory->user->create();

		wordpoints_entities()->register(
			'test_entity'
			, 'WordPoints_PHPUnit_Mock_Entity_Restricted_Visibility'
		);

		$this->listen_for_filter( 'wordpoints_entity_user_can_view' );

		$this->assertTrue(
			wordpoints_entity_user_can_view( $user_id, 'test_entity', 1 )
		);

		$this->assertEquals(
			1
			, $this->filter_was_called( 'wordpoints_entity_user_can_view' )
		);

		add_filter( 'wordpoints_entity_user_can_view', '__return_false' );

		$this->assertFalse(
			wordpoints_entity_user_can_view( $user_id, 'test_entity', 1 )
		);
	}

	/**
	 * Test checking if a restricted entity can be viewed.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_entity_user_can_view
	 */
	public function test_user_can_view_restricted_not_viewable() {

		$this->mock_apps();

		$user_id = $this->factory->user->create();

		wordpoints_entities()->register(
			'test_entity'
			, 'WordPoints_PHPUnit_Mock_Entity_Restricted_Visibility'
		);

		WordPoints_PHPUnit_Mock_Entity_Restricted_Visibility::$can_view = false;

		$this->listen_for_filter( 'wordpoints_entity_user_can_view' );

		$this->assertFalse(
			wordpoints_entity_user_can_view( $user_id, 'test_entity', 1 )
		);

		$this->assertEquals(
			1
			, $this->filter_was_called( 'wordpoints_entity_user_can_view' )
		);

		add_filter( 'wordpoints_entity_user_can_view', '__return_true' );

		$this->assertTrue(
			wordpoints_entity_user_can_view( $user_id, 'test_entity', 1 )
		);
	}

	/**
	 * Test the entity context registration function.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_entity_contexts_init
	 */
	public function test_contexts() {

		$this->mock_apps();

		$entities = wordpoints_entities();
		$contexts = $entities->get_sub_app( 'contexts' );

		wordpoints_entity_contexts_init( $contexts );

		$this->assertTrue( $contexts->is_registered( 'network' ) );
		$this->assertTrue( $contexts->is_registered( 'site' ) );
	}
}

// EOF
