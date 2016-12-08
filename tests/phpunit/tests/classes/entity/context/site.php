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

	/**
	 * Test switching to a different site on a multisite install.
	 *
	 * @since 2.2.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_switch_to() {

		$site_id = $this->factory->blog->create();

		$context = new WordPoints_Entity_Context_Site( 'site' );

		$this->assertTrue( $context->switch_to( $site_id ) );
		$this->assertEquals( $site_id, get_current_blog_id() );
	}

	/**
	 * Test switching to a different site when not on a multisite install.
	 *
	 * @since 2.2.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_switch_to_not_multisite() {

		$context = new WordPoints_Entity_Context_Site( 'site' );

		$this->assertFalse( $context->switch_to( 1 ) );
	}

	/**
	 * Test switching back to the previous site on a multisite install.
	 *
	 * @since 2.2.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_switch_back() {

		$site_id = $this->factory->blog->create();

		$context = new WordPoints_Entity_Context_Site( 'site' );

		$current_site_id = get_current_blog_id();

		switch_to_blog( $site_id );

		$this->assertTrue( $context->switch_back() );
		$this->assertEquals( $current_site_id, get_current_blog_id() );
	}

	/**
	 * Test switching back to the previous site when not on a multisite install.
	 *
	 * @since 2.2.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_switch_back_not_multisite() {

		$context = new WordPoints_Entity_Context_Site( 'site' );

		$this->assertFalse( $context->switch_back() );
	}
}

// EOF
