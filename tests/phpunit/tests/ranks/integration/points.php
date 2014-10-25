<?php

/**
 * A test case for integration of the Ranks and Points components.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Test that the integrations between the Ranks and Points components work.
 *
 * @since 1.7.0
 *
 * @group ranks
 * @group ranks-points
 */
class WordPoints_Ranks_Points_Integration_Test extends WordPoints_Ranks_UnitTestCase {

	/**
	 * Set up before each test.
	 *
	 * @since 1.7.0
	 */
	public function setUp() {

		parent::setUp();

		$this->create_points_type();

		WordPoints_Rank_Groups::register_group(
			'points_type-points'
			, array( 'name' => 'Points' )
		);

		WordPoints_Rank_Types::register_type(
			'points-points'
			, 'WordPoints_Points_Rank_Type'
			, array( 'points_type' => 'points' )
		);

		WordPoints_Rank_Groups::register_type_for_group(
			'points-points'
			, 'points_type-points'
		);
	}

	/**
	 * Clean up after each test.
	 *
	 * @since 1.7.0
	 */
	public function tearDown() {

		WordPoints_Rank_Types::deregister_type( 'points-points' );
		WordPoints_Rank_Groups::deregister_group( 'points_type-points' );

		parent::tearDown();
	}

	/**
	 * Test that the My Points widget recognizes the %ranks% placeholder.
	 *
	 * @since 1.7.0
	 */
	public function test_my_points_widget_ranks_placeholder() {

		$user_id = $this->factory->user->create();
		$rank_id = $this->factory->wordpoints_rank->create(
			array(
				'rank_group' => 'points_type-points',
				'type'       => 'points-points',
				'meta'       => array( 'points' => 50 ),
			)
		);

		wordpoints_set_points( $user_id, 100, 'points', 'test' );

		wp_set_current_user( $user_id );

		ob_start();
		the_widget(
			'WordPoints_My_Points_Widget'
			, array(
				'text' => 'Rank: %rank%',
				'points_type' => 'points',
				'title' => 'My Points',
			)
		);
		$widget = ob_get_clean();

		$formatted = wordpoints_format_rank(
			$rank_id
			, 'my-points-widget'
			, array( 'user_id' => $user_id )
		);

		$this->assertNotEquals( false, strpos( $widget, "Rank: {$formatted}" ) );
	}
}

// EOF
