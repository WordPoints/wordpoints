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
		$this->assertTrue( $sub_apps->is_registered( 'restrictions' ) );
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

		$this->assertSame( 1, $this->filter_was_called( $filter ) );

		$children = $entities->get_sub_app( 'children' );

		$this->assertTrue( $entities->is_registered( 'post\post' ) );
		$this->assertTrue( $children->is_registered( 'post\post', 'author' ) );
		$this->assertTrue( $children->is_registered( 'post\post', 'comment_count' ) );
		$this->assertTrue( $children->is_registered( 'post\post', 'content' ) );
		$this->assertTrue( $children->is_registered( 'post\post', 'date_modified' ) );
		$this->assertTrue( $children->is_registered( 'post\post', 'date_published' ) );
		$this->assertTrue( $children->is_registered( 'post\post', 'excerpt' ) );
		$this->assertTrue( $children->is_registered( 'post\post', 'title' ) );

		$this->assertTrue( $entities->is_registered( 'comment\post' ) );
		$this->assertTrue( $children->is_registered( 'comment\post', 'post\post' ) );
		$this->assertTrue( $children->is_registered( 'comment\post', 'author' ) );
		$this->assertTrue( $children->is_registered( 'comment\post', 'content' ) );
		$this->assertTrue( $children->is_registered( 'comment\post', 'date' ) );
		$this->assertTrue( $children->is_registered( 'comment\post', 'parent' ) );

		$this->assertTrue( $children->is_registered( 'post\post', 'terms\post_tag' ) );
		$this->assertTrue( $children->is_registered( 'post\post', 'terms\category' ) );
		$this->assertTrue( $children->is_registered( 'post\post', 'terms\post_format' ) );

		$this->assertTrue( $entities->is_registered( 'post\page' ) );
		$this->assertTrue( $children->is_registered( 'post\page', 'author' ) );
		$this->assertTrue( $children->is_registered( 'post\page', 'comment_count' ) );
		$this->assertTrue( $children->is_registered( 'post\page', 'content' ) );
		$this->assertTrue( $children->is_registered( 'post\page', 'date_modified' ) );
		$this->assertTrue( $children->is_registered( 'post\page', 'date_published' ) );
		$this->assertTrue( $children->is_registered( 'post\page', 'parent' ) );
		$this->assertTrue( $children->is_registered( 'post\page', 'title' ) );

		$this->assertTrue( $entities->is_registered( 'comment\page' ) );
		$this->assertTrue( $children->is_registered( 'comment\page', 'post\page' ) );
		$this->assertTrue( $children->is_registered( 'comment\page', 'author' ) );
		$this->assertTrue( $children->is_registered( 'comment\page', 'content' ) );
		$this->assertTrue( $children->is_registered( 'comment\page', 'date' ) );
		$this->assertTrue( $children->is_registered( 'comment\page', 'parent' ) );

		$this->assertTrue( $entities->is_registered( 'post\attachment' ) );
		$this->assertTrue( $children->is_registered( 'post\attachment', 'author' ) );
		$this->assertTrue( $children->is_registered( 'post\attachment', 'comment_count' ) );
		$this->assertTrue( $children->is_registered( 'post\attachment', 'date_modified' ) );
		$this->assertTrue( $children->is_registered( 'post\attachment', 'date_published' ) );
		$this->assertTrue( $children->is_registered( 'post\attachment', 'title' ) );

		$this->assertTrue( $entities->is_registered( 'comment\attachment' ) );
		$this->assertTrue( $children->is_registered( 'comment\attachment', 'post\attachment' ) );
		$this->assertTrue( $children->is_registered( 'comment\attachment', 'author' ) );
		$this->assertTrue( $children->is_registered( 'comment\attachment', 'content' ) );
		$this->assertTrue( $children->is_registered( 'comment\attachment', 'date' ) );
		$this->assertTrue( $children->is_registered( 'comment\attachment', 'parent' ) );

		$this->assertTrue( $entities->is_registered( 'term\post_tag' ) );
		$this->assertTrue( $children->is_registered( 'term\post_tag', 'count' ) );
		$this->assertTrue( $children->is_registered( 'term\post_tag', 'description' ) );
		$this->assertTrue( $children->is_registered( 'term\post_tag', 'name' ) );

		$this->assertTrue( $entities->is_registered( 'term\category' ) );
		$this->assertTrue( $children->is_registered( 'term\post_tag', 'count' ) );
		$this->assertTrue( $children->is_registered( 'term\post_tag', 'description' ) );
		$this->assertTrue( $children->is_registered( 'term\post_tag', 'name' ) );

		$this->assertTrue( $entities->is_registered( 'term\post_format' ) );
		$this->assertTrue( $children->is_registered( 'term\post_tag', 'count' ) );
		$this->assertTrue( $children->is_registered( 'term\post_tag', 'description' ) );
		$this->assertTrue( $children->is_registered( 'term\post_tag', 'name' ) );

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

		$this->assertSame(
			get_post_types( array( 'public' => true ) )
			, wordpoints_get_post_types_for_entities()
		);

		$this->assertSame( 1, $this->filter_was_called( $filter ) );
	}

	/**
	 * Test the get taxonomies for entities function.
	 *
	 * @since 2.4.0
	 *
	 * @covers ::wordpoints_get_taxonomies_for_entities
	 */
	public function test_get_taxonomies_for_entities() {

		$filter = 'wordpoints_register_entities_for_taxonomies';
		$this->listen_for_filter( $filter );

		$this->assertSame(
			get_taxonomies( array( 'public' => true ) )
			, wordpoints_get_taxonomies_for_entities()
		);

		$this->assertSame( 1, $this->filter_was_called( $filter ) );
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

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$restrictions->get_sub_app( 'view' )->register(
			'test_1'
			, array( $entity_slug )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Applicable'
		);

		$this->assertTrue(
			wordpoints_entity_user_can_view( $user_id, $entity_slug, 1 )
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

		$entity_slug = $this->factory->wordpoints->entity->create();

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$restrictions->get_sub_app( 'view' )->register(
			'test_1'
			, array( $entity_slug )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$user_id = $this->factory->user->create();

		$this->assertFalse(
			wordpoints_entity_user_can_view( $user_id, $entity_slug, 1 )
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

	/**
	 * Test the entity restrictions app init function.
	 *
	 * @since 2.2.0
	 *
	 * @covers ::wordpoints_entities_restrictions_app_init
	 */
	public function test_restrictions() {

		$restrictions = new WordPoints_Entity_Restrictions( 'restrictions' );

		wordpoints_entities_restrictions_app_init( $restrictions );

		$sub_apps = $restrictions->sub_apps();

		$this->assertTrue( $sub_apps->is_registered( 'know' ) );
		$this->assertTrue( $sub_apps->is_registered( 'view' ) );
	}

	/**
	 * Test the 'know' entity restriction registration function.
	 *
	 * @since 2.2.0
	 *
	 * @covers ::wordpoints_entity_restrictions_know_init
	 */
	public function test_know_restrictions() {

		$restrictions = new WordPoints_Class_Registry_Deep_Multilevel();

		wordpoints_entity_restrictions_know_init( $restrictions );

		$this->assertTrue( $restrictions->is_registered( 'unregistered' ) );
		$this->assertTrue( $restrictions->is_registered( 'legacy' ) );

		$this->assertTrue( $restrictions->is_registered( 'status_nonpublic', array( 'post\post' ) ) );
		$this->assertTrue( $restrictions->is_registered( 'status_nonpublic', array( 'post\page' ) ) );
		$this->assertTrue( $restrictions->is_registered( 'status_nonpublic', array( 'post\attachment' ) ) );

		$this->assertTrue( $restrictions->is_registered( 'post_status_nonpublic', array( 'comment\post' ) ) );
		$this->assertTrue( $restrictions->is_registered( 'post_status_nonpublic', array( 'comment\page' ) ) );
		$this->assertTrue( $restrictions->is_registered( 'post_status_nonpublic', array( 'comment\attachment' ) ) );
	}

	/**
	 * Test the 'view' entity restriction registration function.
	 *
	 * @since 2.2.0
	 *
	 * @covers ::wordpoints_entity_restrictions_view_init
	 */
	public function test_view_restrictions() {

		$restrictions = new WordPoints_Class_Registry_Deep_Multilevel();

		wordpoints_entity_restrictions_view_init( $restrictions );

		$this->assertTrue( $restrictions->is_registered( 'password_protected', array( 'post\post', 'content' ) ) );
		$this->assertTrue( $restrictions->is_registered( 'password_protected', array( 'post\page', 'content' ) ) );
		$this->assertTrue( $restrictions->is_registered( 'password_protected', array( 'post\attachment', 'content' ) ) );
	}
}

// EOF
