<?php

/**
 * DB tables uninstaller.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Uninstalls tables from the database.
 *
 * @since 2.4.0
 */
class WordPoints_Uninstaller_DB_Tables implements WordPoints_RoutineI {

	/**
	 * The names of the tables to uninstall.
	 *
	 * @since 2.4.0
	 *
	 * @var string[]
	 */
	protected $tables;

	/**
	 * The prefix to use when uninstalling the tables, 'site' or 'base'.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * @since 2.4.0
	 *
	 * @param string[] $tables The names of the table to uninstall, sans DB prefix.
	 * @param string   $prefix The DB prefix to use, 'site' or 'base'.
	 */
	public function __construct( $tables, $prefix = 'site' ) {

		$this->tables = $tables;
		$this->prefix = $prefix;
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {

		global $wpdb;

		// Null will use the current blog's prefix, 0 the base prefix.
		if ( 'site' === $this->prefix ) {
			$site_id = null;
		} else {
			$site_id = 0;
		}

		$prefix = $wpdb->get_blog_prefix( $site_id );

		foreach ( $this->tables as $table ) {

			$table = str_replace( '`', '``', $table );

			$wpdb->query( 'DROP TABLE IF EXISTS `' . $prefix . $table . '`' ); // WPCS: unprepared SQL, cache pass.
		}
	}
}

// EOF
