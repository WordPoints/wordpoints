<?php

/**
 * Test case for WordPoints_Uninstaller_Metadata.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Uninstaller_Metadata.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Uninstaller_Metadata
 */
class WordPoints_Uninstaller_Metadata_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests uninstalling metadata.
	 *
	 * @since 2.4.0
	 */
	public function test_uninstalls_metadata() {

		$post_id = $this->factory->post->create();
		add_post_meta( $post_id, 'test', 'a' );
		add_post_meta( $post_id, 'test', 'b' );

		$post_id_2 = $this->factory->post->create();
		add_post_meta( $post_id_2, 'test', 'a' );

		$uninstaller = new WordPoints_Uninstaller_Metadata( 'post', array( 'test' ) );
		$uninstaller->run();

		$this->assertSame( array(), get_post_meta( $post_id, 'test' ) );
		$this->assertSame( array(), get_post_meta( $post_id_2, 'test' ) );
	}

	/**
	 * Tests uninstalling user metadata that prefixed.
	 *
	 * @since 2.4.0
	 */
	public function test_uninstalls_metadata_prefixed() {

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, 'test', 'a' );
		update_user_option( $user_id, 'test', 'b' );

		$this->assertSame( 'b', get_user_option( 'test', $user_id ) );

		$uninstaller = new WordPoints_Uninstaller_Metadata( 'user', array( 'test' ), true );
		$uninstaller->run();

		// If the user option had not been deleted, 'b' would have been returned.
		$this->assertSame( 'a', get_user_option( 'test', $user_id ) );
		$this->assertSame( 'a', get_user_meta( $user_id, 'test', true ) );
	}
}

// EOF
