<?php

/**
 * DB tables installer class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Installer for database tables.
 *
 * @since 2.4.0
 */
class WordPoints_Installer_DB_Tables implements WordPoints_RoutineI {

	/**
	 * Schema of the tables to install, indexed by table name.
	 *
	 * @since 2.4.0
	 *
	 * @var string[]
	 */
	protected $tables;

	/**
	 * Prefix to use for the tables, 'site' or 'base'.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * @since 2.4.0
	 *
	 * @param string[] $tables The DB field schema for a table (i.e., the part of the
	 *                         `CREATE TABLE` query within the main parentheses)
	 *                         indexed by table name.
	 * @param string   $prefix Prefix to use for the tables, 'site' (default) or
	 *                         'base'.
	 */
	public function __construct( $tables, $prefix = 'site' ) {

		$this->tables = $tables;
		$this->prefix = $prefix;

		/**
		 * Upgrade functions, including `dbDelta()`.
		 *
		 * @since 2.4.0
		 */
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {
		dbDelta( $this->get_db_schema() );
	}

	/**
	 * Gets the database schema for the tables.
	 *
	 * @since 2.4.0
	 *
	 * @return string[] The database schema for the tables.
	 */
	protected function get_db_schema() {

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		if ( 'site' === $this->prefix ) {
			$prefix = $wpdb->prefix;
		} else {
			$prefix = $wpdb->base_prefix;
		}

		$schema = array();

		foreach ( $this->tables as $table_name => $table_schema ) {

			$table_name   = str_replace( '`', '``', $table_name );
			$table_schema = trim( $table_schema );

			$schema[] = "CREATE TABLE {$prefix}{$table_name} (
				{$table_schema}
			) {$charset_collate}";
		}

		return $schema;
	}
}

// EOF
