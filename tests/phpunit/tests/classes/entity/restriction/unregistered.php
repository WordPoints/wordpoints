<?php

/**
 * Test case for WordPoints_Entity_Restriction_Unregistered.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.2.0
 */

/**
 * Tests WordPoints_Entity_Restriction_Unregistered.
 *
 * @since 2.2.0
 *
 * @covers WordPoints_Entity_Restriction_Unregistered
 */
class WordPoints_Entity_Restriction_Unregistered_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test that it doesn't apply when the entity is registered.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_registered() {

		$restriction = new WordPoints_Entity_Restriction_Unregistered(
			0
			, array( 'post\post' )
		);

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it applies when the entity is unregistered.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_unregistered() {

		$restriction = new WordPoints_Entity_Restriction_Unregistered(
			0
			, array( 'nonexistent' )
		);

		$this->assertTrue( $restriction->applies() );
	}

	/**
	 * Test that the user can when the entity is registered.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_registered() {

		$restriction = new WordPoints_Entity_Restriction_Unregistered(
			0
			, array( 'post\post' )
		);

		$this->assertTrue( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can't when the entity isn't registered.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_unregistered() {

		$restriction = new WordPoints_Entity_Restriction_Unregistered(
			0
			, array( 'nonexistent' )
		);

		$this->assertFalse( $restriction->user_can( 0 ) );
	}
}

// EOF
