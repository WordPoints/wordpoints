<?php

/**
 * Module server API expirable module license interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for an expirable module license for a remote server's module API.
 *
 * @since 2.4.0
 */
interface WordPoints_Module_Server_API_Module_License_ExpirableI
	extends WordPoints_Module_Server_API_Module_LicenseI {

	/**
	 * Checks whether this license expires.
	 *
	 * @since 2.4.0
	 *
	 * @return bool|WP_Error Whether the license expires, or an error on failure.
	 */
	public function expires();

	/**
	 * Checks whether this license is expired.
	 *
	 * @since 2.4.0
	 *
	 * @return bool|WP_Error Whether the license is expired, or an error on failure.
	 */
	public function is_expired();

	/**
	 * Gets the expiration date for this license.
	 *
	 * @since 2.4.0
	 *
	 * @return DateTime|false The expiration date, or false if none.
	 */
	public function get_expiration_date();
}

// EOF
