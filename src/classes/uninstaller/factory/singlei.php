<?php

/**
 * Single uninstaller factory interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for uninstaller factories that provide uninstallers for single sites.
 *
 * @since 2.4.0
 */
interface WordPoints_Uninstaller_Factory_SingleI {

	/**
	 * Gets uninstallers for uninstalling on a single site.
	 *
	 * @since 2.4.0
	 *
	 * @return WordPoints_RoutineI[] Uninstaller routines.
	 */
	public function get_for_single();
}

// EOF
