<?php

/**
 * Module server API module license interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for a module license for a remote server's module API.
 *
 * @since 2.4.0
 */
interface WordPoints_Module_Server_API_Module_LicenseI {

	/**
	 * Check if this license is valid for the module.
	 *
	 * @since 2.4.0
	 *
	 * @return bool|WP_Error Whether the license is valid, or an error object if
	 *                       unable to determine validity.
	 */
	public function is_valid();
}

// EOF
