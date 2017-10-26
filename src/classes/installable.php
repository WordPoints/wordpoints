<?php

/**
 * Installable class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Represents an installable entity.
 *
 * Provides info about the entity and getters and setters for some stored data
 * relating to it.
 *
 * @since 2.4.0
 */
abstract class WordPoints_Installable implements WordPoints_InstallableI {

	/**
	 * The type of entity.
	 *
	 * For example, 'module' or 'component'.
	 *
	 * Note that this is singular, even though in the 'wordpoints_data' option the
	 * plural forms are used for legacy reasons.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The slug of this entity.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * @since 2.4.0
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * @since 2.4.0
	 */
	public function get_db_version( $network = false ) {

		$wordpoints_data = wordpoints_get_maybe_network_array_option(
			'wordpoints_data'
			, $network
		);

		if ( 'wordpoints' === $this->slug ) {

			if ( isset( $wordpoints_data['version'] ) ) {
				return $wordpoints_data['version'];
			}

		} elseif ( isset( $wordpoints_data[ "{$this->type}s" ][ $this->slug ]['version'] ) ) {
			return $wordpoints_data[ "{$this->type}s" ][ $this->slug ]['version'];
		}

		return false;
	}

	/**
	 * @since 2.4.0
	 */
	public function set_db_version( $version = null, $network = false ) {

		if ( null === $version ) {
			$version = $this->get_version();
		}

		$wordpoints_data = wordpoints_get_maybe_network_array_option(
			'wordpoints_data'
			, $network
		);

		if ( 'wordpoints' === $this->slug ) {
			$wordpoints_data['version'] = $version;
		} else {
			$wordpoints_data[ "{$this->type}s" ][ $this->slug ]['version'] = $version;
		}

		wordpoints_update_maybe_network_option(
			'wordpoints_data'
			, $wordpoints_data
			, $network
		);
	}

	/**
	 * @since 2.4.0
	 */
	public function unset_db_version( $network = false ) {

		$wordpoints_data = wordpoints_get_maybe_network_array_option(
			'wordpoints_data'
			, $network
		);

		if ( 'wordpoints' === $this->slug ) {
			unset( $wordpoints_data['version'] );
		} else {
			unset( $wordpoints_data[ "{$this->type}s" ][ $this->slug ]['version'] );
		}

		wordpoints_update_maybe_network_option(
			'wordpoints_data'
			, $wordpoints_data
			, $network
		);
	}

	/**
	 * @since 2.4.0
	 */
	public function is_network_installed() {

		$network_installed = wordpoints_get_array_option(
			'wordpoints_network_installed'
			, 'site'
		);

		return isset( $network_installed[ $this->type ][ $this->slug ] );
	}

	/**
	 * @since 2.4.0
	 */
	public function set_network_installed() {
		$this->set_option( 'network_installed' );
	}

	/**
	 * @since 2.4.0
	 */
	public function unset_network_installed() {
		$this->unset_option( 'network_installed' );
	}

	/**
	 * @since 2.4.0
	 */
	public function set_network_install_skipped() {
		$this->set_option( 'network_install_skipped' );
	}

	/**
	 * @since 2.4.0
	 */
	public function unset_network_install_skipped() {
		$this->unset_option( 'network_install_skipped' );
	}

	/**
	 * @since 2.4.0
	 */
	public function set_network_update_skipped( $updating_from = null ) {

		if ( ! isset( $updating_from ) ) {
			$updating_from = $this->get_db_version( true );
		}

		$this->set_option( 'network_update_skipped', $updating_from );
	}

	/**
	 * @since 2.4.0
	 */
	public function unset_network_update_skipped() {
		$this->unset_option( 'network_update_skipped' );
	}

	/**
	 * Sets an option in the database for this entity.
	 *
	 * @since 2.4.0
	 *
	 * @param string $option The name of the option to set.
	 * @param mixed  $value  The value of the option.
	 */
	protected function set_option( $option, $value = true ) {

		$data = wordpoints_get_array_option(
			"wordpoints_{$option}"
			, 'site'
		);

		$data[ $this->type ][ $this->slug ] = $value;

		update_site_option( "wordpoints_{$option}", $data );
	}

	/**
	 * Deletes an option in the database for this entity.
	 *
	 * @since 2.4.0
	 *
	 * @param string $option The name of the option to delete.
	 */
	protected function unset_option( $option ) {

		$data = wordpoints_get_array_option(
			"wordpoints_{$option}"
			, 'site'
		);

		unset( $data[ $this->type ][ $this->slug ] );

		update_site_option( "wordpoints_{$option}", $data );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_installed_site_ids() {

		if ( $this->is_network_installed() ) {

			$site_ids = $this->get_all_site_ids();

		} else {

			$site_ids = wordpoints_get_array_option(
				$this->get_installed_site_ids_option_name()
				, 'site'
			);

			$site_ids = $this->validate_site_ids( $site_ids );
		}

		return $site_ids;
	}

	/**
	 * Gets the name of the option where the list of installed sites is stored.
	 *
	 * @since 2.4.0
	 *
	 * @return string The option name.
	 */
	protected function get_installed_site_ids_option_name() {

		if ( 'wordpoints' === $this->slug ) {
			$option_prefix = 'wordpoints';
		} elseif ( 'component' === $this->type ) {
			$option_prefix = "wordpoints_{$this->slug}";
		} else {
			$option_prefix = "wordpoints_{$this->type}_{$this->slug}";
		}

		return "{$option_prefix}_installed_sites";
	}

	/**
	 * Gets the IDs of all sites on the network.
	 *
	 * @since 2.4.0
	 *
	 * @return array The IDs of all sites on the network.
	 */
	protected function get_all_site_ids() {

		return get_sites(
			array(
				'fields'     => 'ids',
				'network_id' => get_current_network_id(),
				'number'     => 0,
			)
		);
	}

	/**
	 * Validates a list of site IDs against the database.
	 *
	 * @since 2.4.0
	 *
	 * @param array $site_ids The site IDs to validate.
	 *
	 * @return int[] The validated site IDs.
	 */
	protected function validate_site_ids( $site_ids ) {

		if ( empty( $site_ids ) || ! is_array( $site_ids ) ) {
			return array();
		}

		$site_ids = get_sites(
			array(
				'fields'     => 'ids',
				'network_id' => get_current_network_id(),
				'number'     => 0,
				'site__in'   => $site_ids,
			)
		);

		return $site_ids;
	}

	/**
	 * @since 2.4.0
	 */
	public function add_installed_site_id( $id = null ) {

		if ( empty( $id ) ) {
			$id = get_current_blog_id();
		}

		$option_name = $this->get_installed_site_ids_option_name();

		$sites = wordpoints_get_array_option( $option_name, 'site' );

		if ( ! in_array( $id, $sites, true ) ) {

			$sites[] = $id;

			update_site_option( $option_name, $sites );
		}
	}

	/**
	 * @since 2.4.0
	 */
	public function delete_installed_site_ids() {
		delete_site_option( $this->get_installed_site_ids_option_name() );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_install_routines() {

		return array_merge_recursive(
			$this->get_db_tables_install_routines()
			, $this->get_custom_caps_install_routines()
		);
	}

	/**
	 * Gets the install routines for database tables for this entity.
	 *
	 * @since 2.4.0
	 *
	 * @return WordPoints_Installer_DB_Tables[] Routines for installing DB tables.
	 */
	protected function get_db_tables_install_routines() {

		$routines = array();

		$db_tables = wordpoints_map_context_shortcuts( $this->get_db_tables() );

		if ( isset( $db_tables['single'] ) ) {
			$routines['single'][] = new WordPoints_Installer_DB_Tables(
				$db_tables['single']
				, 'base'
			);
		}

		if ( isset( $db_tables['site'] ) ) {
			$routines['site'][] = new WordPoints_Installer_DB_Tables(
				$db_tables['site']
			);
		}

		if ( isset( $db_tables['network'] ) ) {
			$routines['network'][] = new WordPoints_Installer_DB_Tables(
				$db_tables['network']
				, 'base'
			);
		}

		return $routines;
	}

	/**
	 * Gets database tables for this entity.
	 *
	 * An array of arrays, where each sub-array holds the tables for a particular
	 * context. Within each sub-array, the value of each element is the DB field
	 * schema for a table (i.e., the part of the CREATE TABLE query within the main
	 * parentheses), and the keys are the table names. The base DB prefix will be
	 * prepended to table names for $single and $network, while $site tables will be
	 * prepended with blog prefix instead.
	 *
	 * @since 2.4.0
	 *
	 * @return  string[][] $db_tables {
	 *      @type string[] $single    Tables for a single site (non-multisite) install.
	 *      @type string[] $site      Tables for each site in a multisite network.
	 *      @type string[] $network   Tables for a multisite network.
	 *      @type string[] $local     Tables for $single and $site.
	 *      @type string[] $global    Tables for $single and $network.
	 *      @type string[] $universal Tables for $single, $site, and $network.
	 * }
	 */
	protected function get_db_tables() {
		return array();
	}

	/**
	 * Gets install routines for custom capabilities for this entity.
	 *
	 * @since 2.4.0
	 *
	 * @return WordPoints_Installer_Caps[][] Custom caps installers.
	 */
	protected function get_custom_caps_install_routines() {

		$caps = $this->get_custom_caps();

		if ( empty( $caps ) ) {
			return array();
		}

		return array(
			'site'   => array( new WordPoints_Installer_Caps( $caps ) ),
			'single' => array( new WordPoints_Installer_Caps( $caps ) ),
		);
	}

	/**
	 * Gets the custom capabilities used by this entity.
	 *
	 * The function should return an array of capabilities of the format processed
	 * by {@see wordpoints_add_custom_caps()}.
	 *
	 * @since 2.4.0
	 *
	 * @return string[] The custom caps (keys) and their corresponding core caps
	 *                  (values).
	 */
	protected function get_custom_caps() {
		return array();
	}

	/**
	 * @since 2.4.0
	 */
	public function get_update_routine_factories() {
		return array();
	}

	/**
	 * @since 2.4.0
	 */
	public function get_uninstall_routines() {

		$routines = array(
			'single'  => array(),
			'site'    => array(),
			'network' => array(),
		);

		$factories = $this->get_uninstall_routine_factories();

		if ( is_multisite() ) {

			foreach ( $factories as $factory ) {

				if ( $factory instanceof WordPoints_Uninstaller_Factory_SiteI ) {
					$routines['site'] = array_merge( $routines['site'], $factory->get_for_site() );
				}

				if ( $factory instanceof WordPoints_Uninstaller_Factory_NetworkI ) {
					$routines['network'] = array_merge( $routines['network'], $factory->get_for_network() );
				}
			}

		} else {

			foreach ( $factories as $factory ) {
				if ( $factory instanceof WordPoints_Uninstaller_Factory_SingleI ) {
					$routines['single'] = array_merge( $routines['single'], $factory->get_for_single() );
				}
			}
		}

		return $routines;
	}

	/**
	 * Gets a list of factories to create the uninstall routines for this entity.
	 *
	 * @since 2.4.0
	 *
	 * @return object[] Uninstall routine factories.
	 */
	protected function get_uninstall_routine_factories() {

		$factories = array();

		$db_tables = $this->get_db_tables();

		if ( ! empty( $db_tables ) ) {
			$factories[] = new WordPoints_Uninstaller_Factory_DB_Tables(
				array_map( 'array_keys', $db_tables )
			);
		}

		$custom_caps = $this->get_custom_caps();

		if ( ! empty( $custom_caps ) ) {
			$factories[] = new WordPoints_Uninstaller_Factory_Caps(
				array_keys( $custom_caps )
			);
		}

		return $factories;
	}
}

// EOF
