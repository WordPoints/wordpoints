<?php

/**
 * Test case for WordPoints_Uninstaller_Factory_Metadata.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Uninstaller_Factory_Metadata.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Uninstaller_Factory_Metadata
 */
class WordPoints_Uninstaller_Factory_Metadata_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests getting the uninstaller for a single site.
	 *
	 * @since 2.4.0
	 */
	public function test_get_for_single() {

		$factory = new WordPoints_Uninstaller_Factory_Metadata(
			'post'
			, array( 'single' => array( 'test' ) )
		);

		$uninstallers = $factory->get_for_single();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_Metadata', $uninstallers[0] );

		$post_id = $this->factory->post->create();
		add_post_meta( $post_id, 'test', 'a' );
		add_post_meta( $post_id, 'test', 'b' );

		$post_id_2 = $this->factory->post->create();
		add_post_meta( $post_id_2, 'test', 'a' );

		$uninstallers[0]->run();

		$this->assertSame( array(), get_post_meta( $post_id, 'test' ) );
		$this->assertSame( array(), get_post_meta( $post_id_2, 'test' ) );
	}

	/**
	 * Tests getting the uninstaller for a single site.
	 *
	 * @since 2.4.0
	 */
	public function test_get_for_site() {

		$factory = new WordPoints_Uninstaller_Factory_Metadata(
			'post'
			, array( 'site' => array( 'test' ) )
		);

		$uninstallers = $factory->get_for_site();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_Metadata', $uninstallers[0] );

		$post_id = $this->factory->post->create();
		add_post_meta( $post_id, 'test', 'a' );
		add_post_meta( $post_id, 'test', 'b' );

		$post_id_2 = $this->factory->post->create();
		add_post_meta( $post_id_2, 'test', 'a' );

		$uninstallers[0]->run();

		$this->assertSame( array(), get_post_meta( $post_id, 'test' ) );
		$this->assertSame( array(), get_post_meta( $post_id_2, 'test' ) );
	}

	/**
	 * Tests getting the uninstaller for a single site.
	 *
	 * @since 2.4.0
	 */
	public function test_get_for_site_user() {

		$factory = new WordPoints_Uninstaller_Factory_Metadata(
			'user'
			, array( 'site' => array( 'test' ) )
		);

		$uninstallers = $factory->get_for_site();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_Metadata', $uninstallers[0] );

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, 'test', 'a' );
		update_user_option( $user_id, 'test', 'b' );

		$this->assertSame( 'b', get_user_option( 'test', $user_id ) );

		$uninstallers[0]->run();

		// If the user option had not been deleted, 'b' would have been returned.
		$this->assertSame( 'a', get_user_option( 'test', $user_id ) );
		$this->assertSame( 'a', get_user_meta( $user_id, 'test', true ) );
	}

	/**
	 * Tests getting the uninstaller for a network site.
	 *
	 * @since 2.4.0
	 */
	public function test_get_for_network() {

		$factory = new WordPoints_Uninstaller_Factory_Metadata(
			'user'
			, array( 'network' => array( 'test' ) )
		);

		$uninstallers = $factory->get_for_network();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_Metadata', $uninstallers[0] );

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, 'test', 'a' );
		add_user_meta( $user_id, 'test', 'b' );

		$user_id_2 = $this->factory->user->create();
		add_user_meta( $user_id_2, 'test', 'a' );

		$uninstallers[0]->run();

		$this->assertSame( array(), get_user_meta( $user_id, 'test' ) );
		$this->assertSame( array(), get_user_meta( $user_id_2, 'test' ) );
	}

	/**
	 * Tests that it supports wildcards.
	 *
	 * @since 2.4.0
	 */
	public function test_supports_wildcards() {

		$factory = new WordPoints_Uninstaller_Factory_Metadata(
			'post'
			, array( 'site' => array( 'test%' ) )
		);

		$uninstallers = $factory->get_for_site();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_Metadata_Wildcards', $uninstallers[0] );

		$post_id = $this->factory->post->create();
		add_post_meta( $post_id, 'test', 'a' );
		add_post_meta( $post_id, 'test_2', 'b' );
		add_post_meta( $post_id, 'other', 'c' );

		$uninstallers[0]->run();

		$this->assertSame( array(), get_post_meta( $post_id, 'test' ) );
		$this->assertSame( array(), get_post_meta( $post_id, 'test_2' ) );
		$this->assertSame( 'c', get_post_meta( $post_id, 'other', true ) );
	}

	/**
	 * Test that it supports wildcards for user options.
	 *
	 * @since 2.4.0
	 */
	public function test_supports_wildcards_prefixed() {

		$factory = new WordPoints_Uninstaller_Factory_Metadata(
			'user'
			, array( 'site' => array( 'test%' ) )
		);

		$uninstallers = $factory->get_for_site();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_Metadata_Wildcards', $uninstallers[0] );

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, 'test', 'a' );
		update_user_option( $user_id, 'test_2', 'b' );
		update_user_option( $user_id, 'other', 'c' );

		$uninstallers[0]->run();

		$this->assertSame( 'a', get_user_meta( $user_id, 'test', true ) );
		$this->assertFalse( get_user_option( 'test_2', $user_id ) );
		$this->assertSame( 'c', get_user_option( 'other', $user_id ) );
	}

	/**
	 * Tests getting the uninstallers when there are no meta keys to uninstall.
	 *
	 * @since 2.4.0
	 */
	public function test_no_keys() {

		$factory = new WordPoints_Uninstaller_Factory_Metadata(
			'user'
			, array( 'single' => array() )
		);

		$uninstallers = $factory->get_for_single();

		$this->assertSame( array(), $uninstallers );
	}

	/**
	 * Tests that it maps context shortcuts.
	 *
	 * @since 2.4.0
	 */
	public function test_maps_shortcuts() {

		$factory = new WordPoints_Uninstaller_Factory_Metadata(
			'user'
			, array( 'local' => array( 'test' ) )
		);

		$this->assertCount( 1, $factory->get_for_single() );
		$this->assertCount( 1, $factory->get_for_site() );
		$this->assertCount( 0, $factory->get_for_network() );
	}
}

// EOF
