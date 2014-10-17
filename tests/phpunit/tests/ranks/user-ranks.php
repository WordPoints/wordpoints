<?php

/**
 * A test case for the user rank API.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Test that the user ranks API works.
 *
 * @since 1.7.0
 *
 * @group ranks
 */
class WordPoints_User_Ranks_Test extends WordPoints_Ranks_UnitTestCase {

	/**
	 * Test that the user's rank defaults to the base rank of the group.
	 *
	 * @since 1.7.0
	 */
	public function test_user_rank_defaults_to_base() {

		$user_id = $this->factory->user->create();

		$rank = wordpoints_get_rank(
			wordpoints_get_user_rank( $user_id, $this->rank_group )
		);

		$this->assertInstanceOf( 'WordPoints_Rank', $rank );
		$this->assertEquals( 'base', $rank->type );
	}

	/**
	 * Test getting a user's rank from an invalid group.
	 *
	 * @since 1.7.0
	 */
	public function test_get_invalid_group() {

		$this->assertFalse(
			wordpoints_get_user_rank( $this->factory->user->create(), 'invalid' )
		);
	}

	/**
	 * Test updating a user's rank.
	 *
	 * @since 1.7.0
	 */
	public function test_update_user_rank() {

		$rank_id = $this->factory->wordpoints_rank->create();
		$user_id = $this->factory->user->create();

		$result = wordpoints_update_user_rank( $user_id, $rank_id );

		$this->assertTrue( $result );

		$this->assertEquals(
			$rank_id
			, wordpoints_get_user_rank( $user_id, $this->rank_group )
		);
	}

	/**
	 * Test update with a non-existant rank.
	 *
	 * @since 1.7.0
	 */
	public function test_update_invalid_rank() {

		$rank_id = $this->factory->wordpoints_rank->create();
		$user_id = $this->factory->user->create();

		wordpoints_delete_rank( $rank_id );

		$result = wordpoints_update_user_rank( $user_id, $rank_id );

		$this->assertFalse( $result );

		$this->assertNotEquals(
			$rank_id
			, wordpoints_get_user_rank( $user_id, $this->rank_group )
		);
	}

	/**
	 * Test updating with the same rank.
	 *
	 * @since 1.7.0
	 */
	public function test_update_same_rank() {

		$user_id = $this->factory->user->create();

		$result = wordpoints_update_user_rank(
			$user_id
			, wordpoints_get_user_rank( $user_id, $this->rank_group )
		);

		$this->assertTrue( $result );
	}
}

// EOF
