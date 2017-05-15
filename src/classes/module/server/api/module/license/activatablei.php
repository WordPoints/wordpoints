<?php

/**
 * Module server API activatable module license interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for an activatable module license for a remote server's module API.
 *
 * @since 2.4.0
 */
interface WordPoints_Module_Server_API_Module_License_ActivatableI
	extends WordPoints_Module_Server_API_Module_LicenseI {

	/**
	 * Checks whether this license is activatable.
	 *
	 * @since 2.4.0
	 *
	 * @return bool Whether this license can be activated for the module.
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
	 * Activates the license for the module.
	 *
	 * @since 2.4.0
	 *
	 * @return bool|WP_Error Whether the license was activated, or an error on
	 *                       failure.
	 */
	public function activate();
}

// EOF
