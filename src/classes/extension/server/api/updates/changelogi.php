<?php

/**
 * Updates changelog extension server API interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for a remote API offering extension change logs.
 *
 * @since 2.4.0
 */
interface WordPoints_Extension_Server_API_Updates_ChangelogI
	extends WordPoints_Extension_Server_API_UpdatesI {

	/**
	 * Gets the changelog for the latest version of an extension.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Extension_Server_API_Extension_DataI $extension_data The extension data.
	 *
	 * @return string The changelog text.
	 */
	public function get_extension_changelog(
		WordPoints_Extension_Server_API_Extension_DataI $extension_data
	);
}

// EOF
