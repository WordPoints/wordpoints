<?php

/**
 * Test case for WordPoints_Points_Hook_Extension_Legacy_Reversals.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Points_Hook_Extension_Legacy_Reversals.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Points_Hook_Extension_Legacy_Reversals
 */
class WordPoints_Points_Hook_Extension_Legacy_Reversals_Test
	extends WordPoints_Hook_Extension_Reversals_Test {

	/**
	 * @since 2.1.0
	 */
	protected $extension_slug = 'points_legacy_reversals';

	/**
	 * @since 2.1.0
	 */
	protected $extension_class = 'WordPoints_Points_Hook_Extension_Legacy_Reversals';

	/**
	 * Test checking whether we should hit the target when there are unreversed logs.
	 *
	 * @since 2.1.0
	 */
	public function test_should_hit_no_hits_unreversed_logs() {

		$event_slug = 'test_event';

		$arg = new WordPoints_PHPUnit_Mock_Hook_Arg( 'test_entity' );

		$this->create_points_type();

		wordpoints_add_points(
			$this->factory->user->create()
			, 10
			, 'points'
			, $event_slug
			, array( $arg->get_entity_slug() => $arg->get_value() )
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create(
			array( 'event' => $event_slug )
		);

		$reaction->add_meta( 'legacy_meta_key', $arg->get_entity_slug() );
		$reaction->add_meta(
			$this->extension_slug
			, array( 'test_reverse' => 'test_fire' )
		);

		$fire = new WordPoints_Hook_Fire(
			new WordPoints_Hook_Event_Args( array( $arg ) )
			, $reaction
			, 'test_reverse'
		);

		$this->assertTrue( $this->extension->should_hit( $fire ) );
	}

	/**
	 * Test checking whether we should hit the target when there are unreversed logs.
	 *
	 * @since 2.1.0
	 */
	public function test_should_hit_no_hits_unreversed_logs_integration() {

		$this->create_points_type();

		$settings = array( 'points' => 10 );

		wordpointstests_add_points_hook( 'wordpoints_post_points_hook', $settings );

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create(
			array( 'post_author' => $user_id )
		);

		$this->assertSame(
			$settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);

		$reaction = $this->create_points_reaction(
			array(
				'event' => 'post_publish\post',
				'target' => array( 'post\post', 'author', 'user' ),
			)
		);

		$this->assertIsReaction( $reaction );

		$reaction->add_meta( 'legacy_log_type', 'post_publish' );
		$reaction->add_meta( 'legacy_meta_key', 'post_id' );
		$reaction->add_meta(
			$this->extension_slug
			, array( 'test_reverse' => 'test_fire' )
		);

		$arg = new WordPoints_PHPUnit_Mock_Hook_Arg( 'post\post' );
		$arg->value = $post_id;

		$fire = new WordPoints_Hook_Fire(
			new WordPoints_Hook_Event_Args( array( $arg ) )
			, $reaction
			, 'test_reverse'
		);

		$this->assertTrue( $this->extension->should_hit( $fire ) );
	}

	/**
	 * Test checking whether we should hit the target when all logs are reversed.
	 *
	 * @since 2.1.0
	 */
	public function test_should_hit_no_hits_no_unreversed_logs() {

		$event_slug = 'test_event';

		$arg = new WordPoints_PHPUnit_Mock_Hook_Arg( 'test_entity' );

		$this->create_points_type();

		wordpoints_add_points(
			$this->factory->user->create()
			, 10
			, 'points'
			, $event_slug
			, array(
				$arg->get_entity_slug() => $arg->get_value(),
				'auto_reversed'         => true,
			)
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create(
			array( 'event' => $event_slug )
		);

		$reaction->add_meta( 'legacy_meta_key', $arg->get_entity_slug() );
		$reaction->add_meta(
			$this->extension_slug
			, array( 'test_reverse' => 'test_fire' )
		);

		$fire = new WordPoints_Hook_Fire(
			new WordPoints_Hook_Event_Args( array( $arg ) )
			, $reaction
			, 'test_reverse'
		);

		$this->assertFalse( $this->extension->should_hit( $fire ) );
	}

	/**
	 * Test checking whether we should hit the target when there are unreversed logs
	 * for a different entity.
	 *
	 * @since 2.1.0
	 */
	public function test_should_hit_no_hits_unreversed_logs_different_entities() {

		$event_slug = 'test_event';

		$arg = new WordPoints_PHPUnit_Mock_Hook_Arg( 'test_entity' );

		$this->create_points_type();

		wordpoints_add_points(
			$this->factory->user->create()
			, 10
			, 'points'
			, $event_slug
			, array( $arg->get_entity_slug() => $arg->get_value() + 1 )
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create(
			array( 'event' => $event_slug )
		);

		$reaction->add_meta( 'legacy_meta_key', $arg->get_entity_slug() );
		$reaction->add_meta(
			$this->extension_slug
			, array( 'test_reverse' => 'test_fire' )
		);

		$fire = new WordPoints_Hook_Fire(
			new WordPoints_Hook_Event_Args( array( $arg ) )
			, $reaction
			, 'test_reverse'
		);

		$this->assertFalse( $this->extension->should_hit( $fire ) );
	}


	/**
	 * Test checking whether we should hit the target when there are unreversed logs
	 * for different stateful entities.
	 *
	 * @since 2.1.0
	 */
	public function test_should_hit_no_hits_unreversed_logs_different_stateful_entities() {

		$event_slug = 'test_event';

		$arg = new WordPoints_PHPUnit_Mock_Hook_Arg( 'test_entity' );

		$this->create_points_type();

		wordpoints_add_points(
			$this->factory->user->create()
			, 10
			, 'points'
			, $event_slug
			, array( $arg->get_entity_slug() => $arg->get_value() )
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create(
			array( 'event' => $event_slug )
		);

		$reaction->add_meta( 'legacy_meta_key', $arg->get_entity_slug() );
		$reaction->add_meta(
			$this->extension_slug
			, array( 'test_reverse' => 'test_fire' )
		);

		$another_arg = new WordPoints_PHPUnit_Mock_Hook_Arg( 'another' );
		$another_arg->is_stateful = true;

		$fire = new WordPoints_Hook_Fire(
			new WordPoints_Hook_Event_Args( array( $arg, $another_arg ) )
			, $reaction
			, 'test_reverse'
		);

		$this->assertTrue( $this->extension->should_hit( $fire ) );
	}

	/**
	 * Test checking whether we should hit the target with no legacy meta key.
	 *
	 * @since 2.1.0
	 */
	public function test_should_hit_no_hits_unreversed_logs_no_legacy_meta_key() {

		$event_slug = 'test_event';

		$arg = new WordPoints_PHPUnit_Mock_Hook_Arg( 'test_entity' );

		$this->create_points_type();

		wordpoints_add_points(
			$this->factory->user->create()
			, 10
			, 'points'
			, $event_slug
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create(
			array( 'event' => $event_slug )
		);

		$reaction->add_meta(
			$this->extension_slug
			, array( 'test_reverse' => 'test_fire' )
		);

		$fire = new WordPoints_Hook_Fire(
			new WordPoints_Hook_Event_Args( array( $arg ) )
			, $reaction
			, 'test_reverse'
		);

		$this->assertTrue( $this->extension->should_hit( $fire ) );
	}

	/**
	 * Test checking whether we should hit the target with a legacy log type.
	 *
	 * @since 2.1.0
	 */
	public function test_should_hit_no_hits_unreversed_logs_legacy_log_type() {

		$event_slug = 'test_event';

		$arg = new WordPoints_PHPUnit_Mock_Hook_Arg( 'test_entity' );

		$this->create_points_type();

		wordpoints_add_points(
			$this->factory->user->create()
			, 10
			, 'points'
			, 'legacy_event'
			, array( $arg->get_entity_slug() => $arg->get_value() )
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create(
			array( 'event' => $event_slug )
		);

		$reaction->add_meta( 'legacy_meta_key', $arg->get_entity_slug() );
		$reaction->add_meta(
			$this->extension_slug
			, array( 'test_reverse' => 'test_fire' )
		);

		$reaction->add_meta( 'legacy_log_type', 'legacy_event' );

		$fire = new WordPoints_Hook_Fire(
			new WordPoints_Hook_Event_Args( array( $arg ) )
			, $reaction
			, 'test_reverse'
		);

		$this->assertTrue( $this->extension->should_hit( $fire ) );
	}

	/**
	 * Test that it does nothing after a hit by default.
	 *
	 * @since 2.1.0
	 */
	public function test_after_miss_no_settings() {

		$event_slug = 'test_event';

		$arg = new WordPoints_PHPUnit_Mock_Hook_Arg( 'test_entity' );

		$this->create_points_type();

		$log_id = wordpoints_add_points(
			$this->factory->user->create()
			, 10
			, 'points'
			, $event_slug
			, array( $arg->get_entity_slug() => $arg->get_value() )
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create(
			array( 'event' => $event_slug )
		);

		$fire = new WordPoints_Hook_Fire(
			new WordPoints_Hook_Event_Args( array( $arg ) )
			, $reaction
			, 'test_reverse'
		);

		$this->extension->after_miss( $fire );

		$this->assertSame(
			array()
			, wordpoints_get_points_log_meta( $log_id, 'auto_reversed' )
		);
	}

	/**
	 * Test that it does nothing after a hit by default.
	 *
	 * @since 2.1.0
	 */
	public function test_after_miss() {

		$event_slug = 'test_event';

		$arg = new WordPoints_PHPUnit_Mock_Hook_Arg( 'test_entity' );

		$this->create_points_type();

		$log_id = wordpoints_add_points(
			$this->factory->user->create()
			, 10
			, 'points'
			, $event_slug
			, array( $arg->get_entity_slug() => $arg->get_value() )
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create(
			array( 'event' => $event_slug )
		);

		$reaction->add_meta( 'legacy_meta_key', $arg->get_entity_slug() );
		$reaction->add_meta(
			$this->extension_slug
			, array( 'test_reverse' => 'test_fire' )
		);

		$fire = new WordPoints_Hook_Fire(
			new WordPoints_Hook_Event_Args( array( $arg ) )
			, $reaction
			, 'test_reverse'
		);

		$this->extension->after_miss( $fire );

		$this->assertSame(
			array( '0' )
			, wordpoints_get_points_log_meta( $log_id, 'auto_reversed' )
		);
	}
}

// EOF
