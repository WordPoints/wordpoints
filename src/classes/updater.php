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
	 * The update routine factories for the entity.
	 *
	 * Only used while getting the update routines.
	 *
	 * @since 2.4.0
	 *
	 * @var WordPoints_Updater_FactoryI[]
	 */
	protected $update_factories;

	/**
	 * @since 2.4.0
	 *
	 * @param WordPoints_InstallableI $installable The entity to update.
	 * @param bool                    $network     Whether it is network activated.
	 */
	public function __construct( WordPoints_InstallableI $installable, $network ) {

		$this->installable  = $installable;
		$this->network_wide = $network;
		$this->updates      = $this->get_update_routines();
	}

	/**
	 * Gets the update routines for the installable.
	 *
	 * @since 2.4.0
	 *
	 * @return WordPoints_RoutineI[][] The update routines, grouped by context.
	 */
	protected function get_update_routines() {

		$routines = array();

		$this->update_factories = $this->installable->get_update_routine_factories();

		$db_version = $this->installable->get_db_version( $this->network_wide );

		if ( is_multisite() ) {

			$routines['network'] = $this->get_routines(
				'network'
				, $this->get_network_db_version_installable()
			);

			$routines['site'] = $this->get_routines( 'site', $db_version );

		} else {
			$routines['single'] = $this->get_routines( 'single', $db_version );
		}

		unset( $this->update_factories );

		return $routines;
	}

	/**
	 * Gets the network DB version for the installable.
	 *
	 * The site version is always based on network-activation status, but for per-
	 * site active installables, we still use the network version when getting the
	 * network routines, since they don't need to run every time as each site is
	 * updated, only once for the network, and thus are versioned independently.
	 *
	 * For legacy installables that do not have the network version set yet, we
	 * determine the most recent version that the network has been updated to by
	 * checking the versions of each of the sites; the latest version found is the
	 * version that the network has been updated to (since previously the network
	 * updates would be run along with the per-site updates). If there wasn't any
	 * version set, that just means that the installable has been newly added to the
	 * code and so was not installed previously; in that case, all of the per-site
	 * updates should (and will) be run (since no version is considered lower than
	 * any version).
	 *
	 * @since 2.4.0
	 *
	 * @return string THe network DB version, or false if none could be determined.
	 */
	protected function get_network_db_version_installable() {

		$db_version = $this->installable->get_db_version( true );

		// Network active always uses the network version.
		if ( $this->network_wide ) {
			return $db_version;
		}

		// For per-site active, we can use the version if it is set.
		if ( $db_version ) {
			return $db_version;
		}

		// For legacy installables, however, it may not be yet.
		$versions = array();

		$ms_switched_state = new WordPoints_Multisite_Switched_State();
		$ms_switched_state->backup();

		// So we get all of the per-site versions.
		foreach ( $this->installable->get_installed_site_ids() as $site_id ) {
			switch_to_blog( $site_id );
			$versions[ $site_id ] = $this->installable->get_db_version();
		}

		$ms_switched_state->restore();

		// The latest per-site version is the one the network has been updated to.
		foreach ( array_unique( $versions ) as $version ) {
			if ( version_compare( $version, $db_version, '>' ) ) {
				$db_version = $version;
			}
		}

		$ms_switched_state->backup();

		// All of the sites will have been updated to this version.
		foreach ( $versions as $site_id => $version ) {

			// So make sure they have this version set as their DB version.
			if ( $version !== $db_version ) {
				switch_to_blog( $site_id );
				$this->installable->set_db_version( $db_version );
			}
		}

		$ms_switched_state->restore();

		return $db_version;
	}

	/**
	 * Gets the update routines for a given context.
	 *
	 * @since 2.4.0
	 *
	 * @param string $context    The context to get the routines for.
	 * @param string $db_version The database version of the installable.
	 *
	 * @return WordPoints_RoutineI[] The update routines for this context.
	 */
	protected function get_routines( $context, $db_version ) {

		$routines = array();

		foreach ( $this->update_factories as $factory ) {

			if ( version_compare( $factory->get_version(), $db_version, '>' ) ) {
				$routines = array_merge( $routines, $factory->{"get_for_{$context}"}() );
			}
		}

		return $routines;
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
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

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

		$this->installable->set_db_version( null, true );
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
