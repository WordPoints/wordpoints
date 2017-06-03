<?php

/**
 * Extension updates class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Model for a list of extension updates.
 *
 * @since 2.4.0
 */
class WordPoints_Extension_Updates implements WordPoints_Extension_UpdatesI {

	/**
	 * The new versions of extensions that have updates, indexed by extension basename.
	 *
	 * @since 2.4.0
	 *
	 * @var string[]
	 */
	protected $new_versions;

	/**
	 * The versions of all of the extensions that were checked, indexed by basename.
	 *
	 * @since 2.4.0
	 *
	 * @var string[]
	 */
	protected $checked_versions;

	/**
	 * The time that the check took place.
	 *
	 * @since 2.4.0
	 *
	 * @var int
	 */
	protected $time;

	/**
	 * @since 2.4.0
	 *
	 * @param string[] $versions The new versions of the extensions with updates,
	 *                           indexed by extension slug.
	 * @param string[] $checked  The versions of all of the extensions that were
	 *                           checked, indexed by extension slug.
	 * @param int      $time     The timestamp of the time the check took place.
	 *                           Defaults to now.
	 */
	public function __construct(
		array $versions = array(),
		array $checked = array(),
		$time = null
	) {

		if ( is_null( $time ) ) {
			$time = time();
		}

		$this->new_versions     = $versions;
		$this->checked_versions = $checked;
		$this->time             = $time;
	}

	/**
	 * @since 2.4.0
	 */
	public function get_time_checked() {
		return $this->time;
	}

	/**
	 * @since 2.4.0
	 */
	public function set_time_checked( $time ) {
		$this->time = $time;
	}

	/**
	 * @since 2.4.0
	 */
	public function get_versions_checked() {
		return $this->checked_versions;
	}

	/**
	 * @since 2.4.0
	 */
	public function set_versions_checked( array $extensions ) {
		$this->checked_versions = $extensions;
	}

	/**
	 * @since 2.4.0
	 */
	public function get_new_versions() {
		return $this->new_versions;
	}

	/**
	 * @since 2.4.0
	 */
	public function set_new_versions( array $versions ) {
		$this->new_versions = $versions;
	}

	/**
	 * @since 2.4.0
	 */
	public function has_update( $extension ) {
		return isset( $this->new_versions[ $extension ] );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_new_version( $extension ) {

		if ( ! isset( $this->new_versions[ $extension ] ) ) {
			return false;
		}

		return $this->new_versions[ $extension ];
	}

	/**
	 * @since 2.4.0
	 */
	public function set_new_version( $extension, $version ) {
		$this->new_versions[ $extension ] = $version;
	}

	/**
	 * Fills the object with the data from the database.
	 *
	 * @since 2.4.0
	 */
	public function fill() {

		$cache = get_site_transient( 'wordpoints_extension_updates' );

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( isset( $cache['time_checked'] ) ) {
			$this->time = $cache['time_checked'];
		} else {
			$this->time = 0;
		}

		if ( isset( $cache['checked_versions'] ) ) {
			$this->checked_versions = $cache['checked_versions'];
		} else {
			$this->checked_versions = array();
		}

		if ( isset( $cache['new_versions'] ) ) {
			$this->new_versions = $cache['new_versions'];
		} else {
			$this->new_versions = array();
		}
	}

	/**
	 * Saves the data in the object to the database.
	 *
	 * @since 2.4.0
	 */
	public function save() {

		$data = array(
			'time_checked'     => $this->time,
			'checked_versions' => $this->checked_versions,
			'new_versions'     => $this->new_versions,
		);

		set_site_transient( 'wordpoints_extension_updates', $data );
	}
}

// EOF
