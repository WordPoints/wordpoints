<?php

/**
 * Extension server API activatable extension license interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for an activatable extension license for a remote server's extension API.
 *
 * @since 2.4.0
 */
interface WordPoints_Extension_Server_API_Extension_License_ActivatableI
	extends WordPoints_Extension_Server_API_Extension_LicenseI {

	/**
	 * Checks whether this license is activatable.
	 *
	 * @since 2.4.0
	 *
	 * @return bool Whether this license can be activated for the extension.
	 */
	public function is_activatable();

	/**
	 * Checks whether this license is active.
	 *
	 * @since 2.4.0
	 *
	 * @return bool|WP_Error Whether the license is active, or an error on failure.
	 */
	public function is_active();

	/**
	 * Activates the license for the extension.
	 *
	 * @since 2.4.0
	 *
	 * @return bool|WP_Error Whether the license was activated, or an error on
	 *                       failure.
	 */
	public function activate();
}

// EOF
