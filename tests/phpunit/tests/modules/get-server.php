<?php

/**
 * A test case for wordpoints_get_server_for_module().
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests wordpoints_get_server_for_module().
 *
 * @since 2.4.0
 *
 * @covers ::wordpoints_get_server_for_module
 */
class WordPoints_Get_Server_For_Module_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test that it returns the server object.
	 *
	 * @since 2.4.0
	 */
	public function test_returns_server() {

		$module = array( 'server' => 'wordpoints.org' );

		$server = wordpoints_get_server_for_module( $module );

		$this->assertInstanceOf( 'WordPoints_Module_ServerI', $server );
		$this->assertSame( $module['server'], $server->get_slug() );
	}

	/**
	 * Test that it calls the wordpoints_server_for_module filter.
	 *
	 * @since 2.4.0
	 */
	public function test_calls_filter() {

		add_filter(
			'wordpoints_server_for_module'
			, array( $this, 'filter_wordpoints_server_for_module' )
			, 10
			, 2
		);

		$server = wordpoints_get_server_for_module(
			array( 'server' => 'wordpoints.org' )
		);

		$this->assertSame( __CLASS__, $server->get_slug() );
	}

	/**
	 * Filters the server slug for a module.
	 *
	 * @since 2.4.0
	 *
	 * @see self::test_calls_filter()
	 *
	 * @param string $server The slug of the server to use.
	 * @param array  $module The module data.
	 *
	 * @return string The filtered server slug.
	 */
	public function filter_wordpoints_server_for_module( $server, $module ) {

		$this->assertSame( 'wordpoints.org', $server );
		$this->assertSame( array( 'server' => 'wordpoints.org' ), $module );

		return __CLASS__;
	}

	/**
	 * Test that it returns false if the module doesn't specify a server.
	 *
	 * @since 2.4.0
	 */
	public function test_returns_false_if_not_set() {

		$this->assertFalse( wordpoints_get_server_for_module( array() ) );
	}

	/**
	 * Test that it returns false if the filter returns false.
	 *
	 * @since 2.4.0
	 */
	public function test_returns_false_from_filter() {

		add_filter( 'wordpoints_server_for_module', '__return_false' );

		$server = wordpoints_get_server_for_module(
			array( 'server' => 'wordpoints.org' )
		);

		$this->assertFalse( $server );
	}
}

// EOF
