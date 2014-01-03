<?php

/**
 * Points Logs.
 *
 * @package WordPoints\Points
 * @since 1.0.0
 */

/**
 * Static class to handle registration of points logs queries.
 *
 * This class allows predifined queries for the points logs to be registered. The
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
	 * action is perfermed by the init() method, and is only performed if needed.
	 *
	 * @since 1.0.0
	 *
	 * @type bool $initialized
	 */
	private static $initialized = false;

	/**
	 * The registered queries.
	 *
	 * An array of query arg sets indexed by query slug.
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
		self::register_query( 'default', array() );

		self::$initialized = true;
	}

	/**
	 * Registers a query.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The query's unique identifier.
	 * @param array  $args The arguments for the query. {@see
	 *        WordPoints_Points_Logs_Query::__construct()}
	 *
	 * @return bool Whether the query was registered.
	 */
	public static function register_query( $slug, array $args ) {

		if ( empty( $slug ) || isset( self::$queries[ $slug ] ) ) {
			return false;
		}

		self::$queries[ $slug ] = $args;

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
	 * Retrieve the arguments for a registered query.
	 *
	 * @since 1.0.0
	 *
	 * @param string $query_slug The query's slug.
	 *
	 * @return array The query's args.
	 */
	public static function get_query_args( $query_slug ) {

		self::init();

		if ( isset( self::$queries[ $query_slug ] ) ) {
			return self::$queries[ $query_slug ];
		}
	}

} // class WordPoints_Points_Log_Queries

/**
 * Registers a query.
 *
 * A false return value indicates that the query slug is empty or already taken.
 *
 * Queries cannot be deregistered at present. Use the
 * 'wordpoints_points_logs_query_args' filter instead.
 *
 * @since 1.0.0
 *
 * @uses WordPoints_Points_Log_Queries::register_query()
 *
 * @param string $slug The query's unique identifier.
 * @param array  $args The arguments for the query. {@see
 *        WordPoints_Points_Logs_Query::__construct()}
 *
 * @return bool Whether the query was registered.
 */
function wordpoints_register_points_logs_query( $slug, array $args ) {

	return WordPoints_Points_Log_Queries::register_query( $slug, $args );
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
 * @return array|bool The args for the query, or false on failure.
 */
function wordpoints_get_points_logs_query_args( $points_type, $query_slug = 'default' ) {

	$args = WordPoints_Points_Log_Queries::get_query_args( $query_slug );

	if ( is_null( $args ) || ! wordpoints_is_points_type( $points_type ) ) {
		return false;
	}

	$defaults = array(
		'fields'       => array( 'id', 'user_id', 'points', 'points_type', 'log_type', 'text', 'date' ),
		'user__not_in' => wordpoints_get_excluded_users( 'points_logs' ),
		'points_type'  => $points_type,
	);

	$args = array_merge( $defaults, $args );

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
 * @return WordPoints_Points_Logs_Query|bool Logs query instance, or false.
 */
function wordpoints_get_points_logs_query( $points_type, $query_slug = 'default' ) {

	$args = wordpoints_get_points_logs_query_args( $points_type, $query_slug );

	if ( ! $args ) {
		return false;
	}

	return new WordPoints_Points_Logs_Query( $args );
}

/**
 * Display the logs in a table.
 *
 * This function takes an instance of a WordPoints_Points_Logs_Query and displays the
 * results in a table.
 *
 * When $datatables is true, it is important that you call this function before it is
 * too late for scripts to be enqueued, or enqueue the scripts yourself ahead of
 * time.
 *
 * @since 1.0.0
 *
 * @uses WordPoints_Points_Logs_Query::get()
 *
 * @see wordpoints_get_points_logs_query()
 *
 * @param WordPoints_Points_Logs_Query $logs The query to use to get the logs.
 * @param array                        $args Display arguments {
 *        @type bool $datatable  Whether to display the table as a jQuery DataTable.
 *              Default is true.
 *        @type bool $show_users Whether to show the users column of the table.
 *              Default is true. The column will still be output, but will be hidden
 *              with CSS.
 * }
 *
 * @return void
 */
function wordpoints_show_points_logs( $logs, array $args = array() ) {

	if ( ! $logs instanceof WordPoints_Points_Logs_Query ) {
		return;
	}

	$defaults = array(
		'datatable'  => true,
		'show_users' => true,
	);

	$args = array_merge( $defaults, $args );

	$extra_classes = '';

	if ( $args['datatable'] ) {

		$extra_classes .= ' datatables';

		wordpoints_enqueue_datatables( '.wordpoints-points-logs.datatables' );
	}

	if ( ! $args['show_users'] ) {

		$extra_classes .= ' hide-user-column';

		?>

		<style type="text/css">
		.wordpoints-points-logs.hide-user-column th:first-child,
		.wordpoints-points-logs.hide-user-column tr td:first-child {
			display: none;
		}
		</style>

		<?php
	}

	$columns = array(
		'user'        => _x( 'User', 'points logs table heading', 'wordpoints' ),
		'points'      => _x( 'Points', 'points logs table heading', 'wordpoints' ),
		'description' => _x( 'Description', 'points logs table heading', 'wordpoints' ),
		'time'        => _x( 'Time', 'points logs table heading', 'wordpoints' ),
	);

	?>

	<table class="wordpoints-points-logs widefat<?php echo esc_attr( $extra_classes ); ?>">
		<thead><tr><th scope="col"><?php echo $columns['user']; ?></th><th scope="col"><?php echo $columns['points']; ?></th><th scope="col"><?php echo $columns['description']; ?></th><th scope="col"><?php echo $columns['time']; ?></th></tr></thead>
		<tfoot><tr><th scope="col"><?php echo $columns['user']; ?></th><th scope="col"><?php echo $columns['points']; ?></th><th scope="col"><?php echo $columns['description']; ?></th><th scope="col"><?php echo $columns['time']; ?></th></tr></tfoot>
		<tbody>

			<?php

			$current_time = current_time( 'timestamp', true );

			foreach ( $logs->get() as $log ) {

				$user = get_userdata( $log->user_id );

				?>

				<tr class="wordpoints-log-id-<?php echo $log->id; ?>">
					<td><?php echo sanitize_user_field( 'display_name', $user->display_name, $log->user_id, 'display' ); ?></td>
					<td><?php echo wordpoints_format_points( $log->points, $log->points_type, 'logs' ); ?></td>
					<td><?php echo $log->text; ?></td>
					<td title="<?php echo $log->date; ?> UTC"><?php echo human_time_diff( strtotime( $log->date ), $current_time ); ?></td>
				</tr>

				<?php
			}

			?>

		</tbody>
	</table>

	<?php

} // function wordpoints_points_show_logs()

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
 * @action wordpoints_register_points_logs_queries
 */
function wordpoints_register_default_points_logs_queries() {

	/**
	 * Return only the logs for the current user.
	 *
	 * @since 1.0.0
	 */
	wordpoints_register_points_logs_query( 'current_user', array( 'user_id' => get_current_user_id() ) );

	/**
	 * Return all logs for the whole multisite network.
	 *
	 * @since 1.2.0
	 */
	wordpoints_register_points_logs_query( 'network', array( 'blog_id' => false ) );
}
add_action( 'wordpoints_register_points_logs_queries', 'wordpoints_register_default_points_logs_queries' );

/**
 * Admin manage logs render.
 *
 * @since 1.0.0
 *
 * @action wordpoints_render_log-profile_edit
 */
function wordpoints_points_logs_profile_edit( $text, $points, $points_type, $user_id, $log_type, $meta ) {

	$user_name = sanitize_user_field( 'display_name', get_userdata( $meta['user_id'] )->display_name, $meta['user_id'], 'display' );

	return sprintf( _x( 'Points adjusted by %s. Reason: %s', 'points log description', 'wordpoints' ), $user_name, esc_html( $meta['reason'] ) );
}
add_action( 'wordpoints_points_log-profile_edit', 'wordpoints_points_logs_profile_edit', 10, 6 );

// end of file /components/points/includes/logs.php
