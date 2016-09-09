<?php

/**
 * Mock hook reaction storage class for the PHPUnit tests.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Mock hook reaction storage for the PHPUnit tests.
 *
 * @since 2.1.0
 */
class WordPoints_PHPUnit_Mock_Hook_Reaction_Store extends WordPoints_Hook_Reaction_Store_Options {

	/**
	 * @since 2.1.0
	 */
	protected $reaction_class = 'WordPoints_PHPUnit_Mock_Hook_Reaction';

	/**
	 * The ID of the context for this store.
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
