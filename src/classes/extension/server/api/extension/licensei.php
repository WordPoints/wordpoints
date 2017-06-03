<?php

/**
 * Extension server API extension license interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for an extension license for a remote server's extension API.
 *
 * @since 2.4.0
 */
interface WordPoints_Extension_Server_API_Extension_LicenseI {

	/**
	 * Check if this license is valid for the extension.
	 *
	 * @since 2.4.0
	 *
	 * @return bool|WP_Error Whether the license is valid, or an error object if
	 *                       unable to determine validity.
	 */
	public function is_valid();
}

// EOF
