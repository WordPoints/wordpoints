<?php

/**
 * Extension updates interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Defines the interface for interacting with a list of extension updates.
 *
 * @since 2.4.0
 */
interface WordPoints_Extension_UpdatesI {

	/**
	 * Gets the timestamp on which the extension updates were checked for.
	 *
	 * @since 2.4.0
	 *
	 * @return int The time that the extension update check was performed.
	 */
	public function get_time_checked();

	/**
	 * Sets the timestamp on which the extension updates were check for.
	 *
	 * @since 2.4.0
	 *
	 * @param int $time The timestamp that the extension update check was performed.
	 */
	public function set_time_checked( $time );

	/**
	 * Gets the extension versions that were checked, indexed by extension basename.
	 *
	 * @since 2.4.0
	 *
	 * @return string[] The extension versions that were checked for updates.
	 */
	public function get_versions_checked();

	/**
	 * Sets the extension versions that were checked.
	 *
	 * @since 2.4.0
	 *
	 * @param string[] $versions The extension versions that were checked, indexed by
	 *                           extension basename.
	 */
	public function set_versions_checked( array $versions );

	/**
	 * Gets the new versions of the extensions for which updates are available.
	 *
	 * @since 2.4.0
	 *
	 * @return string[] The new extension versions available for update.
	 */
	public function get_new_versions();

	/**
	 * Sets the new extension versions for which updates were found.
	 *
	 * @since 2.4.0
	 *
	 * @param string[] $versions The new extension versions that are available to
	 *                           update to, indexed by extension basename.
	 */
	public function set_new_versions( array $versions );

	/**
	 * Checks whether an extension has an update.
	 *
	 * @since 2.4.0
	 *
	 * @param string $extension The extension basename.
	 *
	 * @return bool Whether the extension has an update.
	 */
	public function has_update( $extension );

	/**
	 * Gets the new version of an extension that has an update.
	 *
	 * @since 2.4.0
	 *
	 * @param string $extension The extension basename.
	 *
	 * @return string|false The new version, or false if no update for the extension.
	 */
	public function get_new_version( $extension );

	/**
	 * Sets the new version of an extension that has an update.
	 *
	 * @since 2.4.0
	 *
	 * @param string $extension The extension basename.
	 * @param string $version   The new version available.
	 */
	public function set_new_version( $extension, $version );
}

// EOF
