<?php

/**
 * Network uninstaller factory interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for uninstaller factories that provide uninstallers for networks.
 *
 * @since 2.4.0
 */
interface WordPoints_Uninstaller_Factory_NetworkI {

	/**
	 * Gets uninstallers for uninstalling on a network.
	 *
	 * @since 2.4.0
	 *
	 * @return WordPoints_RoutineI[] Uninstaller routines.
	 */
	public function get_for_network();
}

// EOF
