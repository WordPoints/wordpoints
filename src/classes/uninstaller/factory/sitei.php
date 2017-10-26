<?php

/**
 * Site uninstaller factory interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for uninstaller factories that provide uninstallers for sites.
 *
 * @since 2.4.0
 */
interface WordPoints_Uninstaller_Factory_SiteI {

	/**
	 * Gets uninstallers for uninstalling on a site on the network.
	 *
	 * @since 2.4.0
	 *
	 * @return WordPoints_RoutineI[] Uninstaller routines.
	 */
	public function get_for_site();
}

// EOF
