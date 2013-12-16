<?php

/**
 * Test the WordPoints array option wrapper.
 *
 * @package WordPoints\Tests\Options
 * @since 1.0.0
 */

/**
 * Test wordpoints_get_array_option().
 *
 * @since 1.0.1
 */
class WordPoints_Get_Array_Option_Test extends WP_UnitTestCase {

	/**
	 * Test that wordpoints_get_array_option() handles incorrect types properly.
	 *
	 * @since 1.0.0
	 */
	public function test_get_typechecks() {

		add_option( 'wordpoints_not_array', 'blah' );
		$array_option = wordpoints_get_array_option( 'wordpoints_not_array' );
		$this->assertEquals( array(), $array_option );

		$array_option = wordpoints_get_array_option( 'wordpoints_not_array', 'site' );
		$this->assertEquals( array(), $array_option );
	}
}

// end of file /tests/phpunit/tests/options.php
