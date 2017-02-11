<?php

/**
 * Test case for WordPoints_Entity_Restriction_Legacy.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.2.0
 */

/**
 * Tests WordPoints_Entity_Restriction_Legacy.
 *
 * @since 2.2.0
 *
 * @covers WordPoints_Entity_Restriction_Legacy
 */
class WordPoints_Entity_Restriction_Legacy_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test that it doesn't apply when the entity is not restricted.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_not_restricted() {

		$entity_slug = $this->factory->wordpoints->entity->create();

		$restriction = new WordPoints_Entity_Restriction_Legacy(
			1
			, array( $entity_slug )
		);

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it doesn't apply when the entity is not registered.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_not_registered() {

		$restriction = new WordPoints_Entity_Restriction_Legacy(
			1
			, array( 'nonexistent' )
		);

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it doesn't apply when the entity is registered but not an entity.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_not_entity() {

		$this->mock_apps();

		wordpoints_entities()->register(
			'test_entity'
			, 'WordPoints_PHPUnit_Mock_Entityish'
		);

		$restriction = new WordPoints_Entity_Restriction_Legacy(
			1
			, array( 'test_entity' )
		);

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it applies when the entity is restricted but not from them.
	 *
	 * @since 2.2.0
	 *
	 * @expectedDeprecated WordPoints_Entity_Restriction_Legacy::__construct
	 */
	public function test_applies_restricted() {

		$this->mock_apps();

		wordpoints_entities()->register(
			'test_entity'
			, 'WordPoints_PHPUnit_Mock_Entity_Restricted_Visibility'
		);

		$restriction = new WordPoints_Entity_Restriction_Legacy(
			1
			, array( 'test_entity' )
		);

		$this->assertTrue( $restriction->applies() );
	}

	/**
	 * Test that the user can when the entity is not restricted.
	 *
	 * @since 2.2.0
	 *
	 * @expectedDeprecated wordpoints_entity_user_can_view
	 */
	public function test_user_can_not_restricted() {

		$user_id = $this->factory->user->create();

		$entity_slug = $this->factory->wordpoints->entity->create();

		$this->listen_for_filter( 'wordpoints_entity_user_can_view' );

		$restriction = new WordPoints_Entity_Restriction_Legacy(
			1
			, array( $entity_slug )
		);

		$this->assertTrue( $restriction->user_can( $user_id ) );

		$this->assertSame(
			1
			, $this->filter_was_called( 'wordpoints_entity_user_can_view' )
		);

		add_filter( 'wordpoints_entity_user_can_view', '__return_false' );

		$this->assertFalse( $restriction->user_can( $user_id ) );
	}

	/**
	 * Test that the user can when the entity is not registered.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_not_registered() {

		$user_id = $this->factory->user->create();

		$this->listen_for_filter( 'wordpoints_entity_user_can_view' );

		$restriction = new WordPoints_Entity_Restriction_Legacy(
			1
			, array( 'nonexistent' )
		);

		$this->assertTrue( $restriction->user_can( $user_id ) );

		$this->assertSame(
			0
			, $this->filter_was_called( 'wordpoints_entity_user_can_view' )
		);
	}

	/**
	 * Test that the user can when the entity is registered but not an entity.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_not_entity() {

		$this->mock_apps();

		$user_id = $this->factory->user->create();

		wordpoints_entities()->register(
			'test_entity'
			, 'WordPoints_PHPUnit_Mock_Entityish'
		);

		$this->listen_for_filter( 'wordpoints_entity_user_can_view' );

		$restriction = new WordPoints_Entity_Restriction_Legacy(
			1
			, array( 'test_entity' )
		);

		$this->assertTrue( $restriction->user_can( $user_id ) );

		$this->assertSame(
			0
			, $this->filter_was_called( 'wordpoints_entity_user_can_view' )
		);
	}

	/**
	 * Test that the user can when the entity is restricted but not from them.
	 *
	 * @since 2.2.0
	 *
	 * @expectedDeprecated wordpoints_entity_user_can_view
	 * @expectedDeprecated WordPoints_Entity_Restriction_Legacy::__construct
	 */
	public function test_user_can_restricted() {

		$this->mock_apps();

		$user_id = $this->factory->user->create();

		wordpoints_entities()->register(
			'test_entity'
			, 'WordPoints_PHPUnit_Mock_Entity_Restricted_Visibility'
		);

		$this->listen_for_filter( 'wordpoints_entity_user_can_view' );

		$restriction = new WordPoints_Entity_Restriction_Legacy(
			1
			, array( 'test_entity' )
		);

		$this->assertTrue( $restriction->user_can( $user_id ) );

		$this->assertSame(
			1
			, $this->filter_was_called( 'wordpoints_entity_user_can_view' )
		);

		add_filter( 'wordpoints_entity_user_can_view', '__return_false' );

		$this->assertFalse( $restriction->user_can( $user_id ) );
	}

	/**
	 * Test that the user can't when the entity is restricted from them.
	 *
	 * @since 2.2.0
	 *
	 * @expectedDeprecated wordpoints_entity_user_can_view
	 * @expectedDeprecated WordPoints_Entity_Restriction_Legacy::__construct
	 */
	public function test_user_can_restricted_not_viewable() {

		$this->mock_apps();

		$user_id = $this->factory->user->create();

		wordpoints_entities()->register(
			'test_entity'
			, 'WordPoints_PHPUnit_Mock_Entity_Restricted_Visibility'
		);

		WordPoints_PHPUnit_Mock_Entity_Restricted_Visibility::$can_view = false;

		$this->listen_for_filter( 'wordpoints_entity_user_can_view' );

		$restriction = new WordPoints_Entity_Restriction_Legacy(
			1
			, array( 'test_entity' )
		);

		$this->assertFalse( $restriction->user_can( $user_id ) );

		$this->assertSame(
			1
			, $this->filter_was_called( 'wordpoints_entity_user_can_view' )
		);

		add_filter( 'wordpoints_entity_user_can_view', '__return_true' );

		$this->assertTrue( $restriction->user_can( $user_id ) );
	}
}

// EOF
