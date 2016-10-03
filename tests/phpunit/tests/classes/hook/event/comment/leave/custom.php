<?php

/**
 * Test case for the Comment Leave hook event.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.2.0
 */

/**
 * Tests the Comment Leave hook event.
 *
 * @since 2.2.0
 *
 * @covers WordPoints_Hook_Event_Comment_Leave
 */
class WordPoints_Hook_Event_Comment_Leave_Custom_Test
	extends WordPoints_Hook_Event_Comment_Leave_Test {

	/**
	 * @since 2.2.0
	 */
	protected $dynamic_slug = 'wordpoints_hecl_test';

	/**
	 * @since 2.2.0
	 */
	public function data_provider_targets() {

		WordPoints_PHPUnit_Factory::$factory->post_type->create(
			array(
				'name' => $this->dynamic_slug,
				'supports' => array( 'title', 'editor', 'comments' ),
			)
		);

		wordpoints_register_post_type_entities( $this->dynamic_slug );
		wordpoints_register_post_type_hook_actions( $this->dynamic_slug );
		wordpoints_register_post_type_hook_events( $this->dynamic_slug );

		return parent::data_provider_targets();
	}
}

// EOF
