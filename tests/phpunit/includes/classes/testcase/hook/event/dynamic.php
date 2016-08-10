<?php

/**
 * Dynamic hook event test case class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Parent test case for testing a dynamic hook event.
 *
 * @since 2.1.0
 */
abstract class WordPoints_PHPUnit_TestCase_Hook_Event_Dynamic
	extends WordPoints_PHPUnit_TestCase_Hook_Event {

	/**
	 * The value for the dynamic portion of the slugs.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $dynamic_slug;

	/**
	 * @since 2.1.0
	 */
	public function setUp() {

		$this->dynamize();

		parent::setUp();
	}

	/**
	 * @since 2.1.0
	 */
	public function data_provider_targets() {

		$this->dynamize();

		return parent::data_provider_targets();
	}

	/**
	 * Fills in the dynamic slug with the correct dynamic part.
	 *
	 * @since 2.1.0
	 */
	protected function dynamize() {

		$this->event_slug = str_replace( '\\', '\\' . $this->dynamic_slug, $this->event_slug );

		foreach ( $this->expected_targets as $index => $expected_target ) {
			foreach ( $expected_target as $key => $slug ) {
				$this->expected_targets[ $index ][ $key ] = str_replace(
					'\\'
					, '\\' . $this->dynamic_slug
					, $slug
				);
			}
		}
	}
}

// EOF
