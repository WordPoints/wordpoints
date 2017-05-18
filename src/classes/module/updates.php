<?php

/**
 * Module updates class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Model for a list of module updates.
 *
 * @since 2.4.0
 */
class WordPoints_Module_Updates implements WordPoints_Module_UpdatesI {

	/**
	 * The new versions of modules that have updates, indexed by module basename.
	 *
	 * @since 2.4.0
	 *
	 * @var string[]
	 */
	protected $new_versions;

	/**
	 * The versions of all of the modules that were checked, indexed by basename.
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
	 * @param string[] $versions The new versions of the modules with updates,
	 *                           indexed by module slug.
	 * @param string[] $checked  The versions of all of the modules that were
	 *                           checked, indexed by module slug.
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
	public function set_versions_checked( array $modules ) {
		$this->checked_versions = $modules;
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
	public function has_update( $module ) {
		return isset( $this->new_versions[ $module ] );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_new_version( $module ) {

		if ( ! isset( $this->new_versions[ $module ] ) ) {
			return false;
		}

		return $this->new_versions[ $module ];
	}

	/**
	 * @since 2.4.0
	 */
	public function set_new_version( $module, $version ) {
		$this->new_versions[ $module ] = $version;
	}

	/**
	 * Fills the object with the data from the database.
	 *
	 * @since 2.4.0
	 */
	public function fill() {

		$cache = get_site_transient( 'wordpoints_module_updates' );

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

		set_site_transient( 'wordpoints_module_updates', $data );
	}
}

// EOF
