<?php

/**
 * Points Logs.
 *
 * @package WordPoints\Points
 * @since 1.0.0
 */

/**
 * Registers a query.
 *
 * A false return value indicates that the query slug is empty or already taken.
 *
 * Queries cannot be deregistered at present. Use the
 * 'wordpoints_points_logs_query_args' filter instead.
 *
 * To have your query cached, set $data['cache_queries'] to true. You can specify
 * the cache key to use in  $data['cache_key'], though this is optional, and
 * '$slug:%points_type%' will by  used by default. For more information on logs
 * query caching, see WordPoints_Points_Logs_Query::prime_cache().
 *
 * @since 1.0.0
 * @since 1.5.0 The $data parameter was added.
 *
 * @uses WordPoints_Points_Log_Queries::register_query()
 *
 * @param string $slug The query's unique identifier. Should contain only lowercase
 *                     letters, numbers, and the underscore (_).
 * @param array  $args The arguments for the query {@see
 *                     WordPoints_Points_Logs_Query::__construct()}.
 * @param array  $data {
 *        Other data for this query.
 *
 *        @type string       $cache_key     Cache key format.
 *        @type string|array $cache_queries Whether to cache this query.
 *        @type bool         $network_wide  Whether this is a network-wide query.
 * }
 *
 * @return bool Whether the query was registered.
 */
function wordpoints_register_points_logs_query( $slug, array $args, array $data = array() ) {

	return WordPoints_Points_Log_Queries::register_query( $slug, $args, $data );
}

/**
 * Check if a particular points log query is registered.
 *
 * @since 1.0.0
 *
 * @uses WordPoints_Points_Log_Queries::is_query()
 *
 * @param string $slug The slug of the query to check for.
 *
 * @return bool Whether $slug is the slug of a registered query.
 */
function wordpoints_is_points_logs_query( $slug ) {

	return WordPoints_Points_Log_Queries::is_query( $slug );
}

/**
 * Retreive the arguments for a points logs query.
 *
 * @since 1.0.0
 *
 * @uses WordPoints_Points_Log_Queries::get_query_args()
 *
 * @param string $points_type The type of points the query will be for.
 * @param string $query_slug  The slug of the query whose args you wish to get.
 *
 * @return array|false The args for the query, or false on failure.
 */
function wordpoints_get_points_logs_query_args( $points_type, $query_slug = 'default' ) {

	$args = WordPoints_Points_Log_Queries::get_query_args( $query_slug );

	if ( is_null( $args ) || ! wordpoints_is_points_type( $points_type ) ) {
		return false;
	}

	$defaults = array(
		'user_id__not_in' => wordpoints_get_excluded_users( 'points_logs' ),
		'points_type'     => $points_type,
	);

	$args = array_merge( $defaults, $args );

	// The current user needs to be set dynamically, since it can change at times.
	if ( 'current_user' === $query_slug ) {
		$args['user_id'] = get_current_user_id();
	}

	/**
	 * The arguments for a points log query.
	 *
	 * These arguments will be used to create a new WordPoints_Points_Logs_Query.
	 *
	 * @since 1.0.0
	 *
	 * @see WordPoints_Points_Logs_Query::__construct()
	 *
	 * @param array  $args        The arguments for the query.
	 * @param string $query_slug  The slug for the query.
	 * @param string $points_type The points type the query is being made for.
	 */
	return apply_filters( 'wordpoints_points_logs_query_args', $args, $query_slug, $points_type );
}

/**
 * Get a points log query.
 *
 * This function retrieves the {@see WordPoints_Points_Log_Query} instance for a
 * registered query by slug.
 *
 * @since 1.0.0
 *
 * @uses wordpoints_get_points_logs_query_args() To get the $args for the query.
 *
 * @param string $points_type The type of points whose logs to query.
 * @param string $query_slug  The slug of a registered query. The default is
 *        'default', which will return a list of the most recent points logs, with
 *        users excluded according to the general settings.
 *
 * @return WordPoints_Points_Logs_Query|false Logs query instance, or false.
 */
function wordpoints_get_points_logs_query( $points_type, $query_slug = 'default' ) {

	$args = wordpoints_get_points_logs_query_args( $points_type, $query_slug );

	if ( ! $args ) {
		return false;
	}

	$query = new WordPoints_Points_Logs_Query( $args );

	$query_data = WordPoints_Points_Log_Queries::get_query_data( $query_slug );

	if ( $query_data['cache_queries'] ) {

		$query->prime_cache(
			$query_data['cache_key']
			, null
			, $query_data['network_wide']
		);
	}

	return $query;
}

/**
 * Displays the points logs from a query.
 *
 * This function takes an instance of a WordPoints_Points_Logs_Query and displays the
 * results in a table (or optionally some other view).
 *
 * @since 1.0.0
 * @since 1.6.0 The datatable argument was deprecated.
 * @since 1.6.0 The paginate argument was added.
 * @since 1.6.0 The searchable argument was added.
 * @since 2.2.0 The view argument was added. Previously the table HTML was hard-coded
 *              within the function.
 *
 * @uses WordPoints_Points_Logs_Query::get()
 *
 * @see wordpoints_get_points_logs_query()
 *
 * @param WordPoints_Points_Logs_Query $logs_query The query to use to get the logs.
 * @param array                        $args {
 *        Display settings.
 *
 *        @type string $view       The slug of the view to use to display the logs.
 *                                 The default is 'table'.
 *        @type bool   $paginate   Whether to paginate the table. Default is true.
 *        @type bool   $searchable Whether to display a search form. Default is true.
 *        @type bool   $show_users Whether to show the users column of the table.
 *                                 Default is true.
 * }
 *
 * @return void
 */
function wordpoints_show_points_logs( $logs_query, array $args = array() ) {

	if ( ! $logs_query instanceof WordPoints_Points_Logs_Query ) {
		return;
	}

	$defaults = array(
		'view'       => 'table',
		'paginate'   => true,
		'searchable' => true,
		'datatable'  => true,
		'show_users' => true,
	);

	$args = array_merge( $defaults, $args );

	if ( ! $args['datatable'] ) {

		_deprecated_argument(
			__FUNCTION__
			, '1.6.0'
			, '$args["datatable"] is deprecated and should no longer be used. Use $args["paginate"] instead.'
		);

		$args['paginate'] = false;
	}

	$view = wordpoints_component( 'points' )
		->get_sub_app( 'logs' )
		->get_sub_app( 'views' )
		->get( $args['view'], array( $logs_query, $args ) );

	if ( ! $view instanceof WordPoints_Points_Logs_View ) {
		return;
	}

	$view->display();

} // End function wordpoints_points_show_logs().

/**
 * Display points logs by query slug.
 *
 * @since 1.0.0
 *
 * @uses wordpoints_get_points_logs_query()
 * @uses wordpoints_show_points_logs()
 *
 * @param string $points_type The type of whose logs to show.
 * @param string $query_slug  The query to use to display the logs.
 * @param array  $args        Arguments to pass to wordpoints_show_points_logs().
 */
function wordpoints_show_points_logs_query( $points_type, $query_slug = 'default', array $args = array() ) {

	wordpoints_show_points_logs( wordpoints_get_points_logs_query( $points_type, $query_slug ), $args );
}

/**
 * Register default logs queries.
 *
 * @since 1.0.0
 *
 * @WordPress\action wordpoints_register_points_logs_queries
 */
function wordpoints_register_default_points_logs_queries() {

	/**
	 * Return only the logs for the current user.
	 *
	 * @since 1.0.0
	 */
	wordpoints_register_points_logs_query(
		'current_user'
		, array( 'user_id' => get_current_user_id() )
		, array(
			'cache_key'     => 'current_user:%points_type%:%user_id%',
			'cache_queries' => true,
		)
	);

	/**
	 * Return all logs for the whole multisite network.
	 *
	 * @since 1.2.0
	 */
	wordpoints_register_points_logs_query(
		'network'
		, array( 'blog_id' => false )
		, array(
			'network_wide'  => true,
			'cache_queries' => true,
		)
	);
}

/**
 * Admin manage logs render.
 *
 * @since 1.0.0
 *
 * @WordPress\action wordpoints_points_log-profile_edit
 */
function wordpoints_points_logs_profile_edit( $text, $points, $points_type, $user_id, $log_type, $meta ) {

	$user_name = sanitize_user_field( 'display_name', get_userdata( $meta['user_id'] )->display_name, $meta['user_id'], 'display' );

	// translators: 1. User name; 2. Reason given.
	return sprintf( _x( 'Points adjusted by %1$s. Reason: %2$s', 'points log description', 'wordpoints' ), $user_name, esc_html( $meta['reason'] ) );
}

/**
 * Generate the log entry for a comment_disapprove transaction.
 *
 * @since 1.9.0
 *
 * @WordPress\action wordpoints_points_log-comment_disapprove
 */
function wordpoints_points_logs_comment_disapprove( $text, $points, $points_type, $user_id, $log_type, $meta ) {

	switch ( $meta['status'] ) {

		case 'spam':
			$text = _x( 'Comment marked as spam.', 'points log description', 'wordpoints' );
		break;

		case 'trash':
			$text = _x( 'Comment moved to trash.', 'points log description', 'wordpoints' );
		break;

		default:
			$text = _x( 'Comment unapproved.', 'points log description', 'wordpoints' );
	}

	return $text;
}

/**
 * Generate the log entry for a post_delete transaction.
 *
 * @since 1.9.0
 *
 * @WordPress\action wordpoints_points_log-post_delete
 */
function wordpoints_points_logs_post_delete( $text, $points, $points_type, $user_id, $log_type, $meta ) {

	if ( isset( $meta['post_type'] ) ) {

		$post_type = get_post_type_object( $meta['post_type'] );

		if ( ! is_null( $post_type ) ) {

			return sprintf(
				// translators: Singular post type name.
				_x( '%s deleted.', 'points log description', 'wordpoints' )
				, $post_type->labels->singular_name
			);
		}
	}

	return _x( 'Post deleted.', 'points log description', 'wordpoints' );
}

/**
 * Clear the logs caches when new logs are added.
 *
 * Automatically clears the caches for registered points logs queries.
 *
 * @since 1.5.0
 *
 * @WordPress\action wordpoints_points_altered
 *
 * @param int    $user_id     The ID of the user being awarded points.
 * @param int    $points      The number of points. Not used.
 * @param string $points_type The type of points being awarded.
 */
function wordpoints_clean_points_logs_cache( $user_id, $points, $points_type ) {

	wordpoints_flush_points_logs_caches(
		array( 'user_id' => $user_id, 'points_type' => $points_type )
	);
}

/**
 * Flush the points logs caches.
 *
 * It clears the cache for all points types by default, but doesn't clear the caches
 * for specific users. To clear the cache(s) for a user, you must pass the $user_id
 * argument. For this reason, you should always pass the $user_id argument, except in
 * cases where the current_user queries will not be run again anyway (such as when
 * the users are being deleted).
 *
 * @since 2.0.0
 *
 * @param array $args {
 *        Arguments to limit which caches to flush.
 *
 *        @type string|string[] $points_type Only clear cache for these points types.
 *        @type int             $user_id     Only clear the cache for this user.
 * }
 */
function wordpoints_flush_points_logs_caches( $args = array() ) {

	$args = array_merge( array( 'points_type' => false, 'user_id' => 0 ), $args );

	$find = array(
		'%points_type%',
		'%user_id%',
	);

	if ( empty( $args['points_type'] ) ) {
		$points_types = array_keys( wordpoints_get_points_types() );
	} else {
		$points_types = (array) $args['points_type'];
	}

	foreach ( $points_types as $points_type ) {
		foreach ( WordPoints_Points_Log_Queries::get_queries() as $query ) {

			if ( ! empty( $query['cache_key'] ) ) {

				if ( $query['network_wide'] ) {
					$group = 'wordpoints_network_points_logs_query';
				} else {
					$group = 'wordpoints_points_logs_query';
				}

				$replace = array(
					$points_type,
					$args['user_id'],
				);

				wp_cache_delete(
					str_replace( $find, $replace, $query['cache_key'] )
					, $group
				);
			}
		}
	}
}

/**
 * Check if a user can view a points log entry.
 *
 * @since 2.1.0
 * @since 2.2.0 Now uses the points logs viewing restrictions API.
 *
 * @param int    $user_id The ID of the user.
 * @param object $log     The object for the points log entry..
 *
 * @return bool Whether the user can view this points log entry.
 */
function wordpoints_user_can_view_points_log( $user_id, $log ) {

	// We do this just once here for optimization, as otherwise it would run 3 times.
	if ( $log->blog_id && get_current_blog_id() !== (int) $log->blog_id ) {
		$switched = switch_to_blog( $log->blog_id );
	}

	/** @var WordPoints_Points_Logs_Viewing_Restrictions $viewing_restrictions */
	$viewing_restrictions = wordpoints_component( 'points' )
		->get_sub_app( 'logs' )
		->get_sub_app( 'viewing_restrictions' );

	$can_view = $viewing_restrictions->get_restriction( $log )
		->user_can( $user_id );

	if ( $can_view ) {
		$can_view = $viewing_restrictions->apply_legacy_filters( $user_id, $log );
	}

	if ( isset( $switched ) ) {
		restore_current_blog();
	}

	return $can_view;
}

/**
 * Check whether a user can view a points log.
 *
 * @since 2.1.0
 * @deprecated 2.2.0 Use the points logs restrictions API instead.
 *
 * @WordPress\filter wordpoints_user_can_view_points_log
 *
 * @param bool   $can_view Whether the user can view the points log.
 * @param int    $user_id  The ID of the user.
 * @param object $log      The points log's data.
 *
 * @return bool Whether the user can view the points log.
 */
function wordpoints_hooks_user_can_view_points_log( $can_view, $user_id, $log ) {

	_deprecated_function( __FUNCTION__, '2.2.0' );

	if ( ! $can_view ) {
		return $can_view;
	}

	$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Hooks( $log );

	return $restriction->user_can( $user_id );
}

// EOF
