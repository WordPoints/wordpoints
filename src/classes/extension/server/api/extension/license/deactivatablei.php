<?php

/**
 * Extension server API deactivatable extension license interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for a deactivatable extension license for a remote server's extension API.
 *
 * @since 2.4.0
 */
interface WordPoints_Extension_Server_API_Extension_License_DeactivatableI
	extends WordPoints_Extension_Server_API_Extension_LicenseI {

	/**
	 * Checks whether this license is deactivatable.
	 *
	 * @since 2.4.0
	 *
	 * @return bool Whether this license can be deactivated for the extension.
	 */
	public function is_deactivatable();

	/**
	 * Deactivates the license for the extension.
	 *
	 * @since 2.4.0
	 *
	 * @return bool|WP_Error Whether the license was deactivated, or an error on
	 *                       failure.
	 */
	public function deactivate();
}

// EOF
