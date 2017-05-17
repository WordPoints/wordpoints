<?php

/**
 * Module updates interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Defines the interface for interacting with a list of module updates.
 *
 * @since 2.4.0
 */
interface WordPoints_Module_UpdatesI {

	/**
	 * Gets the timestamp on which the module updates were checked for.
	 *
	 * @since 2.4.0
	 *
	 * @return int The time that the module update check was performed.
	 */
	public function get_time_checked();

	/**
	 * Sets the timestamp on which the module updates were check for.
	 *
	 * @since 2.4.0
	 *
	 * @param int $time The timestamp that the module update check was performed.
	 */
	public function set_time_checked( $time );

	/**
	 * Gets the module versions that were checked, indexed by module basename.
	 *
	 * @since 2.4.0
	 *
	 * @return string[] The module versions that were checked for updates.
	 */
	public function get_versions_checked();

	/**
	 * Sets the module versions that were checked.
	 *
	 * @since 2.4.0
	 *
	 * @param string[] $versions The module versions that were checked, indexed by
	 *                           module basename.
	 */
	public function set_versions_checked( array $versions );

	/**
	 * Gets the new versions of the modules for which updates are available.
	 *
	 * @since 2.4.0
	 *
	 * @return string[] The new module versions available for update.
	 */
	public function get_new_versions();

	/**
	 * Sets the new module versions for which updates were found.
	 *
	 * @since 2.4.0
	 *
	 * @param string[] $versions The new module versions that are available to update
	 *                           to, indexed by module basename.
	 */
	public function set_new_versions( array $versions );

	/**
	 * Gets the new version of a module that has an update.
	 *
	 * @since 2.4.0
	 *
	 * @param string $module The module basename.
	 *
	 * @return string|false The new version, or false if no update for the module.
	 */
	public function get_new_version( $module );

	/**
	 * Sets the new version of a module that has an update.
	 *
	 * @since 2.4.0
	 *
	 * @param string $module  The module basename.
	 * @param string $version The new version available.
	 */
	public function set_new_version( $module, $version );
}

// EOF
