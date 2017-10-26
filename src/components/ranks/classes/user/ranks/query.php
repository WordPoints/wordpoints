<?php

/**
 * User ranks query class.
 *
 * @package WordPoints\Ranks
 * @since   2.4.0
 */

/**
 * Queries the user ranks database table.
 *
 * Note that not all data is stored in the table, and while this query class works
 * around that in some cases, you may need to take it into account. Specifically,
 * the base rank in a rank group is not always assigned to users, it is just implied.
 * When you use the rank_id query arg to query for users of a base rank, this query
 * class automatically detects that and fills in the data for any users who do not
 * have their rank set, just before the query is run. However, if you are running a
 * query that may involve a base rank but does not specify it in the rank_id arg,
 * then you should probably call the `maybe_fill_in_base_rank_for_users()` method
 * with the ID of the base rank in question. This is generally only necessary if only
 * the base rank in the group has been created; when other ranks are added to the
 * group a query is run which triggers the filling of the base rank data for that
 * group.
 *
 * @since 2.4.0
 */
class WordPoints_User_Ranks_Query extends WordPoints_DB_Query {

	/**
	 * @since 2.4.0
	 */
	protected $columns = array(
		'id'         => array( 'format' => '%d', 'unsigned' => true ),
		'rank_id'    => array( 'format' => '%d', 'unsigned' => true ),
		'user_id'    => array( 'format' => '%d', 'unsigned' => true ),
		'rank_group' => array( 'format' => '%s' ),
		'blog_id'    => array( 'format' => '%d', 'unsigned' => true ),
		'site_id'    => array( 'format' => '%d', 'unsigned' => true ),
	);

	//
	// Public Methods.
	//

	/**
	 * Construct the class.
	 *
	 * All of the arguments are expected *not* to be SQL escaped.
	 *
	 * @since 2.4.0
	 *
	 * @see WordPoints_DB_Query::$columns For a fuller explanation of the {column},
	 *                                    {column}__in, {column}__not_in, and
	 *                                    {column}__compare args.
	 *
	 * @param array $args {
	 *        The arguments for the query.
	 *
	 *        @type string|array $fields              Fields to include in the results. Default is all fields.
	 *        @type int          $id                  The ID of the user rank row to retrieve.
	 *        @type string       $id__compare         The comparison operator to use with the above value.
	 *        @type int[]        $id__in              A list of IDs to query for.
	 *        @type int[]        $id__not_in          A list of IDs to exclude.
	 *        @type int          $rank_id             The ID of the rank to query for.
	 *        @type string       $rank_id__compare    The comparison operator to use with the above value.
	 *        @type int[]        $rank_id__in         A list of rank IDs to query for.
	 *        @type int[]        $user_id__not_in     A list of rank IDs to exclude.
	 *        @type int          $user_id             The ID of the user to query for.
	 *        @type string       $user_id__compare    The comparison operator to use with the above value.
	 *        @type int[]        $user_id__in         A list of user IDs to query for.
	 *        @type int[]        $rank_id__not_in     A list of user IDs to exclude.
	 *        @type string       $rank_group          The slug of the rank group to query for.
	 *        @type string       $rank_group__compare The comparison operator to use with the above value.
	 *        @type string[]     $rank_group__in      A list of rank groups to query for.
	 *        @type string[]     $rank_group__not_in  A list of rank groups to exclude.
	 *        @type int          $blog_id             Limit results to those from this blog within the network (multisite). Default is $wpdb->blogid (current blog).
	 *        @type string       $blog_id__compare    Comparison operator for the above value.
	 *        @type int[]        $blog_id__in         Limit results to these blogs.
	 *        @type int[]        $blog_id__not_in     Exclude these blogs.
	 *        @type int          $site_id             Limit results to this network. Default is $wpdb->siteid (current network). There isn't currently
	 *                                                a use for this one, but its possible in future that WordPress will allow multi-network installs.
	 *        @type string       $site_id__compare    Comparison operator for the above value.
	 *        @type int[]        $site_id__in         Limit results to these sites.
	 *        @type int[]        $site_id__not_in     Exclude these sites.
	 *        @type int          $limit               The maximum number of results to return. Default is null
	 *                                                (no limit).
	 *        @type int          $start               The start for the LIMIT clause. Default: 0.
	 *        @type string       $order_by            The field to use to order the results.
	 *                                                Supports 'meta_value'.
	 *        @type string       $order               The order for the query: ASC or DESC (default).
	 * }
	 */
	public function __construct( $args = array() ) {

		global $wpdb;

		$this->table_name = $wpdb->wordpoints_user_ranks;

		$this->defaults['blog_id'] = $wpdb->blogid;
		$this->defaults['site_id'] = $wpdb->siteid;

		parent::__construct( $args );
	}

	/**
	 * @since 2.4.0
	 */
	public function count() {

		if ( ! $this->maybe_fill_in_base_rank_for_users() ) {
			return false;
		}

		return parent::count();
	}

	/**
	 * @since 2.4.0
	 */
	public function get( $method = 'results' ) {

		if ( ! $this->maybe_fill_in_base_rank_for_users() ) {
			return false;
		}

		return parent::get( $method );
	}

	/**
	 * Fills in the base rank for a user, if needed.
	 *
	 * @since 2.4.0
	 *
	 * @param int $rank_id The ID of the rank. If null, the rank_id query arg will be
	 *                     used, if set.
	 *
	 * @return bool True, or false if filling in the rank failed.
	 */
	public function maybe_fill_in_base_rank_for_users( $rank_id = null ) {

		if ( ! isset( $rank_id ) ) {
			if ( ! isset( $this->args['rank_id'] ) ) {
				return true;
			} else {
				$rank_id = $this->args['rank_id'];
			}
		}

		$rank = wordpoints_get_rank( $rank_id );

		if ( ! $rank || 'base' !== $rank->type ) {
			return true;
		}

		$filled = wordpoints_get_array_option( 'wordpoints_filled_base_ranks' );

		if ( isset( $filled[ $rank->ID ] ) ) {
			return true;
		}

		if ( ! $this->fill_in_base_rank_for_users( $rank ) ) {
			return false;
		}

		$filled[ $rank->ID ] = true;

		update_option( 'wordpoints_filled_base_ranks', $filled );

		return true;
	}

	//
	// Protected Methods.
	//

	/**
	 * Fills in the user ranks table with a particular rank.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Rank $rank The rank to give un-ranked users.
	 *
	 * @return bool Whether the user ranks were filled successfully.
	 */
	protected function fill_in_base_rank_for_users( WordPoints_Rank $rank ) {

		global $wpdb;

		$query = new WP_User_Query(
			array( 'fields' => 'ID', 'count_total' => false )
		);

		$result = $wpdb->query( // WPCS: unprepared SQL OK.
			$wpdb->prepare( // WPCS: unprepared SQL OK.
				"
					INSERT INTO `{$wpdb->wordpoints_user_ranks}`
						(`user_id`,`rank_id`,`rank_group`,`blog_id`,`site_id`)
					SELECT `{$wpdb->users}`.`ID`, %d, %s, %d, %d
					{$query->query_from}
					{$query->query_where}
					AND `{$wpdb->users}`.`ID` NOT IN (
						SELECT user_ranks.`user_id`
						FROM `{$wpdb->wordpoints_user_ranks}` AS user_ranks
						WHERE user_ranks.`rank_group` = %s
					)
				"
				, $rank->ID
				, $rank->rank_group
				, $wpdb->blogid
				, $wpdb->siteid
				, $rank->rank_group
			)
		); // WPCS: cache OK.

		if ( false === $result ) {
			return false;
		}

		return true;
	}
}

// EOF
