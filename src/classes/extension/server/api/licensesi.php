<?php

/**
 * Extension licenses server API interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for a remote API that utilizes extension licenses.
 *
 * @since 2.4.0
 */
interface WordPoints_Extension_Server_API_LicensesI {

	/**
	 * Checks if a particular extension requires a license.
	 *
	 * Just because an API utilizes licenses, it doesn't mean that every extension
	 * requires one. Some extensions could be premium, while others are free, for
	 * example.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Extension_Server_API_Extension_DataI $extension_data The extension data.
	 *
	 * @return bool Whether the extension requires a license.
	 */
	public function extension_requires_license(
		WordPoints_Extension_Server_API_Extension_DataI $extension_data
	);

	/**
	 * Gets the API object for interacting with an extension license.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Extension_Server_API_Extension_DataI $extension_data The extension data.
	 * @param string                                          $license_key    The license key.
	 *
	 * @return WordPoints_Extension_Server_API_Extension_LicenseI|false
	 *         The license object, or false if the extension doesn't require a license.
	 */
	public function get_extension_license_object(
		WordPoints_Extension_Server_API_Extension_DataI $extension_data,
		$license_key
	);
}

// EOF
