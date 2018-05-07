<?php

/**
 * Test case for WordPoints_Points_Hook_Reactor.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Points_Hook_Reactor.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Points_Hook_Reactor
 */
class WordPoints_Hook_Reactor_Points_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * The reactor class being tested.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $reactor_class = 'WordPoints_Points_Hook_Reactor';

	/**
	 * The slug of the reversal extension being used in the tests.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $reversal_extension_slug = 'reversals';

	/**
	 * The reactor being used in the tests.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Points_Hook_Reactor
	 */
	protected $reactor;

	/**
	 * @since 2.1.0
	 */
	public function setUp() {

		parent::setUp();

		$this->reactor = new $this->reactor_class();
	}

	/**
	 * Test getting the settings fields.
	 *
	 * @since 2.1.0
	 */
	public function test_get_settings_fields() {

		$settings_fields = $this->reactor->get_settings_fields();

		$this->assertInternalType( 'array', $settings_fields );

		$this->assertArrayHasKey( 'points', $settings_fields );
		$this->assertArrayHasKey( 'label', $settings_fields['points'] );

		$this->assertArrayHasKey( 'log_text', $settings_fields );
		$this->assertArrayHasKey( 'label', $settings_fields['log_text'] );

		$this->assertArrayHasKey( 'description', $settings_fields );
		$this->assertArrayHasKey( 'label', $settings_fields['description'] );

	}

	/**
	 * Test validating the settings.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_valid_settings
	 *
	 * @param array $settings An array of valid settings.
	 */
	public function test_validate_settings( array $settings ) {

		$validator  = new WordPoints_Hook_Reaction_Validator( array() );
		$event_args = new WordPoints_Hook_Event_Args( array() );

		$event_args->add_entity(
			new WordPoints_PHPUnit_Mock_Entity( 'user' )
		);

		$event_args->set_validator( $validator );

		$this->create_points_type();

		$result = $this->reactor->validate_settings(
			$settings
			, $validator
			, $event_args
		);

		$this->assertFalse( $validator->had_errors() );
		$this->assertSame( array(), $validator->get_field_stack() );
		$this->assertNull( $event_args->get_current() );

		$this->assertSame( $settings, $result );
	}

	/**
	 * Provides several different sets of valid settings.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] Sets of valid settings.
	 */
	public function data_provider_valid_settings() {

		$settings = array(
			'target'      => array( 'user' ),
			'points'      => 10,
			'points_type' => 'points',
			'description' => 'Testing.',
			'log_text'    => 'Testing.',
		);

		$alt           = $settings;
		$alt['points'] = -20;

		return array(
			'positive_points' => array( $settings ),
			'negative_points' => array( $alt ),
		);
	}

	/**
	 * Test validating the settings they are invalid.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_invalid_settings
	 *
	 * @param array  $settings The settings, with one invalid or missing.
	 * @param string $invalid  The slug of the setting that is invalid or missing.
	 */
	public function test_validate_settings_invalid( array $settings, $invalid ) {

		$validator  = new WordPoints_Hook_Reaction_Validator( array() );
		$event_args = new WordPoints_Hook_Event_Args( array() );

		$event_args->add_entity(
			new WordPoints_PHPUnit_Mock_Entity( 'user' )
		);

		$event_args->set_validator( $validator );

		$this->create_points_type();

		$result = $this->reactor->validate_settings(
			$settings
			, $validator
			, $event_args
		);

		$this->assertTrue( $validator->had_errors() );

		$errors = $validator->get_errors();

		$this->assertCount( 1, $errors );
		$this->assertSame( array( $invalid ), $errors[0]['field'] );

		$this->assertSame( array(), $validator->get_field_stack() );
		$this->assertNull( $event_args->get_current() );

		$this->assertSame( $settings, $result );
	}

	/**
	 * Provides an array of possible settings, each with one invalid item.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] Every possible set of settings with one invalid item.
	 */
	public function data_provider_invalid_settings() {

		$invalid_settings = array();

		$invalid_setting_fields = array(
			'points_type' => 'invalid',
			'points'      => false,
		);

		$all_settings = array(
			'target'      => array( 'user' ),
			'points'      => 10,
			'points_type' => 'points',
			'description' => 'Testing.',
			'log_text'    => 'Testing.',
		);

		foreach ( $all_settings as $slug => $value ) {

			$invalid_settings[ "no_{$slug}" ] = array( $all_settings, $slug );

			unset( $invalid_settings[ "no_{$slug}" ][0][ $slug ] );

			if ( isset( $invalid_setting_fields[ $slug ] ) ) {
				$invalid_settings[ "invalid_{$slug}" ]             = array( $all_settings, $slug );
				$invalid_settings[ "invalid_{$slug}" ][0][ $slug ] = $invalid_setting_fields[ $slug ];
			}
		}

		return $invalid_settings;
	}

	/**
	 * Test updating the settings.
	 *
	 * @since 2.1.0
	 */
	public function test_update_settings() {

		$this->create_points_type();

		$settings = array(
			'target'      => array( 'user' ),
			'points'      => 10,
			'points_type' => 'points',
			'description' => 'Testing.',
			'log_text'    => 'Testing.',
		);

		$reaction = $this->factory->wordpoints->hook_reaction->create_and_get();

		$this->assertIsReaction( $reaction );

		$this->reactor->update_settings( $reaction, $settings );

		$this->assertSame( $settings['target'], $reaction->get_meta( 'target' ) );
		$this->assertSame( $settings['points'], $reaction->get_meta( 'points' ) );
		$this->assertSame( $settings['points_type'], $reaction->get_meta( 'points_type' ) );
		$this->assertSame( $settings['description'], $reaction->get_meta( 'description' ) );
		$this->assertSame( $settings['log_text'], $reaction->get_meta( 'log_text' ) );
	}

	/**
	 * Test checking if the target can be hit.
	 *
	 * @since 2.4.2
	 */
	public function test_can_hit() {

		$event_args = new WordPoints_Hook_Event_Args( array() );

		/** @var WordPoints_Entity_User $entity */
		$entity = wordpoints_entities()->get( 'user' );

		$user_id = $this->factory->user->create();

		$entity->set_the_value( $user_id );

		$event_args->add_entity( $entity );

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'test_fire' );
		$fire->hit();

		$this->assertTrue( $this->reactor->can_hit( $entity, $fire ) );
	}

	/**
	 * Test checking if the target can be hit when a reversal action is occurring.
	 *
	 * @since 2.4.2
	 */
	public function test_can_hit_reversal() {

		$event_args = new WordPoints_Hook_Event_Args( array() );

		/** @var WordPoints_Entity_User $entity */
		$entity = wordpoints_entities()->get( 'user' );

		$user_id = $this->factory->user->create();

		$entity->set_the_value( $user_id );

		$event_args->add_entity( $entity );

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'toggle_off' );
		$fire->hit();

		$this->assertTrue( $this->reactor->can_hit( $entity, $fire ) );
	}

	/**
	 * Test checking if the target can be hit when the entity ID isn't set.
	 *
	 * @since 2.4.2
	 */
	public function test_can_hit_value_not_set() {

		$event_args = new WordPoints_Hook_Event_Args( array() );

		/** @var WordPoints_Entity_User $entity */
		$entity = wordpoints_entities()->get( 'user' );

		$event_args->add_entity( $entity );

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'test_fire' );
		$fire->hit();

		$this->assertFalse( $this->reactor->can_hit( $entity, $fire ) );
	}

	/**
	 * Test hitting the target.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_valid_settings
	 *
	 * @param array $settings Reaction settings.
	 */
	public function test_hit( array $settings ) {

		$settings['event']   = 'user_register';
		$settings['reactor'] = 'points';

		$event_args = new WordPoints_Hook_Event_Args( array() );

		/** @var WordPoints_Entity_User $entity */
		$entity = wordpoints_entities()->get( 'user' );

		$user_id = $this->factory->user->create();

		$entity->set_the_value( $user_id );

		$event_args->add_entity( $entity );

		$this->create_points_type();

		wordpoints_set_points( $user_id, 100, 'points', 'test' );

		$this->assertSame( 100, wordpoints_get_points( $user_id, 'points' ) );

		$reaction = wordpoints_hooks()
			->get_reaction_store( 'points' )
			->create_reaction( $settings );

		$this->assertIsReaction( $reaction );

		$filter = new WordPoints_PHPUnit_Mock_Filter();
		$filter->add_filter( 'wordpoints_points_hook_reactor_points_to_award', 10, 6 );

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'test_fire' );
		$fire->hit();

		$this->reactor->hit( $fire );

		$this->assertSame(
			100 + $settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);

		$this->assertSame( 1, $filter->call_count );
		$this->assertSame( array( $settings['points'], $fire ), $filter->calls[0] );

		$query = new WordPoints_Points_Logs_Query(
			array( 'log_type' => 'user_register' )
		);

		$log = $query->get( 'row' );

		$this->assertSame( $user_id, (int) $log->user_id );
		$this->assertSame( $settings['points'], (int) $log->points );
		$this->assertSame( $settings['points_type'], $log->points_type );
		$this->assertSame( $settings['event'], $log->log_type );
		$this->assertSame( $settings['log_text'], $log->text );

		$this->assertSame(
			$user_id
			, (int) wordpoints_get_points_log_meta( $log->id, 'user', true )
		);

		$this->assertSame(
			array( 'user' => (string) $user_id )
			, wordpoints_get_points_log_meta( $log->id, 'user_guid', true )
		);

		$this->assertSame(
			$fire->hit_id
			, (int) wordpoints_get_points_log_meta( $log->id, 'hook_hit_id', true )
		);
	}

	/**
	 * Test reversing an event.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_valid_settings
	 *
	 * @param array $settings Reaction settings.
	 */
	public function test_reverse_hits( array $settings ) {

		$settings['event']   = 'user_register';
		$settings['reactor'] = 'points';
		$settings[ $this->reversal_extension_slug ] = array( 'toggle_off' => 'toggle_on' );

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

		$this->reactor->hit( $fire );

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
		$reverse_fire->data[ $this->reversal_extension_slug ]['hit_ids'] = array( $fire->hit_id );

		$this->reactor->reverse_hit( $reverse_fire );

		$this->assertSame( 1, $query->count() );

		$this->assertSame( 100, wordpoints_get_points( $user_id, 'points' ) );

		$reverse_query = new WordPoints_Points_Logs_Query(
			array( 'log_type' => 'reverse-user_register' )
		);

		$this->assertSame( 1, $reverse_query->count() );

		$reverse_log_id = $reverse_query->get( 'row' )->id;
		$log_id         = $query->get( 'row' )->id;

		$this->assertSame(
			$reverse_log_id
			, wordpoints_get_points_log_meta( $log_id, 'auto_reversed', true )
		);

		$this->assertSame(
			$fire->hit_id
			, (int) wordpoints_get_points_log_meta( $log_id, 'hook_hit_id', true )
		);

		$this->assertSame(
			$log_id
			, wordpoints_get_points_log_meta(
				$reverse_log_id
				, 'original_log_id'
				, true
			)
		);

		$this->assertSame(
			$reverse_fire->hit_id
			, (int) wordpoints_get_points_log_meta(
				$reverse_log_id
				, 'hook_hit_id'
				, true
			)
		);
	}

	/**
	 * Test that only the hits from the last fire are reversed.
	 *
	 * @since 2.1.0
	 */
	public function test_reverse_hits_only_reverses_hits_from_last_fire() {

		$this->create_points_type();

		$hooks = wordpoints_hooks();

		$points_target = $hooks->get_reaction_store( 'points' );
		$instance      = $points_target->create_reaction(
			array(
				'event'                        => 'post_publish\post',
				'reactor'                      => $this->reactor->get_slug(),
				'points'                       => 10,
				'points_type'                  => 'points',
				'target'                       => array( 'post\post', 'author', 'user' ),
				'description'                  => 'Testing.',
				'log_text'                     => 'Testing.',
				$this->reversal_extension_slug => array( 'toggle_off' => 'toggle_on' ),
			)
		);

		$this->assertInstanceOf( 'WordPoints_Hook_ReactionI', $instance );

		$user_id = $this->factory->user->create();

		$this->assertSame( 0, wordpoints_get_points( $user_id, 'points' ) );

		$post_id = $this->factory->post->create(
			array(
				'post_type'   => 'post',
				'post_author' => $user_id,
			)
		);

		$this->assertSame( 10, wordpoints_get_points( $user_id, 'points' ) );

		// Block the reactor from handling this reverse.
		$instance->update_meta( 'blocker', array( 'toggle_off' => true ) );

		wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );

		// The points shouldn't have been removed.
		$this->assertSame( 10, wordpoints_get_points( $user_id, 'points' ) );

		wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) );

		// Points should have been awarded again.
		$this->assertSame( 20, wordpoints_get_points( $user_id, 'points' ) );

		// Stop blocking reverses.
		$instance->delete_meta( 'blocker' );

		wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );

		// Only the last hit should have been reversed.
		$this->assertSame( 10, wordpoints_get_points( $user_id, 'points' ) );
	}

	/**
	 * Test that we don't explode it we get a fire without the expected data.
	 *
	 * @since 2.1.0
	 */
	public function test_reverse_hit_no_reversals_hit_ids() {

		$fire = new WordPoints_Hook_Fire(
			new WordPoints_Hook_Event_Args( array() )
			, $this->factory->wordpoints->hook_reaction->create()
			, 'toggle_off'
		);

		$this->reactor->reverse_hit( $fire );

		$fire->data[ $this->reversal_extension_slug ] = array();

		$this->reactor->reverse_hit( $fire );
	}
}

// EOF
