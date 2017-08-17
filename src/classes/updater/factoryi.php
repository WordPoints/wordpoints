<?php

/**
 * Updater factory interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for a factory for update routines.
 *
 * @since 2.4.0
 */
interface WordPoints_Updater_FactoryI {

	/**
	 * Gets the version that these update routines are for.
	 *
	 * @since 2.4.0
	 *
	 * @return string The version the update routines are for.
	 */
	public function get_version();

	/**
	 * Gets the update routines for single sites.
	 *
	 * @since 2.4.0
	 *
	 * @return WordPoints_RoutineI[] The update routines for single sites.
	 */
	public function get_for_single();

	/**
	 * Gets the update routines for sites on a multisite network.
	 *
	 * @since 2.4.0
	 *
	 * @return WordPoints_RoutineI[] The update routines for sites on the network.
	 */
	public function get_for_site();

	/**
	 * Gets the update routines for the network.
	 *
	 * @since 2.4.0
	 *
	 * @return WordPoints_RoutineI[] The update routines for the network.
	 */
	public function get_for_network();
}

// EOF
