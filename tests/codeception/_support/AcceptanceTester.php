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
	 * Make this a site with some legacy points hooks disabled.
	 *
	 * @since 2.1.0
	 *
	 * @param array $hooks The list of hook handler ID bases to disable.
	 */
	public function haveSiteWithDisabledLegacyPointsHooks( $hooks ) {
		update_site_option(
			'wordpoints_legacy_points_hooks_disabled'
			, $hooks
		);
	}
}

// EOF
