<?php

/**
 * Test case for WordPoints_Uninstaller_Metadata_Wildcards.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Uninstaller_Metadata_Wildcards.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Uninstaller_Metadata_Wildcards
 */
class WordPoints_Uninstaller_Metadata_Wildcards_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests that it supports wildcards.
	 *
	 * @since 2.4.0
	 */
	public function test_uninstalls_metadata_wildcards() {

		$post_id = $this->factory->post->create();
		add_post_meta( $post_id, 'test', 'a' );
		add_post_meta( $post_id, 'test_2', 'b' );
		add_post_meta( $post_id, 'other', 'c' );

		$uninstaller = new WordPoints_Uninstaller_Metadata_Wildcards(
			'post'
			, 'test%'
		);

		$uninstaller->run();

		$this->assertSame( array(), get_post_meta( $post_id, 'test' ) );
		$this->assertSame( array(), get_post_meta( $post_id, 'test_2' ) );
		$this->assertSame( 'c', get_post_meta( $post_id, 'other', true ) );
	}

	/**
	 * Test that it supports wildcards for user options.
	 *
	 * @since 2.4.0
	 */
	public function test_uninstalls_user_metadata_wildcards_prefixed() {

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, 'test', 'a' );
		update_user_option( $user_id, 'test_2', 'b' );
		update_user_option( $user_id, 'other', 'c' );

		$uninstaller = new WordPoints_Uninstaller_Metadata_Wildcards(
			'user'
			, 'test%'
			, true
		);

		$uninstaller->run();

		$this->assertSame( 'a', get_user_meta( $user_id, 'test', true ) );
		$this->assertFalse( get_user_option( 'test_2', $user_id ) );
		$this->assertSame( 'c', get_user_option( 'other', $user_id ) );
	}
}

// EOF
