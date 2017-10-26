<?php

/**
 * Installer class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Runs the install routine for an entity.
 *
 * @since 2.4.0
 */
class WordPoints_Installer extends WordPoints_Routine {

	/**
	 * The installable object for the entity being installed.
	 *
	 * @since 2.4.0
	 *
	 * @var WordPoints_InstallableI
	 */
	protected $installable;

	/**
	 * The routines for this entity's installation, organized by context.
	 *
	 * @since 2.4.0
	 *
	 * @var WordPoints_RoutineI[][]
	 */
	protected $install_routines;

	/**
	 * @since 2.4.0
	 *
	 * @param WordPoints_InstallableI $installable  The installable entity.
	 * @param bool                    $network_wide Whether to install the entity
	 *                                              network-wide.
	 */
	public function __construct(
		WordPoints_InstallableI $installable,
		$network_wide
	) {

		$this->installable  = $installable;
		$this->network_wide = $network_wide;
	}

	/**
	 * Runs the install routine.
	 *
	 * @since 2.4.0
	 */
	public function run() {

		$this->install_routines = $this->installable->get_install_routines();

		/**
		 * Upgrade functions, including `dbDelta()`, for table creation/updates.
		 *
		 * @since 2.4.0
		 */
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		parent::run();
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_sites() {

		$this->installable->set_network_installed();

		if ( ! isset( $this->install_routines['site'] ) ) {
			return;
		}

		if ( $this->skip_per_site_install() ) {
			$this->installable->set_network_install_skipped();
		} else {
			parent::run_for_sites();
		}
	}

	/**
	 * Check whether we should run the install for each site in the network.
	 *
	 * On large networks we don't attempt the per-site install.
	 *
	 * @since 2.4.0
	 *
	 * @return bool Whether to skip the per-site installation.
	 */
	protected function skip_per_site_install() {
		return wp_is_large_network();
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_network() {

		if ( ! empty( $this->install_routines['network'] ) ) {
			$this->run_routines( $this->install_routines['network'] );
		}

		$this->installable->set_db_version( null, true );
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_site() {

		if ( ! empty( $this->install_routines['site'] ) ) {
			$this->run_routines( $this->install_routines['site'] );
		}

		if ( ! $this->network_wide ) {
			$this->installable->add_installed_site_id();
			$this->installable->set_db_version();
		}
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_single() {

		if ( ! empty( $this->install_routines['single'] ) ) {
			$this->run_routines( $this->install_routines['single'] );
		}

		$this->installable->set_db_version();
	}
}

// EOF
