<?php

/**
 * Extension server interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Defines the interface for representing a remote extension server.
 *
 * @since 2.4.0
 */
interface WordPoints_Extension_ServerI {

	/**
	 * Get the slug for this server.
	 *
	 * This is the unique identifier for the server.
	 *
	 * @since 2.4.0
	 *
	 * @return string The server slug.
	 */
	public function get_slug();

	/**
	 * Get the URL for this server.
	 *
	 * This is a full URL including the scheme and any path.
	 *
	 * @since 2.4.0
	 *
	 * @return string The server URL.
	 */
	public function get_url();

	/**
	 * Get the API to use to interact with this server.
	 *
	 * @since 2.4.0
	 *
	 * @return WordPoints_Extension_Server_APII|false The API object, or false if not
	 *                                                available.
	 */
	public function get_api();
}

// EOF
