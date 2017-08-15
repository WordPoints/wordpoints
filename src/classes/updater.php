<?php

/**
 * Updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Runs the update routines for an installable entity.
 *
 * @since 2.4.0
 */
class WordPoints_Updater extends WordPoints_Routine {

	/**
	 * The entity being updated.
	 *
	 * @since 2.4.0
	 *
	 * @var WordPoints_InstallableI
	 */
	protected $installable;

	/**
	 * The update routines for the entity.
	 *
	 * The routines are indexed by the context that they should run in: 'site',
	 * 'network', or 'single' for non-multisite.
	 *
	 * @since 2.4.0
	 *
	 * @var WordPoints_RoutineI[][]
	 */
	protected $updates = array();

	/**
	 * @since 2.4.0
	 *
	 * @param WordPoints_InstallableI $installable The entity to update.
	 * @param bool                    $network     Whether it is network activated.
	 */
	public function __construct( WordPoints_InstallableI $installable, $network ) {

		$this->installable  = $installable;
		$this->network_wide = $network;
		$this->updates      = $this->installable->get_update_routines();
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {

		/**
		 * Upgrade functions, including `dbDelta()`, for table creation/updates.
		 *
		 * @since 2.4.0
		 */
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// We need to run even if there are no updates, or else the DB version of the
		// entity won't be updated.
		parent::run();
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_sites() {

		if ( empty( $this->updates['site'] ) ) {
			return;
		}

		if ( $this->skip_per_site_update() ) {
			$this->installable->set_network_update_skipped();
		} else {
			parent::run_for_sites();
		}
	}

	/**
	 * Checks if we should skip the update for each site on the network.
	 *
	 * On large multisite networks we don't attempt the per-site update.
	 *
	 * @since 2.4.0
	 *
	 * @return bool Whether to skip the per-site update.
	 */
	protected function skip_per_site_update() {
		return wp_is_large_network();
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_network() {

		if ( ! empty( $this->updates['network'] ) ) {
			$this->run_routines( $this->updates['network'] );
		}

		if ( $this->network_wide ) {
			$this->installable->set_db_version( null, true );
		}
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_site() {

		if ( ! empty( $this->updates['site'] ) ) {
			$this->run_routines( $this->updates['site'] );
		}

		if ( ! $this->network_wide ) {
			$this->installable->set_db_version();
			$this->installable->add_installed_site_id();
		}
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_single() {

		if ( ! empty( $this->updates['single'] ) ) {
			$this->run_routines( $this->updates['single'] );
		}

		$this->installable->set_db_version();
	}
}

// EOF
