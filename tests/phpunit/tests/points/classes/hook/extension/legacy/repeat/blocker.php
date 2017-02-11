<?php

/**
 * Test case for WordPoints_Points_Hook_Extension_Legacy_Repeat_Blocker.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Points_Hook_Extension_Legacy_Repeat_Blocker.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Points_Hook_Extension_Legacy_Repeat_Blocker
 */
class WordPoints_Points_Hook_Extension_Legacy_Repeat_Blocker_Test
	extends WordPoints_Hook_Extension_Repeat_Blocker_Test {

	/**
	 * @since 2.1.0
	 */
	protected $extension_class = 'WordPoints_Points_Hook_Extension_Legacy_Repeat_Blocker';

	/**
	 * @since 2.1.0
	 */
	protected $extension_slug = 'points_legacy_repeat_blocker';

	/**
	 * The hook reaction being used in the tests.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_ReactionI
	 */
	protected $reaction;

	/**
	 * The entity being used in the tests.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Entity
	 */
	protected $entity;

	/**
	 * Test that repeats are detected per-log type.
	 *
	 * @since 2.1.0
	 */
	public function test_legacy_repeats_detected_per_log_type() {

		$this->fire_event_legacy();

		// Change the log type of this reaction.
		$this->reaction->update_meta( 'legacy_log_type', 'other' );

		$this->assertFireHits();
	}

	/**
	 * Test that repeats are detected per-user ID.
	 *
	 * @since 2.1.0
	 */
	public function test_legacy_repeats_detected_per_user_id() {

		$this->fire_event_legacy();

		// Change the user ID.
		$this->entity->set_the_value( $this->factory->user->create() );

		// Fire the event again.
		$this->assertFireHits();
	}

	/**
	 * Test that repeats are detected per-entity ID.
	 *
	 * @since 2.1.0
	 */
	public function test_legacy_repeats_detected_per_entity_id() {

		$this->fire_event_legacy();

		// Change the entity ID.
		$entities = $this->event_args->get_signature_args();
		reset( $entities )->set_the_value( $this->factory->user->create() );

		$this->assertFireHits();

	}

	/**
	 * Test that repeats are detected per-points type.
	 *
	 * @since 2.1.0
	 */
	public function test_legacy_repeats_detected_per_points_type() {

		$this->fire_event_legacy();

		// Change the points type.
		wordpoints_add_points_type( array( 'name' => 'Other' ) );
		$this->reaction->update_meta( 'points_type', 'other' );

		$this->assertFireHits();
	}

	/**
	 * Test that it integrates correctly with the points hooks.
	 *
	 * @since 2.1.0
	 */
	public function test_should_hit_integration() {

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
			, array( 'test_fire' => true )
		);

		$arg = new WordPoints_PHPUnit_Mock_Hook_Arg( 'post\post' );
		$arg->value = $post_id;

		$fire = new WordPoints_Hook_Fire(
			new WordPoints_Hook_Event_Args( array( $arg ) )
			, $reaction
			, 'test_fire'
		);

		$extension = new $this->extension_class();

		$this->assertFalse( $extension->should_hit( $fire ) );
	}

	//
	// Helpers.
	//

	/**
	 * Fire an event for a legacy reaction.
	 *
	 * @since 2.1.0
	 */
	protected function fire_event_legacy() {

		$this->create_points_type();

		$user_id = $this->factory->user->create();

		// Just so it will not be the same as $user_id.
		$entity_id = $this->factory->user->create();

		// First, log a points transaction.
		wordpoints_alter_points(
			$user_id
			, 10
			, 'points'
			, 'test_log_type'
			, array(
				'target:test_entity' => $user_id,
				'test_entity'        => $entity_id,
			)
		);

		$this->mock_apps();

		$this->hooks = wordpoints_hooks();
		$extensions = $this->hooks->get_sub_app( 'extensions' );
		$extensions->register( $this->extension_slug, $this->extension_class );
		$extensions->register(
			'test_extension'
			, 'WordPoints_PHPUnit_Mock_Hook_Extension'
		);

		$this->hooks->get_sub_app( 'events' )->get_sub_app( 'args' )->register(
			'test_event'
			, 'target:test_entity'
			, 'WordPoints_PHPUnit_Mock_Hook_Arg'
		);

		$this->reactor = $this->factory->wordpoints->hook_reactor->create_and_get();
		$this->reaction = $this->factory->wordpoints->hook_reaction->create(
			array(
				'target'              => array( 'target:test_entity' ),
				$this->extension_slug => array( 'test_fire' => true ),
			)
		);

		$this->reaction->update_meta( 'legacy_log_type', 'test_log_type' );
		$this->reaction->update_meta( 'legacy_meta_key', 'test_entity' );

		$arg        = new WordPoints_PHPUnit_Mock_Hook_Arg( 'test_entity' );
		$arg->value = $entity_id;

		$this->entity = new WordPoints_PHPUnit_Mock_Entity( 'target:test_entity' );
		$this->entity->set_the_value( $user_id );

		$this->event_args = new WordPoints_Hook_Event_Args( array( $arg ) );
		$this->event_args->add_entity( $this->entity );

		$this->hooks->fire( 'test_event', $this->event_args, 'test_fire' );

		// The extension should not have been checked.
		$this->extension = $extensions->get( 'test_extension' );

		$this->assertCount( 0, $this->extension->hit_checks );
		$this->assertCount( 0, $this->extension->hits );

		// The reactor should not have been hit.
		$this->assertCount( 0, $this->reactor->hits );
	}

	/**
	 * Assert that firing the hook again will hit.
	 *
	 * @since 2.1.0
	 */
	protected function assertFireHits() {

		// Fire the event again.
		$this->hooks->fire( 'test_event', $this->event_args, 'test_fire' );

		// The extension should have been checked.
		$this->assertCount( 1, $this->extension->hit_checks );
		$this->assertCount( 1, $this->extension->hits );

		// The reactor should have been hit.
		$this->assertCount( 1, $this->reactor->hits );
	}
}

// EOF
