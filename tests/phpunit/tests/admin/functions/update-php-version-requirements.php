<?php

/**
 * Test case for the update PHP version requirement functions.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.3.0
 */

/**
 * Tests the update PHP version requirement functions.
 *
 * @since 2.3.0
 */
class WordPoints_Admin_Update_PHP_Version_Requirements_Functions_Test
	extends WordPoints_PHPUnit_TestCase_Admin {

	/**
	 * The PHP version string being used in the test.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	protected $php_version;

	/**
	 * Tests getting the version required for an update.
	 *
	 * @since 2.3.0
	 *
	 * @dataProvider data_provider_versions
	 *
	 * @covers ::wordpoints_admin_get_php_version_required_for_update
	 *
	 * @param string $version The PHP version string to test.
	 * @param mixed  $result  The expected result, if different from $version.
	 */
	public function test_get_version( $version, $result = null ) {

		add_filter(
			'pre_site_transient_update_plugins'
			, array( $this, 'return_wordpoints_update' )
		);

		add_filter(
			'plugins_api'
			, array( $this, 'return_wordpoints_info' )
		);

		$this->php_version = $version;

		if ( ! isset( $result ) ) {
			$result = $this->php_version;
		}

		$this->assertSame(
			$result
			, wordpoints_admin_get_php_version_required_for_update()
		);

		remove_filter(
			'pre_site_transient_update_plugins'
			, array( $this, 'return_wordpoints_update' )
		);

		$this->assertSame(
			$result
			, get_site_transient( 'update_plugins' )
				->response['wordpoints/wordpoints.php']
				->wordpoints_required_php
		);
	}

	/**
	 * Data provider for PHP versions to check.
	 *
	 * @since 2.3.0
	 *
	 * @return array[] A list of PHP version strings to check against, and optionally
	 *                 the expected result.
	 */
	public function data_provider_versions() {
		return array(
			'x.y' => array( '5.4' ),
			'x.y.z' => array( '5.3.9' ),
			'x' => array( '7', false ),
			'empty' => array( '', false ),
			'xx.yy' => array( '75.88' ),
		);
	}

	/**
	 * Tests getting the version required for an update when it is already cached.
	 *
	 * @since 2.3.0
	 *
	 * @dataProvider data_provider_versions
	 *
	 * @covers ::wordpoints_admin_get_php_version_required_for_update
	 *
	 * @param string $version The PHP version string to test.
	 * @param mixed  $result  The expected result, if different from $version.
	 */
	public function test_get_version_already_cached( $version, $result = null ) {

		$this->php_version = '5.2.7';

		if ( ! isset( $result ) ) {
			$result = $version;
		}

		$transient = $this->return_wordpoints_update();
		$transient
			->response['wordpoints/wordpoints.php']
			->wordpoints_required_php = $result;

		set_site_transient( 'update_plugins', $transient );

		add_filter(
			'plugins_api'
			, array( $this, 'return_wordpoints_info' )
		);

		$this->assertSame(
			$result
			, wordpoints_admin_get_php_version_required_for_update()
		);
	}

	/**
	 * Returns a WordPoints update in the format of the update_plugins transient.
	 *
	 * @since 2.3.0
	 *
	 * @return object A mock transient value with an update for WordPoints.
	 */
	public function return_wordpoints_update() {
		return (object) array(
			'response' => array(
				'wordpoints/wordpoints.php' => (object) array(
					'id' => 'w.org/plugins/wordpoints',
					'slug' => 'wordpoints',
					'plugin' => 'wordpoints/wordpoints.php',
					'new_version' => '2.2.2',
					'url' => 'https://wordpress.org/plugins/wordpoints/',
					'package' => 'https://downloads.wordpress.org/plugin/wordpoints.2.2.2.zip',
					'upgrade_notice' => '<ul>
						<li>Fixes a bug that caused event reactions not to award points for some custom post
						types, like bbPress forum topics.</li>
						</ul>',
					'tested' => '4.8-alpha-39357',
					'compatibility' => (object) array(),
				),
			),
		);
	}

	/**
	 * Returns WordPoints info in the format of the plugins_api() function.
	 *
	 * @since 2.3.0
	 *
	 * @return object A mock result of a plugin information request for WordPoints.
	 */
	public function return_wordpoints_info() {
		return (object) array(
			'name' => 'WordPoints',
			'slug' => 'wordpoints',
			'version' => '2.2.2',
			'requires' => '4.6',
			'tested' => '4.8-alpha-39357',
			'compatibility' => array(),
			'homepage' => 'https://wordpoints.org/',
			'sections' => array(
				'description' => "<h4>Features</h4>
					<p>This plugin lets you create one or multiple types of points which you can use to
					reward your users when certain events occur on your site. It also includes
					a Ranks component, which lets you create ranks for your users based on how many
					points they have.</p>
					<p><strong>Requires PHP {$this->php_version}+</strong></p>",
			),
			'short_description' => 'Gamify your site, track user rep, or run a rewards program. WordPoints has a powerful core, infinitely extendable via add-on modules.',
			'download_link' => 'https://downloads.wordpress.org/plugin/wordpoints.2.2.2.zip',
		);
	}
}

// EOF
