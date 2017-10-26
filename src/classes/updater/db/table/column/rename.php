<?php

/**
 * Rename DB table column name updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Renames a column of a table in the database.
 *
 * @since 2.4.0
 */
class WordPoints_Updater_DB_Table_Column_Rename implements WordPoints_RoutineI {

	/**
	 * The name of the table.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * The column's current name.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $from;

	/**
	 * The column's new name.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $to;

	/**
	 * The column's definition.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $definition;

	/**
	 * The table prefix to use, 'site' or 'base'.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $table_prefix;

	/**
	 * @since 2.4.0
	 *
	 * @param string $table        The table name.
	 * @param string $from         The old column name.
	 * @param string $to           The new column name.
	 * @param string $definition   The new column definition.
	 * @param string $table_prefix The table prefix, 'site', or 'base'.
	 */
	public function __construct( $table, $from, $to, $definition, $table_prefix = 'site' ) {

		$this->table        = $table;
		$this->from         = $from;
		$this->to           = $to;
		$this->definition   = $definition;
		$this->table_prefix = $table_prefix;
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {

		global $wpdb;

		if ( 'site' === $this->table_prefix ) {
			$table = $wpdb->prefix . $this->table;
		} else {
			$table = $wpdb->base_prefix . $this->table;
		}

		$table = wordpoints_escape_mysql_identifier( $table );
		$from  = wordpoints_escape_mysql_identifier( $this->from );
		$to    = wordpoints_escape_mysql_identifier( $this->to );

		$wpdb->query( // WPCS: unprepared SQL OK.
			"
				ALTER TABLE {$table}
				CHANGE {$from} {$to} {$this->definition}
			"
		); // WPCS: cache OK.
	}
}

// EOF
