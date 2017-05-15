<?php

/**
 * Module server API module data interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for a module license for a remote server's module API.
 *
 * @since 2.4.0
 */
interface WordPoints_Module_Server_API_Module_DataI {

	/**
	 * Returns the ID of the module the data is for.
	 *
	 * @since 2.4.0
	 *
	 * @return string The ID of the module the data is for.
	 */
	public function get_id();

	/**
	 * Retrieves a piece of data for a module.
	 *
	 * @since 2.4.0
	 *
	 * @param string $key The piece of data to get.
	 *
	 * @return mixed The information for this module, or null if not found.
	 */
	public function get( $key );

	/**
	 * Saves a piece of data for a module.
	 *
	 * @since 2.4.0
	 *
	 * @param string $key   The piece of data to set.
	 * @param mixed  $value The value to save.
	 *
	 * @return bool Whether the value was saved successfully.
	 */
	public function set( $key, $value );

	/**
	 * Deletes a piece of data for a module.
	 *
	 * @since 2.4.0
	 *
	 * @param string $key The piece of data to delete.
	 *
	 * @return bool Whether the data was deleted successfully.
	 */
	public function delete( $key );
}

// EOF
