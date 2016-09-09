<?php

/**
 * Mock unmet hook condition class for the PHPUnit tests.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Mock unmet hook condition for the PHPUnit tests.
 *
 * @since 2.1.0
 */
class WordPoints_PHPUnit_Mock_Hook_Condition_Unmet
	extends WordPoints_PHPUnit_Mock_Hook_Condition {

	/**
	 * @since 2.1.0
	 */
	protected $slug = 'unmet_condition';

	/**
	 * @since 2.1.0
	 */
	public function is_met( array $settings, WordPoints_Hook_Event_Args $args ) {
		return false;
	}
}

// EOF
