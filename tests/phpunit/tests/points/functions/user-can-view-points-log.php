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
	 * Test that the user can view the points log by default.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_user_can_view_points_log
	 */
	public function test_can_view_by_default() {

		$log = $this->factory->wordpoints->points_log->create_and_get();
		$user_id = $this->factory->user->create();

		$this->assertTrue(
			wordpoints_user_can_view_points_log( $user_id, $log )
		);
	}

	/**
	 * Test that it calls the proper filters.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_user_can_view_points_log
	 */
	public function test_calls_generic_filter() {

		$log = $this->factory->wordpoints->points_log->create_and_get();
		$user_id = $this->factory->user->create();

		$filter = new WordPoints_Mock_Filter();
		$filter->add_filter( 'wordpoints_user_can_view_points_log', 10, 6 );

		$this->assertTrue(
			wordpoints_user_can_view_points_log( $user_id, $log )
		);

		$this->assertEquals(
			array( array( true, $user_id, $log ) )
			, $filter->calls
		);
	}

	/**
	 * Test that it calls the proper filters.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_user_can_view_points_log
	 */
	public function test_calls_specific_filter() {

		$log = $this->factory->wordpoints->points_log->create_and_get();
		$user_id = $this->factory->user->create();

		$filter = new WordPoints_Mock_Filter();
		$filter->add_filter(
			"wordpoints_user_can_view_points_log-{$log->log_type}"
			, 10
			, 6
		);

		$this->assertTrue(
			wordpoints_user_can_view_points_log( $user_id, $log )
		);

		$this->assertEquals(
			array( array( true, $log, $user_id ) )
			, $filter->calls
		);
	}

	/**
	 * Test that the value from the first filter is passed to the second.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_user_can_view_points_log
	 */
	public function test_specific_filter_value_passed_to_generic_filter() {

		$log = $this->factory->wordpoints->points_log->create_and_get();
		$user_id = $this->factory->user->create();

		$generic_filter = new WordPoints_Mock_Filter();
		$generic_filter->add_filter( 'wordpoints_user_can_view_points_log', 10, 6 );

		$specific_filter = new WordPoints_Mock_Filter( false );
		$specific_filter->add_filter(
			"wordpoints_user_can_view_points_log-{$log->log_type}"
		);

		$this->assertFalse(
			wordpoints_user_can_view_points_log( $user_id, $log )
		);

		$this->assertEquals(
			array( array( false, $user_id, $log ) )
			, $generic_filter->calls
		);
	}

	/**
	 * Test it sets the user as the current user when calling the specific filter.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_user_can_view_points_log
	 */
	public function test_sets_current_user_when_calling_specific_filter() {

		$log = $this->factory->wordpoints->points_log->create_and_get();
		$user_id = $this->factory->user->create();

		$generic_filter = new WordPoints_Mock_Filter();
		$generic_filter->listen_for_current_user(
			'wordpoints_user_can_view_points_log'
		);

		$specific_filter = new WordPoints_Mock_Filter();
		$specific_filter->listen_for_current_user(
			"wordpoints_user_can_view_points_log-{$log->log_type}"
		);

		$current_user = $this->factory->user->create();
		wp_set_current_user( $current_user );

		$this->assertTrue(
			wordpoints_user_can_view_points_log( $user_id, $log )
		);

		$this->assertEquals( $user_id, $specific_filter->current_user[0] );
		$this->assertEquals( $current_user, $generic_filter->current_user[0] );
		$this->assertEquals( $current_user, get_current_user_id() );
	}

	/**
	 * Test that it returns the values returned by the filters.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_user_can_view_points_log
	 */
	public function test_returns_false_if_generic_filter_returns_false() {

		$log = $this->factory->wordpoints->points_log->create_and_get();
		$user_id = $this->factory->user->create();

		$filter = new WordPoints_Mock_Filter( false );
		$filter->add_filter( 'wordpoints_user_can_view_points_log' );

		$this->assertFalse(
			wordpoints_user_can_view_points_log( $user_id, $log )
		);
	}

	/**
	 * Test that it returns the values returned by the filters.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_user_can_view_points_log
	 */
	public function test_returns_false_if_specific_filter_returns_false() {

		$log = $this->factory->wordpoints->points_log->create_and_get();
		$user_id = $this->factory->user->create();

		$filter = new WordPoints_Mock_Filter( false );
		$filter->add_filter(
			"wordpoints_user_can_view_points_log-{$log->log_type}"
		);

		$this->assertFalse(
			wordpoints_user_can_view_points_log( $user_id, $log )
		);
	}

	/**
	 * Test the hooks API integration function returns true by default.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_hooks_user_can_view_points_log
	 */
	public function test_hooks_true_by_default() {

		$event_slug = $this->factory->wordpoints->hook_event->create();

		$user_id = $this->factory->user->create();
		$log = $this->factory->wordpoints->points_log->create_and_get(
			array( 'log_type' => $event_slug )
		);

		$this->assertTrue(
			wordpoints_hooks_user_can_view_points_log( true, $user_id, $log )
		);
	}

	/**
	 * Test the hooks API integration function returns true by default.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_hooks_user_can_view_points_log
	 */
	public function test_hooks_returns_true_for_unrecognized_event() {

		$user_id = $this->factory->user->create();
		$log = $this->factory->wordpoints->points_log->create_and_get(
			array( 'log_type' => 'not_event' )
		);

		$this->assertTrue(
			wordpoints_hooks_user_can_view_points_log( true, $user_id, $log )
		);
	}

	/**
	 * Test the hooks API integration function returns false if it was passed that.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_hooks_user_can_view_points_log
	 */
	public function test_hooks_returns_false_if_passed_false() {

		$event_slug = $this->factory->wordpoints->hook_event->create();

		$user_id = $this->factory->user->create();
		$log = $this->factory->wordpoints->points_log->create_and_get(
			array( 'log_type' => $event_slug )
		);

		$this->assertFalse(
			wordpoints_hooks_user_can_view_points_log( false, $user_id, $log )
		);
	}

	/**
	 * Test the hooks API function returns true if the user can view the entity.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_hooks_user_can_view_points_log
	 */
	public function test_hooks_can_view_entity() {

		$event_slug = $this->factory->wordpoints->hook_event->create();

		$user_id = $this->factory->user->create();
		$log = $this->factory->wordpoints->points_log->create_and_get(
			array(
				'log_type' => $event_slug,
				'log_meta' => array( 'test_entity' => 1 ),
			)
		);

		$this->assertTrue(
			wordpoints_hooks_user_can_view_points_log( true, $user_id, $log )
		);
	}

	/**
	 * Test the hooks API function returns false if the user can't view the entity.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_hooks_user_can_view_points_log
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

		$user_id = $this->factory->user->create();
		$log = $this->factory->wordpoints->points_log->create_and_get(
			array(
				'log_type' => $event_slug,
				'log_meta' => array( 'test_entity' => 1 ),
			)
		);

		$this->assertFalse(
			wordpoints_hooks_user_can_view_points_log( true, $user_id, $log )
		);
	}

	/**
	 * Test that it returns true for reverse logs if the user can view the entity.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_hooks_user_can_view_points_log
	 */
	public function test_hooks_reverse_can_view_entity() {

		$event_slug = $this->factory->wordpoints->hook_event->create();

		$user_id = $this->factory->user->create();
		$log = $this->factory->wordpoints->points_log->create_and_get(
			array(
				'log_type' => "reverse-{$event_slug}",
				'log_meta' => array( 'test_entity' => 1 ),
			)
		);

		$this->assertTrue(
			wordpoints_hooks_user_can_view_points_log( true, $user_id, $log )
		);
	}

	/**
	 * Test that it returns false for reverse logs if the user can't view the entity.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_hooks_user_can_view_points_log
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

		$user_id         = $this->factory->user->create();
		$original_log_id = $this->factory->wordpoints->points_log->create(
			array( 'log_meta' => array( 'test_entity' => 1 ) )
		);

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array(
				'log_type' => "reverse-{$event_slug}",
				'log_meta' => array( 'original_log_id' => $original_log_id ),
			)
		);

		$this->assertFalse(
			wordpoints_hooks_user_can_view_points_log( true, $user_id, $log )
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
		$user_id = $this->factory->user->create();

		$post_id = $this->factory->post->create(
			array( 'post_status' => 'publish', 'post_author' => $post_author_id )
		);

		$query = new WordPoints_Points_Logs_Query(
			array( 'order' => 'DESC', 'orderby' => 'id' )
		);

		$log = $query->get( 'row' );

		$this->assertTrue(
			wordpoints_user_can_view_points_log( $user_id, $log )
		);

		$this->assertTrue(
			wordpoints_user_can_view_points_log( $post_author_id, $log )
		);

		wp_update_post( array( 'ID' => $post_id, 'post_status' => 'private' ) );

		$this->assertFalse(
			wordpoints_user_can_view_points_log( $user_id, $log )
		);

		$this->assertTrue(
			wordpoints_user_can_view_points_log( $post_author_id, $log )
		);

		// Now also check the reverse log.
		$reverse_log = $query->get( 'row' );

		$this->assertFalse(
			wordpoints_user_can_view_points_log( $user_id, $reverse_log )
		);

		$this->assertTrue(
			wordpoints_user_can_view_points_log( $post_author_id, $reverse_log )
		);
	}
}

// EOF
