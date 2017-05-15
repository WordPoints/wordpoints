<?php

/**
 * Module server API deactivatable module license interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for a deactivatable module license for a remote server's module API.
 *
 * @since 2.4.0
 */
interface WordPoints_Module_Server_API_Module_License_DeactivatableI
	extends WordPoints_Module_Server_API_Module_LicenseI {

	/**
	 * Checks whether this license is deactivatable.
	 *
	 * @since 2.4.0
	 *
	 * @return bool Whether this license can be deactivated for the module.
	 */
	public function is_deactivatable();

	/**
	 * Deactivates the license for the module.
	 *
	 * @since 2.4.0
	 *
	 * @return bool|WP_Error Whether the license was deactivated, or an error on
	 *                       failure.
	 */
	public function deactivate();
}

// EOF
