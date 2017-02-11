<?php

/**
 * Test the WordPoints array option wrapper.
 *
 * @package WordPoints\Tests\Options
 * @since 1.0.0
 */

/**
 * Test custom option API.
 *
 * @since 1.0.1
 */
class WordPoints_Option_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test that wordpoints_get_array_option() handles incorrect types properly.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_get_array_option
	 *
	 * @expectedDeprecated wordpoints_get_array_option
	 */
	public function test_get_array_typechecks() {

		add_option( 'wordpoints_not_array', 'blah' );
		$array_option = wordpoints_get_array_option( 'wordpoints_not_array' );
		$this->assertSame( array(), $array_option );

		add_site_option( 'wordpoints_not_array', 'blah' );
		$array_option = wordpoints_get_array_option( 'wordpoints_not_array', 'site' );
		$this->assertSame( array(), $array_option );

		$array_option = wordpoints_get_array_option( 'wordpoints_not_array', 'network' );
		$this->assertSame( array(), $array_option );
	}

	/**
	 * Test get and update of network options.
	 *
	 * @since 1.2.0
	 *
	 * @covers ::wordpoints_get_network_option
	 * @covers ::wordpoints_update_network_option
	 * @covers ::wordpoints_add_network_option
	 * @covers ::wordpoints_delete_network_option
	 *
	 * @expectedDeprecated wordpoints_get_network_option
	 * @expectedDeprecated wordpoints_update_network_option
	 * @expectedDeprecated wordpoints_add_network_option
	 * @expectedDeprecated wordpoints_delete_network_option
	 */
	public function test_network_options() {

		add_option( 'wordpoints_test', array( 'option' ) );
		add_site_option( 'wordpoints_test', array( 'site_option' ) );

		$option = wordpoints_get_network_option( 'wordpoints_test' );

		if ( is_wordpoints_network_active() ) {
			$this->assertSame( array( 'site_option' ), $option );
		} else {
			$this->assertSame( array( 'option' ), $option );
		}

		wordpoints_update_network_option( 'wordpoints_test', array( 'test' ) );

		if ( is_wordpoints_network_active() ) {
			$option = get_site_option( 'wordpoints_test' );
		} else {
			$option = get_option( 'wordpoints_test' );
		}

		$this->assertSame( array( 'test' ), $option );

		wordpoints_delete_network_option( 'wordpoints_test' );

		if ( is_wordpoints_network_active() ) {
			$this->assertFalse( get_site_option( 'wordpoints_test' ) );
		} else {
			$this->assertFalse( get_option( 'wordpoints_test' ) );
		}

		wordpoints_add_network_option( 'another_test', 'test' );

		if ( is_wordpoints_network_active() ) {
			$this->assertSame( 'test', get_site_option( 'another_test' ) );
		} else {
			$this->assertSame( 'test', get_option( 'another_test' ) );
		}
	}
}

// EOF
