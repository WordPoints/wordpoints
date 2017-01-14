<?php

/**
 * Test case for the Post Publish hook event.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.2.0
 */

/**
 * Tests the Post Publish hook event.
 *
 * @since 2.2.0
 *
 * @covers WordPoints_Hook_Event_Post_Publish
 */
class WordPoints_Hook_Event_Post_Publish_Custom_Test
	extends WordPoints_Hook_Event_Post_Publish_Test {

	/**
	 * @since 2.2.0
	 */
	protected $dynamic_slug = 'wordpoints_hepp_test';

	/**
	 * @since 2.2.0
	 */
	public function data_provider_targets() {

		WordPoints_PHPUnit_Factory::$factory->post_type->create(
			array( 'name' => $this->dynamic_slug )
		);

		return parent::data_provider_targets();
	}
}

// EOF
