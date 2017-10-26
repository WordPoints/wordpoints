<?php

/**
 * Test case for WordPoints_Entity_Restriction_Wrapper.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.2.0
 */

/**
 * Tests WordPoints_Entity_Restriction_Wrapper.
 *
 * @since 2.2.0
 *
 * @covers WordPoints_Entity_Restriction_Wrapper
 */
class WordPoints_Entity_Restriction_Wrapper_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test that it doesn't apply when there are no restrictions.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_none() {

		$restriction = new WordPoints_Entity_Restriction_Wrapper(
			0
			, array( 'post\post' )
		);

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it doesn't apply when the restrictions don't apply.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_none_apply() {

		$restriction = new WordPoints_Entity_Restriction_Wrapper(
			0
			, array( 'post\post' )
			, array(
				new WordPoints_PHPUnit_Mock_Entity_Restriction(
					0
					, array( 'post\post' )
					, true
					, false
				),
				new WordPoints_PHPUnit_Mock_Entity_Restriction(
					0
					, array( 'post\post' )
					, true
					, false
				),
			)
		);

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it applies when some of the restrictions apply.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_some_apply() {

		$restriction = new WordPoints_Entity_Restriction_Wrapper(
			0
			, array( 'post\post' )
			, array(
				new WordPoints_PHPUnit_Mock_Entity_Restriction(
					0
					, array( 'post\post' )
					, true
					, false
				),
				new WordPoints_PHPUnit_Mock_Entity_Restriction(
					0
					, array( 'post\post' )
					, true
				),
			)
		);

		$this->assertTrue( $restriction->applies() );
	}

	/**
	 * Test that the user can when there are no restrictions.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_none() {

		$restriction = new WordPoints_Entity_Restriction_Wrapper(
			0
			, array( 'post\post' )
		);

		$this->assertTrue( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can when the restrictions don't apply.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_none_apply() {

		$restriction = new WordPoints_Entity_Restriction_Wrapper(
			0
			, array( 'post\post' )
			, array(
				new WordPoints_PHPUnit_Mock_Entity_Restriction(
					0
					, array( 'post\post' )
					, true
					, false
				),
				new WordPoints_PHPUnit_Mock_Entity_Restriction(
					0
					, array( 'post\post' )
					, true
					, false
				),
			)
		);

		$this->assertTrue( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can when the restrictions apply but don't restrict.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_some_apply() {

		$restriction = new WordPoints_Entity_Restriction_Wrapper(
			0
			, array( 'post\post' )
			, array(
				new WordPoints_PHPUnit_Mock_Entity_Restriction(
					0
					, array( 'post\post' )
					, true
					, false
				),
				new WordPoints_PHPUnit_Mock_Entity_Restriction(
					0
					, array( 'post\post' )
					, true
				),
			)
		);

		$this->assertTrue( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can't when some restrictions say they can't.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_some_cant() {

		$restriction = new WordPoints_Entity_Restriction_Wrapper(
			0
			, array( 'post\post' )
			, array(
				new WordPoints_PHPUnit_Mock_Entity_Restriction(
					0
					, array( 'post\post' )
					, true
					, false
				),
				new WordPoints_PHPUnit_Mock_Entity_Restriction(
					0
					, array( 'post\post' )
				),
				new WordPoints_PHPUnit_Mock_Entity_Restriction(
					0
					, array( 'post\post' )
					, true
				),
			)
		);

		$this->assertFalse( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can when there are no restrictions.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_none_context() {

		$context = $this->factory->wordpoints->entity_context->create_and_get();

		$this->assertSame( 1, $context->get_current_id() );

		$context_slug = $context->get_slug();

		$restriction = new WordPoints_Entity_Restriction_Wrapper(
			0
			, array( 'post\post' )
			, array()
			, array( $context_slug => 5 )
		);

		$this->assertTrue( $restriction->user_can( 0 ) );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test that the user can when the restrictions don't apply.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_none_apply_context() {

		$context = $this->factory->wordpoints->entity_context->create_and_get();

		$this->assertSame( 1, $context->get_current_id() );

		$restriction = new WordPoints_Entity_Restriction_Wrapper(
			0
			, array( 'post\post' )
			, array(
				new WordPoints_PHPUnit_Mock_Entity_Restriction(
					0
					, array( 'post\post' )
					, true
					, false
				),
				new WordPoints_PHPUnit_Mock_Entity_Restriction(
					0
					, array( 'post\post' )
					, true
					, false
				),
			)
			, array( $context->get_slug() => 5 )
		);

		$this->assertTrue( $restriction->user_can( 0 ) );

		$this->assertSame( 1, $context->get_current_id() );
	}

	/**
	 * Test that the user can when the restrictions apply but don't restrict.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_some_apply_context() {

		$context = $this->factory->wordpoints->entity_context->create_and_get();

		$this->assertSame( 1, $context->get_current_id() );

		$mock = new WordPoints_PHPUnit_Mock_Entity_Restriction(
			0
			, array( 'post\post' )
			, true
		);

		$context_slug             = $context->get_slug();
		$mock->listen_for_context = $context_slug;

		$restriction = new WordPoints_Entity_Restriction_Wrapper(
			0
			, array( 'post\post' )
			, array(
				new WordPoints_PHPUnit_Mock_Entity_Restriction(
					0
					, array( 'post\post' )
					, true
					, false
				),
				$mock,
			)
			, array( $context_slug => 5 )
		);

		$this->assertTrue( $restriction->user_can( 0 ) );

		$this->assertSame( 1, $context->get_current_id() );
		$this->assertSame( 5, $mock->context[0] );
	}

	/**
	 * Test that the user can't when some restrictions say they can't.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_some_cant_context() {

		$context = $this->factory->wordpoints->entity_context->create_and_get();

		$this->assertSame( 1, $context->get_current_id() );

		$mock = new WordPoints_PHPUnit_Mock_Entity_Restriction(
			0
			, array( 'post\post' )
		);

		$context_slug             = $context->get_slug();
		$mock->listen_for_context = $context_slug;

		$restriction = new WordPoints_Entity_Restriction_Wrapper(
			0
			, array( 'post\post' )
			, array(
				new WordPoints_PHPUnit_Mock_Entity_Restriction(
					0
					, array( 'post\post' )
					, true
					, false
				),
				$mock,
				new WordPoints_PHPUnit_Mock_Entity_Restriction(
					0
					, array( 'post\post' )
					, true
				),
			)
			, array( $context_slug => 5 )
		);

		$this->assertFalse( $restriction->user_can( 0 ) );

		$this->assertSame( 1, $context->get_current_id() );
		$this->assertSame( 5, $mock->context[0] );
	}
}

// EOF
