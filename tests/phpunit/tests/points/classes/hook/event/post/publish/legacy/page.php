<?php

/**
 * Test case for the legacy Post Publish hook event for the page post type.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests the legacy Post Publish hook event for the page post type.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Points_Hook_Event_Post_Publish_Legacy
 */
class WordPoints_Points_Hook_Event_Post_Publish_Legacy_Page_Test
	extends WordPoints_Points_Hook_Event_Post_Publish_Legacy_Test {

	/**
	 * @since 2.1.0
	 */
	protected $dynamic_slug = 'page';

	/**
	 * @since 2.3.0
	 */
	protected $expected_targets = array(
		array( 'post\\', 'author', 'user' ),
		array( 'post\\', 'parent', 'post\\', 'author', 'user' ),
	);

	/**
	 * @since 2.3.0
	 */
	protected function create_post( array $args = array() ) {

		$args['post_parent'] = parent::create_post();

		return parent::create_post( $args );
	}
}

// EOF
