<?php

/**
 * Test the points log meta functions.
 *
 * @package WordPoints\Tests\Points
 * @since 1.0.0
 */

/**
 * Points log meta test case.
 *
 * @since 1.0.0
 *
 * @group points
 */
class WordPoints_Points_Logs_Meta_Test extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * Test the log meta flow.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_add_points_log_meta
	 * @covers ::wordpoints_get_points_log_meta
	 * @covers ::wordpoints_update_points_log_meta
	 */
	public function test_log_meta() {

		$log_id = 1;

		wordpoints_add_points_log_meta( $log_id, 'test', 'one' );
		$this->assertSame( 'one', wordpoints_get_points_log_meta( $log_id, 'test', true ) );

		wordpoints_update_points_log_meta( $log_id, 'test', 'two' );
		$this->assertSame( 'two', wordpoints_get_points_log_meta( $log_id, 'test', true ) );

		$this->assertSame( array( 'two' ), wordpoints_get_points_log_meta( $log_id, 'test' ) );
		$this->assertSame( array( 'test' => array( 'two' ) ), wordpoints_get_points_log_meta( $log_id ) );

		$result = wordpoints_update_points_log_meta( $log_id, 'test', 'three', 'one' );
		$this->assertFalse( $result );
		$this->assertSame( 'two', wordpoints_get_points_log_meta( $log_id, 'test', true ) );

		$result = wordpoints_update_points_log_meta( $log_id, 'test', 'three', 'two' );
		$this->assertTrue( $result );
		$this->assertSame( 'three', wordpoints_get_points_log_meta( $log_id, 'test', true ) );
	}

	/**
	 * Test deleting all log meta.
	 *
	 * @since 1.8.0
	 *
	 * @covers ::wordpoints_points_log_delete_all_metadata
	 */
	public function test_delete_all_log_meta() {

		$log_id = 1;

		wordpoints_update_points_log_meta( $log_id, 'test', 'one' );
		$this->assertSame( 'one', wordpoints_get_points_log_meta( $log_id, 'test', true ) );

		wordpoints_update_points_log_meta( $log_id, 'test_2', 'two' );
		$this->assertSame( 'two', wordpoints_get_points_log_meta( $log_id, 'test_2', true ) );

		wordpoints_points_log_delete_all_metadata( $log_id );

		$this->assertSame( '', wordpoints_get_points_log_meta( $log_id, 'test', true ) );
		$this->assertSame( '', wordpoints_get_points_log_meta( $log_id, 'test_2', true ) );
		$this->assertSame( array(), wordpoints_get_points_log_meta( $log_id ) );
	}

	/**
	 * Test that the data isn't expected slashed.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_add_points_log_meta
	 * @covers ::wordpoints_get_points_log_meta
	 * @covers ::wordpoints_update_points_log_meta
	 * @covers ::wordpoints_delete_points_log_meta
	 */
	public function test_log_meta_slashing() {

		$log_id = 1;

		wordpoints_add_points_log_meta( $log_id, 'slash\test', 'test\slashing' );
		$this->assertSame( 'test\slashing', wordpoints_get_points_log_meta( $log_id, 'slash\test', true ) );

		wordpoints_update_points_log_meta( $log_id, 'slash\test', 'test\slashing2' );
		$this->assertSame( 'test\slashing2', wordpoints_get_points_log_meta( $log_id, 'slash\test', true ) );

		wordpoints_delete_points_log_meta( $log_id, 'slash\test', 'test\slashing2' );
		$this->assertSame( '', wordpoints_get_points_log_meta( $log_id, 'slash\test', true ) );
	}

	/**
	 * Test the $unique parameter of the add meta function.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_add_points_log_meta
	 */
	public function test_add_log_meta_unique() {

		$log_id = 1;

		$this->assertInternalType(
			'int'
			, wordpoints_add_points_log_meta( $log_id, 'test', 'one' )
		);

		$this->assertSame(
			'one'
			, wordpoints_get_points_log_meta( $log_id, 'test', true )
		);

		$this->assertFalse(
			wordpoints_add_points_log_meta( $log_id, 'test', 'two', true )
		);

		$this->assertSame(
			'one'
			, wordpoints_get_points_log_meta( $log_id, 'test', true )
		);
	}

	/**
	 * Test the $unique parameter of the add meta function is false by default.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_add_points_log_meta
	 */
	public function test_add_log_meta_unique_false_by_default() {

		$log_id = 1;

		$this->assertInternalType(
			'int'
			, wordpoints_add_points_log_meta( $log_id, 'test', 'one' )
		);

		$this->assertSame(
			array( 'one' )
			, wordpoints_get_points_log_meta( $log_id, 'test' )
		);

		$this->assertInternalType(
			'int'
			, wordpoints_add_points_log_meta( $log_id, 'test', 'two' )
		);

		$this->assertSame(
			array( 'one', 'two' )
			, wordpoints_get_points_log_meta( $log_id, 'test' )
		);
	}
}

// EOF
