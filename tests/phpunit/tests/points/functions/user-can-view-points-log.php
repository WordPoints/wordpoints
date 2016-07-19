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
	extends WordPoints_Points_UnitTestCase {

	/**
	 * Test that the user can view the points log by default.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_user_can_view_points_log
	 */
	public function test_can_view_by_default() {

		$log = $this->factory->wordpoints_points_log->create_and_get();
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

		$log = $this->factory->wordpoints_points_log->create_and_get();
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

		$log = $this->factory->wordpoints_points_log->create_and_get();
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

		$log = $this->factory->wordpoints_points_log->create_and_get();
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

		$log = $this->factory->wordpoints_points_log->create_and_get();
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

		$log = $this->factory->wordpoints_points_log->create_and_get();
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

		$log = $this->factory->wordpoints_points_log->create_and_get();
		$user_id = $this->factory->user->create();

		$filter = new WordPoints_Mock_Filter( false );
		$filter->add_filter(
			"wordpoints_user_can_view_points_log-{$log->log_type}"
		);

		$this->assertFalse(
			wordpoints_user_can_view_points_log( $user_id, $log )
		);
	}
}

// EOF
