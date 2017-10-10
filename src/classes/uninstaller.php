<?php

/**
 * Uninstaller class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Runs the uninstall routines for an uninstallable entity.
 *
 * @since 2.4.0
 */
class WordPoints_Uninstaller extends WordPoints_Routine {

	/**
	 * The entity being uninstalled.
	 *
	 * @since 2.4.0
	 *
	 * @var WordPoints_InstallableI
	 */
	protected $installable;

	/**
	 * The uninstall routines for this entity.
	 *
	 * The routines are indexed by the context that they should run in: 'site',
	 * 'network', or 'single' for non-multisite.
	 *
	 * @since 2.4.0
	 *
	 * @var WordPoints_RoutineI[][]
	 */
	protected $uninstall_routines;

	/**
	 * @since 2.4.0
	 *
	 * @param WordPoints_InstallableI $installable The entity to uninstall.
	 */
	public function __construct( WordPoints_InstallableI $installable ) {

		$this->installable = $installable;

		// We're being uninstalled from the whole network, so we need to uninstall
		// from all of the sites in the network that we've been installed on, not
		// just the current one.
		$this->network_wide = true;

		// We run the per-site routines before the network routines, in case the per-
		// site routines depend on network-level data.
		$this->run_network_first = false;

		$this->uninstall_routines = $this->installable->get_uninstall_routines();
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_sites() {

		if ( $this->skip_per_site_uninstall() ) {
			return;
		}

		parent::run_for_sites();
	}

	/**
	 * Checks if we should skip running the uninstall for each site on the network.
	 *
	 * On when we are installed on a large number of sites we don't attempt the per-
	 * site uninstall.
	 *
	 * @since 2.4.0
	 *
	 * @return bool Whether to skip the per-site uninstallation.
	 */
	protected function skip_per_site_uninstall() {

		if (
			wp_is_large_network()
			&& (
				$this->installable->is_network_installed()
				|| count( $this->installable->get_installed_site_ids() ) > 10000
			)
		) {
			return true;
		}

		return false;
	}

	/**
	 * @since 2.4.0
	 */
	protected function get_site_ids() {

		return $this->installable->get_installed_site_ids();
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_network() {

		if ( ! empty( $this->uninstall_routines['network'] ) ) {
			$this->run_routines( $this->uninstall_routines['network'] );
		}

		$this->installable->unset_db_version( true );
		$this->installable->delete_installed_site_ids();

		// If WordPoints is being uninstalled, the options will already have been
		// deleted, and calling these methods will actually create them again.
		if ( 'wordpoints' !== $this->installable->get_slug() ) {
			$this->installable->unset_network_installed();
			$this->installable->unset_network_install_skipped();
			$this->installable->unset_network_update_skipped();
		}
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_site() {

		if ( ! empty( $this->uninstall_routines['site'] ) ) {
			$this->run_routines( $this->uninstall_routines['site'] );
		}

		$this->installable->unset_db_version();
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_single() {

		if ( ! empty( $this->uninstall_routines['single'] ) ) {
			$this->run_routines( $this->uninstall_routines['single'] );
		}

		$this->installable->unset_db_version();
	}
}

// EOF
