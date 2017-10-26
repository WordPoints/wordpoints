<?php

/**
 * Installables app class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * A registry for installable entities.
 *
 * Ensures that the update routines are run when needed, and that network-active
 * entities on multisite are installed on new sites when they are created.
 *
 * @since 2.4.0
 */
class WordPoints_Installables_App {

	/**
	 * The class name or loader function for each entity, indexed by type and slug.
	 *
	 * @since 2.4.0
	 *
	 * @var (string|callable)[][]
	 */
	protected $loaders = array();

	/**
	 * The versions of each non-network installable, indexed by type and slug.
	 *
	 * @since 2.4.0
	 *
	 * @var string[][]
	 */
	protected $versions = array();

	/**
	 * The versions of each network active installable, indexed by type and slug.
	 *
	 * @since 2.4.0
	 *
	 * @var string[][]
	 */
	protected $network_versions = array();

	/**
	 * Registers an installable.
	 *
	 * @since 2.4.0
	 *
	 * @param string          $type         The type of installable (extension,
	 *                                      component, etc.).
	 * @param string          $slug         The slug of this installable.
	 * @param string|callable $loader       The class name of the installable object,
	 *                                      or a loader function that will return it.
	 *                                      The function may also return false if no
	 *                                      object is available.
	 * @param string          $version      The code version of this installable.
	 * @param bool            $network_wide Whether this installable is network active.
	 */
	public function register( $type, $slug, $loader, $version, $network_wide = false ) {

		$this->loaders[ $type ][ $slug ] = $loader;

		if ( $network_wide ) {
			$this->network_versions[ $type ][ $slug ] = $version;
		} else {
			$this->versions[ $type ][ $slug ] = $version;
		}
	}

	/**
	 * Runs any updates for the registered installables that are needed.
	 *
	 * @since 2.4.0
	 */
	public function maybe_update() {

		$this->maybe_do_updates();

		if ( is_wordpoints_network_active() ) {
			$this->maybe_do_updates( true );
		}
	}

	/**
	 * Runs any updates for a group the registered installables that are needed.
	 *
	 * @since 2.4.0
	 *
	 * @param bool $network Whether to run for the network or non-network group.
	 */
	protected function maybe_do_updates( $network = false ) {

		$option = 'wordpoints_installable_versions';

		$last_checked = wordpoints_get_maybe_network_array_option(
			$option
			, $network
		);

		$versions = $network ? $this->network_versions : $this->versions;

		if ( $last_checked === $versions ) {
			return;
		}

		foreach ( $versions as $type => $installables ) {

			if ( isset( $last_checked[ $type ] ) ) {

				if ( $installables === $last_checked[ $type ] ) {
					continue;
				}

				$diff = array_diff_assoc( $installables, $last_checked[ $type ] );

			} else {

				$diff = $installables;
			}

			foreach ( $diff as $slug => $version ) {
				$this->update( $type, $slug, $version, $network );
			}
		}

		wordpoints_update_maybe_network_option( $option, $versions, $network );
	}

	/**
	 * Updates a particular installable.
	 *
	 * @since 2.4.0
	 *
	 * @param string $type    The type of installable.
	 * @param string $slug    The slug of the installable.
	 * @param string $version The code version of the installable.
	 * @param bool   $network Whether the installable is network activated.
	 */
	protected function update( $type, $slug, $version, $network ) {

		$installable = $this->get_installable( $type, $slug );

		if ( ! $installable ) {
			return;
		}

		$db_version = $installable->get_db_version( $network );

		if ( ! $db_version && 'plugin' === $type && 'wordpoints' === $slug ) {

			// WordPoints had a bug in 2.0.0 causing it not to be installed, see #349.
			$installer = new WordPoints_Installer( $installable, $network );
			$installer->run();

			return;
		}

		if ( ! version_compare( $version, $db_version, '>' ) ) {
			return;
		}

		// Run the update if the database version is actually less.
		$updater = new WordPoints_Updater( $installable, $network );
		$updater->run();
	}

	/**
	 * Installs any network active installables on a new site when it is created.
	 *
	 * @since 2.4.0
	 *
	 * @param int $site_id The ID of the site.
	 */
	public function install_on_new_site( $site_id ) {

		foreach ( $this->network_versions as $type => $installables ) {
			foreach ( $installables as $slug => $loader ) {

				$installable = $this->get_installable( $type, $slug );

				if ( ! $installable ) {
					continue;
				}

				$installer = new WordPoints_Installer_Site( $installable, $site_id );
				$installer->run();
			}
		}
	}

	/**
	 * Gets the installable object for an entity.
	 *
	 * @since 2.4.0
	 *
	 * @param string $type The type of installable.
	 * @param string $slug The slug of the installable.
	 *
	 * @return WordPoints_InstallableI|false The installable object, or false.
	 */
	protected function get_installable( $type, $slug ) {

		$loader = $this->loaders[ $type ][ $slug ];

		// May be a loader function or the class name itself.
		if ( is_callable( $loader ) ) {
			$installable = call_user_func( $loader, $type, $slug );
		} else {
			$installable = new $loader( $slug );
		}

		if ( ! $installable instanceof WordPoints_InstallableI ) {
			return false;
		}

		return $installable;
	}
}

// EOF
