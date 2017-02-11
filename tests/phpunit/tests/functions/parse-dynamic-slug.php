<?php

/**
 * Test case for wordpoints_parse_dynamic_slug().
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests wordpoints_parse_dynamic_slug().
 *
 * @since 2.1.0
 *
 * @covers ::wordpoints_parse_dynamic_slug
 */
class WordPoints_Parse_Dynamic_Slug_Functions_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test parsing a slug.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_slugs
	 */
	public function test_parse( $slug, $parsed ) {

		$this->assertSame( $parsed, wordpoints_parse_dynamic_slug( $slug ) );
	}

	/**
	 * Provides sets of slugs and the expected parsed values.
	 *
	 * @since 2.1.0
	 */
	public function data_provider_slugs() {
		return array(
			'not_dynamic' => array( 'slug', array( 'dynamic' => false, 'generic' => false ) ),
			'dynamic' => array( 'slug\dynamic', array( 'dynamic' => 'dynamic', 'generic' => 'slug' ) ),
			'empty' => array( '', array( 'dynamic' => false, 'generic' => false ) ),
			'empty_dynamic' => array( 'slug\\', array( 'dynamic' => '', 'generic' => 'slug' ) ),
			'empty_generic' => array( '\slug', array( 'dynamic' => 'slug', 'generic' => '' ) ),
		);
	}
}

// EOF
