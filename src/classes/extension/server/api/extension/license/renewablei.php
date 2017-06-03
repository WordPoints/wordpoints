<?php

/**
 * Extension server API renewable extension license interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for a renewable extension license for a remote server's extension API.
 *
 * @since 2.4.0
 */
interface WordPoints_Extension_Server_API_Extension_License_RenewableI
	extends WordPoints_Extension_Server_API_Extension_LicenseI {

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
