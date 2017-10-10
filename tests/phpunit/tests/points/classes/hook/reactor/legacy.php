<?php

/**
 * Test case for WordPoints_Points_Hook_Reactor_Legacy.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Points_Hook_Reactor_Legacy.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Points_Hook_Reactor_Legacy
 */
class WordPoints_Points_Hook_Reactor_Legacy_Test
	extends WordPoints_Hook_Reactor_Points_Test {

	/**
	 * @since 2.1.0
	 */
	protected $reactor_class = 'WordPoints_Points_Hook_Reactor_Legacy';

	/**
	 * @since 2.1.0
	 */
	protected $reversal_extension_slug = 'points_legacy_reversals';

	/**
	 * Test updating the settings.
	 *
	 * @since 2.1.0
	 */
	public function test_update_settings() {

		$reactor = new WordPoints_Points_Hook_Reactor_Legacy();

		$this->create_points_type();

		$settings = array(
			'target'          => array( 'user' ),
			'points'          => 10,
			'points_type'     => 'points',
			'description'     => 'Testing.',
			'log_text'        => 'Testing.',
			'legacy_log_type' => 'test',
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create_and_get();

		$this->assertIsReaction( $reaction );

		$reactor->update_settings( $reaction, $settings );

		$this->assertSame( $settings['target'], $reaction->get_meta( 'target' ) );
		$this->assertSame( $settings['points'], $reaction->get_meta( 'points' ) );
		$this->assertSame( $settings['points_type'], $reaction->get_meta( 'points_type' ) );
		$this->assertSame( $settings['description'], $reaction->get_meta( 'description' ) );
		$this->assertSame( $settings['log_text'], $reaction->get_meta( 'log_text' ) );
		$this->assertSame( $settings['legacy_log_type'], $reaction->get_meta( 'legacy_log_type' ) );
	}

	/**
	 * Test reversing an event.
	 *
	 * @since 2.1.0
	 */
	public function test_reverse_hits_legacy() {

		$settings = array(
			'target'      => array( 'user' ),
			'points'      => 10,
			'points_type' => 'points',
			'description' => 'Testing.',
			'log_text'    => 'Testing.',
		);

		$settings['event']   = 'user_register';
		$settings['reactor'] = 'points_legacy';
		$settings['points_legacy_reversals'] = array( 'toggle_off' => 'toggle_on' );

		$reactor = new WordPoints_Points_Hook_Reactor_Legacy();

		$user_id = $this->factory->user->create();

		$arg        = new WordPoints_PHPUnit_Mock_Hook_Arg( 'user' );
		$arg->value = $user_id;

		$event_args = new WordPoints_Hook_Event_Args( array( $arg ) );

		$this->create_points_type();

		wordpoints_set_points( $user_id, 100, 'points', 'test' );

		$this->assertSame( 100, wordpoints_get_points( $user_id, 'points' ) );

		$reaction = wordpoints_hooks()
			->get_reaction_store( 'points' )
			->create_reaction( $settings );

		$this->assertIsReaction( $reaction );

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'toggle_on' );
		$fire->hit();

		$reactor->hit( $fire );

		$this->assertSame(
			100 + $settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);

		$query = new WordPoints_Points_Logs_Query(
			array( 'log_type' => 'user_register' )
		);

		$this->assertSame( 1, $query->count() );

		$reverse_fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'toggle_off' );
		$reverse_fire->hit();
		$reverse_fire->data[ $this->reversal_extension_slug ]['points_logs'] = $query->get();

		$reactor->reverse_hit( $reverse_fire );

		$this->assertSame( 1, $query->count() );

		$this->assertSame( 100, wordpoints_get_points( $user_id, 'points' ) );

		$query = new WordPoints_Points_Logs_Query(
			array( 'log_type' => 'reverse-user_register' )
		);

		$this->assertSame( 1, $query->count() );
	}

	/**
	 * Test reversing an event marks it as such so it isn't reversed a second time.
	 *
	 * @since 2.1.0
	 */
	public function test_reverse_hit_only_reverses_once() {

		$points = 10;

		$user_id = $this->factory->user->create();

		$this->create_points_type();

		wordpoints_set_points( $user_id, 100, 'points', 'test' );

		$this->assertSame( 100, wordpoints_get_points( $user_id, 'points' ) );

		$reaction = wordpoints_hooks()
			->get_reaction_store( 'points' )
			->create_reaction(
				array(
					'event'                   => 'user_register',
					'reactor'                 => 'points_legacy',
					'target'                  => array( 'user' ),
					'points'                  => $points,
					'points_type'             => 'points',
					'description'             => 'Testing.',
					'log_text'                => 'Testing.',
					'points_legacy_reversals' => array(
						'toggle_off' => 'toggle_on',
					),
				)
			);

		$this->assertIsReaction( $reaction );

		// Simulate a legacy points hook fire.
		wordpoints_alter_points(
			$user_id
			, $points
			, 'points'
			, 'user_register'
			, array( 'user' => $user_id )
		);

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'     => 'user_register',
				'meta_key'     => 'auto_reversed',
				'meta_compare' => 'NOT EXISTS',
			)
		);

		$this->assertSame( 1, $query->count() );

		// Reverse fire once.
		$arg        = new WordPoints_PHPUnit_Mock_Hook_Arg( 'user' );
		$arg->value = $user_id;

		$event_args = new WordPoints_Hook_Event_Args( array( $arg ) );

		wordpoints_hooks()->fire( 'user_register', $event_args, 'toggle_off' );

		$this->assertSame( 0, $query->count() );

		$this->assertSame( 100, wordpoints_get_points( $user_id, 'points' ) );

		$reverse_query = new WordPoints_Points_Logs_Query(
			array( 'log_type' => 'reverse-user_register' )
		);

		$this->assertSame( 1, $reverse_query->count() );

		// Reverse fire a second time.
		wordpoints_hooks()->fire( 'user_register', $event_args, 'toggle_off' );

		$this->assertSame( 0, $query->count() );

		// A second reverse should not have occurred.
		$this->assertSame( 100, wordpoints_get_points( $user_id, 'points' ) );

		$this->assertSame( 1, $reverse_query->count() );
	}
}

// EOF
