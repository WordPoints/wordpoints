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
class WordPoints_Points_Logs_Meta_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test the log meta flow.
	 *
	 * @since 1.0.0
	 */
	function test_log_meta() {

		$log_id = 1;

		wordpoints_add_points_log_meta( $log_id, 'test', 'one' );
		$this->assertEquals( 'one', wordpoints_get_points_log_meta( $log_id, 'test', true ) );

		wordpoints_update_points_log_meta( $log_id, 'test', 'two' );
		$this->assertEquals( 'two', wordpoints_get_points_log_meta( $log_id, 'test', true ) );

		$this->assertEquals( array( 'two' ), wordpoints_get_points_log_meta( $log_id, 'test' ) );
		$this->assertEquals( array( 'test' => 'two' ), wordpoints_get_points_log_meta( $log_id, null, true ) );
		$this->assertEquals( array( 'test' => array( 'two' ) ), wordpoints_get_points_log_meta( $log_id ) );

		$result = wordpoints_update_points_log_meta( $log_id, 'test', 'three', 'one' );
		$this->assertEquals( 0, $result );
		$this->assertEquals( 'two', wordpoints_get_points_log_meta( $log_id, 'test', true ) );

		$result = wordpoints_update_points_log_meta( $log_id, 'test', 'three', 'two' );
		$this->assertEquals( 1, $result );
		$this->assertEquals( 'three', wordpoints_get_points_log_meta( $log_id, 'test', true ) );
	}
}

// EOF
