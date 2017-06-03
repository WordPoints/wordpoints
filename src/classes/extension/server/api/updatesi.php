<?php

/**
 * Extension updates API interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for a remote API offering extension updates.
 *
 * Extension update APIs are basically web URL endpoints that supply extension
 * updates. Each API might be of a different type, using different GET parameters to
 * identify the extension, for example. Each type of update API needs to be handled a
 * little differently.
 *
 * @since 2.4.0
 */
interface WordPoints_Extension_Server_API_UpdatesI {

	/**
	 * Gets the latest version of the extension available from the server.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Extension_Server_API_Extension_DataI $extension_data The extension data.
	 *
	 * @return string The version number.
	 */
	public function get_extension_latest_version(
		WordPoints_Extension_Server_API_Extension_DataI $extension_data
	);
}

// EOF
