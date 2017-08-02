<?php

/**
 * Test case for WordPoints_Installable_Basic.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Installable_Basic.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Installable_Basic
 */
class WordPoints_Installable_Basic_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests that the slug and version are set on construction.
	 *
	 * @since 2.4.0
	 */
	public function test_construct_slug_version_set() {

		$installable = new WordPoints_Installable_Basic( 'module', 'test', '1.0.0' );

		$this->assertSame( 'test', $installable->get_slug() );
		$this->assertSame( '1.0.0', $installable->get_version() );
	}
}

// EOF
