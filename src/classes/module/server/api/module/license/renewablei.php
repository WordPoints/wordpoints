<?php

/**
 * Module server API renewable module license interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for a renewable module license for a remote server's module API.
 *
 * @since 2.4.0
 */
interface WordPoints_Module_Server_API_Module_License_RenewableI
	extends WordPoints_Module_Server_API_Module_LicenseI {

	/**
	 * Checks whether this license is renewable.
	 *
	 * @since 2.4.0
	 *
	 * @return bool|WP_Error Whether the license can be renewed, or an error on
	 *                       failure.
	 */
	public function is_renewable();
}

// EOF
