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

	/**
	 * Activate a component.
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug The component slug.
	 *
	 * @return bool Whether the component was activated successfully.
	 */
	public function hadActivatedComponent( $slug ) {
		return WordPoints_Components::instance()->activate( $slug );
	}

	/**
	 * Wait for a new rank to be displayed on the screen.
	 *
	 * @since 1.0.0
	 *
	 * @param string $context The context in which the rank should appear.
	 * @param int    $timeout The number of seconds to wait before timing out.
	 */
	public function waitForNewRank( $context = '', $timeout = null ) {

		$I = $this;

		// Wait until the fields are actually interactive.
		// Attempting to set a field value immediately after creating the new
		// rank  will result in an error: "Element is not currently interactable
		// and may not be manipulated."
		$I->waitForElementChange(
			"{$context} .wordpoints-rank.new [name=name]"
			, function ( \Facebook\WebDriver\WebDriverElement $element ) {

				try {

					// It should be OK that we clear this since this is a new
					// rank and doesn't have a name yet.
					$element->clear();

				} catch ( \Facebook\WebDriver\Exception\InvalidElementStateException $e ) {

					codecept_debug(
						'Error while waiting for new rank:' . $e->getMessage()
					);
				}

				return ! isset( $e );
			}
			, $timeout
		);
	}
}

// EOF
