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

		WordPoints_Rank_Types::deregister_type( 'points-credits' );
		WordPoints_Rank_Groups::deregister_group( 'points_type-credits' );

		parent::tearDown();
	}

	/**
	 * Test that the My Points widget recognizes the %ranks% placeholder.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_ranks_points_widget_text_filter
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

	/**
	 * Test the points_type attribute is supported if the points component is active.
	 *
	 * @since 1.8.0
	 *
	 * @covers ::wordpoints_user_rank_shortcode_points_type_attr
	 */
	public function test_points_type_attr_of_user_ranks_shortcode() {

		$user_id = $this->factory->user->create();

		$rank = wordpoints_get_rank(
			WordPoints_Rank_Groups::get_group( 'points_type-points' )->get_rank( 0 )
		);

		$result = wordpointstests_do_shortcode_func(
			'wordpoints_user_rank'
			, array( 'user_id' => $user_id, 'points_type' => 'points' )
		);

		$formatted_rank = wordpoints_get_formatted_user_rank(
			$user_id
			, $this->rank_group
			, 'user_rank_shortcode'
		);

		$this->assertEquals( $formatted_rank, $result );
	}

	/**
	 * Test that wordpoints_register_points_ranks() registers a group for each points type.
	 *
	 * @since 1.9.0
	 *
	 * @covers ::wordpoints_register_points_ranks
	 */
	public function test_wordpoints_register_points_ranks_group_for_each_points_type() {

		$this->set_points_types();

		wordpoints_register_points_ranks();

		$this->assertTrue(
			WordPoints_Rank_Groups::is_group_registered( 'points_type-credits' )
		);

		$this->assertEquals(
			'Credits'
			, WordPoints_Rank_Groups::get_group( 'points_type-credits' )->name
		);
	}

	/**
	 * Test that wordpoints_register_points_ranks() registers a rank type for each points type.
	 *
	 * @since 1.9.0
	 *
	 * @covers ::wordpoints_register_points_ranks
	 */
	public function test_wordpoints_register_points_ranks_type_for_each_points_type() {

		$this->set_points_types();

		wordpoints_register_points_ranks();

		$this->assertTrue(
			WordPoints_Rank_Types::is_type_registered( 'points-credits' )
		);

		$this->assertTrue(
			WordPoints_Rank_Groups::is_type_registered_for_group(
				'points-credits'
				, 'points_type-credits'
			)
		);

		$meta = WordPoints_Rank_Types::get_type( 'points-credits' )
			->get_meta_fields();

		$this->assertEquals( 'credits', $meta['points_type']['default'] );
	}

	//
	// Helpers.
	//

	/**
	 * Set the points types option in the database.
	 *
	 * @since 1.9.0
	 */
	protected function set_points_types() {

		wordpoints_update_network_option(
			'wordpoints_points_types'
			, array(
				'credits' => array(
					'name'   => 'Credits',
					'prefix' => '$',
					'suffix' => '',
				),
			)
		);
	}
}

// EOF
