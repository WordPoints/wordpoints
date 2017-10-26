<?php

/**
 * Extension server API expirable extension license interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for an expirable extension license for a remote server's extension API.
 *
 * @since 2.4.0
 */
interface WordPoints_Extension_Server_API_Extension_License_ExpirableI
	extends WordPoints_Extension_Server_API_Extension_LicenseI {

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
