<?php

/**
 * Test case for the Comment Leave hook event.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests the Comment Leave hook event.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Event_Comment_Leave
 */
class WordPoints_Hook_Event_Comment_Leave_Page_Test extends WordPoints_Hook_Event_Comment_Leave_Test {

	/**
	 * @since 2.1.0
	 */
	protected $dynamic_slug = 'page';

	/**
	 * @since 2.3.0
	 */
	public function setUp() {

		$this->expected_targets[] = array(
			'comment\\',
			'post\\',
			'post\\',
			'parent',
			'post\\',
			'author',
			'user',
		);

		parent::setUp();
	}
}

// EOF
