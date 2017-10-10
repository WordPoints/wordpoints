<?php

/**
 * Basic installable class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Represents an installable with no special routines specified.
 *
 * This is useful in back-compat code, so that we can manage the installable info
 * via the new code, but still run routines independently.
 *
 * @since 2.4.0
 */
class WordPoints_Installable_Basic extends WordPoints_Installable {

	/**
	 * The code version of this installable.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Constructs the installable.
	 *
	 * @since 2.4.0
	 *
	 * @param string $type    The type of entity.
	 * @param string $slug    The slug of the entity.
	 * @param string $version The current code version of the entity.
	 */
	public function __construct( $type, $slug, $version ) {

		$this->type    = $type;
		$this->slug    = $slug;
		$this->version = $version;
	}

	/**
	 * @since 2.4.0
	 */
	public function get_version() {
		return $this->version;
	}
}

// EOF
