<?php

/**
 * Mock hook reaction class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Mock hook reaction for the PHPUnit tests.
 *
 * @since 2.1.0
 */
class WordPoints_PHPUnit_Mock_Hook_Reaction extends WordPoints_Hook_Reaction_Options {

	/**
	 * @since 2.1.0
	 *
	 * @var WordPoints_PHPUnit_Mock_Hook_Reaction_Store
	 */
	public $store;

	/**
	 * The ID of the context in which this reaction exists.
	 *
	 * @since 2.1.0
	 *
	 * @var array
	 */
	public $context_id;

	/**
	 * @since 2.1.0
	 */
	public function get_context_id() {

		if ( isset( $this->context_id ) ) {
			return $this->context_id;
		}

		return parent::get_context_id();
	}
}

// EOF
