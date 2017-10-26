<?php

/**
 * Hook reaction class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Bootstrap for representing a hook reaction.
 *
 * @since 2.1.0
 */
abstract class WordPoints_Hook_Reaction implements WordPoints_Hook_ReactionI {

	/**
	 * @since 2.1.0
	 */
	protected $ID;

	/**
	 * The reaction storage object.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_Reaction_StoreI
	 */
	protected $store;

	//
	// Public Methods.
	//

	/**
	 * Construct the class for a hook reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param int                             $id    The ID of a hook reaction.
	 * @param WordPoints_Hook_Reaction_StoreI $store The storage object.
	 */
	public function __construct( $id, WordPoints_Hook_Reaction_StoreI $store ) {

		$this->ID    = wordpoints_int( $id );
		$this->store = $store;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_id() {
		return $this->ID;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_guid() {

		return array(
			'id'         => $this->ID,
			'mode'       => $this->get_mode_slug(),
			'store'      => $this->get_store_slug(),
			'context_id' => $this->get_context_id(),
		);
	}

	/**
	 * @since 2.1.0
	 */
	public function get_reactor_slug() {
		return $this->get_meta( 'reactor' );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_mode_slug() {
		return $this->store->get_mode_slug();
	}

	/**
	 * @since 2.1.0
	 */
	public function get_store_slug() {
		return $this->store->get_slug();
	}

	/**
	 * @since 2.1.0
	 */
	public function get_context_id() {
		return $this->store->get_context_id();
	}
}

// EOF
