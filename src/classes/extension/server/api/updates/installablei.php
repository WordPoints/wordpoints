<?php

/**
 * Updates installable extension server API interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for a remote API offering extension updates that are auto-installable.
 *
 * @since 2.4.0
 */
interface WordPoints_Extension_Server_API_Updates_InstallableI
	extends WordPoints_Extension_Server_API_UpdatesI {

	/**
	 * Gets the URL of the zip package for the latest version of an extension.
	 *
	 * @since  2.4.0
	 *
	 * @param WordPoints_Extension_Server_API_Extension_DataI $extension_data The extension data.
	 *
	 * @return string The package URL.
	 */
	public function get_extension_package_url(
		WordPoints_Extension_Server_API_Extension_DataI $extension_data
	);
}

// EOF
