<?php

/**
 * Extension server API interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for extension server APIs.
 *
 * This is just the basic interface that is generic to all APIs; additional methods
 * are defined on supplementary interfaces.
 *
 * @since 2.4.0
 */
interface WordPoints_Extension_Server_APII {

	/**
	 * Returns the slug of this API.
	 *
	 * @since 2.4.0
	 *
	 * @return string The slug of this API.
	 */
	public function get_slug();
}

// EOF
