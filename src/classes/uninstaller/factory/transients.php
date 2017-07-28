<?php

/**
 * Transients uninstaller factory class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Factory for uninstall routines for transients.
 *
 * @since 2.4.0
 */
class WordPoints_Uninstaller_Factory_Transients
	implements WordPoints_Uninstaller_Factory_SingleI,
		WordPoints_Uninstaller_Factory_SiteI,
		WordPoints_Uninstaller_Factory_NetworkI {

	/**
	 * The transients to uninstall, indexed by context.
	 *
	 * @since 2.4.0
	 *
	 * @var string[][]
	 */
	protected $transients;

	/**
	 * @since 2.4.0
	 *
	 * @param string[][] $transients The transients to uninstall, indexed by context.
	 */
	public function __construct( $transients ) {

		$this->transients = wordpoints_map_context_shortcuts( $transients );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_for_single() {
		return $this->get_for_context( 'single' );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_for_site() {
		return $this->get_for_context( 'site' );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_for_network() {
		return $this->get_for_context( 'network' );
	}

	/**
	 * Gets the uninstallers for a particular context.
	 *
	 * @since 2.4.0
	 *
	 * @param string $context The context.
	 *
	 * @return WordPoints_Uninstaller_Callback[] The uninstallers for this context.
	 */
	public function get_for_context( $context ) {

		$routines = array();

		if ( isset( $this->transients[ $context ] ) ) {
			$routines[] = new WordPoints_Uninstaller_Callback(
				'network' === $context ? 'delete_site_transient' : 'delete_transient'
				, $this->transients[ $context ]
			);
		}

		return $routines;
	}
}

// EOF
