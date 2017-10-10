<?php

/**
 * Test case for WordPoints_Entity_Restrictions.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.2.0
 */

/**
 * Tests WordPoints_Entity_Restrictions.
 *
 * @since 2.2.0
 *
 * @covers WordPoints_Entity_Restrictions
 */
class WordPoints_Entity_Restrictions_Test extends WordPoints_PHPUnit_TestCase {

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
	 * Test that it doesn't apply when there are no restrictions.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_applies_none( $hierarchy ) {

		$this->mock_apps();

		$restrictions = new WordPoints_Entity_Restrictions( 'test' );

		$restriction = $restrictions->get( 0, $hierarchy );

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Data provider for valid entity hierarchies.
	 *
	 * @since 2.2.0
	 *
	 * @return array[] List of valid entity hierarchies.
	 */
	public function data_provider_entity_hierarchies() {
		return array(
			'string'           => array( 'test_entity' ),
			'single'           => array( array( 'test_entity' ) ),
			'child'            => array( array( 'test_entity', 'child' ) ),
			'grandchild'       => array( array( 'other_entity', 'child', 'test_entity' ) ),
			'great_grandchild' => array(
				array( 'other_entity', 'other_child', 'test_entity', 'child' ),
			),
		);
	}

	/**
	 * Test that it doesn't apply when the restrictions don't apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_applies_none_apply( $hierarchy ) {

		$this->mock_apps();

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$know_restrictions = $restrictions->get_sub_app( 'know' );
		$know_restrictions->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$know_restrictions->register(
			'test_2'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$restriction = $restrictions->get( 0, $hierarchy );

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it applies when some of the restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_applies_some_apply( $hierarchy ) {

		$this->mock_apps();

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$know_restrictions = $restrictions->get_sub_app( 'know' );
		$know_restrictions->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$know_restrictions->register(
			'test_2'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Applicable'
		);

		$restriction = $restrictions->get( 0, $hierarchy );

		$this->assertTrue( $restriction->applies() );
	}

	/**
	 * Test that it applies when some of the top-level restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_applies_top_level_apply( $hierarchy ) {

		$this->mock_apps();

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$know_restrictions = $restrictions->get_sub_app( 'know' );
		$know_restrictions->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$know_restrictions->register(
			'test_2'
			, array()
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Applicable'
		);

		$restriction = $restrictions->get( 0, $hierarchy );

		// Top-level only applies to the next level below.
		if ( 1 === count( (array) $hierarchy ) ) {
			$this->assertTrue( $restriction->applies() );
		} else {
			$this->assertFalse( $restriction->applies() );
		}
	}

	/**
	 * Test that it applies when some of the 'know' restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_applies_some_know_apply( $hierarchy ) {

		$this->mock_apps();

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$restrictions->get_sub_app( 'view' )->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$restrictions->get_sub_app( 'know' )->register(
			'test_2'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Applicable'
		);

		$restriction = $restrictions->get( 0, $hierarchy, 'view' );

		$this->assertTrue( $restriction->applies() );
	}

	/**
	 * Test that it applies when some of the top-level 'know' restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_applies_top_level_know_apply( $hierarchy ) {

		$this->mock_apps();

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$restrictions->get_sub_app( 'view' )->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$restrictions->get_sub_app( 'know' )->register(
			'test_2'
			, array()
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Applicable'
		);

		$restriction = $restrictions->get( 0, $hierarchy, 'view' );

		// Top-level only applies to the next level below.
		if ( 1 === count( (array) $hierarchy ) ) {
			$this->assertTrue( $restriction->applies() );
		} else {
			$this->assertFalse( $restriction->applies() );
		}
	}

	/**
	 * Test that the user can when there are no restrictions.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_none( $hierarchy ) {

		$this->mock_apps();

		$restrictions = new WordPoints_Entity_Restrictions( 'test' );

		$restriction = $restrictions->get( 0, $hierarchy );

		$this->assertTrue( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can when the restrictions don't apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_none_apply( $hierarchy ) {

		$this->mock_apps();

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$know_restrictions = $restrictions->get_sub_app( 'know' );
		$know_restrictions->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$know_restrictions->register(
			'test_2'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$restriction = $restrictions->get( 0, $hierarchy );

		$this->assertTrue( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can when some of the restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_some_apply( $hierarchy ) {

		$this->mock_apps();

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$know_restrictions = $restrictions->get_sub_app( 'know' );
		$know_restrictions->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$know_restrictions->register(
			'test_2'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Applicable'
		);

		$restriction = $restrictions->get( 0, $hierarchy );

		$this->assertTrue( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can't when some of the restrictions apply to them.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_some_restricted( $hierarchy ) {

		$this->mock_apps();

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$know_restrictions = $restrictions->get_sub_app( 'know' );
		$know_restrictions->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$know_restrictions->register(
			'test_2'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$restriction = $restrictions->get( 0, $hierarchy );

		$this->assertFalse( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can when some of the top-level restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_top_level_apply( $hierarchy ) {

		$this->mock_apps();

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$know_restrictions = $restrictions->get_sub_app( 'know' );
		$know_restrictions->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$know_restrictions->register(
			'test_2'
			, array()
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Applicable'
		);

		$restriction = $restrictions->get( 0, $hierarchy );

		$this->assertTrue( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can when some of the top-level restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_top_level_restricted( $hierarchy ) {

		$this->mock_apps();

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$know_restrictions = $restrictions->get_sub_app( 'know' );
		$know_restrictions->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$know_restrictions->register(
			'test_2'
			, array()
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$restriction = $restrictions->get( 0, $hierarchy );

		// Top-level only applies to the next level below.
		if ( 1 === count( (array) $hierarchy ) ) {
			$this->assertFalse( $restriction->user_can( 0 ) );
		} else {
			$this->assertTrue( $restriction->user_can( 0 ) );
		}
	}

	/**
	 * Test that the user can when some of the 'know' restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_some_know_apply( $hierarchy ) {

		$this->mock_apps();

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$restrictions->get_sub_app( 'view' )->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$restrictions->get_sub_app( 'know' )->register(
			'test_2'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Applicable'
		);

		$restriction = $restrictions->get( 0, $hierarchy, 'view' );

		$this->assertTrue( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can't when some of the 'know' restrictions apply to them.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_some_know_restricted( $hierarchy ) {

		$this->mock_apps();

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$restrictions->get_sub_app( 'view' )->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$restrictions->get_sub_app( 'know' )->register(
			'test_2'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$restriction = $restrictions->get( 0, $hierarchy, 'view' );

		$this->assertFalse( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can when some of the top-level 'know' restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_top_level_know_apply( $hierarchy ) {

		$this->mock_apps();

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$restrictions->get_sub_app( 'view' )->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$restrictions->get_sub_app( 'know' )->register(
			'test_2'
			, array()
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Applicable'
		);

		$restriction = $restrictions->get( 0, $hierarchy, 'view' );

		$this->assertTrue( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can't when some of the top-level 'know' restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_top_level_know_restricted( $hierarchy ) {

		$this->mock_apps();

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$restrictions->get_sub_app( 'view' )->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$restrictions->get_sub_app( 'know' )->register(
			'test_2'
			, array()
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$restriction = $restrictions->get( 0, $hierarchy, 'view' );

		// Top-level only applies to the next level below.
		if ( 1 === count( (array) $hierarchy ) ) {
			$this->assertFalse( $restriction->user_can( 0 ) );
		} else {
			$this->assertTrue( $restriction->user_can( 0 ) );
		}
	}

	/**
	 * Test that the user can when there are no restrictions.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_none_context( $hierarchy ) {

		$context = $this->factory->wordpoints->entity_context->create_and_get();

		$this->assertSame( 1, $context->get_current_id() );

		$context_slug = $context->get_slug();

		$restrictions = new WordPoints_Entity_Restrictions( 'test' );

		$guid        = array( $context_slug => 5, 'test_entity' => 0 );
		$restriction = $restrictions->get( $guid, $hierarchy );

		$this->assertTrue( $restriction->user_can( 0 ) );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test that the user can when the restrictions don't apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_none_apply_context( $hierarchy ) {

		$context = $this->factory->wordpoints->entity_context->create_and_get();

		$this->assertSame( 1, $context->get_current_id() );

		$context_slug = $context->get_slug();

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = $context_slug;

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$know_restrictions = $restrictions->get_sub_app( 'know' );
		$know_restrictions->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$know_restrictions->register(
			'test_2'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$guid        = array( $context_slug => 5, 'test_entity' => 1 );
		$restriction = $restrictions->get( $guid, $hierarchy );

		$this->assertSame( 1, $context->get_current_id() );

		$construct_args = array(
			'context'   => 5,
			'entity_id' => 1,
			'hierarchy' => (array) $hierarchy,
		);

		$this->assertSame(
			array( $construct_args, $construct_args )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts_construct
		);

		$this->assertTrue( $restriction->user_can( 0 ) );

		$this->assertSame(
			array()
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts
		);

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test that the user can when some of the restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_some_apply_context( $hierarchy ) {

		$context = $this->factory->wordpoints->entity_context->create_and_get();

		$this->assertSame( 1, $context->get_current_id() );

		$context_slug = $context->get_slug();

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = $context_slug;

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$know_restrictions = $restrictions->get_sub_app( 'know' );
		$know_restrictions->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$know_restrictions->register(
			'test_2'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Applicable'
		);

		$guid        = array( $context_slug => 5, 'test_entity' => 1 );
		$restriction = $restrictions->get( $guid, $hierarchy );

		$this->assertSame( 1, $context->get_current_id() );

		$construct_args = array(
			'context'   => 5,
			'entity_id' => 1,
			'hierarchy' => (array) $hierarchy,
		);

		$this->assertSame(
			array( $construct_args, $construct_args )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts_construct
		);

		$this->assertTrue( $restriction->user_can( 0 ) );

		$this->assertSame(
			array( 5 )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts
		);

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test that the user can't when some of the restrictions apply to them.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_some_restricted_context( $hierarchy ) {

		$context = $this->factory->wordpoints->entity_context->create_and_get();

		$this->assertSame( 1, $context->get_current_id() );

		$context_slug = $context->get_slug();

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = $context_slug;

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$know_restrictions = $restrictions->get_sub_app( 'know' );
		$know_restrictions->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$know_restrictions->register(
			'test_2'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$guid        = array( $context_slug => 5, 'test_entity' => 1 );
		$restriction = $restrictions->get( $guid, $hierarchy );

		$this->assertSame( 1, $context->get_current_id() );

		$construct_args = array(
			'context'   => 5,
			'entity_id' => 1,
			'hierarchy' => (array) $hierarchy,
		);

		$this->assertSame(
			array( $construct_args, $construct_args )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts_construct
		);

		$this->assertFalse( $restriction->user_can( 0 ) );

		$this->assertSame(
			array( 5 )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts
		);

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test that the user can when some of the top-level restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_top_level_apply_context( $hierarchy ) {

		$context = $this->factory->wordpoints->entity_context->create_and_get();

		$this->assertSame( 1, $context->get_current_id() );

		$context_slug = $context->get_slug();

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = $context_slug;

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$know_restrictions = $restrictions->get_sub_app( 'know' );
		$know_restrictions->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$know_restrictions->register(
			'test_2'
			, array()
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Applicable'
		);

		$guid        = array( $context_slug => 5, 'test_entity' => 1 );
		$restriction = $restrictions->get( $guid, $hierarchy );

		$this->assertSame( 1, $context->get_current_id() );

		$depth = count( (array) $hierarchy );

		$construct_args = array(
			'context'   => 5,
			'entity_id' => 1,
			'hierarchy' => (array) $hierarchy,
		);

		$this->assertSame(
			1 === $depth
				? array( $construct_args, $construct_args )
				: array( $construct_args )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts_construct
		);

		$this->assertTrue( $restriction->user_can( 0 ) );

		$this->assertSame(
			1 === $depth ? array( 5 ) : array()
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts
		);

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test that the user can when some of the top-level restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_top_level_restricted_context( $hierarchy ) {

		$context = $this->factory->wordpoints->entity_context->create_and_get();

		$this->assertSame( 1, $context->get_current_id() );

		$context_slug = $context->get_slug();

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = $context_slug;

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$know_restrictions = $restrictions->get_sub_app( 'know' );
		$know_restrictions->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$know_restrictions->register(
			'test_2'
			, array()
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$guid        = array( $context_slug => 5, 'test_entity' => 1 );
		$restriction = $restrictions->get( $guid, $hierarchy );

		$this->assertSame( 1, $context->get_current_id() );

		$depth = count( (array) $hierarchy );

		$construct_args = array(
			'context'   => 5,
			'entity_id' => 1,
			'hierarchy' => (array) $hierarchy,
		);

		$this->assertSame(
			1 === $depth
				? array( $construct_args, $construct_args )
				: array( $construct_args )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts_construct
		);

		// Top-level only applies to the next level below.
		if ( 1 === $depth ) {
			$this->assertFalse( $restriction->user_can( 0 ) );
		} else {
			$this->assertTrue( $restriction->user_can( 0 ) );
		}

		$this->assertSame(
			1 === $depth ? array( 5 ) : array()
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts
		);

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test that the user can when some of the 'know' restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_some_know_apply_context( $hierarchy ) {

		$context = $this->factory->wordpoints->entity_context->create_and_get();

		$this->assertSame( 1, $context->get_current_id() );

		$context_slug = $context->get_slug();

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = $context_slug;

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$restrictions->get_sub_app( 'view' )->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$restrictions->get_sub_app( 'know' )->register(
			'test_2'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Applicable'
		);

		$guid        = array( $context_slug => 5, 'test_entity' => 1 );
		$restriction = $restrictions->get( $guid, $hierarchy, 'view' );

		$this->assertSame( 1, $context->get_current_id() );

		$construct_args = array(
			'context'   => 5,
			'entity_id' => 1,
			'hierarchy' => (array) $hierarchy,
		);

		$this->assertSame(
			array( $construct_args, $construct_args )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts_construct
		);

		$this->assertTrue( $restriction->user_can( 0 ) );

		$this->assertSame(
			array( 5 )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts
		);

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test that the user can't when some of the 'know' restrictions apply to them.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_some_know_restricted_context( $hierarchy ) {

		$context = $this->factory->wordpoints->entity_context->create_and_get();

		$this->assertSame( 1, $context->get_current_id() );

		$context_slug = $context->get_slug();

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = $context_slug;

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$restrictions->get_sub_app( 'view' )->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$restrictions->get_sub_app( 'know' )->register(
			'test_2'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$guid        = array( $context_slug => 5, 'test_entity' => 1 );
		$restriction = $restrictions->get( $guid, $hierarchy, 'view' );

		$this->assertSame( 1, $context->get_current_id() );

		$construct_args = array(
			'context'   => 5,
			'entity_id' => 1,
			'hierarchy' => (array) $hierarchy,
		);

		$this->assertSame(
			array( $construct_args, $construct_args )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts_construct
		);

		$this->assertFalse( $restriction->user_can( 0 ) );

		$this->assertSame(
			array( 5 )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts
		);

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test that the user can when some of the top-level 'know' restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_top_level_know_apply_context( $hierarchy ) {

		$context = $this->factory->wordpoints->entity_context->create_and_get();

		$this->assertSame( 1, $context->get_current_id() );

		$context_slug = $context->get_slug();

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = $context_slug;

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$restrictions->get_sub_app( 'view' )->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$restrictions->get_sub_app( 'know' )->register(
			'test_2'
			, array()
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Applicable'
		);

		$guid        = array( $context_slug => 5, 'test_entity' => 1 );
		$restriction = $restrictions->get( $guid, $hierarchy, 'view' );

		$this->assertSame( 1, $context->get_current_id() );

		$depth = count( (array) $hierarchy );

		$construct_args = array(
			'context'   => 5,
			'entity_id' => 1,
			'hierarchy' => (array) $hierarchy,
		);

		$this->assertSame(
			1 === $depth
				? array( $construct_args, $construct_args )
				: array( $construct_args )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts_construct
		);

		$this->assertTrue( $restriction->user_can( 0 ) );

		$this->assertSame(
			1 === $depth ? array( 5 ) : array()
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts
		);

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test that the user can't when some of the top-level 'know' restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @dataProvider data_provider_entity_hierarchies
	 *
	 * @param string|string[] $hierarchy The entity hierarchy to check.
	 */
	public function test_user_can_top_level_know_restricted_context( $hierarchy ) {

		$context = $this->factory->wordpoints->entity_context->create_and_get();

		$this->assertSame( 1, $context->get_current_id() );

		$context_slug = $context->get_slug();

		WordPoints_PHPUnit_Mock_Entity_Restriction::$listen_for_contexts = $context_slug;

		/** @var WordPoints_Entity_Restrictions $restrictions */
		$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		$restrictions->get_sub_app( 'view' )->register(
			'test_1'
			, (array) $hierarchy
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction_Not_Applicable'
		);

		$restrictions->get_sub_app( 'know' )->register(
			'test_2'
			, array()
			, 'WordPoints_PHPUnit_Mock_Entity_Restriction'
		);

		$guid        = array( $context_slug => 5, 'test_entity' => 1 );
		$restriction = $restrictions->get( $guid, $hierarchy, 'view' );

		$this->assertSame( 1, $context->get_current_id() );

		$depth = count( (array) $hierarchy );

		$construct_args = array(
			'context'   => 5,
			'entity_id' => 1,
			'hierarchy' => (array) $hierarchy,
		);

		$this->assertSame(
			1 === $depth
				? array( $construct_args, $construct_args )
				: array( $construct_args )
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts_construct
		);

		// Top-level only applies to the next level below.
		if ( 1 === $depth ) {
			$this->assertFalse( $restriction->user_can( 0 ) );
		} else {
			$this->assertTrue( $restriction->user_can( 0 ) );
		}

		$this->assertSame(
			1 === $depth ? array( 5 ) : array()
			, WordPoints_PHPUnit_Mock_Entity_Restriction::$contexts
		);

		$this->assertSame( 1, $context->get_current_id() );
	}
}

// EOF
