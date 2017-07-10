<?php

/**
 * Site installer class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * An installer that runs only for a particular site on the network.
 *
 * For use when installing a network-wide entity on a new site when it is added to
 * the network.
 *
 * @since 2.4.0
 */
class WordPoints_Installer_Site extends WordPoints_Installer {

	/**
	 * The ID of the site to run on.
	 *
	 * @since 2.4.0
	 *
	 * @var int
	 */
	protected $site_id;

	/**
	 * @since 2.4.0
	 *
	 * @param WordPoints_InstallableI $installable The entity to install.
	 * @param int                     $site_id     The site to run the installer on.
	 */
	public function __construct(
		WordPoints_InstallableI $installable,
		$site_id
	) {

		$this->site_id = $site_id;

		parent::__construct( $installable, true );
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_network() {}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_sites() {
		// No need to run skipping logic when there is a large network, or set the
		// db version or network install status, etc.
		WordPoints_Routine::run_for_sites();
	}

	/**
	 * @since 2.4.0
	 */
	protected function get_site_ids() {
		return array( $this->site_id );
	}
}

// EOF
