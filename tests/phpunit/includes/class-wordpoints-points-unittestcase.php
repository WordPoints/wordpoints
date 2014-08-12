<?php

/**
 * Test case parent for the points tests.
 *
 * @package WordPoints\Tests\Points
 * @since 1.0.0
 */

/**
 * Points unit test case.
 *
 * This test case creates the 'Points' points type on set up so that doesn't have to
 * be repeated in each of the tests.
 *
 * @since 1.0.0
 */
class WordPoints_Points_UnitTestCase extends WP_UnitTestCase {

	/**
	 * The default points data set up for each test.
	 *
	 * @since 1.0.0
	 *
	 * @type array $points_data
	 */
	protected $points_data;

	/**
	 * The list of filters currently being watched.
	 *
	 * @since 1.5.0
	 *
	 * @see WordPoints_Points_UnitTestCase::listen_for_filter()
	 *
	 * @type array $watched_filters
	 */
	protected $watched_filters = array();

	/**
	 * Set up the points type.
	 *
	 * @since 1.0.0
	 */
	public function setUp() {

		parent::setUp();

		$this->factory->wordpoints_points_log =
			new WordPoints_UnitTest_Factory_For_Points_Log( $this->factory );

		WordPoints_Points_Hooks::set_network_mode( false );

		$this->create_points_type();

		add_filter( 'query', array( $this, 'do_not_alter_tables' ) );
	}

	/**
	 * Clean up after each test.
	 *
	 * @since 1.3.0
	 */
	public function tearDown() {

		WordPoints_Points_Hooks::set_network_mode( false );

		foreach ( $this->watched_filters as $filter => $data ) {

			remove_filter( $filter, array( $this, 'filter_listner' ) );
		}

		remove_filter( 'query', array( $this, 'do_not_alter_tables' ) );

		parent::tearDown();
	}

	/**
	 * Create the points type used in the tests.
	 *
	 * @since 1.5.1
	 */
	protected function create_points_type() {

		$this->points_data = array(
			'name'   => 'Points',
			'prefix' => '$',
			'suffix' => 'pts.',
		);

		wordpoints_add_network_option(
			'wordpoints_points_types'
			, array( 'points' => $this->points_data )
		);
	}

	/**
	 * Alter temporary tables.
	 *
	 * @since 1.5.1
	 *
	 * @filter query Added by self::setUp().
	 */
	public function do_not_alter_tables( $query ) {

		if ( 'ALTER TABLE' === substr( trim( $query ), 0, 11 ) ) {
			$query = 'SELECT "Do not alter tables during tests!"';
		}

		return $query;
	}

	/**
	 * Set the version of the points component.
	 *
	 * @since 1.4.0
	 *
	 * @param string $version The version to set. Defaults to 1.0.0.
	 */
	protected function set_points_db_version( $version = '1.0.0' ) {

		$wordpoints_data = wordpoints_get_network_option( 'wordpoints_data' );
		$wordpoints_data['components']['points']['version'] = $version;
		wordpoints_update_network_option( 'wordpoints_data', $wordpoints_data );
	}

	/**
	 * Get the version of the points component.
	 *
	 * @since 1.4.0
	 *
	 * @return string The version of the points component.
	 */
	protected function get_points_db_version() {

		$wordpoints_data = wordpoints_get_network_option( 'wordpoints_data' );

		return ( isset( $wordpoints_data['components']['points']['version'] ) )
			? $wordpoints_data['components']['points']['version']
			: '';
	}

	/**
	 * Listen for a WordPress action or filter.
	 *
	 * To limit the counting based on the filtered value, you can pass a
	 * $count_callback, which will be called with the value being filtered. The
	 * callback should return a boolean value, which will determine whether the
	 * filter call is counted.
	 *
	 * @since 1.5.0
	 *
	 * @param string   $filter         The filter to listen for.
	 * @param callable $count_callback Function to call to test if this filter call
	 *                                 should be counted.
	 */
	protected function listen_for_filter( $filter, $count_callback = null ) {

		$this->watched_filters[ $filter ]['count'] = 0;

		if ( isset( $count_callback ) ) {
			$this->watched_filters[ $filter ]['callback'] = $count_callback;
		}

		add_filter( $filter, array( $this, 'filter_listner' ) );
	}

	/**
	 * Increments the call count for a filter when it gets called.
	 *
	 * The count won't be incremented if there is a count callback for this filter,
	 * and it returns false.
	 *
	 * @since 1.5.0
	 *
	 * @param mixed $var The value being filtered.
	 */
	public function filter_listner( $var ) {

		$filter = current_filter();

		if (
			! isset( $this->watched_filters[ $filter ]['callback'] )
			|| call_user_func( $this->watched_filters[ $filter ]['callback'], $var )
		) {
			$this->watched_filters[ $filter ]['count']++;
		}

		return $var;
	}

	/**
	 * Get the number of times a fitler was called.
	 *
	 * @since 1.5.0
	 *
	 * @param string $filter The filter to check for.
	 *
	 * @return int How many times this filter was called.
	 */
	protected function filter_was_called( $filter ) {

		return $this->watched_filters[ $filter ]['count'];
	}

	/**
	 * Check if an SQL string is a points logs query.
	 *
	 * @since 1.5.0
	 *
	 * @param string $sql The SQL query string.
	 *
	 * @return bool Whether the query is a points logs query.
	 */
	protected function is_points_logs_query( $sql ) {

		return strpos( $sql, "FROM `{$GLOBALS['wpdb']->wordpoints_points_logs}`" ) !== false;
	}

	/**
	 * Check if an SQL string is a top users query.
	 *
	 * @since 1.5.0
	 *
	 * @param string $sql The SQL query string.
	 *
	 * @return bool Whether the query is a points logs query.
	 */
	protected function is_top_users_query( $sql ) {

		global $wpdb;

		if ( ! strpos( $sql, $wpdb->usermeta ) ) {
			return false;
		}

		$meta_key = wordpoints_get_points_user_meta_key( 'points' );

		return 0 === strpos(
			$sql,
				"
					SELECT `user_ID`
					FROM {$wpdb->usermeta}
					WHERE `meta_key` = '{$meta_key}'
					ORDER BY CONVERT(`meta_value`, SIGNED INTEGER) DESC
				"
		);
	}
}

// end of file /tests/class-wordpoints-points-unittestcase.php
