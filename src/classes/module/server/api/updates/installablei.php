<?php

/**
 * Updates installable module server API interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for a remote API offering module updates that are auto-installable.
 *
 * @since 2.4.0
 */
interface WordPoints_Module_Server_API_Updates_InstallableI
	extends WordPoints_Module_Server_API_UpdatesI {

	/**
	 * Gets the URL of the zip package for the latest version of a module.
	 *
	 * @since  2.4.0
	 *
	 * @param WordPoints_Module_Server_API_Module_DataI $module_data The module data.
	 *
	 * @return string The package URL.
	 */
	public function get_module_package_url(
		WordPoints_Module_Server_API_Module_DataI $module_data
	);
}

// EOF
