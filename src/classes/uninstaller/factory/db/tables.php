<?php

/**
 * DB tables uninstaller factory class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Factory for DB table uninstallers.
 *
 * @since 2.4.0
 */
class WordPoints_Uninstaller_Factory_DB_Tables
	implements WordPoints_Uninstaller_Factory_SingleI,
		WordPoints_Uninstaller_Factory_SiteI,
		WordPoints_Uninstaller_Factory_NetworkI {

	/**
	 * The names of tables to uninstall, indexed by context.
	 *
	 * @since 2.4.0
	 *
	 * @var string[][]
	 */
	protected $db_tables;

	/**
	 * @since 2.4.0
	 *
	 * @param string[][] $db_tables The table names, indexed by context.
	 */
	public function __construct( array $db_tables ) {

		$this->db_tables = wordpoints_map_context_shortcuts( $db_tables );
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
	 * @return WordPoints_Uninstaller_DB_Tables[] The uninstallers for this context.
	 */
	protected function get_for_context( $context ) {

		$routines = array();

		if ( ! empty( $this->db_tables[ $context ] ) ) {
			$routines[] = new WordPoints_Uninstaller_DB_Tables(
				$this->db_tables[ $context ]
				, 'site' === $context ? 'site' : 'base'
			);
		}

		return $routines;
	}
}

// EOF
