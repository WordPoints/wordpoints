<?php

/**
 * Test case for the user can view points log functions.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests the user can view points log functions.
 *
 * @since 2.1.0
 */
class WordPoints_Points_User_Can_View_Points_Log_Functions_Test
	extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * @since 2.2.0
	 */
	protected $shared_fixtures = array(
		'user' => 1,
		'points_log' => array( 'get' => true ),
	);

	/**
	 * Test that the user can view the points log by default.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_user_can_view_points_log
	 */
	public function test_can_view_by_default() {

		$log = $this->fixtures['points_log'][0];

		$this->assertTrue(
			wordpoints_user_can_view_points_log( $this->fixture_ids['user'][0], $log )
		);
	}

	/**
	 * Test that it calls the proper filters.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_user_can_view_points_log
	 *
	 * @expectedDeprecated wordpoints_user_can_view_points_log
	 */
	public function test_calls_generic_filter() {

		$log = $this->fixtures['points_log'][0];

		$filter = new WordPoints_PHPUnit_Mock_Filter();
		$filter->add_filter( 'wordpoints_user_can_view_points_log', 10, 6 );

		$this->assertTrue(
			wordpoints_user_can_view_points_log( $this->fixture_ids['user'][0], $log )
		);

		$this->assertEquals(
			array( array( true, $this->fixture_ids['user'][0], $log ) )
			, $filter->calls
		);
	}

	/**
	 * Test that it calls the proper filters.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_user_can_view_points_log
	 *
	 * @expectedDeprecated wordpoints_user_can_view_points_log-test
	 */
	public function test_calls_specific_filter() {

		$log = $this->fixtures['points_log'][0];

		$filter = new WordPoints_PHPUnit_Mock_Filter();
		$filter->add_filter(
			"wordpoints_user_can_view_points_log-{$log->log_type}"
			, 10
			, 6
		);

		$this->assertTrue(
			wordpoints_user_can_view_points_log( $this->fixture_ids['user'][0], $log )
		);

		$this->assertEquals(
			array( array( true, $log, $this->fixture_ids['user'][0] ) )
			, $filter->calls
		);
	}

	/**
	 * Test that the value from the first filter is passed to the second.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_user_can_view_points_log
	 *
	 * @expectedDeprecated wordpoints_user_can_view_points_log
	 * @expectedDeprecated wordpoints_user_can_view_points_log-test
	 */
	public function test_specific_filter_value_passed_to_generic_filter() {

		$log = $this->fixtures['points_log'][0];

		$generic_filter = new WordPoints_PHPUnit_Mock_Filter();
		$generic_filter->add_filter( 'wordpoints_user_can_view_points_log', 10, 6 );

		$specific_filter = new WordPoints_PHPUnit_Mock_Filter( false );
		$specific_filter->add_filter(
			"wordpoints_user_can_view_points_log-{$log->log_type}"
		);

		$this->assertFalse(
			wordpoints_user_can_view_points_log( $this->fixture_ids['user'][0], $log )
		);

		$this->assertEquals(
			array( array( false, $this->fixture_ids['user'][0], $log ) )
			, $generic_filter->calls
		);
	}

	/**
	 * Test it sets the user as the current user when calling the specific filter.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_user_can_view_points_log
	 *
	 * @expectedDeprecated wordpoints_user_can_view_points_log
	 * @expectedDeprecated wordpoints_user_can_view_points_log-test
	 */
	public function test_sets_current_user_when_calling_specific_filter() {

		$log = $this->fixtures['points_log'][0];

		$generic_filter = new WordPoints_PHPUnit_Mock_Filter();
		$generic_filter->listen_for_current_user(
			'wordpoints_user_can_view_points_log'
		);

		$specific_filter = new WordPoints_PHPUnit_Mock_Filter();
		$specific_filter->listen_for_current_user(
			"wordpoints_user_can_view_points_log-{$log->log_type}"
		);

		$current_user = $this->factory->user->create();
		wp_set_current_user( $current_user );

		$this->assertTrue(
			wordpoints_user_can_view_points_log( $this->fixture_ids['user'][0], $log )
		);

		$this->assertEquals( $this->fixture_ids['user'][0], $specific_filter->current_user[0] );
		$this->assertEquals( $current_user, $generic_filter->current_user[0] );
		$this->assertEquals( $current_user, get_current_user_id() );
	}

	/**
	 * Test that it returns the values returned by the filters.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_user_can_view_points_log
	 *
	 * @expectedDeprecated wordpoints_user_can_view_points_log
	 */
	public function test_returns_false_if_generic_filter_returns_false() {

		$log = $this->fixtures['points_log'][0];

		$filter = new WordPoints_PHPUnit_Mock_Filter( false );
		$filter->add_filter( 'wordpoints_user_can_view_points_log' );

		$this->assertFalse(
			wordpoints_user_can_view_points_log( $this->fixture_ids['user'][0], $log )
		);
	}

	/**
	 * Test that it returns the values returned by the filters.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_user_can_view_points_log
	 *
	 * @expectedDeprecated wordpoints_user_can_view_points_log-test
	 */
	public function test_returns_false_if_specific_filter_returns_false() {

		$log = $this->fixtures['points_log'][0];

		$filter = new WordPoints_PHPUnit_Mock_Filter( false );
		$filter->add_filter(
			"wordpoints_user_can_view_points_log-{$log->log_type}"
		);

		$this->assertFalse(
			wordpoints_user_can_view_points_log( $this->fixture_ids['user'][0], $log )
		);
	}

	/**
	 * Test that the user can't when some of the restrictions apply to them.
	 *
	 * @since 2.2.0
	 */
	public function test_returns_false_if_some_restricted() {

		$log = $this->fixtures['points_log'][0];

		$this->mock_apps();

		/** @var WordPoints_Points_Logs_Viewing_Restrictions $restrictions */
		$restrictions = wordpoints_component( 'points' )
			->get_sub_app( 'logs' )
			->get_sub_app( 'viewing_restrictions' );

		$restrictions->register(
			'test'
			, 'test_1'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Not_Applicable'
		);

		$restrictions->register(
			'test'
			, 'test_2'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction'
		);

		$this->assertFalse(
			wordpoints_user_can_view_points_log( $this->fixture_ids['user'][0], $log )
		);
	}

	/**
	 * Test the hooks API integration function returns true by default.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_hooks_user_can_view_points_log
	 *
	 * @expectedDeprecated wordpoints_hooks_user_can_view_points_log
	 */
	public function test_hooks_true_by_default() {

		$event_slug = $this->factory->wordpoints->hook_event->create();

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array( 'log_type' => $event_slug )
		);

		$this->assertTrue(
			wordpoints_hooks_user_can_view_points_log( true, $this->fixture_ids['user'][0], $log )
		);
	}

	/**
	 * Test the hooks API integration function returns true by default.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_hooks_user_can_view_points_log
	 *
	 * @expectedDeprecated wordpoints_hooks_user_can_view_points_log
	 */
	public function test_hooks_returns_true_for_unrecognized_event() {

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array( 'log_type' => 'not_event' )
		);

		$this->assertTrue(
			wordpoints_hooks_user_can_view_points_log( true, $this->fixture_ids['user'][0], $log )
		);
	}

	/**
	 * Test the hooks API integration function returns false if it was passed that.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_hooks_user_can_view_points_log
	 *
	 * @expectedDeprecated wordpoints_hooks_user_can_view_points_log
	 */
	public function test_hooks_returns_false_if_passed_false() {

		$event_slug = $this->factory->wordpoints->hook_event->create();

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array( 'log_type' => $event_slug )
		);

		$this->assertFalse(
			wordpoints_hooks_user_can_view_points_log( false, $this->fixture_ids['user'][0], $log )
		);
	}

	/**
	 * Test the hooks API function returns true if the user can view the entity.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_hooks_user_can_view_points_log
	 *
	 * @expectedDeprecated wordpoints_hooks_user_can_view_points_log
	 */
	public function test_hooks_can_view_entity() {

		$event_slug = $this->factory->wordpoints->hook_event->create();

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array(
				'log_type' => $event_slug,
				'log_meta' => array( 'test_entity' => 1 ),
			)
		);

		wordpoints_entity_restrictions_know_init(
			wordpoints_entities()
				->get_sub_app( 'restrictions' )
				->get_sub_app( 'know' )
		);

		$this->assertTrue(
			wordpoints_hooks_user_can_view_points_log( true, $this->fixture_ids['user'][0], $log )
		);
	}

	/**
	 * Test the hooks API function returns false if the user can't view the entity.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_hooks_user_can_view_points_log
	 *
	 * @expectedDeprecated wordpoints_hooks_user_can_view_points_log
	 * @expectedDeprecated WordPoints_Entity_Restriction_Legacy::__construct
	 */
	public function test_hooks_cannot_view_entity() {

		WordPoints_PHPUnit_Mock_Entity_Restricted_Visibility::$can_view = false;

		$this->factory->wordpoints->entity->create(
			array(
				'slug' => 'test_entity',
				'class' => 'WordPoints_PHPUnit_Mock_Entity_Restricted_Visibility',
			)
		);

		$event_slug = $this->factory->wordpoints->hook_event->create();

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array(
				'log_type' => $event_slug,
				'log_meta' => array( 'test_entity' => 1 ),
			)
		);

		wordpoints_entity_restrictions_know_init(
			wordpoints_entities()
				->get_sub_app( 'restrictions' )
				->get_sub_app( 'know' )
		);

		$this->assertFalse(
			wordpoints_hooks_user_can_view_points_log( true, $this->fixture_ids['user'][0], $log )
		);
	}

	/**
	 * Test that it returns true for reverse logs if the user can view the entity.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_hooks_user_can_view_points_log
	 *
	 * @expectedDeprecated wordpoints_hooks_user_can_view_points_log
	 */
	public function test_hooks_reverse_can_view_entity() {

		$event_slug = $this->factory->wordpoints->hook_event->create();

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array(
				'log_type' => "reverse-{$event_slug}",
				'log_meta' => array( 'test_entity' => 1 ),
			)
		);

		wordpoints_entity_restrictions_know_init(
			wordpoints_entities()
				->get_sub_app( 'restrictions' )
				->get_sub_app( 'know' )
		);

		$this->assertTrue(
			wordpoints_hooks_user_can_view_points_log( true, $this->fixture_ids['user'][0], $log )
		);
	}

	/**
	 * Test that it returns false for reverse logs if the user can't view the entity.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_hooks_user_can_view_points_log
	 *
	 * @expectedDeprecated wordpoints_hooks_user_can_view_points_log
	 * @expectedDeprecated WordPoints_Entity_Restriction_Legacy::__construct
	 */
	public function test_hooks_reverse_cannot_view_entity() {

		WordPoints_PHPUnit_Mock_Entity_Restricted_Visibility::$can_view = false;

		$this->factory->wordpoints->entity->create(
			array(
				'slug' => 'test_entity',
				'class' => 'WordPoints_PHPUnit_Mock_Entity_Restricted_Visibility',
			)
		);

		$event_slug = $this->factory->wordpoints->hook_event->create();

		$this->fixture_ids['user'][0] = $this->factory->user->create();
		$original_log_id              = $this->factory->wordpoints->points_log->create(
			array( 'log_meta' => array( 'test_entity' => 1 ) )
		);

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array(
				'log_type' => "reverse-{$event_slug}",
				'log_meta' => array( 'original_log_id' => $original_log_id ),
			)
		);

		wordpoints_entity_restrictions_know_init(
			wordpoints_entities()
				->get_sub_app( 'restrictions' )
				->get_sub_app( 'know' )
		);

		$this->assertFalse(
			wordpoints_hooks_user_can_view_points_log( true, $this->fixture_ids['user'][0], $log )
		);
	}

	/**
	 * Test the hooks API function is hooked into the filter and integrated properly.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_hooks_user_can_view_points_log
	 */
	public function test_hooks_integration() {

		$reaction = $this->create_points_reaction(
			array(
				'event' => 'post_publish\post',
				'target' => array( 'post\post', 'author', 'user' ),
			)
		);

		$this->assertIsReaction( $reaction );

		$post_author_id = $this->factory->user->create();

		$post_id = $this->factory->post->create(
			array( 'post_status' => 'publish', 'post_author' => $post_author_id )
		);

		$query = new WordPoints_Points_Logs_Query(
			array( 'order' => 'DESC', 'order_by' => 'id' )
		);

		$log = $query->get( 'row' );

		$this->assertTrue(
			wordpoints_user_can_view_points_log( $this->fixture_ids['user'][0], $log )
		);

		$this->assertTrue(
			wordpoints_user_can_view_points_log( $post_author_id, $log )
		);

		wp_update_post( array( 'ID' => $post_id, 'post_status' => 'private' ) );

		$this->assertFalse(
			wordpoints_user_can_view_points_log( $this->fixture_ids['user'][0], $log )
		);

		$this->assertTrue(
			wordpoints_user_can_view_points_log( $post_author_id, $log )
		);

		// Now also check the reverse log.
		$reverse_log = $query->get( 'row' );

		$this->assertFalse(
			wordpoints_user_can_view_points_log( $this->fixture_ids['user'][0], $reverse_log )
		);

		$this->assertTrue(
			wordpoints_user_can_view_points_log( $post_author_id, $reverse_log )
		);
	}
}

// EOF
