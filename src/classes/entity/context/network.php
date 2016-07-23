<?php

/**
 * Network entity context class.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Represents the network context.
 *
 * On multisite installs there are many "sites" on a "network".
 *
 * @since 2.1.0
 */
class WordPoints_Entity_Context_Network extends WordPoints_Entity_Context {

	/**
	 * @since 2.1.0
	 */
	public function get_current_id() {

		if ( ! is_multisite() ) {
			return 1;
		}

		return (int) get_current_site()->id;
	}
}

// EOF
