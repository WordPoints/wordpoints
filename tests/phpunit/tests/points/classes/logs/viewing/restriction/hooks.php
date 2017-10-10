<?php

/**
 * Test case for WordPoints_Points_Logs_Viewing_Restriction_Hooks.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.2.0
 */

/**
 * Tests WordPoints_Points_Logs_Viewing_Restriction_Hooks.
 *
 * @since 2.2.0
 *
 * @covers WordPoints_Points_Logs_Viewing_Restriction_Hooks
 */
class WordPoints_Points_Logs_Viewing_Restriction_Hooks_Test
	extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * @since 2.2.0
	 */
	protected $shared_fixtures = array(
		'user'       => 1,
		'points_log' => array(
			array(
				'get'  => true,
				'args' => array(
					'log_type' => 'test_event',
					'log_meta' => array( 'test_entity' => 1 ),
				),
			),
			array(
				'get'  => true,
				'args' => array(
					'log_type' => 'test_event',
					'log_meta' => array(
						'test_entity_guid' => array(
							'test_context' => 3,
							'test_entity'  => 1,
						),
					),
				),
			),
			array(
				'get'  => true,
				'args' => array( 'log_type' => 'reverse-test_event' ),
			),
		),
	);

	/**
	 * The entity view restrictions registry.
	 *
	 * @since 2.2.0
	 *
	 * @var WordPoints_Class_Registry_DeepI
	 */
	protected $view_restrictions;

	/**
	 * @since 2.2.0
	 */
	public function setUp() {

		parent::setUp();

		$this->mock_apps();

		$restrictions            = wordpoints_entities()->get_sub_app( 'restrictions' );
		$this->view_restrictions = $restrictions->get_sub_app( 'view' );

		wordpoints_entity_restrictions_know_init(
			$restrictions->get_sub_app( 'know' )
		);

		wordpoints_entity_restrictions_view_init( $this->view_restrictions );

		$this->factory->wordpoints->hook_event->create(
			array( 'slug' => 'test_event' )
		);
	}

	/**
	 * @since 2.2.0
	 */
	public function tearDown() {

		WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts_construct  = array();
		WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts            = array();
		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = false;

		parent::tearDown();
	}

	/**
	 * Test getting the description when the entity has no restrictions.
	 *
	 * @since 2.2.0
	 */
	public function test_get_description_entity_not_restricted() {

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][0]
		);

		$this->assertSame( array(), $restriction->get_description() );
	}

	/**
	 * Test getting the description when the entity is restricted.
	 *
	 * @since 2.2.0
	 */
	public function test_get_description_entity_restricted() {

		$this->factory->wordpoints->entity->create(
			array( 'slug' => 'other_entity' )
		);

		$event_slug = $this->factory->wordpoints->hook_event->create(
			array(
				'args' => array(
					'test_entity'  => 'WordPoints_PHPUnit_Mock_Hook_Arg',
					'other_entity' => 'WordPoints_PHPUnit_Mock_Hook_Arg',
				),
			)
		);

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array(
				'log_type' => $event_slug,
				'log_meta' => array( 'test_entity' => 1, 'other_entity' => 2 ),
			)
		);

		$this->view_restrictions->register(
			'test'
			, array( 'test_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$this->view_restrictions->register(
			'test'
			, array( 'other_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks( $log );

		$descriptions = $restriction->get_description();

		$this->assertInternalType( 'array', $descriptions );
		$this->assertCount( 1, $descriptions );
		$this->assertStringMatchesFormat( '%sMock Entity%s', $descriptions[0] );
	}

	/**
	 * Test getting the description when there are applicable restrictions for the entity.
	 *
	 * @since 2.2.0
	 */
	public function test_get_description_entity_restriction_applicable() {

		$this->factory->wordpoints->entity->create(
			array( 'slug' => 'other_entity' )
		);

		$event_slug = $this->factory->wordpoints->hook_event->create(
			array(
				'args' => array(
					'test_entity'  => 'WordPoints_PHPUnit_Mock_Hook_Arg',
					'other_entity' => 'WordPoints_PHPUnit_Mock_Hook_Arg',
				),
			)
		);

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array(
				'log_type' => $event_slug,
				'log_meta' => array( 'test_entity' => 1, 'other_entity' => 2 ),
			)
		);

		$this->view_restrictions->register(
			'test'
			, array( 'test_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$this->view_restrictions->register(
			'test'
			, array( 'other_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Applicable'
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks( $log );

		$descriptions = $restriction->get_description();

		$this->assertInternalType( 'array', $descriptions );
		$this->assertCount( 2, $descriptions );
		$this->assertStringMatchesFormat( '%sMock Entity%s', $descriptions[0] );
		$this->assertStringMatchesFormat( '%sMock Entity%s', $descriptions[1] );
	}

	/**
	 * Test that it doesn't apply by default.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_no_log_meta() {

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array( 'log_type' => 'test_event' )
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks( $log );

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it doesn't apply for an unrecognized event.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_unrecognized_event() {

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array( 'log_type' => 'not_event' )
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks( $log );

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it doesn't apply if the entity has no restrictions.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_entity_not_restricted() {

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][0]
		);

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it applies if the entity is restricted.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_entity_restricted() {

		$this->view_restrictions->register(
			'test'
			, array( 'test_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][0]
		);

		$this->assertTrue( $restriction->applies() );
	}

	/**
	 * Test that it applies if there are applicable restrictions for the entity.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_entity_restriction_applicable() {

		$this->view_restrictions->register(
			'test'
			, array( 'test_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Applicable'
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][0]
		);

		$this->assertTrue( $restriction->applies() );
	}

	/**
	 * Test that it doesn't apply if the entity restrictions don't apply.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_entity_restriction_not_applicable() {

		$this->view_restrictions->register(
			'test'
			, array( 'test_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][0]
		);

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it doesn't apply if the entity has no restrictions.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_entity_not_restricted_guid() {

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$this->assertSame( 1, $context->get_current_id() );

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][1]
		);

		$this->assertFalse( $restriction->applies() );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test that it applies if the entity is restricted.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_entity_restricted_guid() {

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = 'test_context';

		$this->view_restrictions->register(
			'test'
			, array( 'test_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$this->assertSame( 1, $context->get_current_id() );

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][1]
		);

		$this->assertTrue( $restriction->applies() );

		$this->assertSame( 1, $context->get_current_id() );

		$construct_args = array(
			'context'   => '3',
			'entity_id' => '1',
			'hierarchy' => array( 'test_entity' ),
		);

		$this->assertSame(
			array( $construct_args )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts_construct
		);
	}

	/**
	 * Test that it applies if there are applicable restrictions for the entity.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_entity_restriction_applicable_guid() {

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = 'test_context';

		$this->view_restrictions->register(
			'test'
			, array( 'test_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Applicable'
		);

		$this->assertSame( 1, $context->get_current_id() );

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][1]
		);

		$this->assertTrue( $restriction->applies() );

		$this->assertSame( 1, $context->get_current_id() );

		$construct_args = array(
			'context'   => '3',
			'entity_id' => '1',
			'hierarchy' => array( 'test_entity' ),
		);

		$this->assertSame(
			array( $construct_args )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts_construct
		);
	}

	/**
	 * Test that it doesn't apply if the entity restrictions don't apply.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_entity_restriction_not_applicable_guid() {

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = 'test_context';

		$this->view_restrictions->register(
			'test'
			, array( 'test_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$this->assertSame( 1, $context->get_current_id() );

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][1]
		);

		$this->assertFalse( $restriction->applies() );

		$this->assertSame( 1, $context->get_current_id() );

		$construct_args = array(
			'context'   => '3',
			'entity_id' => '1',
			'hierarchy' => array( 'test_entity' ),
		);

		$this->assertSame(
			array( $construct_args )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts_construct
		);
	}

	/**
	 * Test that it doesn't apply to reverse logs when the entity isn't restricted.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_reverse_entity_not_restricted() {

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][2]
		);

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it applies to reverse logs when the entity is restricted.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_reverse_entity_restricted() {

		$log = $this->fixtures['points_log'][2];

		wordpoints_update_points_log_meta(
			$log->id
			, 'original_log_id'
			, $this->fixture_ids['points_log'][0]
		);

		$this->view_restrictions->register(
			'test'
			, array( 'test_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks( $log );

		$this->assertTrue( $restriction->applies() );
	}

	/**
	 * Test that it doesn't apply to reverse logs when the entity isn't restricted.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_reverse_entity_not_restricted_guid() {

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = 'test_context';

		$this->assertSame( 1, $context->get_current_id() );

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][1]
		);

		$this->assertFalse( $restriction->applies() );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test that it applies to reverse logs when the entity is restricted.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_reverse_entity_restricted_guid() {

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = 'test_context';

		$log = $this->fixtures['points_log'][2];

		wordpoints_update_points_log_meta(
			$log->id
			, 'original_log_id'
			, $this->fixture_ids['points_log'][1]
		);

		$this->view_restrictions->register(
			'test'
			, array( 'test_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$this->assertSame( 1, $context->get_current_id() );

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks( $log );

		$this->assertTrue( $restriction->applies() );

		$this->assertSame( 1, $context->get_current_id() );

		$construct_args = array(
			'context'   => '3',
			'entity_id' => '1',
			'hierarchy' => array( 'test_entity' ),
		);

		$this->assertSame(
			array( $construct_args )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts_construct
		);
	}

	/**
	 * Test the user can by default.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_no_log_meta() {

		$event_slug = $this->factory->wordpoints->hook_event->create();

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array( 'log_type' => $event_slug )
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks( $log );

		$this->assertTrue( $restriction->user_can( $this->fixture_ids['user'][0] ) );
	}

	/**
	 * Test the user can for an unrecognized event.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_unrecognized_event() {

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array( 'log_type' => 'not_event' )
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks( $log );

		$this->assertTrue( $restriction->user_can( $this->fixture_ids['user'][0] ) );
	}

	/**
	 * Test the user can if they can view the entity.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_entity_not_restricted() {

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][0]
		);

		$this->assertTrue( $restriction->user_can( $this->fixture_ids['user'][0] ) );
	}

	/**
	 * Test the user can't if they can't view the entity.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_entity_restricted() {

		$this->view_restrictions->register(
			'test'
			, array( 'test_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][0]
		);

		$this->assertFalse( $restriction->user_can( $this->fixture_ids['user'][0] ) );
	}

	/**
	 * Test the user can if the entity has applicable restrictions but not blocking.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_entity_restriction_applicable() {

		$this->view_restrictions->register(
			'test'
			, array( 'test_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Applicable'
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][0]
		);

		$this->assertTrue( $restriction->user_can( $this->fixture_ids['user'][0] ) );
	}

	/**
	 * Test the user can if the entity has some restrictions but they don't apply.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_entity_restriction_not_applicable() {

		$this->view_restrictions->register(
			'test'
			, array( 'test_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][0]
		);

		$this->assertTrue( $restriction->user_can( $this->fixture_ids['user'][0] ) );
	}

	/**
	 * Test the user can if they can view the entity.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_entity_not_restricted_guid() {

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][1]
		);

		$this->assertSame( 1, $context->get_current_id() );

		$this->assertTrue( $restriction->user_can( $this->fixture_ids['user'][0] ) );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test the user can't if they can't view the entity.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_entity_restricted_guid() {

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = 'test_context';

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$this->view_restrictions->register(
			'test'
			, array( 'test_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$this->assertSame( 1, $context->get_current_id() );

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][1]
		);

		$this->assertFalse( $restriction->user_can( $this->fixture_ids['user'][0] ) );

		$this->assertSame( 1, $context->get_current_id() );

		$construct_args = array(
			'context'   => '3',
			'entity_id' => '1',
			'hierarchy' => array( 'test_entity' ),
		);

		$this->assertSame(
			array( $construct_args )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts_construct
		);
	}

	/**
	 * Test the user can if the entity has applicable restrictions but not blocking.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_entity_restriction_applicable_guid() {

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = 'test_context';

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$this->view_restrictions->register(
			'test'
			, array( 'test_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Applicable'
		);

		$this->assertSame( 1, $context->get_current_id() );

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][1]
		);

		$this->assertTrue( $restriction->user_can( $this->fixture_ids['user'][0] ) );

		$construct_args = array(
			'context'   => '3',
			'entity_id' => '1',
			'hierarchy' => array( 'test_entity' ),
		);

		$this->assertSame(
			array( $construct_args )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts_construct
		);
	}

	/**
	 * Test the user can if the entity has some restrictions but they don't apply.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_entity_restriction_not_applicable_guid() {

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = 'test_context';

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$this->view_restrictions->register(
			'test'
			, array( 'test_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$this->assertSame( 1, $context->get_current_id() );

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][1]
		);

		$this->assertTrue( $restriction->user_can( $this->fixture_ids['user'][0] ) );

		$this->assertSame( 1, $context->get_current_id() );

		$construct_args = array(
			'context'   => '3',
			'entity_id' => '1',
			'hierarchy' => array( 'test_entity' ),
		);

		$this->assertSame(
			array( $construct_args )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts_construct
		);
	}

	/**
	 * Test that the user can view reverse logs if they can view the entity.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_reverse_entity_not_restricted() {

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][2]
		);

		$this->assertTrue( $restriction->user_can( $this->fixture_ids['user'][0] ) );
	}

	/**
	 * Test that the user can't view reverse logs if they can't view the entity.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_reverse_entity_restricted() {

		$log = $this->fixtures['points_log'][2];

		wordpoints_update_points_log_meta(
			$log->id
			, 'original_log_id'
			, $this->fixture_ids['points_log'][0]
		);

		$this->view_restrictions->register(
			'test'
			, array( 'test_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks( $log );

		$this->assertFalse(
			$restriction->user_can( $this->fixture_ids['user'][0] )
		);
	}

	/**
	 * Test that the user can view reverse logs if they can view the entity.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_reverse_entity_not_restricted_guid() {

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = 'test_context';

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$this->assertSame( 1, $context->get_current_id() );

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks(
			$this->fixtures['points_log'][2]
		);

		$this->assertTrue( $restriction->user_can( $this->fixture_ids['user'][0] ) );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test that the user can't view reverse logs if they can't view the entity.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_reverse_entity_restricted_guid() {

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = 'test_context';

		$context = $this->factory->wordpoints->entity_context->create_and_get(
			array( 'slug' => 'test_context' )
		);

		$log = $this->fixtures['points_log'][2];

		wordpoints_update_points_log_meta(
			$log->id
			, 'original_log_id'
			, $this->fixture_ids['points_log'][1]
		);

		$this->view_restrictions->register(
			'test'
			, array( 'test_entity' )
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$this->assertSame( 1, $context->get_current_id() );

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks( $log );

		$this->assertFalse(
			$restriction->user_can( $this->fixture_ids['user'][0] )
		);

		$this->assertSame( 1, $context->get_current_id() );

		$construct_args = array(
			'context'   => '3',
			'entity_id' => '1',
			'hierarchy' => array( 'test_entity' ),
		);

		$this->assertSame(
			array( $construct_args )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts_construct
		);
	}
}

// EOF
