<?php

/**
 * Hook event test case class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Parent test case for testing a hook event.
 *
 * @since 2.1.0
 */
abstract class WordPoints_PHPUnit_TestCase_Hook_Event extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * The class of the event being tested.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $event_class;

	/**
	 * The slug of the event being tested.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $event_slug;

	/**
	 * A list of targets which are expected to be tested.
	 *
	 * This helps us make sure that the tests are actually testing the most common use-
	 * cases.
	 *
	 * @since 2.1.0
	 *
	 * @var string[][]
	 */
	protected $expected_targets = array();

	/**
	 * Whether the event is reversible.
	 *
	 * @since 2.1.0
	 *
	 * @var bool
	 */
	protected $is_reversible = true;

	/**
	 * An instance of the event being tested.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_EventI
	 */
	protected $event;

	/**
	 * Shortcut to the hooks app.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hooks
	 */
	protected $hooks;

	/**
	 * A list of targets which are expected to be tested.
	 *
	 * @since 2.1.0
	 *
	 * @var string[][]
	 */
	protected static $_expected_targets = array();

	/**
	 * The targets that have been tested.
	 *
	 * @since 2.1.0
	 *
	 * @var string[][]
	 */
	protected static $tested_targets = array();

	/**
	 * @since 2.1.0
	 */
	public static function tearDownAfterClass() {

		parent::tearDownAfterClass();

		foreach ( self::$_expected_targets as $expected_target ) {
			if ( ! in_array( $expected_target, self::$tested_targets, true ) ) {
				self::fail(
					'Expected target not tested: '
					. self::target_implode( $expected_target ) . PHP_EOL . PHP_EOL
					. 'Tested targets:' . PHP_EOL
					. implode(
						PHP_EOL
						, array_map(
							array( __CLASS__, 'target_implode' )
							, self::$tested_targets
						)
					)
				);
			}
		}

		self::$tested_targets = array();
	}

	/**
	 * Create a human-readable string from a target hierarchy.
	 *
	 * @since 2.1.0
	 *
	 * @param string[] $target A target hierarchy.
	 *
	 * @return string The target in human-readable format.
	 */
	protected static function target_implode( $target ) {
		return implode( ' » ', $target );
	}

	/**
	 * @since 2.1.0
	 */
	public function setUp() {

		parent::setUp();

		$this->event = new $this->event_class( $this->event_slug );
		$this->hooks = wordpoints_hooks();
	}

	/**
	 * Test getting the title.
	 *
	 * @since 2.1.0
	 */
	public function test_get_title() {
		$this->assertNotEmpty( $this->event->get_title() );
	}

	/**
	 * Test getting the description.
	 *
	 * @since 2.1.0
	 */
	public function test_get_description() {

		$this->assertNotEmpty( $this->event->get_description() );

		if ( $this->event instanceof WordPoints_Hook_Event_RetroactiveI ) {
			$this->assertNotEmpty( $this->event->get_retroactive_description() );
		}

		if ( $this->event instanceof WordPoints_Hook_Event_ReversingI ) {
			$this->assertNotEmpty( $this->event->get_reversal_text() );
		}
	}

	/**
	 * Test that the event fires.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_targets
	 */
	public function test_fires( $target, $reactor_slug ) {

		self::$tested_targets[] = $target;
		self::$_expected_targets = $this->expected_targets;

		switch ( $reactor_slug ) {

			case 'points':
			case 'points_legacy':
				$this->create_points_type();

				$settings = array(
					'event'       => $this->event_slug,
					'description' => 'Test Description',
					'log_text'    => 'Test Log Text',
					'points'      => 10,
					'points_type' => 'points',
				);

				if ( 'points_legacy' === $reactor_slug ) {
					$settings['points_legacy_reversals'] = array( 'toggle_off' => 'toggle_on' );
				} else {
					$settings['reversals'] = array( 'toggle_off' => 'toggle_on' );
				}

				$assertion = 'assert_user_has_points';
				$reverse_assertion = 'assert_user_has_no_points';
				$reaction_store_slug = 'points';
			break;

			default:
				$this->fail( 'Unknown reactor: ' . $reactor_slug );
				return;
		}

		$settings['target'] = $target;
		$settings['reactor'] = $reactor_slug;

		$reaction = $this->hooks
			->get_reaction_store( $reaction_store_slug )
			->create_reaction( $settings );

		$this->assertIsReaction( $reaction );

		$arg = new WordPoints_Hook_Arg( $target[0] );

		$base_entity_ids = $this->fire_event(
			$arg->get_entity()
			, $reactor_slug
			, $target[0]
		);

		foreach ( (array) $base_entity_ids as $index => $base_entity_id ) {

			$hierarchy = new WordPoints_Hook_Event_Args( array( $arg ) );

			$hierarchy->descend( $target[0] );
			$entity = $hierarchy->get_current();
			$this->assertTrue( $entity->set_the_value( $base_entity_id ) );

			$target_entity = $hierarchy->get_from_hierarchy( $target );

			$this->assertInstanceOf( 'WordPoints_Entity', $target_entity );

			$target_id = $target_entity->get_the_value();

			call_user_func( array( $this, $assertion ), $target_id );

			if ( $this->is_reversible ) {

				$this->reverse_event( $base_entity_id, $index );

				call_user_func( array( $this, $reverse_assertion ), $target_id );
			}
		}
	}

	/**
	 * Provides sets of targets that reactors should be able to hit.
	 *
	 * @since 2.1.0
	 *
	 * @return array[]
	 */
	public function data_provider_targets() {

		$this->hooks = wordpoints_hooks();

		$reactors = $this->hooks->get_sub_app( 'reactors' )->get_all();

		$arg_types_index = array();

		/** @var WordPoints_Hook_ReactorI $reactor */
		foreach ( $reactors as $slug => $reactor ) {
			$arg_types = $reactor->get_arg_types();

			foreach ( $arg_types as $arg_type ) {
				$arg_types_index[ $arg_type ][] = $slug;
			}
		}

		/** @var WordPoints_Hook_ArgI[] $args */
		$args = $this->hooks->get_sub_app( 'events' )->get_sub_app( 'args' )->get_children( $this->event_slug );

		return $this->get_targets_from_args( $args, $arg_types_index );
	}

	/**
	 * Assembles a list of possible targets given a list of args and a list of
	 * reactors that support them.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_ArgI[]|WordPoints_EntityishI[] $args            The args.
	 * @param string[][]                                     $arg_types_index A list of reactor slugs indexed by arg slug.
	 * @param array[][]                                      $targets         The targets data.
	 * @param string[]                                       $target_stack    The target stack.
	 *
	 * @return array[][] The target data.
	 */
	protected function get_targets_from_args( $args, $arg_types_index, array $targets = array(), array $target_stack = array() ) {

		foreach ( $args as $slug => $arg ) {

			$target_stack[] = $slug;

			if ( $arg instanceof WordPoints_Entity_Relationship ) {

				$child_slug = $arg->get_related_entity_slug();
				$arg = $arg->get_child( $child_slug );
				$slug = $arg->get_slug();
				$target_stack[] = $slug;

			} elseif ( $arg instanceof WordPoints_Hook_ArgI ) {

				$slug = $arg->get_entity_slug();

			} else {

				array_pop( $target_stack );
				continue;
			}

			if ( isset( $arg_types_index[ $slug ] ) ) {
				foreach ( $arg_types_index[ $slug ] as $reactor_slug ) {
					$targets[] = array( $target_stack, $reactor_slug );
				}
			}

			$children = wordpoints_entities()->get_sub_app( 'children' )->get_children( $slug );

			$targets = $this->get_targets_from_args(
				$children
				, $arg_types_index
				, $targets
				, $target_stack
			);

			if ( isset( $child_slug ) ) {
				array_pop( $target_stack );
			}

			array_pop( $target_stack );
		}

		return $targets;
	}

	/**
	 * Assert that a user has points.
	 *
	 * @since 2.1.0
	 *
	 * @param int $user_id The ID of the user.
	 */
	protected function assert_user_has_points( $user_id ) {
		$this->assertEquals( 10, wordpoints_get_points( $user_id, 'points' ) );
	}

	/**
	 * Assert that a user has no points.
	 *
	 * @since 2.1.0
	 *
	 * @param int $user_id The ID of the user.
	 */
	protected function assert_user_has_no_points( $user_id ) {
		$this->assertEquals( 0, wordpoints_get_points( $user_id, 'points' ) );
	}

	/**
	 * Fire the event.
	 *
	 * The third arg is not actually declared for backward compatibility with pre-
	 * 2.2.0.
	 *
	 * @since 2.1.0
	 * @since 2.2.0 Now called with the third arg, $arg_slug.
	 *
	 * @param WordPoints_Entity $arg          The object for the main event arg.
	 * @param string            $reactor_slug The reactor slug.
	 * @param string            $arg_slug     The slug of the arg.
	 *
	 * @return mixed The ID of the $arg in the event. You may also return an
	 *               array of args, for each of which the event has been fired.
	 */
	abstract protected function fire_event( $arg, $reactor_slug /* , $arg_slug = null */ );

	/**
	 * Reverse fire the event.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $arg_id The ID of the arg the event is being reversed for.
	 * @param mixed $index The index of this entity ID in the array of entity IDs
	 *                     returned by self::fire_event().
	 */
	protected function reverse_event( $arg_id, $index ) {}
}

// EOF
