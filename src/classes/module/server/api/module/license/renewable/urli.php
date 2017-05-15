<?php

/**
 * Module server API URL renewable module license interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for a module license that is renewable via a remote URL.
 *
 * @since 2.4.0
 */
interface WordPoints_Module_Server_API_Module_License_Renewable_URLI
	extends WordPoints_Module_Server_API_Module_License_RenewableI {

	/**
	 * Gets the renewal URL for this license.
	 *
	 * @since 2.4.0
	 *
	 * @return string|WP_Error The renewal URL, or an error on failure.
	 */
	public function get_renewal_url();
}

// EOF
