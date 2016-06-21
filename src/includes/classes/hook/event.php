<?php

/**
 * Base hook event class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Represents an event that occurs when certain user actions take place.
 *
 * @since 2.1.0
 */
abstract class WordPoints_Hook_Event implements WordPoints_Hook_EventI {

	/**
	 * The unique slug for identifying this event.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * @since 2.1.0
	 *
	 * @param string $slug The event slug.
	 */
	public function __construct( $slug ) {

		$this->slug = $slug;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_slug() {
		return $this->slug;
	}
}

// EOF
