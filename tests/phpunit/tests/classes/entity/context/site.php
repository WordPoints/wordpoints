<?php

/**
 * Test case for WordPoints_Entity_Context_Site.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Entity_Context_Site.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Entity_Context_Site
 */
class WordPoints_Entity_Context_Site_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test getting the current context identifier on a non-multisite install.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_get_current_id() {

		$context = new WordPoints_Entity_Context_Site( 'site' );

		$this->assertEquals( 1, $context->get_current_id() );
	}

	/**
	 * Test getting the current context identifier on a multisite install.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_current_id_multisite() {

		$context = new WordPoints_Entity_Context_Site( 'site' );

		$this->assertEquals( get_current_blog_id(), $context->get_current_id() );
	}

	/**
	 * Test getting the current context identifier on a multisite install.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_current_id_multisite_network_admin() {

		$context = new WordPoints_Entity_Context_Site( 'site' );

		$this->set_network_admin();

		$this->assertFalse( $context->get_current_id() );
	}
}

// EOF
