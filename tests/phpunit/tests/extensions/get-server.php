<?php

/**
 * A test case for wordpoints_get_server_for_extension().
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests wordpoints_get_server_for_extension().
 *
 * @since 2.4.0
 *
 * @covers ::wordpoints_get_server_for_extension
 */
class WordPoints_Get_Server_For_Extension_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test that it returns the server object.
	 *
	 * @since 2.4.0
	 */
	public function test_returns_server() {

		$extension = array( 'server' => 'wordpoints.org' );

		$server = wordpoints_get_server_for_extension( $extension );

		$this->assertInstanceOf( 'WordPoints_Extension_ServerI', $server );
		$this->assertSame( $extension['server'], $server->get_slug() );
	}

	/**
	 * Test that it calls the wordpoints_server_for_extension filter.
	 *
	 * @since 2.4.0
	 */
	public function test_calls_filter() {

		add_filter(
			'wordpoints_server_for_extension'
			, array( $this, 'filter_wordpoints_server_for_extension' )
			, 10
			, 2
		);

		$server = wordpoints_get_server_for_extension(
			array( 'server' => 'wordpoints.org' )
		);

		$this->assertSame( __CLASS__, $server->get_slug() );
	}

	/**
	 * Filters the server slug for an extension.
	 *
	 * @since 2.4.0
	 *
	 * @see self::test_calls_filter()
	 *
	 * @param string $server    The slug of the server to use.
	 * @param array  $extension The extension data.
	 *
	 * @return string The filtered server slug.
	 */
	public function filter_wordpoints_server_for_extension( $server, $extension ) {

		$this->assertSame( 'wordpoints.org', $server );
		$this->assertSame( array( 'server' => 'wordpoints.org' ), $extension );

		return __CLASS__;
	}

	/**
	 * Test that it calls the wordpoints_server_object_for_extension filter.
	 *
	 * @since 2.4.0
	 */
	public function test_calls_object_filter() {

		add_filter(
			'wordpoints_server_object_for_extension'
			, array( $this, 'filter_wordpoints_server_object_for_extension' )
			, 10
			, 2
		);

		$server = wordpoints_get_server_for_extension(
			array( 'server' => 'wordpoints.org' )
		);

		$this->assertSame( __CLASS__, $server->get_slug() );
	}

	/**
	 * Filters the server object for an extension.
	 *
	 * @since 2.4.0
	 *
	 * @see self::test_calls_object_filter()
	 *
	 * @param WordPoints_Extension_ServerI $server    The object for the server to use.
	 * @param array                        $extension The extension data.
	 *
	 * @return WordPoints_Extension_ServerI The filtered server object.
	 */
	public function filter_wordpoints_server_object_for_extension( $server, $extension ) {

		$this->assertInstanceOf( 'WordPoints_Extension_ServerI', $server );
		$this->assertSame( array( 'server' => 'wordpoints.org' ), $extension );

		$server = $this->getMock(
			'WordPoints_Extension_Server'
			, array( 'get_api' )
			, array( __CLASS__ )
		);

		return $server;
	}

	/**
	 * Test that it returns false if the extension doesn't specify a server.
	 *
	 * @since 2.4.0
	 */
	public function test_returns_false_if_not_set() {

		$this->assertFalse( wordpoints_get_server_for_extension( array() ) );
	}

	/**
	 * Test that it returns false if the filter returns false.
	 *
	 * @since 2.4.0
	 */
	public function test_returns_false_from_filter() {

		add_filter( 'wordpoints_server_for_extension', '__return_false' );

		$server = wordpoints_get_server_for_extension(
			array( 'server' => 'wordpoints.org' )
		);

		$this->assertFalse( $server );
	}

	/**
	 * Test that it returns false if the object filter returns false.
	 *
	 * @since 2.4.0
	 */
	public function test_returns_false_from_object_filter() {

		add_filter( 'wordpoints_server_object_for_extension', '__return_false' );

		$server = wordpoints_get_server_for_extension(
			array( 'server' => 'wordpoints.org' )
		);

		$this->assertFalse( $server );
	}
}

// EOF
