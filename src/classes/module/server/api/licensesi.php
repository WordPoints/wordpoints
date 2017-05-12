<?php

/**
 * Module licenses server API interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for a remote API that utilizes module licenses.
 *
 * @since 2.4.0
 */
interface WordPoints_Module_Server_API_LicensesI {

	/**
	 * Checks if a particular module requires a license.
	 *
	 * Just because an API utilizes licenses, it doesn't mean that every module
	 * requires one. Some modules could be premium, while others are free, for
	 * example.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Module_Server_API_Module_DataI $module_data The module data.
	 *
	 * @return bool Whether the module requires a license.
	 */
	public function module_requires_license(
		WordPoints_Module_Server_API_Module_DataI $module_data
	);

	/**
	 * Gets the API object for interacting with a module license.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Module_Server_API_Module_DataI $module_data The module data.
	 * @param string                                    $license_key The license key.
	 *
	 * @return WordPoints_Module_Server_API_Module_LicenseI|false
	 *         The license object, or false if the module doesn't require a license.
	 */
	public function get_module_license_object(
		WordPoints_Module_Server_API_Module_DataI $module_data,
		$license_key
	);
}

// EOF
