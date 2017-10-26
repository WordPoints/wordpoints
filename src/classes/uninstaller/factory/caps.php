<?php

/**
 * Caps uninstaller factory class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Factory for custom capability uninstallers.
 *
 * @since 2.4.0
 */
class WordPoints_Uninstaller_Factory_Caps
	implements WordPoints_Uninstaller_Factory_SingleI,
		WordPoints_Uninstaller_Factory_SiteI {

	/**
	 * The uninstaller routines.
	 *
	 * @since 2.4.0
	 *
	 * @var WordPoints_RoutineI[]
	 */
	protected $routines;

	/**
	 * @since 2.4.0
	 *
	 * @param string[] $caps The custom capabilities to be uninstalled.
	 */
	public function __construct( $caps ) {

		$this->routines = array( new WordPoints_Uninstaller_Caps( $caps ) );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_for_single() {
		return $this->routines;
	}

	/**
	 * @since 2.4.0
	 */
	public function get_for_site() {
		return $this->routines;
	}
}

// EOF
