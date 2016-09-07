<?php

/**
 * Test case for WordPoints_Points_Hook_Extension_Legacy_Periods.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Points_Hook_Extension_Legacy_Periods.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Points_Hook_Extension_Legacy_Periods
 */
class WordPoints_Points_Hook_Extension_Legacy_Periods_Test
	extends WordPoints_Hook_Extension_Periods_Test {

	/**
	 * @since 2.1.0
	 */
	protected $extension_class = 'WordPoints_Points_Hook_Extension_Legacy_Periods';

	/**
	 * @since 2.1.0
	 */
	protected $extension_slug = 'points_legacy_periods';

	/**
	 * Test checking that an event will hit the target only once in a period.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_relative
	 */
	public function test_should_hit_period_started_points( $relative ) {

		$this->create_points_type();

		$settings = array( 'points' => 10, 'period' => DAY_IN_SECONDS );

		/** @var WordPoints_Periodic_Points_Hook $hook */
		$hook = wordpointstests_add_points_hook(
			'wordpoints_periodic_points_hook'
			, $settings
		);

		$user_id = $this->factory->user->create();

		wp_set_current_user( $user_id );

		$hook->hook();

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);

		$hook->delete_callback( $hook->get_id() );

		$reaction = $this->create_points_reaction(
			array(
				'event' => 'user_visit',
				'target' => array( 'current:user' ),
				'points' => $settings['points'],
				$this->extension_slug => array(
					'fire' => array(
						array(
							'length' => $settings['period'],
							'args' => array( array( 'current:user' ) ),
							'relative' => $relative,
						),
					),
				),
			)
		);

		$this->assertIsReaction( $reaction );

		do_action_ref_array( 'wp', array( &$GLOBALS['wp'] ) );

		$this->assertPeriodsExist( 0, $reaction );

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);
	}

	/**
	 * Data provider for the 'relative' period setting
	 *
	 * @since 2.1.0
	 *
	 * @return bool[][] The possible values for the 'relative' setting
	 */
	public function data_provider_relative() {
		return array(
			'absolute' => array( false ),
			'relative' => array( true ),
		);
	}

	/**
	 * Test checking that an event will hit the target only once in a period.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_relative
	 */
	public function test_should_hit_period_started_points_positive_offset( $relative ) {

		update_option( 'gmt_offset', 5 );

		$this->test_should_hit_period_started_points( $relative );
	}

	/**
	 * Test checking that an event will hit the target only once in a period.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_relative
	 */
	public function test_should_hit_period_started_points_negative_offset( $relative ) {

		update_option( 'gmt_offset', -5 );

		$this->test_should_hit_period_started_points( $relative );
	}

	/**
	 * Test checking that an event will hit the target once the period has ended.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_relative
	 */
	public function test_should_hit_period_over_points( $relative ) {

		$this->create_points_type();

		$settings = array( 'points' => 10, 'period' => DAY_IN_SECONDS );

		/** @var WordPoints_Periodic_Points_Hook $hook */
		$hook = wordpointstests_add_points_hook(
			'wordpoints_periodic_points_hook'
			, $settings
		);

		$user_id = $this->factory->user->create();

		wp_set_current_user( $user_id );

		$hook->hook();

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);

		$hook->delete_callback( $hook->get_id() );

		$reaction = $this->create_points_reaction(
			array(
				'event' => 'user_visit',
				'target' => array( 'current:user' ),
				'points' => $settings['points'],
				$this->extension_slug => array(
					'fire' => array(
						array(
							'length' => $settings['period'],
							'args' => array( array( 'current:user' ) ),
							'relative' => $relative,
						),
					),
				),
			)
		);

		$this->assertIsReaction( $reaction );

		do_action_ref_array( 'wp', array( &$GLOBALS['wp'] ) );

		$this->assertPeriodsExist( 0, $reaction );

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);

		$period = $settings['period'];

		// Calculate the time until the end of the period, using local time.
		if ( ! $relative ) {
			$period = current_time( 'timestamp' ) % $period;
		}

		// First only fast-forward part way.
		$this->fast_forward_points( $period - HOUR_IN_SECONDS );

		do_action_ref_array( 'wp', array( &$GLOBALS['wp'] ) );

		$this->assertPeriodsExist( 0, $reaction );

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);

		// Now fast-forward all the way.
		$this->fast_forward_points( $period + 1 );

		do_action_ref_array( 'wp', array( &$GLOBALS['wp'] ) );

		$this->assertPeriodsExist( 1, $reaction );

		$this->assertEquals(
			2 * $settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);
	}

	/**
	 * Test checking that an event will hit the target once the period has ended.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_relative
	 */
	public function test_should_hit_period_over_points_positive_offset( $relative ) {

		update_option( 'gmt_offset', 5 );

		$this->test_should_hit_period_over_points( $relative );
	}

	/**
	 * Test checking that an event will hit the target once the period has ended.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_relative
	 */
	public function test_should_hit_period_over_points_negative_offset( $relative ) {

		update_option( 'gmt_offset', -5 );

		$this->test_should_hit_period_over_points( $relative );
	}

	/**
	 * Test that periods are calculated per-user.
	 *
	 * @since 2.1.0
	 */
	public function test_periods_per_user_id() {

		$this->create_points_type();

		$settings = array( 'points' => 10, 'period' => DAY_IN_SECONDS );

		/** @var WordPoints_Periodic_Points_Hook $hook */
		$hook = wordpointstests_add_points_hook(
			'wordpoints_periodic_points_hook'
			, $settings
		);

		$user_id = $this->factory->user->create();

		wp_set_current_user( $user_id );

		$hook->hook();

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);

		$hook->delete_callback( $hook->get_id() );

		$reaction = $this->create_points_reaction(
			array(
				'event' => 'user_visit',
				'target' => array( 'current:user' ),
				'points' => $settings['points'],
				$this->extension_slug => array(
					'fire' => array(
						array(
							'length' => $settings['period'],
							'args' => array( array( 'current:user' ) ),
						),
					),
				),
			)
		);

		$this->assertIsReaction( $reaction );

		do_action_ref_array( 'wp', array( &$GLOBALS['wp'] ) );

		$this->assertPeriodsExist( 0, $reaction );

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);

		$user_id_2 = $this->factory->user->create();

		wp_set_current_user( $user_id_2 );

		do_action_ref_array( 'wp', array( &$GLOBALS['wp'] ) );

		$this->assertPeriodsExist( 1, $reaction );

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id_2, 'points' )
		);
	}

	/**
	 * Test that periods are calculated per-points type.
	 *
	 * @since 2.1.0
	 */
	public function test_periods_per_points_type() {

		$this->create_points_type();

		$settings = array( 'points' => 10, 'period' => DAY_IN_SECONDS );

		/** @var WordPoints_Periodic_Points_Hook $hook */
		$hook = wordpointstests_add_points_hook(
			'wordpoints_periodic_points_hook'
			, $settings
		);

		$user_id = $this->factory->user->create();

		wp_set_current_user( $user_id );

		$hook->hook();

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);

		$hook->delete_callback( $hook->get_id() );

		$reaction = $this->create_points_reaction(
			array(
				'event' => 'user_visit',
				'target' => array( 'current:user' ),
				'points' => $settings['points'],
				$this->extension_slug => array(
					'fire' => array(
						array(
							'length' => $settings['period'],
							'args' => array( array( 'current:user' ) ),
						),
					),
				),
			)
		);

		$this->assertIsReaction( $reaction );

		do_action_ref_array( 'wp', array( &$GLOBALS['wp'] ) );

		$this->assertPeriodsExist( 0, $reaction );

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);

		// Assign the reaction to another points type.
		wordpoints_add_points_type( array( 'name' => 'Another' ) );
		$reaction->update_meta( 'points_type', 'another' );

		do_action_ref_array( 'wp', array( &$GLOBALS['wp'] ) );

		$this->assertPeriodsExist( 1, $reaction );

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id, 'another' )
		);

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);
	}

	/**
	 * Test that periods are calculated per-log type.
	 *
	 * @since 2.1.0
	 */
	public function test_periods_per_log_type() {

		$this->create_points_type();

		$settings = array( 'points' => 10, 'period' => DAY_IN_SECONDS );

		/** @var WordPoints_Periodic_Points_Hook $hook */
		$hook = wordpointstests_add_points_hook(
			'wordpoints_periodic_points_hook'
			, $settings
		);

		$user_id = $this->factory->user->create();

		wp_set_current_user( $user_id );

		$hook->hook();

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);

		$hook->delete_callback( $hook->get_id() );

		$reaction = $this->create_points_reaction(
			array(
				'event' => 'user_visit',
				'target' => array( 'current:user' ),
				'points' => $settings['points'],
				$this->extension_slug => array(
					'fire' => array(
						array(
							'length' => $settings['period'],
							'args' => array( array( 'current:user' ) ),
						),
					),
				),
			)
		);

		$this->assertIsReaction( $reaction );

		do_action_ref_array( 'wp', array( &$GLOBALS['wp'] ) );

		$this->assertPeriodsExist( 0, $reaction );

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);

		// Assign the reaction to another log type.
		$reaction->update_meta( 'legacy_log_type', 'another' );

		do_action_ref_array( 'wp', array( &$GLOBALS['wp'] ) );

		$this->assertPeriodsExist( 1, $reaction );

		$this->assertEquals(
			2 * $settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);
	}

	/**
	 * Test that caching of the periods is used.
	 *
	 * @since 2.1.0
	 */
	public function test_caching() {

		$this->create_points_type();

		$settings = array( 'points' => 10, 'period' => DAY_IN_SECONDS );

		/** @var WordPoints_Periodic_Points_Hook $hook */
		$hook = wordpointstests_add_points_hook(
			'wordpoints_periodic_points_hook'
			, $settings
		);

		$user_id = $this->factory->user->create();

		wp_set_current_user( $user_id );

		$hook->hook();

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);

		$hook->delete_callback( $hook->get_id() );

		$reaction = $this->create_points_reaction(
			array(
				'event' => 'user_visit',
				'target' => array( 'current:user' ),
				'points' => $settings['points'],
				$this->extension_slug => array(
					'fire' => array(
						array(
							'length' => $settings['period'],
							'args' => array( array( 'current:user' ) ),
						),
					),
				),
			)
		);

		$this->assertIsReaction( $reaction );

		// Listen for queries.
		$point_log_queries = new WordPoints_PHPUnit_Mock_Filter();
		$point_log_queries->count_callback = array( $this, 'is_points_logs_query' );
		add_filter( 'query', array( $point_log_queries, 'filter' ) );

		do_action_ref_array( 'wp', array( &$GLOBALS['wp'] ) );

		$this->assertPeriodsExist( 0, $reaction );

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);

		$this->assertEquals( 1, $point_log_queries->call_count );

		// Run again.
		do_action_ref_array( 'wp', array( &$GLOBALS['wp'] ) );

		$this->assertPeriodsExist( 0, $reaction );

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);

		// Query should not be run again.
		$this->assertEquals( 1, $point_log_queries->call_count );
	}

	/**
	 * Travel forward in time by modifying the hit time of a points log.
	 *
	 * @since 2.1.0
	 *
	 * @param int $seconds The number of seconds to travel forward.
	 */
	protected function fast_forward_points( $seconds ) {

		global $wpdb;

		$id = $wpdb->get_var(
			"
				SELECT `id`
				FROM `{$wpdb->wordpoints_points_logs}`
				ORDER BY `id` DESC
				LIMIT 1
			"
		);

		$updated = $wpdb->update(
			$wpdb->wordpoints_points_logs
			, array(
				'date' => date(
					'Y-m-d H:i:s'
					, current_time( 'timestamp', true ) - $seconds
				),
			)
			, array( 'id' => $id )
			, array( '%s' )
			, array( '%d' )
		);

		$this->assertEquals( 1, $updated );

		// The periods cache will still hold the old date.
		$this->flush_cache();
	}
}

// EOF
