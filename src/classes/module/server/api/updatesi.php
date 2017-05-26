<?php

/**
 * Module updates API interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for a remote API offering module updates.
 *
 * Module update APIs are basically web URL endpoints that supply module updates.
 * Each API might be of a different type, using different GET parameters to identify
 * the module, for example. Each type of update API needs to be handled a little
 * differently.
 *
 * @since 2.4.0
 */
interface WordPoints_Module_Server_API_UpdatesI {

	/**
	 * Gets the latest version of the module available from the server.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Module_Server_API_Module_DataI $module_data The module data.
	 *
	 * @return string The version number.
	 */
	public function get_module_latest_version(
		WordPoints_Module_Server_API_Module_DataI $module_data
	);
}

// EOF
