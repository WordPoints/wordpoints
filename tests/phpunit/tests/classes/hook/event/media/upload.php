<?php

/**
 * Test case for the Media Upload hook event.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests the Media Upload hook event.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Event_Media_Upload
 */
class WordPoints_Hook_Event_Media_Upload_Test extends WordPoints_PHPUnit_TestCase_Hook_Event {

	/**
	 * @since 2.1.0
	 */
	protected $event_class = 'WordPoints_Hook_Event_Media_Upload';

	/**
	 * @since 2.1.0
	 */
	protected $event_slug = 'media_upload';

	/**
	 * @since 2.1.0
	 */
	protected $expected_targets = array(
		array( 'post\\attachment', 'author', 'user' ),
	);

	/**
	 * @since 2.1.0
	 */
	protected function fire_event( $arg, $reactor_slug ) {

		return $this->factory->post->create(
			array(
				'post_author' => $this->factory->user->create(),
				'post_type'   => 'attachment',
			)
		);
	}

	/**
	 * @since 2.1.0
	 */
	protected function reverse_event( $arg_id, $index ) {

		wp_delete_post( $arg_id, true );
	}
}

// EOF
