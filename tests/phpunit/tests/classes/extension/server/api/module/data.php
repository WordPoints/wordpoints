<?php

/**
 * Testcase for WordPoints_Extension_Server_API_Extension_Data.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Test WordPoints_Extension_Server_API_Extension_Data.
 *
 * @since 2.4.0
 *
 * @group extensions
 *
 * @covers WordPoints_Extension_Server_API_Extension_Data
 */
class WordPoints_Extension_Server_API_Extension_Data_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test get_id().
	 *
	 * @since 2.4.0
	 */
	public function test_get_id() {

		$id = '45';

		$server = $this->createMock( 'WordPoints_Extension_ServerI' );

		$data = new WordPoints_Extension_Server_API_Extension_Data( $id, $server );

		$this->assertSame( $id, $data->get_id() );
	}

	/**
	 * Test getting a piece of data.
	 *
	 * @since 2.4.0
	 */
	public function test_get() {

		$id = '45';
		$server_slug = 'example.com';

		update_site_option(
			"wordpoints_extension_data-{$server_slug}-{$id}"
			, array( 'test' => 'a' )
		);

		$server = $this->createMock( 'WordPoints_Extension_ServerI' );
		$server->method( 'get_slug' )->willReturn( $server_slug );

		$data = new WordPoints_Extension_Server_API_Extension_Data( $id, $server );

		$this->assertSame( 'a', $data->get( 'test' ) );
	}

	/**
	 * Test getting a piece of data when it isn't set.
	 *
	 * @since 2.4.0
	 */
	public function test_get_not_set() {

		$id = '45';
		$server_slug = 'example.com';

		update_site_option(
			"wordpoints_extension_data-{$server_slug}-{$id}"
			, array( 'other' => 'a' )
		);

		$server = $this->createMock( 'WordPoints_Extension_ServerI' );
		$server->method( 'get_slug' )->willReturn( $server_slug );

		$data = new WordPoints_Extension_Server_API_Extension_Data( $id, $server );

		$this->assertNull( $data->get( 'test' ) );
	}

	/**
	 * Test setting a piece of data.
	 *
	 * @since 2.4.0
	 */
	public function test_set() {

		$id = '45';
		$server_slug = 'example.com';
		$option = "wordpoints_extension_data-{$server_slug}-{$id}";

		$server = $this->createMock( 'WordPoints_Extension_ServerI' );
		$server->method( 'get_slug' )->willReturn( $server_slug );

		$data = new WordPoints_Extension_Server_API_Extension_Data( $id, $server );

		$this->assertTrue( $data->set( 'test', 'b' ) );

		$this->assertSame( 'b', $data->get( 'test' ) );

		$this->assertSame( array( 'test' => 'b' ), get_site_option( $option ) );
	}

	/**
	 * Test setting a piece of data that already has another value.
	 *
	 * @since 2.4.0
	 */
	public function test_set_already_set() {

		$id = '45';
		$server_slug = 'example.com';
		$option = "wordpoints_extension_data-{$server_slug}-{$id}";

		update_site_option( $option, array( 'test' => 'a' ) );

		$server = $this->createMock( 'WordPoints_Extension_ServerI' );
		$server->method( 'get_slug' )->willReturn( $server_slug );

		$data = new WordPoints_Extension_Server_API_Extension_Data( $id, $server );

		$this->assertTrue( $data->set( 'test', 'b' ) );

		$this->assertSame( 'b', $data->get( 'test' ) );

		$this->assertSame( array( 'test' => 'b' ), get_site_option( $option ) );
	}

	/**
	 * Test setting a piece of data that already has the same value.
	 *
	 * @since 2.4.0
	 */
	public function test_set_already_set_same() {

		$id = '45';
		$server_slug = 'example.com';
		$option = "wordpoints_extension_data-{$server_slug}-{$id}";

		update_site_option( $option, array( 'test' => 'a' ) );

		$server = $this->createMock( 'WordPoints_Extension_ServerI' );
		$server->method( 'get_slug' )->willReturn( $server_slug );

		$data = new WordPoints_Extension_Server_API_Extension_Data( $id, $server );

		$this->assertTrue( $data->set( 'test', 'a' ) );

		$this->assertSame( 'a', $data->get( 'test' ) );

		$this->assertSame( array( 'test' => 'a' ), get_site_option( $option ) );
	}

	/**
	 * Test deleting a piece of data.
	 *
	 * @since 2.4.0
	 */
	public function test_delete() {

		$id = '45';
		$server_slug = 'example.com';
		$option = "wordpoints_extension_data-{$server_slug}-{$id}";

		update_site_option( $option, array( 'test' => 'a' ) );

		$server = $this->createMock( 'WordPoints_Extension_ServerI' );
		$server->method( 'get_slug' )->willReturn( $server_slug );

		$data = new WordPoints_Extension_Server_API_Extension_Data( $id, $server );

		$this->assertTrue( $data->delete( 'test' ) );

		$this->assertNull( $data->get( 'test' ) );

		$this->assertSame( array(), get_site_option( $option ) );
	}

	/**
	 * Test deleting a piece of data that doesn't exist.
	 *
	 * @since 2.4.0
	 */
	public function test_delete_not_exists() {

		$id = '45';
		$server_slug = 'example.com';
		$option = "wordpoints_extension_data-{$server_slug}-{$id}";

		$server = $this->createMock( 'WordPoints_Extension_ServerI' );
		$server->method( 'get_slug' )->willReturn( $server_slug );

		$data = new WordPoints_Extension_Server_API_Extension_Data( $id, $server );

		$this->assertTrue( $data->delete( 'test' ) );

		$this->assertNull( $data->get( 'test' ) );

		$this->assertSame( array(), get_site_option( $option ) );
	}
}

// EOF
