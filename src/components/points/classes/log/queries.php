<?php

/**
 * Points log queries class.
 *
 * @package WordPoints\Points
 * @since   2.3.0
 */

/**
 * Static class to handle registration of points logs queries.
 *
 * This class allows predefined queries for the points logs to be registered. The
 * query is registered with its slug, and the array of arguments that will be
 * passed to {@see WordPoints_Points_Logs_Query}.
 *
 * The methods in this class are not intended to be called directly, use the
 * wrapper functions instead.
 *
 * @since 1.0.0
 */
final class WordPoints_Points_Log_Queries {

	/**
	 * Whether the class has been initialized.
	 *
	 * This is used to check whether the queries have been registered yet. This
	 * action is performed by the init() method, and is only performed if needed.
	 *
	 * @since 1.0.0
	 *
	 * @type bool $initialized
	 */
	private static $initialized = false;

	/**
	 * The registered queries.
	 *
	 * An array of query data indexed by query slug. The data includes the query args
	 * (index 'args') as well as other data (see
	 * wordpoints_register_points_logs_query()'s $data parameter).
	 *
	 * @since 1.0.0
	 *
	 * @type array[]
	 */
	private static $queries;

	/**
	 * Initialize the queries.
	 *
	 * Calls the action to register all queries.
	 *
	 * @since 1.0.0
	 */
	private static function init() {

		if ( self::$initialized ) {
			return;
		}

		/**
		 * Register points logs queries.
		 *
		 * Functions that call wordpoints_register_points_logs_query() should be
		 * hooked to this action.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wordpoints_register_points_logs_queries' );

		// Make sure that the default query is registered.
		self::register_query( 'default', array(), array( 'cache_queries' => true ) );

		self::$initialized = true;
	}

	/**
	 * Registers a query.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 The $data parameter was added.
	 *
	 * @param string $slug The query's unique identifier.
	 * @param array  $args The arguments for the query {@see
	 *                     WordPoints_Points_Logs_Query::__construct()}.
	 * @param array  $data Other data for this query {@see
	 *                     wordpoints_register_points_logs_query()}.
	 *
	 * @return bool Whether the query was registered.
	 */
	public static function register_query( $slug, array $args, array $data = array() ) {

		if ( empty( $slug ) || isset( self::$queries[ $slug ] ) ) {
			return false;
		}

		$defaults = array(
			'cache_key'     => "{$slug}:%points_type%",
			'cache_queries' => false,
			'network_wide'  => false,
		);

		self::$queries[ $slug ]         = array_merge( $defaults, $data );
		self::$queries[ $slug ]['args'] = $args;

		return true;
	}

	/**
	 * Check if a particular query is registered.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The slug of the query to check for.
	 *
	 * @return bool Whether $slug is the slug of a registered query.
	 */
	public static function is_query( $slug ) {

		self::init();

		return isset( self::$queries[ $slug ] );
	}

	/**
	 * Retrieve the list of registered queries and their data.
	 *
	 * @since 1.5.0
	 *
	 * @return array[] The query args and other data. See self::register_query().
	 */
	public static function get_queries() {

		self::init();

		return self::$queries;
	}

	/**
	 * Retrieve the arguments for a registered query.
	 *
	 * @since 1.0.0
	 *
	 * @param string $query_slug The query's slug.
	 *
	 * @return array The query's args.
	 */
	public static function get_query_args( $query_slug ) {

		return self::get_query_data( $query_slug, 'args' );
	}

	/**
	 * Retrieve data for a query.
	 *
	 * @sing 1.5.0
	 *
	 * @param string $query_slug The query's slug.
	 * @param string $data       The data to retrieve. Default is null, to retrieve
	 *                           all data.
	 *
	 * @return mixed The data, or null if not found.
	 */
	public static function get_query_data( $query_slug, $data = null ) {

		self::init();

		if ( isset( self::$queries[ $query_slug ] ) ) {

			if ( empty( $data ) ) {
				return self::$queries[ $query_slug ];
			} elseif ( isset( self::$queries[ $query_slug ][ $data ] ) ) {
				return self::$queries[ $query_slug ][ $data ];
			}
		}

		return null;
	}

} // class WordPoints_Points_Log_Queries

// EOF
