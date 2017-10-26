<?php

/**
 * DB table utf8mb4 updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Maybe update some database tables to the utf8mb4 character set.
 *
 * If the tables already has the correct character set, we do nothing.
 *
 * @since 2.4.0
 */
class WordPoints_Updater_DB_Tables_UTF8MB4 implements WordPoints_RoutineI {

	/**
	 * The names of the tables to update.
	 *
	 * @since 2.4.0
	 *
	 * @var string[]
	 */
	protected $tables;

	/**
	 * The DB prefix to use, 'site' or 'base'.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * @since 2.4.0
	 *
	 * @param string[] $tables The names of the tables to update.
	 * @param string   $prefix The database prefix to use, 'site' or 'base'.
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

		if ( 'utf8mb4' !== $wpdb->charset ) {
			return;
		}

		$prefix = 'site' === $this->prefix ? $wpdb->prefix : $wpdb->base_prefix;

		foreach ( $this->tables as $table_name ) {
			maybe_convert_table_to_utf8mb4( $prefix . $table_name );
		}
	}
}

// EOF
