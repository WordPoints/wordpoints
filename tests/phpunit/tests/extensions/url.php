<?php

/**
 * A test case for wordpoints_extensions_url().
 *
 * @package WordPoints\Tests
 * @since 2.4.0
 */

/**
 * Test the wordpoints_extensions_url() function.
 *
 * @since 2.4.0
 *
 * @covers ::wordpoints_extensions_url
 */
class WordPoints_Extensions_URL_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test that it is filterable.
	 *
	 * @since 2.4.0
	 */
	public function test_filter() {

		$original = wordpoints_extensions_url();

		$filter = new WordPoints_PHPUnit_Mock_Filter( 'test_directory' );
		$filter->add_filter( 'wordpoints_extensions_url' );

		$this->assertSame( 'test_directory', wordpoints_extensions_url() );

		$filter->remove_filter( 'wordpoints_extensions_url' );

		$this->assertSame( $original, wordpoints_extensions_url() );
	}

	/**
	 * Test that it is filterable.
	 *
	 * @since 2.4.0
	 *
	 * @expectedDeprecated wordpoints_modules_url
	 */
	public function test_deprecated_filter() {

		$original = wordpoints_extensions_url();

		$filter = new WordPoints_PHPUnit_Mock_Filter( 'test_directory' );
		$filter->add_filter( 'wordpoints_modules_url' );

		$this->assertSame( 'test_directory', wordpoints_extensions_url() );

		$filter->remove_filter( 'wordpoints_modules_url' );

		$this->assertSame( $original, wordpoints_extensions_url() );
	}

	/**
	 * Tests that it is filtered on legacy installs.
	 *
	 * @since 2.4.0
	 *
	 * @covers ::wordpoints_legacy_modules_path
	 */
	public function test_legacy_path() {

		$this->assertStringMatchesFormat(
			'%A/wordpoints-extensions%A'
			, wordpoints_extensions_url()
		);

		update_site_option( 'wordpoints_legacy_extensions_dir', true );

		$this->assertStringMatchesFormat(
			'%A/wordpoints-modules%A'
			, wordpoints_extensions_url()
		);
	}

	/**
	 * Tests that it is filtered on legacy installs.
	 *
	 * @since 2.4.0
	 *
	 * @covers ::wordpoints_legacy_modules_path
	 */
	public function test_legacy_path_not_updated() {

		$this->assertStringMatchesFormat(
			'%A/wordpoints-extensions%A'
			, wordpoints_extensions_url()
		);

		$this->wordpoints_set_db_version( '2.3.0' );

		$this->assertStringMatchesFormat(
			'%A/wordpoints-modules%A'
			, wordpoints_extensions_url()
		);
	}
}

// EOF
