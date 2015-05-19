<?php

/**
 * A parent class for the WordPoints unit tests.
 *
 * @package WordPoints\Tests
 * @since 1.5.0
 */

/**
 * Test case parent for the unit tests.
 *
 * @since 1.5.0
 */
abstract class WordPoints_UnitTestCase extends WP_UnitTestCase {

	/**
	 * The default points data set up for each test.
	 *
	 * Since 1.0.0, this was a part of the WordPoints_Points_UnitTestCase.
	 *
	 * @since 1.7.0
	 *
	 * @type array $points_data
	 */
	protected $points_data;

	/**
	 * The list of filters currently being watched.
	 *
	 * Since 1.5.0, this was a part of the WordPoints_Points_UnitTestCase.
	 *
	 * @since 1.7.0
	 *
	 * @see WordPoints_Points_UnitTestCase::listen_for_filter()
	 *
	 * @type array $watched_filters
	 */
	protected $watched_filters = array();

	/**
	 * The class name of the widget type that this test is for.
	 *
	 * @since 1.9.0
	 *
	 * @type string $widget_class
	 */
	protected $widget_class;

	/**
	 * The WordPoints component that this testcase is for.
	 *
	 * @since 1.9.0
	 *
	 * @type string $wordpoints_component
	 */
	protected $wordpoints_component;

	/**
	 * The database schema defined by this component.
	 *
	 * @see self::get_db_schema()
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $db_schema;

	/**
	 * The database tables created by this component.
	 *
	 * @see self::get_db_tables()
	 *
	 * @since 2.0.0
	 *
	 * @var string[]
	 */
	protected $db_tables;

	/**
	 * The previous version if this is an update testcase.
	 *
	 * @since 1.9.0
	 *
	 * @type string $previous_version
	 */
	protected $previous_version;

	/**
	 * Set up before each test.
	 *
	 * @since 1.7.0
	 */
	public function setUp() {

		parent::setUp();

		$this->factory->wordpoints_points_log =
			new WordPoints_UnitTest_Factory_For_Points_Log(
				$this->factory
			);

		$this->factory->wordpoints_rank = new WordPoints_UnitTest_Factory_For_Rank(
			$this->factory
		);

		add_filter( 'query', array( $this, 'do_not_alter_tables' ) );
	}

	/**
	 * Clean up after each class.
	 *
	 * @since 1.7.0
	 */
	public function tearDown() {

		foreach ( $this->watched_filters as $filter => $data ) {

			remove_filter( $filter, array( $this, 'filter_listner' ) );
		}

		remove_filter( 'query', array( $this, 'do_not_alter_tables' ) );

		parent::tearDown();
	}

	//
	// Helpers.
	//

	/**
	 * Set the version of the plugin.
	 *
	 * Since 1.3.0, this was a part of the WordPoints_Points_Update_Test.
	 *
	 * @since 1.5.0
	 *
	 * @param string $version The version to set. Defaults to 1.0.0.
	 */
	protected function wordpoints_set_db_version( $version = '1.0.0' ) {

		$wordpoints_data = wordpoints_get_network_option( 'wordpoints_data' );
		$wordpoints_data['version'] = $version;
		wordpoints_update_network_option( 'wordpoints_data', $wordpoints_data );
	}

	/**
	 * Get the version of the plugin.
	 *
	 * Since 1.3.0, this was a part of the WordPoints_Points_Update_Test.
	 *
	 * @since 1.5.0
	 *
	 * @return string The version of the plugin.
	 */
	protected function wordpoints_get_db_version() {

		$wordpoints_data = wordpoints_get_network_option( 'wordpoints_data' );

		return ( isset( $wordpoints_data['version'] ) )
			? $wordpoints_data['version']
			: '';
	}

	/**
	 * Set the version of a component.
	 *
	 * @since 1.8.0
	 *
	 * @param string $component The slug of the component.
	 * @param string $version   The version to set. Defaults to 1.0.0.
	 */
	protected function set_component_db_version( $component, $version = '1.0.0' ) {

		$wordpoints_data = wordpoints_get_network_option( 'wordpoints_data' );
		$wordpoints_data['components'][ $component ]['version'] = $version;
		wordpoints_update_network_option( 'wordpoints_data', $wordpoints_data );
	}

	/**
	 * Get the version of a component.
	 *
	 * @since 1.8.0
	 *
	 * @param string $component The slug of the component.
	 *
	 * @return string The version of the points component.
	 */
	protected function get_component_db_version( $component ) {

		$wordpoints_data = wordpoints_get_network_option( 'wordpoints_data' );

		return ( isset( $wordpoints_data['components'][ $component ]['version'] ) )
			? $wordpoints_data['components'][ $component ]['version']
			: '';
	}

	/**
	 * Set the version of the points component.
	 *
	 * Since 1.4.0 This was part of the WordPoints_Points_UnitTestCase.
	 *
	 * @since 1.7.0
	 * @deprecated 1.8.0 Use self::set_component_db_version() instead.
	 *
	 * @param string $version The version to set. Defaults to 1.0.0.
	 */
	protected function set_points_db_version( $version = '1.0.0' ) {
		$this->set_component_db_version( 'points', $version );
	}

	/**
	 * Get the version of the points component.
	 *
	 * Since 1.4.0 This was part of the WordPoints_Points_UnitTestCase.
	 *
	 * @since 1.7.0
	 * @deprecated 1.8.0 Use self::get_component_db_version() instead.
	 *
	 * @return string The version of the points component.
	 */
	protected function get_points_db_version() {
		$this->get_component_db_version( 'points' );
	}

	/**
	 * Run an update for WordPoints.
	 *
	 * @since 1.10.3
	 *
	 * @param string $from The version to update from.
	 */
	protected function update_wordpoints( $from = null ) {

		if ( ! isset( $from ) ) {
			$from = $this->previous_version;
		}

		$this->wordpoints_set_db_version( $from );

		wordpoints_update();
	}

	/**
	 * Run an update for a component.
	 *
	 * @since 1.8.0
	 *
	 * @param string $component The slug of the component to update.
	 * @param string $from      The version to update from.
	 */
	protected function update_component( $component = null, $from = null ) {

		if ( ! isset( $component ) ) {
			$component = $this->wordpoints_component;
		}

		if ( ! isset( $from ) ) {
			$from = $this->previous_version;
		}

		$this->set_component_db_version( $component, $from );

		// Make sure that the component is marked as active in the database.
		wordpoints_update_network_option(
			'wordpoints_active_components'
			, array( $component => 1 )
		);

		// Run the update.
		WordPoints_Components::instance()->maybe_do_updates();
	}

	/**
	 * Create the points type used in the tests.
	 *
	 * Since 1.5.1 This was part of the WordPoints_Points_UnitTestCase.
	 *
	 * @since 1.7.0
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
	 * Since 1.5.1 This was part of the WordPoints_Points_UnitTestCase.
	 *
	 * @since 1.7.0
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
	 * Create the tables for this component with a specific charset.
	 *
	 * @since 2.0.0
	 *
	 * @param string $charset The character set to create the tables with.
	 */
	protected function create_tables_with_charset( $charset ) {

		global $wpdb;

		$wpdb->query( 'ROLLBACK' );

		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
		remove_filter( 'query', array( $this, 'do_not_alter_tables' ) );

		// Remove the current tables.
		foreach ( $this->get_db_tables() as $table ) {
			$wpdb->query( "DROP TABLE `{$table}`" );
		}

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// Create the tables again with the specified charset.
		$schema = $this->get_db_schema();
		$schema = preg_replace( '/\).*;/', ") DEFAULT CHARSET={$charset};", $schema );
		dbDelta( $schema );

		$this->assertTablesHaveCharset( $charset );
	}

	/**
	 * Get the database tables created by this component.
	 *
	 * @since 2.0.0
	 *
	 * @return array The database tables of the component.
	 */
	public function get_db_tables() {

		if ( ! isset( $this->db_tables ) ) {
			preg_match_all( '/CREATE TABLE (.*) \(/', $this->get_db_schema(), $matches );
			$this->db_tables = $matches[1];
		}

		return $this->db_tables;
	}

	/**
	 * Get the database schema for this component.
	 *
	 * @since 2.0.0
	 *
	 * @return string The database schema defined by this component.
	 */
	public function get_db_schema() {

		if ( ! isset( $this->db_schema ) ) {
			$installer = WordPoints_Components::instance()->get_installer( 'points' );
			$this->db_schema = $installer->get_db_schema();
		}

		return $this->db_schema;
	}

	/**
	 * Listen for a WordPress action or filter.
	 *
	 * To limit the counting based on the filtered value, you can pass a
	 * $count_callback, which will be called with the value being filtered. The
	 * callback should return a boolean value, which will determine whether the
	 * filter call is counted.
	 *
	 * Since 1.5.0 This was part of the WordPoints_Points_UnitTestCase.
	 *
	 * @since 1.7.0
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
	 * Since 1.5.0 This was part of the WordPoints_Points_UnitTestCase.
	 *
	 * @since 1.7.0
	 *
	 * @param mixed $var The value being filtered.
	 *
	 * @return mixed $var.
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
	 * Since 1.5.0 This was part of the WordPoints_Points_UnitTestCase.
	 *
	 * @since 1.7.0
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
	 * Since 1.5.0 This was part of the WordPoints_Points_UnitTestCase.
	 *
	 * @since 1.7.0
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
	 * Since 1.5.0 This was part of the WordPoints_Points_UnitTestCase.
	 *
	 * @since 1.7.0
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

		return false !== strpos(
			$sql
			, '
					ORDER BY COALESCE(CONVERT(`meta`.`meta_value`, SIGNED INTEGER), 0) DESC
					LIMIT'
		);
	}

	/**
	 * Check if an SQL query is a Rank retrieval query.
	 *
	 * @since 1.7.0
	 *
	 * @param string $sql The SQL query string.
	 *
	 * @return bool Whether the query is a get rank query.
	 */
	public function is_wordpoints_get_rank_query( $sql ) {

		global $wpdb;

		return 0 === strpos(
			$sql
			, "
					SELECT id, name, type, rank_group, blog_id, site_id
					FROM {$wpdb->wordpoints_ranks}
					WHERE id = "
		);
	}

	/**
	 * Get the HTML for a widget instance.
	 *
	 * @since 1.9.0
	 *
	 * @param array $instance The settings for the widget instance.
	 * @param array $args     Other arguments for the widget display.
	 *
	 * @return string The HTML for this widget instance.
	 */
	protected function get_widget_html( array $instance = array(), array $args = array() ) {

		ob_start();
		the_widget( $this->widget_class, $instance, $args );
		return ob_get_clean();
	}

	/**
	 * Get the XPath query for a widget instance.
	 *
	 * @since 1.9.0
	 *
	 * @param array $instance The settings for the widget instance.
	 *
	 * @return DOMXPath XPath query object loaded with the widget's HTML.
	 */
	protected function get_widget_xpath( array $instance = array() ) {

		$widget = $this->get_widget_html( $instance );

		$document = new DOMDocument;
		$document->loadHTML( $widget );
		$xpath    = new DOMXPath( $document );

		return $xpath;
	}

	//
	// Assertions.
	//

	/**
	 * Assert that a string is an error returned by one of the shortcodes.
	 *
	 * @since 1.7.0
	 *
	 * @param string $string The string that is expected to be a shortcode error.
	 */
	protected function assertWordPointsShortcodeError( $string ) {

		$document = new DOMDocument;
		$document->loadHTML( $string );
		$xpath = new DOMXPath( $document );
		$this->assertEquals(
			1
			, $xpath->query( '//p[@class = "wordpoints-shortcode-error"]' )->length
		);
	}

	/**
	 * Assert that a string is an error outpur by one of the widgets.
	 *
	 * @since 1.9.0
	 *
	 * @param string $string The string that is expected to be a widget error.
	 */
	protected function assertWordPointsWidgetError( $string ) {

		$document = new DOMDocument;
		$document->loadHTML( $string );
		$xpath = new DOMXPath( $document );
		$this->assertEquals(
			1
			, $xpath->query( '//div[@class = "wordpoints-widget-error"]' )->length
		);
	}

	/**
	 * Assert that all of this component's database tables have a certain charset.
	 *
	 * @since 2.0.0
	 *
	 * @param string $charset The charset that the tables are expected to have.
	 */
	public function assertTablesHaveCharset( $charset ) {

		foreach ( $this->get_db_tables() as $table ) {
			$this->assertTableHasCharset( $charset, $table );
		}
	}

	/**
	 * Assert that a database table has a certain charset.
	 *
	 * @since 2.0.0
	 *
	 * @param string $charset The charset the table is expected to have.
	 * @param string $table   The table name.
	 */
	public function assertTableHasCharset( $charset, $table ) {

		global $wpdb;

		// We append a space followed by another character to the strings so that we
		// can properly handle cases with and without a collation specified, and
		// without utf8 matching utf8mb4, for example.
		$this->assertStringMatchesFormat(
			"%aDEFAULT CHARSET={$charset} %a"
			, $wpdb->get_var( "SHOW CREATE TABLE `{$table}`", 1 ) . ' .'
		);

	}
}

// EOF
