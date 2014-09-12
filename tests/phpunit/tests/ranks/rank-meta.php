<?php

/**
 * A test case for the rank meta API.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Test that the rank meta API functions work correctly.
 *
 * @since 1.7.0
 *
 * @group ranks
 * @group rank_meta
 */
class WordPoints_Rank_Meta_Test extends WordPoints_Ranks_UnitTestCase {

	/**
	 * Test adding rank meta.
	 *
	 * @since 1.7.0
	 */
	public function test_add_rank_meta() {

		$rank_id = $this->factory->wordpoints_rank->create();

		$result = wordpoints_add_rank_meta( $rank_id, 'test', 'value' );

		$this->assertInternalType( 'int', $result );

		$this->assertEquals(
			'value'
			, wordpoints_get_rank_meta( $rank_id, 'test', true )
		);
	}

	/**
	 * Test updating rank meta.
	 *
	 * @since 1.7.0
	 */
	public function test_update_rank_meta() {

		$rank_id = $this->factory->wordpoints_rank->create();

		$result = wordpoints_update_rank_meta( $rank_id, 'test_meta', __METHOD__ );

		$this->assertTrue( $result );

		$this->assertEquals(
			__METHOD__
			, wordpoints_get_rank_meta( $rank_id, 'test_meta', true )
		);
	}

	/**
	 * Test retrieving multiple meta values at once.
	 *
	 * @since 1.7.0
	 */
	public function test_get_multiple_rank_metadata() {

		$rank_id = $this->factory->wordpoints_rank->create();

		wordpoints_add_rank_meta( $rank_id, 'test', 'value' );
		wordpoints_add_rank_meta( $rank_id, 'test_meta', 'test' );

		$this->assertEquals(
			array( 1, 'test' )
			, wordpoints_get_rank_meta( $rank_id, 'test_meta' )
		);

		$all_meta = wordpoints_get_rank_meta( $rank_id );
		$this->assertArrayHasKey( 'test', $all_meta );
		$this->assertEquals( array( 'value' ), $all_meta['test'] );
		$this->assertArrayHasKey( 'test_meta', $all_meta );
		$this->assertEquals( array( 1, 'test' ), $all_meta['test_meta'] );
	}
}

// EOF
