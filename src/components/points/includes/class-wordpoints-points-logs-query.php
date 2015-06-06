<?php

/**
 * WordPoints_Points_Logs_Query class.
 *
 * @package WordPoints\Points
 * @since 1.0.0
 */

/**
 * Query the points logs database table.
 *
 * This class lets you query the points logs database. The arguments are similar to
 * those available in {@link http://codex.wordpress.org/Class_Reference/WP_Query
 * WP_Query}.
 *
 * @since 1.0.0
 */
class WordPoints_Points_Logs_Query {

	//
	// Private Vars.
	//

	/**
	 * The query arguments.
	 *
	 * @since 1.0.0
	 *
	 * @type array $_args
	 */
	private $_args = array();

	/**
	 * The database table columns.
	 *
	 * @since 1.0.0
	 *
	 * @type array $_fields
	 */
	private $_fields = array( 'id', 'user_id', 'log_type', 'text', 'points', 'points_type', 'blog_id', 'site_id', 'date' );

	/**
	 * Whether the query is ready for execution.
	 *
	 * @since 1.0.0
	 *
	 * @type bool $_query_ready
	 */
	private $_query_ready = false;

	/**
	 * Whether query is supposed to use caching.
	 *
	 * @since 1.6.0
	 *
	 * @type bool $_is_cached_query
	 */
	private $_is_cached_query = false;

	/**
	 * The cache key for this query.
	 *
	 * @since 1.6.0
	 *
	 * @type string $_cache_key
	 */
	private $_cache_key;

	/**
	 * The cache group for this query.
	 *
	 * @since 1.6.0
	 *
	 * @type string $_cache_group
	 */
	private $_cache_group;

	/**
	 * The MD5 hash of the query for looking it up the the cache.
	 *
	 * @since 1.6.0
	 *
	 * @type string $_cache_query_md5
	 */
	private $_cache_query_md5;

	/**
	 * The type of select being performed.
	 *
	 * @since 1.0.0
	 *
	 * @type string $_select_type
	 */
	private $_select_type = 'SELECT';

	/**
	 * The SELECT statement for the query.
	 *
	 * @since 1.0.0
	 *
	 * @type string $_select
	 */
	private $_select;

	/**
	 * The SELECT COUNT statement for a count query.
	 *
	 * @since 1.0.0
	 *
	 * @type string $_select_count
	 */
	private $_select_count = 'SELECT COUNT(*)';

	/**
	 * The JOIN query with the log meta table.
	 *
	 * @since 1.0.0
	 *
	 * @type string $_meta_join
	 */
	private $_meta_join;

	/**
	 * The WHERE clause for the query.
	 *
	 * @since 1.0.0
	 *
	 * @type string $_where
	 */
	private $_where;

	/**
	 * The array of conditions for the WHERE clause.
	 *
	 * @since 1.0.0
	 *
	 * @type array $_wheres
	 */
	private $_wheres = array();

	/**
	 * The LIMIT clause for the query.
	 *
	 * @since 1.0.0
	 *
	 * @type string $_limit
	 */
	private $_limit;

	/**
	 * The ORDER clause for the query.
	 *
	 * @since 1.0.0
	 *
	 * @type string $_order
	 */
	private $_order;

	/**
	 * Holds the meta query object when a meta query is being performed.
	 *
	 * @since 1.1.0
	 *
	 * @type WP_Meta_Query $meta_query
	 */
	private $meta_query;

	//
	// Public Methods.
	//

	/**
	 * Construct the class.
	 *
	 * All of the arguments are expected *not* to be SQL escaped.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Introduce 'date_query' argument and support for WP_Date_Query.
	 * @since 1.1.0 Support for WP_Meta_Query. Old meta arguments were deprecated.
	 * @since 1.2.0 Introduce 'id__in' and 'id__not_in' for log IDs.
	 *
	 * @see WP_Date_Query for the proper arguments for $args['date_query'].
	 * @see WP_Meta_Query for the proper arguments for 'meta_query', 'meta_key', 'meta_value', 'meta_compare', and 'meta_type'.
	 *
	 * @param array $args {
	 *        The arguments for the query.
	 *
	 *        @type string|array $fields              Fields to include in the results.
	 *        @type int          $limit               The maximum number of results to return. Default is null (no limit).
	 *        @type int          $start               The start for the LIMIT clause. Default: 0.
	 *        @type string       $orderby             The field to use to order the results. Default: 'date'.
	 *        @type string       $order               The order for the query: ASC or DESC (default).
	 *        @type int          $id__in              Limit results to these log IDs.
	 *        @type int          $id__not_in          Exclude all logs with these IDs.
	 *        @type int          $user_id             Limit results to logs for this user.
	 *        @type int[]        $user__in            Limit results to logs for these users.
	 *        @type int[]        $user__not_in        Exclude all logs for these users from the results.
	 *        @type string       $points_type         Include only results for this type.
	 *        @type string[]     $points_type__in     Limit results to these types.
	 *        @type string[]     $points_type__not_in Exclude logs for these points types from the results.
	 *        @type string       $log_type            Return only logs of this type.
	 *        @type string[]     $log_type__in        Return only logs of these types.
	 *        @type string[]     $log_type__not_in    Exclude these log types from the results.
	 *        @type int          $points              Limit results to transactions of this amount. More uses when used with $points__compare.
	 *        @type string       $points__compare     Comparison operator for logs comparison with $points. May be any of these: '=', '<', '>', '<>', '!=', '<=', '>='. Default is '='.
	 *        @type string       $text                Log text must match this. Method of comparison is determined by $text__compare. Wildcards (% and _)
	 *                                                must be escaped to be treated literally when doing LIKE comparisons.
	 *        @type string       $text__compare       Comparison operator for $text. May be any of these:  '=', '<>', '!=', 'LIKE', 'NOT LIKE'. Default is 'LIKE'.
	 *        @type int          $blog_id             Limit results to those from this blog within the network (mulitsite). Default is $wpdb->blogid (current blog).
	 *        @type int[]        $blog__in            Limit results to these blogs.
	 *        @type int[]        $blog__not_in        Exclude these blogs.
	 *        @type int          $site_id             Limit results to this network. Default is $wpdb->siteid (current network). There isn't currently
	 *                                                a use for this one, but its possible in future that WordPress will allow multi-network installs.
	 *        @type array        $date_query          Arguments for a WP_Date_Query.
	 *        @type string       $meta_key            See WP_Meta_Query.
	 *        @type mixed        $meta_value          See WP_Meta_Query.
	 *        @type string       $meta_compare        See WP_Meta_Query.
	 *        @type string       $meta_type           See WP_Meta_Query.
	 *        @type array        $meta_query          See WP_Meta_Query.
	 * }
	 */
	public function __construct( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'fields'              => 'all',
			'limit'               => null,
			'start'               => 0,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'id__in'              => array(),
			'id__not_in'          => array(),
			'user_id'             => 0,
			'user__in'            => array(),
			'user__not_in'        => array(),
			'points_type'         => '',
			'points_type__in'     => array(),
			'points_type__not_in' => array(),
			'log_type'            => '',
			'log_type__in'        => array(),
			'log_type__not_in'    => array(),
			'points'              => null,
			'points__compare'     => '=',
			'text'                => null,
			'text__compare'       => 'LIKE',
			'blog_id'             => $wpdb->blogid,
			'blog__in'            => array(),
			'blog__not_in'        => array(),
			'site_id'             => $wpdb->siteid,
			'date_query'          => null,
		);

		$this->_args = wp_parse_args( $args, $defaults );

	} // public function __construct()

	/**
	 * Get a query arg.
	 *
	 * @since 1.9.0
	 *
	 * @param string $arg The query arg whose value to retrieve.
	 *
	 * @return mixed|void The query arg's value, or nothing if it isn't set.
	 */
	public function get_arg( $arg ) {

		if ( isset( $this->_args[ $arg ] ) ) {
			return $this->_args[ $arg ];
		}
	}

	/**
	 * Set arguments for the query.
	 *
	 * @since 1.6.0
	 *
	 * @param array $args A list of arguments to set and their values.
	 */
	public function set_args( array $args ) {

		$this->_args = array_merge( $this->_args, $args );

		$this->_query_ready = false;
		$this->_cache_query_md5 = null;
	}

	/**
	 * Count the number of results.
	 *
	 * When used with a query that contains a LIMIT clause, this method currently
	 * returns the count of the query ignoring the LIMIT, as would be the case with
	 * any similar query. However, this behaviour is not hardened and should not be
	 * relied upon. Make inquiry before assuming the constancy of this behaviour.
	 *
	 * @since 1.0.0
	 * @since 1.6.0 The $use_cache argument is deprecated.
	 * @since 2.0.0 The $use_cache argument was removed.
	 *
	 * @return int The number of results.
	 */
	public function count() {

		if ( $this->_is_cached_query ) {
			$cache = $this->_cache_get( 'count' );

			if ( false !== $cache ) {
				return $cache;
			}
		}

		$this->_select_type = 'SELECT COUNT';

		$count = (int) $this->_get( 'var' );

		if ( $this->_is_cached_query ) {
			$this->_cache_set( $count, 'count' );
		}

		return $count;
	}

	/**
	 * Get the results for the query.
	 *
	 * @since 1.0.0
	 * @since 1.6.0 The $use_cache parameter was deprecated.
	 * @since 2.0.0 The $use_cache parameter was removed.
	 *
	 * @param string $method    The method to use. Options are 'results', 'row', and
	 *                          'col', and 'var'.
	 *
	 * @return mixed The results of the query, or false on failure.
	 */
	public function get( $method = 'results' ) {

		$methods = array( 'results', 'row', 'col', 'var' );

		if ( ! in_array( $method, $methods ) ) {

			_doing_it_wrong( __METHOD__, esc_html( sprintf( 'WordPoints Debug Error: invalid get method %s, possible values are %s', $method, implode( ', ', $methods ) ) ), '1.0.0' );

			return false;
		}

		if ( $this->_is_cached_query ) {
			$cache = $this->_cache_get( "get_{$method}" );

			if ( false !== $cache ) {
				return $cache;
			}
		}

		$this->_select_type = 'SELECT';

		$result = $this->_get( $method );

		if ( $this->_is_cached_query ) {
			$this->_cache_set( $result, "get_{$method}" );

			if ( 'results' === $method || 'col' === $method ) {
				$this->_cache_set( count( $result ), 'count' );
			}
		}

		return $result;
	}

	/**
	 * Get a page of the results.
	 *
	 * Useful for displaying paginated results, this function lets you get a slice
	 * of the results.
	 *
	 * If your query is already using the 'start' argument, the results are
	 * calculated relative to that. If your query has a 'limit' set, results will not
	 * be returned beyond the limit.
	 *
	 * The cache is used if it has been primed. If not, only the requested results
	 * are pulled from the database, and these are not cached.
	 *
	 * @since 1.6.0
	 *
	 * @param int $page     The page number to get. Pages are numbered starting at 1.
	 * @param int $per_page The number of logs being displayed per page.
	 *
	 * @return stdClass[]|false The logs for this page, or false if $page or $per_page is invalid.
	 */
	public function get_page( $page, $per_page = 25 ) {

		if ( ! wordpoints_posint( $page ) || ! wordpoints_posint( $per_page ) ) {
			return false;
		}

		$start = ( $page - 1 ) * $per_page;

		// First try the main cache.
		if ( $this->_is_cached_query ) {

			$cache = $this->_cache_get( 'get_results' );

			if ( false !== $cache ) {
				return array_slice(
					$cache
					, $start - $this->_args['start']
					, $per_page
				);
			}
		}

		// Stash the args so we can restore them later.
		$args = $this->_args;

		$this->_args['start'] += $start;

		if ( $this->_args['limit'] ) {
			$this->_args['limit'] -= $start;
		}

		if ( ! $this->_args['limit'] || $this->_args['limit'] > $per_page ) {
			$this->_args['limit'] = $per_page;
		}

		// Regenerate the query limit after changing the start and limit args.
		$this->_prepare_limit();

		$this->_select_type = 'SELECT';

		unset( $this->_cache_query_md5 );

		$results = $this->get();

		// Restore the originial arguments.
		$this->_args = $args;

		// Restore the original limit query portion.
		$this->_limit = '';
		$this->_prepare_limit();

		unset( $this->_cache_query_md5 );

		return $results;
	}

	/**
	 * Get the SQL for the query.
	 *
	 * This function can return the SQL for a SELECT or SELECT COUNT query. To
	 * specify which one to return, set the $select_type parameter. If it is not set,
	 * the type will be that of the last query. If no queries have been made yet,
	 * this defaults to SELECT.
	 *
	 * Useful for debugging.
	 *
	 * @since 1.0.0
	 *
	 * @param string $select_type The type of query, SELECT, or SELECT COUNT.
	 *
	 * @return string The SQL for the query.
	 */
	public function get_sql( $select_type = null ) {

		if ( isset( $select_type ) ) {
			$this->_select_type = $select_type;
		}

		return $this->_get_sql();
	}

	/**
	 * Prime the cache.
	 *
	 * Calling this function will cause this query to be cached, and if the results
	 * are already in the object cache they will be returned instead of a new call
	 * to the database being made. Not all queries are cached, only those for which
	 * this method is called. If you want your query to be cached, then you should
	 * call this function immediately after constructing the new query.
	 *
	 * The $key passed is used as the cache key in the 'wordpoints_points_logs_query'
	 * cache group. Multiple queries can use the same key, and you are encouraged to
	 * group queries under a single key that will be invalidated simultaneously.
	 *
	 * Several placeholders are supported within the key to allow for better query
	 * grouping. They are replaced with the values of the query args of the same
	 * name:
	 *  - %points_type%
	 *  - %user_id%
	 *
	 * The default $key is 'default:%points_type%', which corresponds to the named
	 * log query 'default'. This key's cache is invalidated each time a new log is
	 * added to the database.
	 *
	 * Other keys that are used by WordPoints internally correspond to the other
	 * named points log queries. The cache key is specified when the named query is
	 * registered with wordpoints_register_points_logs_query(). Custom named queries
	 * registered this way can be given their own keys as well. Keep in mind though,
	 * that the caches for queries implementing placeholders will be cleared
	 * automatically by wordpoints_clean_points_logs_cache() when a new matching log
	 * is added to the database.
	 *
	 * The $network parameter determines whether the query will be cached in a global
	 * cache group (for the entire network) or per-site. This is a moot point except
	 * on multisite installs.
	 *
	 * @since 1.5.0
	 * @since 1.9.0 No longer runs any database queries to fill the cache if it is empty.
	 * @since 1.9.0 The $methods paramter was deprecated and is no longer used.
	 *
	 * @param string $key        The cache key to use.
	 * @param string $deprecated Deprecated; no longer used.
	 * @param bool   $network    Whether this is a network-wide query.
	 */
	public function prime_cache( $key = 'default:%points_type%', $deprecated = null, $network = false ) {

		if ( ! is_null( $deprecated ) ) {
			_deprecated_argument(
				__METHOD__
				, '1.9.0'
				, 'The $method argument is deprecated and should no longer be used.'
			);
		}

		$this->_is_cached_query = true;

		$this->_cache_key = str_replace(
			array(
				'%points_type%',
				'%user_id%',
			)
			, array(
				$this->_args['points_type'],
				$this->_args['user_id'],
			)
			, $key
		);

		if ( $network ) {
			$this->_cache_group = 'wordpoints_network_points_logs_query';
		} else {
			$this->_cache_group = 'wordpoints_points_logs_query';
		}
	}

	/**
	 * Filter date query valid columns for WP_Date_Query.
	 *
	 * Adds `date` to the list of valid columns for `wordpoints_points_logs.date`.
	 *
	 * @since 1.1.0
	 *
	 * @filter date_query_valid_columns Added by self::_prepare_where().
	 *
	 * @param string[] $valid_columns The names of the valid columns for date queries.
	 *
	 * @return string[] The valid columns.
	 */
	public function date_query_valid_columns_filter( $valid_columns ) {

		$valid_columns[] = 'date';

		return $valid_columns;
	}

	/**
	 * Filter the meta table id column name for the meta query.
	 *
	 * @filter sanitize_key Added and subsequently removed by self::_prepare_where.
	 *
	 * @param string $key The sanitized value for the key.
	 *
	 * @return string The correct meta table ID column, if the key is wordpoints_points_log_.
	 */
	public function meta_query_meta_table_id_filter( $key ) {

		if ( 'wordpoints_points_log__id' === $key ) {
			$key = 'log_id';
		}

		return $key;
	}

	//
	// Private Methods.
	//

	/**
	 * Get the SQL for a query.
	 *
	 * @since 1.0.0
	 *
	 * @return string The SQL for the query.
	 */
	private function _get_sql() {

		global $wpdb;

		$this->_prepare_query();

		$select = ( 'SELECT COUNT' === $this->_select_type ) ? $this->_select_count : $this->_select;

		return $select
			. "\nFROM `{$wpdb->wordpoints_points_logs}`\n"
			. $this->_meta_join
			. $this->_where
			. $this->_order
			. $this->_limit;
	}

	/**
	 * Perform a get query.
	 *
	 * This function is essentially a wrapper for the wpdb::get_* methods.
	 *
	 * @since 1.0.0
	 *
	 * @param string $method The method to use. See get() for possilbe values.
	 *
	 * @return mixed The query results.
	 */
	private function _get( $method ) {

		global $wpdb;

		$get = "get_{$method}";

		return $wpdb->$get( $this->_get_sql() );
	}

	/**
	 * Get the cached query.
	 *
	 * @since 1.6.0
	 *
	 * @param string $type Optional result type to get from the cache. Default is
	 *                     null, or all result types.
	 *
	 * @return mixed Cached value, or false if none.
	 */
	private function _cache_get( $type = null ) {

		$cache = wp_cache_get( $this->_cache_key, $this->_cache_group );

		if ( ! is_array( $cache ) ) {
			return false;
		}

		$this->_calc_cache_query_md5();

		if ( ! isset( $cache[ $this->_cache_query_md5 ] ) ) {
			return false;
		}

		if ( isset( $type ) ) {
			if ( isset( $cache[ $this->_cache_query_md5 ][ $type ] ) ) {
				return $cache[ $this->_cache_query_md5 ][ $type ];
			} else {
				return false;
			}
		}

		return $cache[ $this->_cache_query_md5 ];
	}

	/**
	 * Set the cache value for this query.
	 *
	 * @since 1.6.0
	 *
	 * @param mixed  $value The value to cache.
	 * @param string $type  Optionally specify a results type to cache. Default is
	 *                      null, or all types.
	 */
	private function _cache_set( $value, $type = null ) {

		$cache = wp_cache_get( $this->_cache_key, $this->_cache_group );

		$this->_calc_cache_query_md5();

		if (
			! isset( $cache[ $this->_cache_query_md5 ] )
			|| ! is_array( $cache[ $this->_cache_query_md5 ] )
		) {
			$cache[ $this->_cache_query_md5 ] = array();
		}

		if ( isset( $type ) ) {
			$cache[ $this->_cache_query_md5 ][ $type ] = $value;
		} else {
			$cache[ $this->_cache_query_md5 ] = $value;
		}

		wp_cache_set( $this->_cache_key, $cache, $this->_cache_group );
	}

	/**
	 * Caclulate the MD5 hash of the query.
	 *
	 * @since 1.6.0
	 */
	private function _calc_cache_query_md5() {

		if ( ! isset( $this->_cache_query_md5 ) ) {
			$this->_cache_query_md5 = md5( $this->get_sql() );
		}
	}

	/**
	 * Prepare the query.
	 *
	 * @since 1.0.0
	 */
	private function _prepare_query() {

		if ( ! $this->_query_ready ) {

			$this->_prepare_select();
			$this->_prepare_where();
			$this->_prepare_orderby();
			$this->_prepare_limit();

			$this->_query_ready = true;
		}
	}

	/**
	 * Prepare the select statement.
	 *
	 * @since 1.0.0
	 */
	private function _prepare_select() {

		// Pull all fields by default.
		$fields = $this->_fields;

		if ( 'all' !== $this->_args['fields'] ) {

			$_fields = (array) $this->_args['fields'];
			$diff    = array_diff( $_fields, $this->_fields );
			$_fields = array_intersect( $this->_fields, $_fields );

			if ( ! empty( $diff ) ) {
				_doing_it_wrong( __METHOD__, esc_html( 'WordPoints Debug Error: invalid field(s) "' . implode( '", "', $diff ) . '" given' ), '1.0.0' );
			}

			if ( ! empty( $_fields ) ) {
				$fields = $_fields;
			}
		}

		$fields = '`' . implode( '`, `', $fields ) . '`';

		$this->_select = "SELECT {$fields}";
	}

	/**
	 * Prepare the where condition.
	 *
	 * @since 1.0.0
	 */
	private function _prepare_where() {

		$this->_wheres = array();

		$this->_prepare_posint__in( $this->_args['id__in'], 'id' );
		$this->_prepare_posint__in( $this->_args['id__not_in'], 'id', 'NOT IN' );
		$this->_prepare_user_where();
		$this->_prepare_points_type_where();
		$this->_prepare_log_type_where();
		$this->_prepare_points_where();
		$this->_prepare_text_where();
		$this->_prepare_multisite_where();
		$this->_prepare_date_where();
		$this->_prepare_meta_where();

		if ( ! empty( $this->_wheres ) ) {

			$this->_where = 'WHERE ' . implode( ' AND ', $this->_wheres ) . "\n";
		}

	} // function _prepare_where()

	/**
	 * Prepare the LIMIT clause for the query.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function _prepare_limit() {

		if ( ! isset( $this->_args['limit'] ) ) {
			return;
		}

		foreach ( array( 'limit', 'start' ) as $var ) {

			$_var = $this->_args[ $var ];

			if ( false === wordpoints_int( $this->_args[ $var ] ) ) {

				_doing_it_wrong(
					__METHOD__
					, sprintf(
						"WordPoints Debug Error: '%s' must be a positive integer, %s given"
						, esc_html( $var )
						, esc_html( strval( $_var ) ? $_var : gettype( $_var ) )
					)
					, '1.0.0'
				);

				$this->_args[ $var ] = 0;
			}
		}

		if ( $this->_args['limit'] > 0 && $this->_args['start'] >= 0 ) {
			$this->_limit = "LIMIT {$this->_args['start']}, {$this->_args['limit']}";
		}
	}

	/**
	 * Prepare the ORDER clause for the query.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function _prepare_orderby() {

		global $wpdb;

		$order    = $this->_args['order'];
		$order_by = $this->_args['orderby'];

		if ( 'none' === $order_by ) {
			return;
		}

		if ( ! in_array( $order, array( 'DESC', 'ASC' ) ) ) {

			_doing_it_wrong( __METHOD__, esc_html( "WordPoints Debug Error: invalid 'order' \"{$order}\", possible values are DESC and ASC" ), '1.0.0' );
			$order = 'DESC';
		}

		if ( 'meta_value' === $order_by ) {

			if ( isset( $this->_args['meta_type'] ) ) {

				$meta_type = $this->meta_query->get_cast_for_type( $this->_args['meta_type'] );
				$order_by  = "CAST({$wpdb->wordpoints_points_log_meta}.meta_value AS {$meta_type}";

			} else {

				$order_by = "{$wpdb->wordpoints_points_log_meta}.meta_value";
			}

		} elseif ( ! in_array( $order_by, $this->_fields ) ) {

			_doing_it_wrong( __METHOD__, esc_html( "WordPoints Debug Error: invalid 'orderby' \"{$order_by}\", possible values are " . implode( ', ', $this->_fields ) ), '1.0.0' );
			return;
		}

		$this->_order = "ORDER BY {$order_by} {$order}\n";
	}

	/**
	 * Prepare an IN or NOT IN condition.
	 *
	 * @since 1.0.0
	 *
	 * @uses wordpoints_prepare__in() To prepare the IN condition.
	 *
	 * @param array  $_in    The array of values for the IN condition.
	 * @param string $column The column to search.
	 * @param string $type   The type of IN condition: 'IN' or 'NOT IN'.
	 * @param string $format The format for the values in $_in ('%s', '%d', '%f').
	 */
	private function _prepare__in( $_in, $column, $type = 'IN', $format = '%s' ) {

		if ( ! empty( $_in ) ) {

			$in = wordpoints_prepare__in( $_in, $format );

			if ( $in ) {
				$this->_wheres[] = "{$column} {$type} ({$in})";
			}
		}
	}

	/**
	 * Prepare and IN or NOT IN condition for integer arrays.
	 *
	 * @since 1.0.0
	 *
	 * @uses wordpoints_prepare__in() To prepare the IN condition.
	 *
	 * @param array  $in     The arg that is the array of values for the IN condition.
	 * @param string $column The column to search.
	 * @param string $type   The type of IN condition: 'IN' or 'NOT IN'.
	 */
	private function _prepare_posint__in( $in, $column, $type = 'IN' ) {

		$in = array_filter( array_map( 'wordpoints_posint', $in ) );
		$this->_prepare__in( $in, $column, $type, '%d' );
	}

	/**
	 * Prepare the user query.
	 *
	 * @since 2.0.0
	 */
	private function _prepare_user_where() {

		global $wpdb;

		if ( wordpoints_posint( $this->_args['user_id'] ) ) {

			$this->_wheres[] = $wpdb->prepare( '`user_id` = %d', $this->_args['user_id'] );

		} else {

			$this->_prepare_posint__in( $this->_args['user__in'], 'user_id' );
			$this->_prepare_posint__in( $this->_args['user__not_in'], 'user_id', 'NOT IN' );
		}
	}

	/**
	 * Prepare the points type query.
	 *
	 * @since 2.0.0
	 */
	private function _prepare_points_type_where() {

		global $wpdb;

		if ( wordpoints_is_points_type( $this->_args['points_type'] ) ) {

			$this->_wheres[] = $wpdb->prepare( '`points_type` = %s', $this->_args['points_type'] );

		} else {

			$points_types = array_keys( wordpoints_get_points_types() );

			if ( is_array( $this->_args['points_type__in'] ) ) {

				$this->_prepare__in( array_intersect( $this->_args['points_type__in'], $points_types ), 'points_type' );
			}

			if ( is_array( $this->_args['points_type__not_in'] ) ) {

				$this->_prepare__in( array_intersect( $this->_args['points_type__not_in'], $points_types ), 'points_type', 'NOT IN' );
			}
		}
	}

	/**
	 * Prepare the log type query.
	 *
	 * @since 2.0.0
	 */
	private function _prepare_log_type_where() {

		global $wpdb;

		if ( ! empty( $this->_args['log_type'] ) ) {

			$this->_wheres[] = $wpdb->prepare( '`log_type` = %s', $this->_args['log_type'] );

		} else {

			$this->_prepare__in( $this->_args['log_type__in'], 'log_type' );
			$this->_prepare__in( $this->_args['log_type__not_in'], 'log_type', 'NOT IN' );
		}
	}

	/**
	 * Prepare the points query.
	 *
	 * @since 2.0.0
	 */
	private function _prepare_points_where() {

		global $wpdb;

		if ( ! isset( $this->_args['points'] ) ) {
			return;
		}

		$_points = $this->_args['points'];

		if ( ! wordpoints_int( $this->_args['points'] ) ) {
			_doing_it_wrong( __METHOD__, esc_html( "WordPoints Debug Error: 'points' must be an integer, " . gettype( $_points ) . ' given' ), '1.0.0' );
			return;
		}

		$comparisons = array( '=', '<', '>', '<>', '!=', '<=', '>=' );

		if ( ! in_array( $this->_args['points__compare'], $comparisons ) ) {

			_doing_it_wrong( __METHOD__, esc_html( "WordPoints Debug Error: invalid 'points__compare' {$this->_args['points__compare']}, possible values are " . implode( ', ', $comparisons ) ), '1.0.0' );
			$this->_args['points__compare'] = '=';
		}

		$this->_wheres[] = $wpdb->prepare( "`points` {$this->_args['points__compare']} %d", $this->_args['points'] );
	}

	/**
	 * Prepare the log text query.
	 *
	 * @since 2.0.0
	 */
	private function _prepare_text_where() {

		global $wpdb;

		if ( empty( $this->_args['text'] ) ) {
			return;
		}

		$comparisons = array( '=', '<>', '!=', 'LIKE', 'NOT LIKE' );

		if ( ! in_array( $this->_args['text__compare'], $comparisons ) ) {

			_doing_it_wrong( __METHOD__, esc_html( "WordPoints Debug Error: invalid 'text__compare' {$this->_args['text__compare']}, possible values are " . implode( ', ', $comparisons ) ), '1.6.0' );
			$this->_args['text__compare'] = '=';
		}

		$this->_wheres[] = $wpdb->prepare( "`text` {$this->_args['text__compare']} %s", $this->_args['text'] );
	}

	/**
	 * Prepare the site and blog queries.
	 *
	 * @since 2.0.0
	 */
	private function _prepare_multisite_where() {

		global $wpdb;

		if ( ! is_multisite() ) {
			return;
		}

		if ( wordpoints_posint( $this->_args['site_id'] ) ) {
			$this->_wheres[] = $wpdb->prepare( '`site_id` = %d', $this->_args['site_id'] );
		}

		if ( ! empty( $this->_args['blog__in'] ) || ! empty( $this->_args['blog__not_in'] ) ) {

			$this->_prepare_posint__in( $this->_args['blog__in'], 'blog_id' );
			$this->_prepare_posint__in( $this->_args['blog__not_in'], 'blog_id', 'NOT IN' );

		} elseif ( wordpoints_posint( $this->_args['blog_id'] ) ) {

			$this->_wheres[] = $wpdb->prepare( '`blog_id` = %d', $this->_args['blog_id'] );
		}
	}

	/**
	 * Prepare the date query.
	 *
	 * @since 2.0.0
	 */
	private function _prepare_date_where() {

		if ( empty( $this->_args['date_query'] ) || ! is_array( $this->_args['date_query'] ) ) {
			return;
		}

		add_filter( 'date_query_valid_columns', array( $this, 'date_query_valid_columns_filter' ) );

		$date_query = new WP_Date_Query( $this->_args['date_query'], 'date' );
		$date_query = $date_query->get_sql();

		if ( ! empty( $date_query ) ) {

			$this->_wheres[] = ltrim( $date_query, ' AND' );
		}

		remove_filter( 'date_query_valid_columns', array( $this, 'date_query_valid_columns_filter' ) );
	}

	/**
	 * Prepare the meta query.
	 *
	 * @since 2.0.0
	 */
	private function _prepare_meta_where() {

		global $wpdb;

		$meta_args = array_intersect_key(
			$this->_args
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

		add_filter( 'sanitize_key', array( $this, 'meta_query_meta_table_id_filter' ) );
		$meta_query = $this->meta_query->get_sql( 'wordpoints_points_log_', $wpdb->wordpoints_points_logs, 'id', $this );
		remove_filter( 'sanitize_key', array( $this, 'meta_query_meta_table_id_filter' ) );

		if ( ! empty( $meta_query['where'] ) ) {
			$this->_wheres[] = ltrim( $meta_query['where'], ' AND' );
		}

		$this->_meta_join = $meta_query['join'] . "\n";
	}

} // class WordPoints_Points_Logs_Query

// EOF
