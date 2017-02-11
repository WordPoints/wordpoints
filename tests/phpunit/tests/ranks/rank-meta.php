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
class WordPoints_Rank_Meta_Test extends WordPoints_PHPUnit_TestCase_Ranks {

	/**
	 * Test adding rank meta.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_add_rank_meta
	 * @covers ::wordpoints_get_rank_meta
	 */
	public function test_add_rank_meta() {

		$rank_id = $this->factory->wordpoints_rank->create();

		$result = wordpoints_add_rank_meta( $rank_id, 'test', 'value' );

		$this->assertInternalType( 'int', $result );

		$this->assertSame(
			'value'
			, wordpoints_get_rank_meta( $rank_id, 'test', true )
		);
	}

	/**
	 * Test updating rank meta.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_update_rank_meta
	 */
	public function test_update_rank_meta() {

		$rank_id = $this->factory->wordpoints_rank->create();

		$result = wordpoints_update_rank_meta( $rank_id, 'test_meta', __METHOD__ );

		$this->assertTrue( $result );

		$this->assertSame(
			__METHOD__
			, wordpoints_get_rank_meta( $rank_id, 'test_meta', true )
		);
	}

	/**
	 * Test deleting rank meta.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_delete_rank_meta
	 */
	public function test_delete_rank_meta() {

		$rank_id = $this->factory->wordpoints_rank->create();

		wordpoints_add_rank_meta( $rank_id, 'test', 'value' );

		$this->assertSame(
			'value'
			, wordpoints_get_rank_meta( $rank_id, 'test', true )
		);

		wordpoints_delete_rank_meta( $rank_id, 'test' );

		$this->assertSame(
			''
			, wordpoints_get_rank_meta( $rank_id, 'test', true )
		);
	}

	/**
	 * Test retrieving multiple meta values at once.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_get_rank_meta
	 */
	public function test_get_multiple_rank_metadata() {

		$rank_id = $this->factory->wordpoints_rank->create();

		wordpoints_add_rank_meta( $rank_id, 'test', 'value' );
		wordpoints_add_rank_meta( $rank_id, 'test_meta', 'test' );

		$this->assertSame(
			array( '1', 'test' )
			, wordpoints_get_rank_meta( $rank_id, 'test_meta' )
		);

		$all_meta = wordpoints_get_rank_meta( $rank_id );
		$this->assertArrayHasKey( 'test', $all_meta );
		$this->assertSame( array( 'value' ), $all_meta['test'] );
		$this->assertArrayHasKey( 'test_meta', $all_meta );
		$this->assertSame( array( '1', 'test' ), $all_meta['test_meta'] );
	}

	/**
	 * Test adding rank meta with slashes.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_add_rank_meta
	 * @covers ::wordpoints_get_rank_meta
	 */
	public function test_add_rank_meta_slashing() {

		$rank_id = $this->factory->wordpoints_rank->create();

		wordpoints_add_rank_meta( $rank_id, 'test\slashing', 'slashed\value' );

		$this->assertSame(
			'slashed\value'
			, wordpoints_get_rank_meta( $rank_id, 'test\slashing', true )
		);
	}

	/**
	 * Test updating rank meta with slashes.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_update_rank_meta
	 */
	public function test_update_rank_meta_slashing() {

		$rank_id = $this->factory->wordpoints_rank->create();

		wordpoints_add_rank_meta( $rank_id, 'test\slashing', 'value' );

		wordpoints_update_rank_meta( $rank_id, 'test\slashing', 'slashed\value' );

		$this->assertSame(
			'slashed\value'
			, wordpoints_get_rank_meta( $rank_id, 'test\slashing', true )
		);
	}

	/**
	 * Test deleting rank meta with slashes.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_delete_rank_meta
	 */
	public function test_delete_rank_meta_slashing() {

		$rank_id = $this->factory->wordpoints_rank->create();

		wordpoints_add_rank_meta( $rank_id, 'test\slashing', 'value' );

		$this->assertSame(
			'value'
			, wordpoints_get_rank_meta( $rank_id, 'test\slashing', true )
		);

		wordpoints_delete_rank_meta( $rank_id, 'test\slashing' );

		$this->assertSame(
			''
			, wordpoints_get_rank_meta( $rank_id, 'test\slashing', true )
		);
	}
}

// EOF
