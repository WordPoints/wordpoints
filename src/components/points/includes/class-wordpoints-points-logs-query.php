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
 * those available in {@link https://codex.wordpress.org/Class_Reference/WP_Query
 * WP_Query}.
 *
 * @since 1.0.0
 * @since 2.3.0 Now extends WordPoints_DB_Query.
 */
class WordPoints_Points_Logs_Query extends WordPoints_DB_Query {

	//
	// Protected Vars.
	//

	/**
	 * @since 2.3.0
	 */
	protected $columns = array(
		'id'          => array( 'format' => '%d', 'unsigned' => true ),
		'user_id'     => array( 'format' => '%d', 'unsigned' => true ),
		'log_type'    => array( 'format' => '%s' ),
		'points'      => array( 'format' => '%d' ),
		'points_type' => array( 'format' => '%s' ),
		'text'        => array( 'format' => '%s' ),
		'blog_id'     => array( 'format' => '%d', 'unsigned' => true ),
		'site_id'     => array( 'format' => '%d', 'unsigned' => true ),
		'date'        => array( 'format' => '%s', 'is_date' => true ),
	);

	/**
	 * @since 2.3.0
	 */
	protected $meta_type = 'wordpoints_points_log_';

	/**
	 * @since 2.3.0
	 */
	protected $deprecated_args = array(
		'start'        => array( 'replacement' => 'offset', 'version' => '2.4.0', 'class' => __CLASS__ ),
		'orderby'      => array( 'replacement' => 'order_by', 'version' => '2.3.0', 'class' => __CLASS__ ),
		'user__in'     => array( 'replacement' => 'user_id__in', 'version' => '2.3.0', 'class' => __CLASS__ ),
		'user__not_in' => array( 'replacement' => 'user_id__not_in', 'version' => '2.3.0', 'class' => __CLASS__ ),
		'blog__in'     => array( 'replacement' => 'blog_id__in', 'version' => '2.3.0', 'class' => __CLASS__ ),
		'blog__not_in' => array( 'replacement' => 'blog_id__not_in', 'version' => '2.3.0', 'class' => __CLASS__ ),
	);

	//
	// Private Vars.
	//

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
	 * The hash of the query for looking it up the cache.
	 *
	 * @since 1.6.0
	 *
	 * @type string $_cache_query_hash
	 */
	private $_cache_query_hash;

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
	 * @since 2.3.0 - The 'orderby' arg was deprecated in favor of 'order_by'.
	 *              - The 'user__in' and 'user__not_in' args were deprecated in favor
	 *                of 'user_id__in' and 'user_id__not_in', respectively.
	 *              - The 'blog__in' and 'blog__not_in' args were deprecated in favor
	 *                of 'blog_id__in' and 'blog_id__not_in', respectively.
	 *              - The *__in and *__not_in args can no longer be used together
	 *                (e.g., 'user_id__in' can't be used with 'user_id__not_in').
	 *              - The 'all' value for the 'fields' arg has been deprecated in
	 *                favor of just passing an empty value.
	 *              - The 'none' value of the 'orderby' arg has been deprecated in
	 *                favor of just passing an empty value.
	 *              - The 'id', 'id__compare', 'user_id__compare', 'points__in',
	 *                'points__not_in', 'points_type__compare', 'log_type__compare',
	 *                'text__in', 'text__not_in', 'blog_id__compare',
	 *                'site_id__compare', 'site_id__in', and 'site_id__not_in' args
	 *                were added.
	 * @since 2.4.0 The 'start' arg was deprecated in favor of 'offset'.
	 *
	 * @see WP_Date_Query for the proper arguments for $args['date_query'].
	 * @see WP_Meta_Query for the proper arguments for 'meta_query', 'meta_key', 'meta_value', 'meta_compare', and 'meta_type'.
	 *
	 * @param array $args {
	 *        The arguments for the query.
	 *
	 *        @type string|array $fields              Fields to include in the results. Defaults to all fields.
	 *        @type int          $limit               The maximum number of results to return. Default is no limit.
	 *        @type int          $offset              The offset for the LIMIT clause. Default: 0.
	 *        @type string       $order_by            The field to use to order the results. Default: 'date'.
	 *        @type string       $order               The order for the query: ASC or DESC (default).
	 *        @type int          $id                  The ID of the log to retrieve.
	 *        @type string       $id__compare         The comparison operator to use with the above value.
	 *        @type int[]        $id__in              Limit results to these log IDs.
	 *        @type int[]        $id__not_in          Exclude all logs with these IDs.
	 *        @type int          $user_id             Limit results to logs for this user.
	 *        @type string       $user_id__compare    The comparison operator to use with the above value.
	 *        @type int[]        $user_id__in         Limit results to logs for these users.
	 *        @type int[]        $user_id__not_in     Exclude all logs for these users from the results.
	 *        @type string       $points_type         Include only results for this type.
	 *        @type string       $points_type__compare The comparison operator to use with the above value.
	 *        @type string[]     $points_type__in     Limit results to these types.
	 *        @type string[]     $points_type__not_in Exclude logs for these points types from the results.
	 *        @type string       $log_type            Return only logs of this type.
	 *        @type string       $log_type__compare   The comparison operator to use with the above value.
	 *        @type string[]     $log_type__in        Return only logs of these types.
	 *        @type string[]     $log_type__not_in    Exclude these log types from the results.
	 *        @type int          $points              Limit results to transactions of this amount. More uses when used with $points__compare.
	 *        @type string       $points__compare     Comparison operator for logs comparison with $points. May be any of these: '=', '<', '>', '<>', '!=', '<=', '>='. Default is '='.
	 *        @type int[]        $points__in          Return only logs for these points amounts.
	 *        @type int[]        $points__not_in      Exclude logs for these points amounts from the results.
	 *        @type string       $text                Log text must match this. Method of comparison is determined by $text__compare. Wildcards (% and _)
	 *                                                must be escaped to be treated literally when doing LIKE comparisons.
	 *        @type string       $text__compare       Comparison operator for $text. May be any of these:  '=', '<>', '!=', 'LIKE', 'NOT LIKE'. Default is 'LIKE'.
	 *        @type string[]     $text__in            Return only logs with these texts.
	 *        @type string[]     $text__not_in        Exclude logs with these texts from the results.
	 *        @type int          $blog_id             Limit results to those from this blog within the network (multisite). Default is $wpdb->blogid (current blog).
	 *        @type string       $blog_id__compare    Comparison operator for $text. May be any of these:  '=', '<>', '!=', 'LIKE', 'NOT LIKE'. Default is 'LIKE'.
	 *        @type int[]        $blog_id__in         Limit results to these blogs.
	 *        @type int[]        $blog_id__not_in     Exclude these blogs.
	 *        @type int          $site_id             Limit results to this network. Default is $wpdb->siteid (current network). There isn't currently
	 *                                                a use for this one, but its possible in future that WordPress will allow multi-network installs.
	 *        @type string       $site_id__compare    Comparison operator for $text. May be any of these:  '=', '<>', '!=', 'LIKE', 'NOT LIKE'. Default is 'LIKE'.
	 *        @type int[]        $site_id__in         Limit results to these sites.
	 *        @type int[]        $site_id__not_in     Exclude these sites.
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

		$this->table_name = $wpdb->wordpoints_points_logs;

		$this->columns['points_type']['values'] = array_keys(
			wordpoints_get_points_types()
		);

		$this->defaults['order_by']      = 'date';
		$this->defaults['text__compare'] = 'LIKE';

		// Back-compat for pre-2.3.0, in case an object or string is passed.
		$args = wp_parse_args( $args );
		$args = $this->convert_deprecated_arg_values( $args );

		parent::__construct( $args );

		if ( is_multisite() ) {
			foreach ( array( 'blog', 'site' ) as $arg ) {

				if (
					// Support passing these as null to override the defaults.
					! array_key_exists( "{$arg}_id", $this->args )
					&& ! isset( $this->args[ "{$arg}_id__in" ] )
					&& ! isset( $this->args[ "{$arg}_id__not_in" ] )
				) {
					$this->args[ "{$arg}_id" ] = $wpdb->{"{$arg}id"};
				}
			}
		}
	}

	/**
	 * Converts deprecated arg values to their new equivalents.
	 *
	 * @since 2.3.0
	 *
	 * @param array $args The raw args.
	 *
	 * @return array $args The args, with any values converted as needed.
	 */
	protected function convert_deprecated_arg_values( $args ) {

		// Back-compat for pre-2.3.0, when the fields arg supported 'all'.
		if ( isset( $args['fields'] ) && 'all' === $args['fields'] ) {

			_deprecated_argument(
				__METHOD__
				, '2.3.0'
				, esc_html( "Passing 'fields' => 'all' is deprecated, just omit the 'fields' arg instead, since all fields returned by default." )
			);

			unset( $args['fields'] );
		}

		// Back-compat for pre-2.3.0, when the orderby arg supported 'none'.
		if ( isset( $args['orderby'] ) && 'none' === $args['orderby'] ) {

			_deprecated_argument(
				__METHOD__
				, '2.3.0'
				, esc_html( "Passing 'orderby' => 'none' is deprecated, pass 'order_by' => null instead." )
			);

			$args['order_by'] = null;
			unset( $args['orderby'] );

			return $args;
		}

		return $args;
	}

	/**
	 * @since 1.6.0
	 * @since 2.3.0 Now returns $this.
	 */
	public function set_args( array $args ) {

		$this->_cache_query_hash = null;

		return parent::set_args( $this->convert_deprecated_arg_values( $args ) );
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
			$cache = $this->cache_get( 'count' );

			if ( false !== $cache ) {
				return $cache;
			}
		}

		$count = parent::count();

		if ( $this->_is_cached_query ) {
			$this->cache_set( $count, 'count' );
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

		if ( $this->_is_cached_query ) {
			$cache = $this->cache_get( "get_{$method}" );

			if ( false !== $cache ) {
				return $cache;
			}
		}

		$result = parent::get( $method );

		if ( $this->_is_cached_query ) {
			$this->cache_set( $result, "get_{$method}" );

			if ( 'results' === $method || 'col' === $method ) {
				$this->cache_set( count( $result ), 'count' );
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
	 * If your query is already using the 'offset' argument, the results are
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
	 * @return object[]|false The logs for this page, or false if $page or $per_page is invalid.
	 */
	public function get_page( $page, $per_page = 25 ) {

		if ( ! wordpoints_posint( $page ) || ! wordpoints_posint( $per_page ) ) {
			return false;
		}

		$offset = ( $page - 1 ) * $per_page;

		// First try the main cache.
		if ( $this->_is_cached_query ) {

			$cache = $this->cache_get( 'get_results' );

			if ( false !== $cache ) {
				return array_slice(
					$cache
					, $offset - $this->args['offset']
					, $per_page
				);
			}
		}

		// Stash the args so we can restore them later.
		$args = $this->args;

		$this->args['offset'] += $offset;

		if ( ! empty( $this->args['limit'] ) ) {
			$this->args['limit'] -= $offset;
		}

		if ( empty( $this->args['limit'] ) || $this->args['limit'] > $per_page ) {
			$this->args['limit'] = $per_page;
		}

		// Regenerate the query limit after changing the offset and limit args.
		$this->prepare_limit();

		unset( $this->_cache_query_hash );

		$results = $this->get();

		// Restore the original arguments.
		$this->args = $args;

		// Restore the original limit query portion.
		$this->limit = '';
		$this->prepare_limit();

		unset( $this->_cache_query_hash );

		return $results;
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
				isset( $this->args['points_type'] ) ? $this->args['points_type'] : '',
				isset( $this->args['user_id'] ) ? $this->args['user_id'] : 0,
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
	// Protected Methods.
	//

	/**
	 * @since 2.3.0
	 */
	protected function prepare_meta_where() {

		add_filter( 'sanitize_key', array( $this, 'meta_query_meta_table_id_filter' ) );
		parent::prepare_meta_where();
		remove_filter( 'sanitize_key', array( $this, 'meta_query_meta_table_id_filter' ) );
	}

	//
	// Private Methods.
	//

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
	private function cache_get( $type = null ) {

		$cache = wp_cache_get( $this->_cache_key, $this->_cache_group );

		if ( ! is_array( $cache ) ) {
			return false;
		}

		$this->calc_cache_query_hash();

		if ( ! isset( $cache[ $this->_cache_query_hash ] ) ) {
			return false;
		}

		if ( isset( $type ) ) {
			if ( isset( $cache[ $this->_cache_query_hash ][ $type ] ) ) {
				return $cache[ $this->_cache_query_hash ][ $type ];
			} else {
				return false;
			}
		}

		return $cache[ $this->_cache_query_hash ];
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
	private function cache_set( $value, $type = null ) {

		$cache = wp_cache_get( $this->_cache_key, $this->_cache_group );

		$this->calc_cache_query_hash();

		if (
			! isset( $cache[ $this->_cache_query_hash ] )
			|| ! is_array( $cache[ $this->_cache_query_hash ] )
		) {
			$cache[ $this->_cache_query_hash ] = array();
		}

		if ( isset( $type ) ) {
			$cache[ $this->_cache_query_hash ][ $type ] = $value;
		} else {
			$cache[ $this->_cache_query_hash ] = $value;
		}

		wp_cache_set( $this->_cache_key, $cache, $this->_cache_group );
	}

	/**
	 * Calculate the hash of the query.
	 *
	 * @since 1.6.0
	 */
	private function calc_cache_query_hash() {

		if ( ! isset( $this->_cache_query_hash ) ) {
			$this->_cache_query_hash = wordpoints_hash( $this->get_sql() );
		}
	}

} // class WordPoints_Points_Logs_Query

// EOF
