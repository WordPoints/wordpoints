<?php

/**
 * Updates changelog module server API interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for a remote API offering module change logs.
 *
 * @since 2.4.0
 */
interface WordPoints_Module_Server_API_Updates_ChangelogI
	extends WordPoints_Module_Server_API_UpdatesI {

	/**
	 * Gets the changelog for the latest version of a module.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Module_Server_API_Module_DataI $module_data The module data.
	 *
	 * @return string The changelog text.
	 */
	public function get_module_changelog(
		WordPoints_Module_Server_API_Module_DataI $module_data
	);
}

// EOF
