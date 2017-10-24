<?php

/**
 * Database query class.
 *
 * @package WordPoints
 * @since 2.1.0
 */

/**
 * Database query bootstrap.
 *
 * This class provides a bootstrap that can be extended to provide a simple, common
 * interface for querying a database. The child class defines the table schema, and
 * this bootstrap takes care of the rest.
 *
 * @since 2.1.0
 */
class WordPoints_DB_Query {

	/**
	 * The name of the table this query class is for.
	 *
	 * This should be the full name of the table, including the prefix. You will
	 * therefore likely need to define it from inside your constructor.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $table_name;

	/**
	 * The columns in the table being queried.
	 *
	 * The keys are the names of the columns. The values are arrays that support the
	 * following keys:
	 *
	 * - format (required) The format (%s, %d, or %f) to use when passing the values
	 *   for this format to $wpdb->prepare().
	 * - values (optional) An array of values that this column can have. Any values
	 *   that aren't in this list will be discarded from a query.
	 * - unsigned (optional) Whether the value is unsigned. If this is true, values
	 *   for this column will be rejected if they are not positive.
	 * - is_date (optional) Whether this is a DATETIME field. If so date queries will
	 *   be supported.
	 *
	 * For each column in this array, the following query args are supported:
	 *
	 * - "{$column}"          A single value that this column should have.
	 * - "{$column}__compare" How to compare the above value to the value in the DB.
	 *                        The default is '='.
	 * - "{$column}__in"      An array of values that this column may have.
	 * - "{$column}__not_in"  An array of values that this column may not have.
	 *
	 * Where {$column} is the name of the column.
	 *
	 * The "{$column}" query arg takes precedence over the "{$column}__in" and
	 * "{$column}__not_in" query args.
	 *
	 * However, if the column specifies that is_date is true, then the above are not
	 * supported, and the following are offered instead:
	 *
	 * - "{$column}_query" Arguments to pass to a WP_Date_Query.
	 *
	 * @since 2.1.0
	 *
	 * @var array[]
	 */
	protected $columns = array();

	/**
	 * The slug of the meta type.
	 *
	 * If this is defined, the 'meta_query', 'meta_key', 'meta_value',
	 * 'meta_compare', and 'meta_type' args are supported, and will be passed to
	 * WP_Meta_Query.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $meta_type;

	/**
	 * The default values for the query args.
	 *
	 * You can override this entirely if needed, or just modify it in your
	 * constructor before calling parent::__construct().
	 *
	 * @since 2.1.0
	 *
	 * @var array
	 */
	protected $defaults = array(
		'offset' => 0,
		'order'  => 'DESC',
	);

	/**
	 * A list of args that are deprecated and information about their replacements.
	 *
	 * Each element of the array should contain the following key-value pairs:
	 *
	 * - 'replacement' - The replacement arg.
	 * - 'version'     - The version in which this arg was deprecated.
	 * - 'class'       - The class this arg is from. Usually you will just want to
	 *                   use `__CLASS__` here.
	 *
	 * @since 2.3.0
	 *
	 * @var string[][]
	 */
	protected $deprecated_args = array();

	/**
	 * The query arguments.
	 *
	 * @since 2.1.0
	 *
	 * @type array $args
	 */
	protected $args = array();

	/**
	 * Whether the query is ready for execution, or still needs to be prepared.
	 *
	 * @since 2.1.0
	 *
	 * @type bool $is_query_ready
	 */
	protected $is_query_ready = false;

	/**
	 * The SELECT statement for the query.
	 *
	 * @since 2.1.0
	 *
	 * @type string $select
	 */
	protected $select;

	/**
	 * The SELECT COUNT statement for a count query.
	 *
	 * @since 2.1.0
	 *
	 * @type string $select_count
	 */
	protected $select_count = 'SELECT COUNT(*)';

	/**
	 * The JOIN query with the meta table.
	 *
	 * @since 2.1.0
	 *
	 * @type string $meta_join
	 */
	protected $meta_join;

	/**
	 * The WHERE clause for the query.
	 *
	 * @since 2.1.0
	 *
	 * @type string $where
	 */
	protected $where;

	/**
	 * The array of conditions for the WHERE clause.
	 *
	 * @since 2.1.0
	 *
	 * @type array $wheres
	 */
	protected $wheres = array();

	/**
	 * The LIMIT clause for the query.
	 *
	 * @since 2.1.0
	 *
	 * @type string $limit
	 */
	protected $limit;

	/**
	 * The ORDER clause for the query.
	 *
	 * @since 2.1.0
	 *
	 * @type string $order
	 */
	protected $order;

	/**
	 * Holds the meta query object when a meta query is being performed.
	 *
	 * @since 2.1.0
	 *
	 * @type WP_Meta_Query $meta_query
	 */
	protected $meta_query;

	//
	// Public Methods.
	//

	/**
	 * Construct the class.
	 *
	 * All of the arguments are expected *not* to be SQL escaped.
	 *
	 * @since 2.1.0
	 * @since 2.4.0 The 'start' query arg was deprecated in favor of 'offset'.
	 *
	 * @see WP_Meta_Query for the proper arguments for 'meta_query', 'meta_key', 'meta_value', 'meta_compare', and 'meta_type'.
	 *
	 * @param array $args {
	 *        The arguments for the query.
	 *
	 *        @type string|array $fields              Fields to include in the results. Default is all fields.
	 *        @type int          $limit               The maximum number of results to return. Default is null (no limit).
	 *        @type int          $offset              The offset for the LIMIT clause. Default: 0.
	 *        @type string       $order_by            The field to use to order the results.
	 *        @type string       $order               The order for the query: ASC or DESC (default).
	 *        @type string       $meta_key            See WP_Meta_Query.
	 *        @type mixed        $meta_value          See WP_Meta_Query.
	 *        @type string       $meta_compare        See WP_Meta_Query.
	 *        @type string       $meta_type           See WP_Meta_Query.
	 *        @type array        $meta_query          See WP_Meta_Query.
	 * }
	 */
	public function __construct( $args = array() ) {

		if ( ! isset( $this->deprecated_args['start'] ) ) {
			$this->deprecated_args['start'] = array(
				'class'       => __CLASS__,
				'version'     => '2.4.0',
				'replacement' => 'offset',
			);
		}

		foreach ( $this->deprecated_args as $arg => $data ) {
			if ( isset( $args[ $arg ] ) ) {

				_deprecated_argument(
					esc_html( "{$data['class']}::__construct" )
					, esc_html( $data['version'] )
					, esc_html( "{$arg} is deprecated, use {$data['replacement']} instead" )
				);

				$args[ $data['replacement'] ] = $args[ $arg ];

				unset( $args[ $arg ] );
			}
		}

		$this->args = array_merge( $this->defaults, $args );
	}

	/**
	 * Get a query arg.
	 *
	 * @since 2.1.0
	 *
	 * @param string $arg The query arg whose value to retrieve.
	 *
	 * @return mixed|null The query arg's value, or null if it isn't set.
	 */
	public function get_arg( $arg ) {

		if ( isset( $this->deprecated_args[ $arg ] ) ) {

			_deprecated_argument(
				esc_html( "{$this->deprecated_args[ $arg ]['class']}::get_arg" )
				, esc_html( $this->deprecated_args[ $arg ]['version'] )
				, esc_html( "{$arg} is deprecated, use {$this->deprecated_args[ $arg ]['replacement']} instead" )
			);

			$arg = $this->deprecated_args[ $arg ]['replacement'];
		}

		if ( isset( $this->args[ $arg ] ) ) {
			return $this->args[ $arg ];
		} else {
			return null;
		}
	}

	/**
	 * Set arguments for the query.
	 *
	 * All of the arguments supported by the constructor may be passed in here, and
	 * will be merged into the array of existing args.
	 *
	 * @since 2.1.0
	 *
	 * @param array $args A list of arguments to set and their values.
	 *
	 * @return WordPoints_DB_Query To allow for method chaining.
	 */
	public function set_args( array $args ) {

		foreach ( $this->deprecated_args as $arg => $data ) {
			if ( isset( $args[ $arg ] ) ) {

				_deprecated_argument(
					esc_html( "{$data['class']}::set_args" )
					, esc_html( $data['version'] )
					, esc_html( "{$arg} is deprecated, use {$data['replacement']} instead" )
				);

				$args[ $data['replacement'] ] = $args[ $arg ];

				unset( $args[ $arg ] );
			}
		}

		$this->args = array_merge( $this->args, $args );

		$this->is_query_ready = false;

		return $this;
	}

	/**
	 * Count the number of results.
	 *
	 * When used with a query that contains a LIMIT clause, this method currently
	 * returns the count of the query ignoring the LIMIT, as would be the case with
	 * any similar query. However, this behaviour is not hardened and should not be
	 * relied upon. Make inquiry before assuming the constancy of this behaviour.
	 *
	 * @since 2.1.0
	 *
	 * @return int The number of results.
	 */
	public function count() {

		global $wpdb;

		$count = (int) $wpdb->get_var( $this->get_sql( 'SELECT COUNT' ) ); // WPCS: unprepared SQL, cache OK

		return $count;
	}

	/**
	 * Get the results for the query.
	 *
	 * @since 2.1.0
	 *
	 * @param string $method The method to use. Options are 'results', 'row', 'col',
	 *                       and 'var'.
	 *
	 * @return mixed The results of the query, or false on failure.
	 */
	public function get( $method = 'results' ) {

		global $wpdb;

		$methods = array( 'results', 'row', 'col', 'var' );

		if ( ! in_array( $method, $methods, true ) ) {

			_doing_it_wrong( __METHOD__, esc_html( sprintf( 'WordPoints Debug Error: invalid get method %s, possible values are %s', $method, implode( ', ', $methods ) ) ), '1.0.0' );

			return false;
		}

		$result = $wpdb->{"get_{$method}"}( $this->get_sql() );

		return $result;
	}

	/**
	 * Get the SQL for the query.
	 *
	 * This function can return the SQL for a SELECT or SELECT COUNT query. To
	 * specify which one to return, set the $select_type parameter. Defaults to
	 * SELECT.
	 *
	 * This function is public for debugging purposes.
	 *
	 * @since 2.1.0
	 *
	 * @param string $select_type The type of query, SELECT, or SELECT COUNT.
	 *
	 * @return string The SQL for the query.
	 */
	public function get_sql( $select_type = 'SELECT' ) {

		$this->prepare_query();

		if ( 'SELECT COUNT' === $select_type ) {
			$select = $this->select_count;
			$order  = '';
		} else {
			$select = $this->select;
			$order  = $this->order;
		}

		return $select
			. "\nFROM `{$this->table_name}`\n"
			. $this->meta_join
			. $this->where
			. $order
			. $this->limit;
	}

	//
	// Filter Methods.
	//

	/**
	 * Filter date query valid columns for WP_Date_Query.
	 *
	 * @since 2.1.0
	 *
	 * @WordPress\filter date_query_valid_columns Added and subsequently removed by
	 *                                            self::prepare_date_where().
	 *
	 * @param string[] $valid_columns The names of the valid columns for date queries.
	 *
	 * @return string[] The valid columns.
	 */
	public function date_query_valid_columns_filter( $valid_columns ) {

		$valid_columns = array_merge(
			$valid_columns
			, array_keys(
				wp_list_filter( $this->columns, array( 'is_date' => true ) )
			)
		);

		return $valid_columns;
	}

	//
	// Protected Methods.
	//

	/**
	 * Prepare the query.
	 *
	 * @since 2.1.0
	 */
	protected function prepare_query() {

		if ( ! $this->is_query_ready ) {

			$this->prepare_select();
			$this->prepare_where();
			$this->prepare_order_by();
			$this->prepare_limit();

			$this->is_query_ready = true;
		}
	}

	/**
	 * Prepare the select statement.
	 *
	 * @since 2.1.0
	 */
	protected function prepare_select() {

		$all_fields = array_keys( $this->columns );
		$fields     = array();

		if ( ! empty( $this->args['fields'] ) ) {

			$fields = (array) $this->args['fields'];
			$diff   = array_diff( $fields, $all_fields );
			$fields = array_intersect( $all_fields, $fields );

			if ( ! empty( $diff ) ) {
				_doing_it_wrong( __METHOD__, esc_html( 'WordPoints Debug Error: invalid field(s) "' . implode( '", "', $diff ) . '" given' ), '1.0.0' );
			}
		}

		// Pull all fields by default.
		if ( empty( $fields ) ) {
			$fields = $all_fields;
		}

		$fields = implode( ', ', array_map( 'wordpoints_escape_mysql_identifier', $fields ) );

		$this->select = "SELECT {$fields}";
	}

	/**
	 * Validates a value against an array of sanitizing functions.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed      $value      The value to validate.
	 * @param callable[] $validators The validators to validate it against.
	 *
	 * @return mixed The validated value, or false if invalid.
	 */
	protected function validate_value( $value, $validators ) {

		foreach ( $validators as $validator ) {

			$value = call_user_func_array( $validator, array( &$value ) );

			if ( false === $value ) {
				break;
			}
		}

		return $value;
	}

	/**
	 * Validates an array of values against an array of sanitizing functions.
	 *
	 * @since 2.1.0
	 *
	 * @param array      $values     The values to validate.
	 * @param callable[] $validators The validators to validate each value against.
	 *
	 * @return array The validated values, with any invalid ones removed.
	 */
	protected function validate_values( $values, $validators ) {

		foreach ( $values as $index => $value ) {

			$value = $this->validate_value( $value, $validators );

			if ( false === $value ) {
				unset( $values[ $index ] );
			}
		}

		return $values;
	}

	/**
	 * Validate an unsigned column.
	 *
	 * The value must be positive, zero-inclusive. We can't just use
	 * wordpoints_posint() because it is zero exclusive.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $value The value to validate.
	 *
	 * @return int|false The validated value or false.
	 */
	protected function validate_unsigned_column( $value ) {

		if ( false !== wordpoints_int( $value ) && $value >= 0 ) {
			return $value;
		}

		return false;
	}

	/**
	 * Get an array of validating/sanitizing functions for the values of a column.
	 *
	 * @since 2.1.0
	 *
	 * @param array $data The data for the column.
	 *
	 * @return callable[] The validation functions.
	 */
	protected function get_validators_for_column( $data ) {

		$validators = array();

		// Default validators for integer columns.
		if ( '%d' === $data['format'] ) {
			if ( ! empty( $data['unsigned'] ) ) {
				$validators[] = array( $this, 'validate_unsigned_column' );
			} else {
				$validators[] = 'wordpoints_int';
			}
		}

		return $validators;
	}

	/**
	 * Prepare the conditions for the WHERE clause for a column.
	 *
	 * @since 2.1.0
	 *
	 * @param string $column The column name.
	 * @param array  $data   The column data.
	 */
	protected function prepare_column_where( $column, $data ) {

		// If a single value has been supplied for the column, it takes precedence.
		if ( isset( $this->args[ $column ] ) ) {
			$this->prepare_column( $column, $data );
		} elseif ( isset( $this->args[ "{$column}__in" ] ) ) {
			$this->prepare_column__in( $column, $data );
		} elseif ( isset( $this->args[ "{$column}__not_in" ] ) ) {
			$this->prepare_column__in( $column, $data, 'NOT IN' );
		}
	}

	/**
	 * Prepare a single-value condition for the WHERE clause for a column.
	 *
	 * @since 2.1.0
	 *
	 * @param string $column The name of the column
	 * @param array  $data   The column data.
	 */
	protected function prepare_column( $column, $data ) {

		global $wpdb;

		if (
			isset( $data['values'] )
			&& ! in_array( $this->args[ $column ], $data['values'], true )
		) {
			return;
		}

		$value = $this->validate_value(
			$this->args[ $column ]
			, $this->get_validators_for_column( $data )
		);

		if ( false === $value ) {
			return;
		}

		$compare = $this->get_comparator_for_column( $column, $data );

		$column = wordpoints_escape_mysql_identifier( $column );

		$this->wheres[] = $wpdb->prepare( // WPCS: unprepared SQL, PreparedSQLPlaceholders replacement count OK.
			"{$column} {$compare} {$data['format']}"
			, $value
		);
	}

	/**
	 * Get the comparator for a column.
	 *
	 * @since 2.1.0
	 *
	 * @param string $column The column name.
	 * @param array  $data   The column data.
	 *
	 * @return string The comparator for the column.
	 */
	protected function get_comparator_for_column( $column, $data ) {

		$comparisons = array( '=', '<', '>', '<>', '!=', '<=', '>=' );

		// MySQL doesn't support LIKE and NOT LIKE for int columns.
		// See https://stackoverflow.com/q/8422455/1924128
		if ( '%s' === $data['format'] ) {
			$comparisons = array_merge( $comparisons, array( 'LIKE', 'NOT LIKE' ) );
		}

		$comparator = '=';

		if (
			isset( $this->args[ "{$column}__compare" ] )
			&& in_array( $this->args[ "{$column}__compare" ], $comparisons, true )
		) {
			$comparator = $this->args[ "{$column}__compare" ];
		}

		return $comparator;
	}

	/**
	 * Prepare the IN or NOT IN conditions for a column.
	 *
	 * @since 2.1.0
	 *
	 * @param string $column The name of the column.
	 * @param array  $data   The column data.
	 * @param string $type   The type of IN clause, IN or NOT IN.
	 */
	protected function prepare_column__in( $column, $data, $type = 'IN' ) {

		$key = "{$column}__" . strtolower( str_replace( ' ', '_', $type ) );

		if ( empty( $this->args[ $key ] ) || ! is_array( $this->args[ $key ] ) ) {
			return;
		}

		$values = $this->args[ $key ];

		if ( isset( $data['values'] ) ) {
			$values = array_intersect( $values, $data['values'] );
		} else {
			$values = $this->validate_values(
				$values
				, $this->get_validators_for_column( $data )
			);
		}

		if ( empty( $values ) ) {
			return;
		}

		$in = wordpoints_prepare__in( $values, $data['format'] );

		if ( false === $in ) {
			return;
		}

		$column = wordpoints_escape_mysql_identifier( $column );

		$this->wheres[] = "{$column} {$type} ({$in})";
	}

	/**
	 * Prepare the WHERE clause for the query.
	 *
	 * @since 2.1.0
	 */
	protected function prepare_where() {

		$this->wheres = array();

		foreach ( $this->columns as $column => $data ) {

			if ( ! empty( $data['is_date'] ) ) {
				$this->prepare_date_where( $column );
			} else {
				$this->prepare_column_where( $column, $data );
			}
		}

		$this->prepare_meta_where();

		if ( ! empty( $this->wheres ) ) {
			$this->where = 'WHERE ' . implode( ' AND ', $this->wheres ) . "\n";
		}
	}

	/**
	 * Prepare the LIMIT clause for the query.
	 *
	 * @since 2.1.0
	 */
	protected function prepare_limit() {

		// MySQL doesn't allow for the offset without a limit, so if no limit is set
		// we can ignore the offset arg. See https://stackoverflow.com/a/271650/1924128
		if ( ! isset( $this->args['limit'] ) ) {
			return;
		}

		foreach ( array( 'limit', 'offset' ) as $key ) {

			// Save a backup of the arg value since wordpoints_int() is by reference.
			$arg = $this->args[ $key ];

			if ( false === wordpoints_int( $this->args[ $key ] ) ) {

				_doing_it_wrong(
					__METHOD__
					, sprintf(
						"WordPoints Debug Error: '%s' must be a positive integer, %s given"
						, esc_html( $key )
						, esc_html( strval( $arg ) ? $arg : gettype( $arg ) )
					)
					, '1.0.0'
				);

				$this->args[ $key ] = 0;
			}
		}

		if ( $this->args['limit'] > 0 && $this->args['offset'] >= 0 ) {
			$this->limit = "LIMIT {$this->args['offset']}, {$this->args['limit']}";
		}
	}

	/**
	 * Prepare the ORDER BY clause for the query.
	 *
	 * @since 2.1.0
	 */
	protected function prepare_order_by() {

		if ( empty( $this->args['order_by'] ) ) {
			$this->order = '';
			return;
		}

		$order    = $this->args['order'];
		$order_by = $this->args['order_by'];

		if ( ! in_array( $order, array( 'DESC', 'ASC' ), true ) ) {

			_doing_it_wrong( __METHOD__, esc_html( "WordPoints Debug Error: invalid 'order' \"{$order}\", possible values are DESC and ASC" ), '2.1.0' );
			$order = 'DESC';
		}

		if ( 'meta_value' === $order_by ) {

			global $wpdb;

			$meta_table_name = wordpoints_escape_mysql_identifier(
				$wpdb->{"{$this->meta_type}meta"}
			);

			if ( isset( $this->args['meta_type'] ) ) {

				$meta_type = $this->meta_query->get_cast_for_type( $this->args['meta_type'] );
				$order_by  = "CAST({$meta_table_name}.meta_value AS {$meta_type})";

			} else {

				$order_by = "{$meta_table_name}.meta_value";
			}

		} elseif ( isset( $this->columns[ $order_by ] ) ) {

			$order_by = wordpoints_escape_mysql_identifier( $order_by );

		} else {

			_doing_it_wrong( __METHOD__, esc_html( "WordPoints Debug Error: invalid 'order_by' \"{$order_by}\", possible values are " . implode( ', ', array_keys( $this->columns ) ) ), '2.1.0' );
			return;
		}

		$this->order = "ORDER BY {$order_by} {$order}\n";
	}

	/**
	 * Prepare the date query for a column.
	 *
	 * @since 2.1.0
	 *
	 * @param string $column The name of the column.
	 */
	protected function prepare_date_where( $column ) {

		if (
			empty( $this->args[ "{$column}_query" ] )
			|| ! is_array( $this->args[ "{$column}_query" ] )
		) {
			return;
		}

		add_filter( 'date_query_valid_columns', array( $this, 'date_query_valid_columns_filter' ) );

		$date_query = new WP_Date_Query( $this->args[ "{$column}_query" ], $column );
		$date_query = $date_query->get_sql();

		if ( ! empty( $date_query ) ) {
			$this->wheres[] = ltrim( $date_query, ' AND' );
		}

		remove_filter( 'date_query_valid_columns', array( $this, 'date_query_valid_columns_filter' ) );
	}

	/**
	 * Prepare the meta query.
	 *
	 * @since 2.1.0
	 */
	protected function prepare_meta_where() {

		if ( empty( $this->meta_type ) ) {
			return;
		}

		$meta_args = array_intersect_key(
			$this->args
			, array(
				'meta_key'     => '',
				'meta_value'   => '',
				'meta_compare' => '',
				'meta_type'    => '',
				'meta_query'   => '',
			)
		);

		if ( empty( $meta_args ) ) {
			return;
		}

		$this->meta_query = new WP_Meta_Query();
		$this->meta_query->parse_query_vars( $meta_args );

		$meta_query = $this->meta_query->get_sql(
			$this->meta_type
			, $this->table_name
			, 'id'
			, $this
		);

		if ( ! empty( $meta_query['where'] ) ) {
			$this->wheres[] = ltrim( $meta_query['where'], ' AND' );
		}

		$this->meta_join = $meta_query['join'] . "\n";
	}
}

// EOF
