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
class WordPoints_Option_Test extends WP_UnitTestCase {

	/**
	 * Test that wordpoints_get_array_option() handles incorrect types properly.
	 *
	 * @since 1.0.0
	 */
	public function test_get_array_typechecks() {

		add_option( 'wordpoints_not_array', 'blah' );
		$array_option = wordpoints_get_array_option( 'wordpoints_not_array' );
		$this->assertEquals( array(), $array_option );

		add_site_option( 'wordpoints_not_array', 'blah' );
		$array_option = wordpoints_get_array_option( 'wordpoints_not_array', 'site' );
		$this->assertEquals( array(), $array_option );

		$array_option = wordpoints_get_array_option( 'wordpoints_not_array', 'network' );
		$this->assertEquals( array(), $array_option );
	}

	/**
	 * Test get and update of network options.
	 *
	 * @since 1.2.0
	 */
	public function test_network_options() {

		add_option( 'wordpoints_test', array( 'option' ) );
		add_site_option( 'wordpoints_test', array( 'site_option' ) );

		$option = wordpoints_get_network_option( 'wordpoints_test' );

		if ( is_wordpoints_network_active() ) {
			$this->assertEquals( array( 'site_option' ), $option );
		} else {
			$this->assertEquals( array( 'option' ), $option );
		}

		wordpoints_update_network_option( 'wordpoints_test', array( 'test' ) );

		if ( is_wordpoints_network_active() ) {
			$option = get_site_option( 'wordpoints_test' );
		} else {
			$option = get_option( 'wordpoints_test' );
		}

		$this->assertEquals( array( 'test' ), $option );

		wordpoints_delete_network_option( 'wordpoints_test' );

		if ( is_wordpoints_network_active() ) {
			$this->assertFalse( get_site_option( 'wordpoints_test' ) );
		} else {
			$this->assertFalse( get_option( 'wordpoints_test' ) );
		}

		wordpoints_add_network_option( 'another_test', 'test' );

		if ( is_wordpoints_network_active() ) {
			$this->assertEquals( 'test', get_site_option( 'another_test' ) );
		} else {
			$this->assertEquals( 'test', get_option( 'another_test' ) );
		}
	}
}

// end of file /tests/phpunit/tests/options.php
