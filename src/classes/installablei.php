<?php

/**
 * Installables interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for an object representing an installable entity.
 *
 * Installables would be plugins, extensions, and WordPoints components.
 *
 * @package WordPoints
 * @since   2.4.0
 */
interface WordPoints_InstallableI {

	/**
	 * Gets the slug of this installable.
	 *
	 * @since 2.4.0
	 *
	 * @return string The installable's slug.
	 */
	public function get_slug();

	/**
	 * Gets the version of this installable.
	 *
	 * This is the version of the code, not necessarily in the database.
	 *
	 * @since 2.4.0
	 *
	 * @return string The installable's version.
	 */
	public function get_version();

	/**
	 * Gets the database version of this installable.
	 *
	 * @since 2.4.0
	 *
	 * @param bool $network Whether to get the network version of the entity, or just
	 *                      for the current site.
	 *
	 * @return false|string The database version of the entity, or false if not set.
	 */
	public function get_db_version( $network = false );

	/**
	 * Sets the version of this installable in the database.
	 *
	 * @since 2.4.0
	 *
	 * @param string $version The version to set. Defaults to the code version.
	 * @param bool   $network Whether to set the network version of the entity, or
	 *                        just for the current site.
	 */
	public function set_db_version( $version = null, $network = false );

	/**
	 * Deletes the version of this installable from the database.
	 *
	 * @since 2.4.0
	 *
	 * @param bool $network Whether to delete the network version of the entity, or
	 *                      just for the current site.
	 */
	public function unset_db_version( $network = false );

	/**
	 * Checks if this installable is network installed on a multisite network.
	 *
	 * Note that this doesn't necessarily mean that the entity is currently network-
	 * active, only that it has been network-installed at some point.
	 *
	 * If this is not a multisite network, the return value should be false.
	 *
	 * @since 2.4.0
	 *
	 * @return bool Whether the entity is network installed.
	 */
	public function is_network_installed();

	/**
	 * Sets this entity's status as network-installed.
	 *
	 * @since 2.4.0
	 */
	public function set_network_installed();

	/**
	 * Deletes this entity's status as network-installed.
	 *
	 * @since 2.4.0
	 */
	public function unset_network_installed();

	/**
	 * Sets that the per-site network installation has been skipped.
	 *
	 * @since 2.4.0
	 */
	public function set_network_install_skipped();

	/**
	 * Deletes the network-install skipped flag for this entity.
	 *
	 * @since 2.4.0
	 */
	public function unset_network_install_skipped();

	/**
	 * Sets that per-site network-updating has been skipped.
	 *
	 * @since 2.4.0
	 *
	 * @param string $updating_from The version that was being updated from. Defaults
	 *                              to the current database version.
	 */
	public function set_network_update_skipped( $updating_from = null );

	/**
	 * Deletes the network-update skipped flag for this entity.
	 *
	 * @since 2.4.0
	 */
	public function unset_network_update_skipped();

	/**
	 * Gets the IDs of all sites on which this installable is installed.
	 *
	 * It's important to note that this isn't just sites where the code is active,
	 * but is those sites that something has actually been installed for, like in
	 * the database. Some of these sites may no longer have the code active. In
	 * other cases, the code may be active but not actually install anything in the
	 * DB, and therefore the sites don't need to be added to this list. It is
	 * basically just for keeping track of where things would need to be uninstalled.
	 *
	 * @since 2.4.0
	 *
	 * @return int[] The IDs of the sites where this entity is installed.
	 */
	public function get_installed_site_ids();

	/**
	 * Add a site's ID to the list of the sites where this entity is installed.
	 *
	 * This indicates that there will be things relating to that site that need to be
	 * uninstalled. See {@see self::get_installed_site_ids()} for more info.
	 *
	 * @since 2.4.0
	 *
	 * @param int $id The ID of the site to add. Defaults to the current site's ID.
	 */
	public function add_installed_site_id( $id = null );

	/**
	 * Deletes the list of sites where this installable is installed.
	 *
	 * @since 2.4.0
	 */
	public function delete_installed_site_ids();

	/**
	 * Gets the install routines for this entity.
	 *
	 * @since 2.4.0
	 *
	 * @return WordPoints_RoutineI[][] Routines for the installation of this
	 *                                 installable, indexed by the context that they
	 *                                 should run in: 'site', 'network', or 'single'
	 *                                 for non-multisite.
	 */
	public function get_install_routines();

	/**
	 * Gets the update routine factories for this entity.
	 *
	 * @since 2.4.0
	 *
	 * @return WordPoints_Updater_FactoryI[] The update routine factories.
	 */
	public function get_update_routine_factories();

	/**
	 * Gets the uninstall routines for this entity.
	 *
	 * @since 2.4.0
	 *
	 * @return WordPoints_RoutineI[][] Routines for uninstalling this installable,
	 *                                 indexed by the context that they should run
	 *                                 in: 'site', 'network', or 'single' for non-
	 *                                 multisite.
	 */
	public function get_uninstall_routines();
}

// EOF
