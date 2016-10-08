<?php

/**
 * Test case for WordPoints_Entity_Contexts.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.2.0
 */

/**
 * Tests WordPoints_Entity_Contexts.
 *
 * @since 2.2.0
 *
 * @covers WordPoints_Entity_Contexts
 */
class WordPoints_Entity_Contexts_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test that it returns false when attempting to switch to an unknown context.
	 *
	 * @since 2.2.0
	 */
	public function test_switch_to_unknown_context() {

		$contexts = wordpoints_entities()->get_sub_app( 'contexts' );

		$this->assertFalse( $contexts->switch_to( array( 'unknown' => 5 ) ) );

		$this->assertFalse( $contexts->switch_back() );
	}

	/**
	 * Test it returns false when attempting to switch to an unknown sub-context.
	 *
	 * @since 2.2.0
	 */
	public function test_switch_to_unknown_sub_context() {

		$context = $this->factory->wordpoints->entity_context->create_and_get();

		$contexts = wordpoints_entities()->get_sub_app( 'contexts' );

		$this->assertEquals( 1, $context->get_current_id() );

		$this->assertFalse(
			$contexts->switch_to(
				array( 'unknown' => 5, $context->get_slug() => 7 )
			)
		);

		$this->assertEquals( 1, $context->get_current_id() );

		$this->assertFalse( $contexts->switch_back() );
	}

	/**
	 * Test it returns false when attempting to switch to an unknown parent context.
	 *
	 * @since 2.2.0
	 */
	public function test_switch_to_unknown_parent_context() {

		$context = $this->factory->wordpoints->entity_context->create_and_get();

		$contexts = wordpoints_entities()->get_sub_app( 'contexts' );

		$this->assertEquals( 1, $context->get_current_id() );

		$this->assertFalse(
			$contexts->switch_to(
				array( $context->get_slug() => 7, 'unknown' => 5 )
			)
		);

		$this->assertEquals( 1, $context->get_current_id() );

		$this->assertFalse( $contexts->switch_back() );
	}

	/**
	 * Test that it returns true when attempting to switch to the current context.
	 *
	 * @since 2.2.0
	 */
	public function test_switch_to_current_context() {

		$context = $this->factory->wordpoints->entity_context->create_and_get();

		$contexts = wordpoints_entities()->get_sub_app( 'contexts' );

		$context->switch_to( 5 );

		$this->assertTrue(
			$contexts->switch_to( array( $context->get_slug() => 5 ) )
		);

		$this->assertEquals( 5, $context->get_current_id() );

		$this->assertTrue( $contexts->switch_back() );

		$this->assertEquals( 5, $context->get_current_id() );
	}

	/**
	 * Test it returns true when attempting to switch to the current sub-context.
	 *
	 * @since 2.2.0
	 */
	public function test_switch_to_current_sub_context() {

		$context = $this->factory->wordpoints->entity_context->create_and_get();
		$parent_slug = $context->get_slug();

		/** @var WordPoints_PHPUnit_Mock_Entity_Context $subcontext */
		$subcontext = $this->factory->wordpoints->entity_context->create_and_get();
		$subcontext->set( 'parent_slug', $parent_slug );

		$contexts = wordpoints_entities()->get_sub_app( 'contexts' );

		$context->switch_to( 5 );
		$subcontext->switch_to( 8 );

		$this->assertTrue(
			$contexts->switch_to(
				array( $subcontext->get_slug() => 8, $parent_slug => 5 )
			)
		);

		$this->assertEquals( 5, $context->get_current_id() );
		$this->assertEquals( 8, $subcontext->get_current_id() );

		$this->assertTrue( $contexts->switch_back() );

		$this->assertEquals( 5, $context->get_current_id() );
		$this->assertEquals( 8, $subcontext->get_current_id() );
	}

	/**
	 * Test that it switched to the passed context.
	 *
	 * @since 2.2.0
	 */
	public function test_switch_to_context() {

		$context = $this->factory->wordpoints->entity_context->create_and_get();

		$contexts = wordpoints_entities()->get_sub_app( 'contexts' );

		$this->assertEquals( 1, $context->get_current_id() );

		$this->assertTrue(
			$contexts->switch_to( array( $context->get_slug() => 5 ) )
		);

		$this->assertEquals( 5, $context->get_current_id() );

		$this->assertTrue( $contexts->switch_back() );

		$this->assertEquals( 1, $context->get_current_id() );
	}

	/**
	 * Test that it switched to the passed (sub-)context.
	 *
	 * @since 2.2.0
	 */
	public function test_switch_to_sub_context() {

		$context = $this->factory->wordpoints->entity_context->create_and_get();
		$parent_slug = $context->get_slug();

		/** @var WordPoints_PHPUnit_Mock_Entity_Context $subcontext */
		$subcontext = $this->factory->wordpoints->entity_context->create_and_get();
		$subcontext->set( 'parent_slug', $parent_slug );

		$contexts = wordpoints_entities()->get_sub_app( 'contexts' );

		$this->assertEquals( 1, $context->get_current_id() );
		$this->assertEquals( 1, $subcontext->get_current_id() );

		$this->assertTrue(
			$contexts->switch_to(
				array( $subcontext->get_slug() => 8, $parent_slug => 5 )
			)
		);

		$this->assertEquals( 5, $context->get_current_id() );
		$this->assertEquals( 8, $subcontext->get_current_id() );

		$this->assertTrue( $contexts->switch_back() );

		$this->assertEquals( 1, $context->get_current_id() );
		$this->assertEquals( 1, $subcontext->get_current_id() );
	}

	/**
	 * Test that it returns false if switching to the passed context failed.
	 *
	 * @since 2.2.0
	 */
	public function test_switch_to_context_failed() {

		/** @var WordPoints_PHPUnit_Mock_Entity_Context $context */
		$context = $this->factory->wordpoints->entity_context->create_and_get();
		$context_slug = $context->get_slug();

		$context::$fail_switching[ $context_slug ] = true;

		$contexts = wordpoints_entities()->get_sub_app( 'contexts' );

		$this->assertEquals( 1, $context->get_current_id() );

		$this->assertFalse(
			$contexts->switch_to( array( $context_slug => 5 ) )
		);

		$this->assertEquals( 1, $context->get_current_id() );

		$this->assertFalse( $contexts->switch_back() );
	}

	/**
	 * Test that it returns false if switching to the passed (sub-)context failed.
	 *
	 * @since 2.2.0
	 */
	public function test_switch_to_sub_context_failed() {

		/** @var WordPoints_PHPUnit_Mock_Entity_Context $context */
		$context = $this->factory->wordpoints->entity_context->create_and_get();
		$parent_slug = $context->get_slug();

		/** @var WordPoints_PHPUnit_Mock_Entity_Context $subcontext */
		$subcontext = $this->factory->wordpoints->entity_context->create_and_get();
		$subcontext->set( 'parent_slug', $parent_slug );
		$subcontext_slug = $subcontext->get_slug();

		$context::$fail_switching[ $subcontext_slug ] = true;

		$contexts = wordpoints_entities()->get_sub_app( 'contexts' );

		$this->assertEquals( 1, $context->get_current_id() );
		$this->assertEquals( 1, $subcontext->get_current_id() );

		$this->assertFalse(
			$contexts->switch_to(
				array( $subcontext_slug => 8, $parent_slug => 5 )
			)
		);

		$this->assertEquals( 1, $context->get_current_id() );
		$this->assertEquals( 1, $subcontext->get_current_id() );

		$this->assertFalse( $contexts->switch_back() );
	}

	/**
	 * Test that it returns false if switching to the passed (sub-)context failed.
	 *
	 * @since 2.2.0
	 */
	public function test_switch_to_sub_context_parent_failed() {

		/** @var WordPoints_PHPUnit_Mock_Entity_Context $context */
		$context = $this->factory->wordpoints->entity_context->create_and_get();
		$parent_slug = $context->get_slug();

		/** @var WordPoints_PHPUnit_Mock_Entity_Context $subcontext */
		$subcontext = $this->factory->wordpoints->entity_context->create_and_get();
		$subcontext->set( 'parent_slug', $parent_slug );
		$subcontext_slug = $subcontext->get_slug();

		$context::$fail_switching[ $parent_slug ] = true;

		$contexts = wordpoints_entities()->get_sub_app( 'contexts' );

		$this->assertEquals( 1, $context->get_current_id() );
		$this->assertEquals( 1, $subcontext->get_current_id() );

		$this->assertFalse(
			$contexts->switch_to(
				array( $subcontext_slug => 8, $parent_slug => 5 )
			)
		);

		$this->assertEquals( 1, $context->get_current_id() );
		$this->assertEquals( 1, $subcontext->get_current_id() );

		$this->assertFalse( $contexts->switch_back() );
	}

	/**
	 * Test that it returns false when attempting to switch back if not switched.
	 *
	 * @since 2.2.0
	 */
	public function test_switch_back_not_switched() {

		$switcher = wordpoints_entities()->get_sub_app( 'contexts' );

		$this->assertFalse( $switcher->switch_back() );
	}

	/**
	 * Test it returns false when switching back if a context is not switched.
	 *
	 * @since 2.2.0
	 */
	public function test_switch_back_context_not_switched() {

		/** @var WordPoints_PHPUnit_Mock_Entity_Context $context */
		$context = $this->factory->wordpoints->entity_context->create_and_get();
		$parent_slug = $context->get_slug();

		/** @var WordPoints_PHPUnit_Mock_Entity_Context $subcontext */
		$subcontext = $this->factory->wordpoints->entity_context->create_and_get();
		$subcontext->set( 'parent_slug', $parent_slug );
		$subcontext_slug = $subcontext->get_slug();

		$contexts = wordpoints_entities()->get_sub_app( 'contexts' );

		$this->assertEquals( 1, $context->get_current_id() );
		$this->assertEquals( 1, $subcontext->get_current_id() );

		$this->assertTrue(
			$contexts->switch_to(
				array( $subcontext_slug => 8, $parent_slug => 5 )
			)
		);

		$this->assertEquals( 5, $context->get_current_id() );
		$this->assertEquals( 8, $subcontext->get_current_id() );

		// Oops.
		$this->assertTrue( $subcontext->switch_back() );

		$this->assertEquals( 5, $context->get_current_id() );
		$this->assertEquals( 1, $subcontext->get_current_id() );

		$this->assertFalse( $contexts->switch_back() );

		$this->assertEquals( 1, $context->get_current_id() );
		$this->assertEquals( 1, $subcontext->get_current_id() );
	}

	/**
	 * Test it returns false when switching back if a parent context is not switched.
	 *
	 * @since 2.2.0
	 */
	public function test_switch_back_parent_context_not_switched() {

		/** @var WordPoints_PHPUnit_Mock_Entity_Context $context */
		$context = $this->factory->wordpoints->entity_context->create_and_get();
		$parent_slug = $context->get_slug();

		/** @var WordPoints_PHPUnit_Mock_Entity_Context $subcontext */
		$subcontext = $this->factory->wordpoints->entity_context->create_and_get();
		$subcontext->set( 'parent_slug', $parent_slug );
		$subcontext_slug = $subcontext->get_slug();

		$contexts = wordpoints_entities()->get_sub_app( 'contexts' );

		$this->assertEquals( 1, $context->get_current_id() );
		$this->assertEquals( 1, $subcontext->get_current_id() );

		$this->assertTrue(
			$contexts->switch_to(
				array( $subcontext_slug => 8, $parent_slug => 5 )
			)
		);

		$this->assertEquals( 5, $context->get_current_id() );
		$this->assertEquals( 8, $subcontext->get_current_id() );

		// Oops.
		$this->assertTrue( $context->switch_back() );

		$this->assertEquals( 1, $context->get_current_id() );
		$this->assertEquals( 8, $subcontext->get_current_id() );

		$this->assertFalse( $contexts->switch_back() );

		$this->assertEquals( 1, $context->get_current_id() );
		$this->assertEquals( 8, $subcontext->get_current_id() );
	}
}

// EOF
