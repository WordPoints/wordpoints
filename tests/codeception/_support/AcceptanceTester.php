<?php

/**
 * Acceptance tester class.
 *
 * @package WordPoints\Codeception
 * @since 2.1.0
 */

/**
 * Tester for use in the acceptance tests.
 *
 * @since 2.1.0
 */
class AcceptanceTester extends \WordPoints\Tests\Codeception\AcceptanceTester {

	/**
	 * Install a test module on the site.
	 *
	 * @since 2.2.0
	 *
	 * @param string $module The module file or directory to symlink.
	 */
	public function haveTestModuleInstalled( $module ) {

		$modules_dir = wordpoints_modules_dir();
		$test_modules_dir = dirname( __FILE__ ) . '/../../phpunit/data/modules/';

		if ( ! file_exists( $modules_dir . $module ) ) {

			global $wp_filesystem;

			WP_Filesystem();

			$wp_filesystem->mkdir( $modules_dir . $module );

			copy_dir( $test_modules_dir . $module, $modules_dir . $module );
		}
	}
}

// EOF
