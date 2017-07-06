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
class WordPoints_Installable implements WordPoints_InstallableI {

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
	 * The code version of this installable.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Constructs the installable.
	 *
	 * @since 2.4.0
	 *
	 * @param string $type    The type of entity.
	 * @param string $slug    The slug of the entity.
	 * @param string $version The current code version of the entity.
	 */
	public function __construct( $type, $slug, $version ) {

		$this->type = $type;
		$this->slug = $slug;
		$this->version = $version;
	}

	/**
	 * @since 2.4.0
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * @since 2.4.0
	 */
	public function get_version() {
		return $this->version;
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
			$version = $this->version;
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

		$site_ids = get_site_transient( 'wordpoints_all_site_ids' );

		if ( ! $site_ids ) {

			$site_ids = get_sites(
				array(
					'fields'     => 'ids',
					'network_id' => get_current_network_id(),
					'number'     => 0,
				)
			);

			set_site_transient( 'wordpoints_all_site_ids', $site_ids, 2 * MINUTE_IN_SECONDS );
		}

		return $site_ids;
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
		$sites[] = $id;

		update_site_option( $option_name, $sites );
	}

	/**
	 * @since 2.4.0
	 */
	public function delete_installed_site_ids() {
		delete_site_option( $this->get_installed_site_ids_option_name() );
	}
}

// EOF
