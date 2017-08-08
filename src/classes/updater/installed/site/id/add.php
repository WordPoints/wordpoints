<?php

/**
 * Add site ID updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Updater that adds the site ID to the list of installed site IDs.
 *
 * @since 2.4.0
 */
class WordPoints_Updater_Installed_Site_ID_Add implements WordPoints_RoutineI {

	/**
	 * The installable object.
	 *
	 * @since 2.4.0
	 *
	 * @var WordPoints_InstallableI
	 */
	protected $installable;

	/**
	 * @since 2.4.0
	 *
	 * @param WordPoints_InstallableI $installable The installable object.
	 */
	public function __construct( WordPoints_InstallableI $installable ) {
		$this->installable = $installable;
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {
		$this->installable->add_installed_site_id();
	}
}

// EOF
