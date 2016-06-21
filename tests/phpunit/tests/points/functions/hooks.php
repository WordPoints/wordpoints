<?php

/**
 * Test case for the hooks functions.
 *
 * @package wordpoints-hooks-api
 * @since   1.0.0
 */

/**
 * Tests the hooks functions.
 *
 * @since 1.0.0
 */
class WordPoints_Hooks_Functions_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * @since 1.0.0
	 */
	public function tearDown() {

		parent::tearDown();

		_unregister_post_type( 'test' );
	}

	/**
	 * Test initializing the API registers the actions.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_init_hooks
	 */
	public function test_init() {

		$action = new WordPoints_Mock_Filter();

		add_action(
			'wordpoints_init_app_registry-hooks-actions'
			, array( $action, 'action' )
		);

		$this->mock_apps();

		WordPoints_App::$main = null;

		$this->assertEquals( 0, $action->call_count );

		wordpoints_init_hooks();

		$this->assertEquals( 1, $action->call_count );
	}

	/**
	 * Test getting the app.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_hooks
	 */
	public function test_get_app() {

		$this->mock_apps();

		$this->assertInstanceOf( 'WordPoints_Hooks', wordpoints_hooks() );
	}

	/**
	 * Test getting the app when the apps haven't been initialized.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_hooks
	 */
	public function test_get_app_not_initialized() {

		$this->mock_apps();

		WordPoints_App::$main = null;

		$this->assertInstanceOf( 'WordPoints_Hooks', wordpoints_hooks() );
	}

	/**
	 * Test the reactor registration function.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_hook_reactors_init
	 */
	public function test_reactors() {

		$this->mock_apps();

		$reactors = new WordPoints_Class_Registry_Persistent();

		wordpoints_hook_reactors_init( $reactors );

		$this->assertTrue( $reactors->is_registered( 'points' ) );
		$this->assertTrue( $reactors->is_registered( 'points_legacy' ) );
	}

	/**
	 * Test the reaction store registration function.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_hook_reaction_stores_init
	 *
	 * @requires WordPoints !network-active
	 */
	public function test_reaction_stores() {

		$this->mock_apps();

		$reaction_stores = new WordPoints_Class_Registry_Children();

		wordpoints_hook_reaction_stores_init( $reaction_stores );

		$this->assertTrue( $reaction_stores->is_registered( 'standard', 'points' ) );
		$this->assertFalse( $reaction_stores->is_registered( 'network', 'points' ) );
	}

	/**
	 * Test the reaction store registration function with WordPoints network active.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_hook_reaction_stores_init
	 *
	 * @requires WordPoints network-active
	 */
	public function test_reaction_stores_network_active() {

		$this->mock_apps();

		$reaction_stores = new WordPoints_Class_Registry_Children();

		wordpoints_hook_reaction_stores_init( $reaction_stores );

		$this->assertTrue( $reaction_stores->is_registered( 'standard', 'points' ) );
		$this->assertTrue( $reaction_stores->is_registered( 'network', 'points' ) );
	}

	/**
	 * Test the extension registration function.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_hook_extension_init
	 */
	public function test_extensions() {

		$this->mock_apps();

		$extensions = new WordPoints_Class_Registry_Persistent();

		wordpoints_hook_extension_init( $extensions );

		$this->assertTrue( $extensions->is_registered( 'blocker' ) );
		$this->assertTrue( $extensions->is_registered( 'repeat_blocker' ) );
		$this->assertTrue( $extensions->is_registered( 'reversals' ) );
		$this->assertTrue( $extensions->is_registered( 'conditions' ) );
		$this->assertTrue( $extensions->is_registered( 'periods' ) );
	}

	/**
	 * Test the conditions registration function.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_hook_conditions_init
	 */
	public function test_conditions() {

		$this->mock_apps();

		$conditions = new WordPoints_Class_Registry_Children();

		wordpoints_hook_conditions_init( $conditions );

		$this->assertTrue( $conditions->is_registered( 'text', 'contains' ) );
		$this->assertTrue( $conditions->is_registered( 'text', 'equals' ) );
		$this->assertTrue( $conditions->is_registered( 'entity', 'equals' ) );
		$this->assertTrue( $conditions->is_registered( 'entity_array', 'contains' ) );
	}

	/**
	 * Test the action registration function.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_hook_actions_init
	 */
	public function test_actions() {

		$this->mock_apps();

		$actions = wordpoints_hooks()->get_sub_app( 'actions' );

		$filter = 'wordpoints_register_hook_actions_for_post_types';
		$this->listen_for_filter( $filter );

		wordpoints_hook_actions_init( $actions );

		$this->assertEquals( 1, $this->filter_was_called( $filter ) );

		$this->assertTrue( $actions->is_registered( 'user_register' ) );
		$this->assertTrue( $actions->is_registered( 'user_delete' ) );
		$this->assertTrue( $actions->is_registered( 'user_visit' ) );

		$this->assertTrue( $actions->is_registered( 'post_publish\post' ) );
		$this->assertTrue( $actions->is_registered( 'post_depublish\post' ) );
		$this->assertTrue( $actions->is_registered( 'post_depublish_delete\post' ) );
		$this->assertTrue( $actions->is_registered( 'comment_approve\post' ) );
		$this->assertTrue( $actions->is_registered( 'comment_new\post' ) );
		$this->assertTrue( $actions->is_registered( 'comment_deapprove\post' ) );

		$this->assertTrue( $actions->is_registered( 'post_publish\page' ) );
		$this->assertTrue( $actions->is_registered( 'post_depublish\page' ) );
		$this->assertTrue( $actions->is_registered( 'post_depublish_delete\page' ) );
		$this->assertTrue( $actions->is_registered( 'comment_approve\page' ) );
		$this->assertTrue( $actions->is_registered( 'comment_new\page' ) );
		$this->assertTrue( $actions->is_registered( 'comment_deapprove\page' ) );

		$this->assertTrue( $actions->is_registered( 'add_attachment' ) );
		$this->assertTrue( $actions->is_registered( 'post_delete\attachment' ) );
		$this->assertTrue( $actions->is_registered( 'comment_approve\attachment' ) );
		$this->assertTrue( $actions->is_registered( 'comment_new\attachment' ) );
		$this->assertTrue( $actions->is_registered( 'comment_deapprove\attachment' ) );
	}

	/**
	 * Test the events registration function.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_hook_events_init
	 */
	public function test_events() {

		$this->mock_apps();

		$events = wordpoints_hooks()->get_sub_app( 'events' );

		$filter = 'wordpoints_register_hook_events_for_post_types';
		$this->listen_for_filter( $filter );

		wordpoints_hook_events_init( $events );

		$this->assertEquals( 1, $this->filter_was_called( $filter ) );

		$this->assert_registered( 'user_register', 'user' );
		$this->assert_registered( 'user_visit', 'current:user' );

		$this->assert_registered( 'post_publish\post', 'post\post' );
		$this->assert_registered( 'post_publish\page', 'post\page' );
		$this->assert_registered( 'media_upload', 'post\attachment' );

		$this->assert_registered( 'comment_leave\post', 'comment\post' );
		$this->assert_registered( 'comment_leave\page', 'comment\page' );
		$this->assert_registered( 'comment_leave\attachment', 'comment\attachment' );
	}

	/**
	 * Test that it registers the expected events.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_register_post_type_hook_events
	 */
	public function test_register_post_type_hook_events() {

		$this->mock_apps();

		$this->factory->wordpoints->post_type->create(
			array( 'name' => 'test', 'supports' => array( 'testing' ) )
		);

		$mock = $this->listen_for_filter(
			'wordpoints_register_post_type_hook_events'
		);

		wordpoints_register_post_type_hook_events( 'test' );

		$this->assertEquals( 1, $mock->call_count );
		$this->assertEquals( array( 'test' ), $mock->calls[0] );

		$this->assert_registered( 'post_publish\test', 'post\test' );
	}

	/**
	 * Test that it registers the expected events for an attachment.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_register_post_type_hook_events
	 */
	public function test_register_post_type_hook_events_attachment() {

		$this->mock_apps();

		wordpoints_register_post_type_hook_events( 'attachment' );

		$this->assert_not_registered( 'post_publish\attachment', 'post\attachment' );
		$this->assert_registered( 'media_upload', 'post\attachment' );
	}

	/**
	 * Test that it registers the comment entities only when comments are supported.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_register_post_type_hook_events
	 */
	public function test_supports_comments() {

		$this->mock_apps();

		$this->factory->wordpoints->post_type->create(
			array( 'name' => 'test', 'supports' => array() )
		);

		wordpoints_register_post_type_hook_events( 'test' );

		$this->assert_not_registered( 'comment_leave\test', 'comment\test' );

		add_post_type_support( 'test', 'comments' );

		wordpoints_register_post_type_hook_events( 'test' );

		$this->assert_registered( 'comment_leave\test', 'comment\test' );

	}

	/**
	 * Assert that an event is registered.
	 *
	 * @since 1.0.0
	 *
	 * @param string          $event_slug The slug of the event.
	 * @param string|string[] $arg_slugs The slugs of the args expected to be
	 *                                   registered for this event.
	 */
	protected function assert_registered( $event_slug, $arg_slugs = array() ) {

		$events = wordpoints_hooks()->get_sub_app( 'events' );

		$this->assertTrue( $events->is_registered( $event_slug ) );

		foreach ( (array) $arg_slugs as $slug ) {

			$this->assertTrue(
				$events->get_sub_app( 'args' )->is_registered( $event_slug, $slug )
				, "The {$slug} arg must be registered for the {$event_slug} event."
			);
		}
	}

	/**
	 * Assert that an event is not registered.
	 *
	 * @since 1.0.0
	 *
	 * @param string          $event_slug The slug of the event.
	 * @param string|string[] $arg_slugs The slugs of the args expected to be
	 *                                   registered for this event.
	 */
	protected function assert_not_registered( $event_slug, $arg_slugs = array() ) {

		$events = wordpoints_hooks()->get_sub_app( 'events' );

		$this->assertFalse( $events->is_registered( $event_slug ) );

		foreach ( (array) $arg_slugs as $slug ) {

			$this->assertFalse(
				$events->get_sub_app( 'args' )->is_registered( $event_slug, $slug )
				, "The {$slug} arg must not be registered for the {$event_slug} event."
			);
		}
	}
}

// EOF
