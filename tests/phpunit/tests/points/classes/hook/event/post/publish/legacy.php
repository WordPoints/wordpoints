<?php

/**
 * Test case for the legacy Post Publish hook event.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests the legacy Post Publish hook event.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Points_Hook_Event_Post_Publish_Legacy
 */
class WordPoints_Points_Hook_Event_Post_Publish_Legacy_Test
	extends WordPoints_Hook_Event_Post_Publish_Test {

	/**
	 * @since 2.1.0
	 */
	protected $event_class = 'WordPoints_Points_Hook_Event_Post_Publish_Legacy';

	/**
	 * @since 2.1.0
	 */
	protected $event_slug = 'points_legacy_post_publish\\';

	/**
	 * @since 2.1.0
	 */
	public function setUp() {

		parent::setUp();

		wordpoints_points_register_legacy_post_publish_events( $this->dynamic_slug );
	}

	/**
	 * @since 2.1.0
	 */
	public function tearDown() {

		wordpoints_hooks()->get_sub_app( 'events' )->deregister( $this->event_slug );

		parent::tearDown();
	}

	/**
	 * @since 2.1.0
	 */
	protected function reverse_event( $arg_id, $index ) {
		wp_delete_post( $arg_id, true );
	}

	/**
	 * @since 2.1.0
	 */
	public function data_provider_targets() {

		wordpoints_points_register_legacy_post_publish_events( $this->dynamic_slug );

		$data = parent::data_provider_targets();

		wordpoints_hooks()->get_sub_app( 'events' )->deregister( $this->event_slug );

		return $data;
	}
}

// EOF
