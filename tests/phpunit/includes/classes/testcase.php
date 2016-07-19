<?php

/**
 * Base test case class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Parent test case.
 *
 * @since 2.1.0
 *
 * @property WordPoints_PHPUnit_Factory_Stub $factory The factory.
 */
abstract class WordPoints_PHPUnit_TestCase extends WP_UnitTestCase {

	/**
	 * The default points data set up for each test.
	 *
	 * @since 1.0.0 This was a part of the WordPoints_Points_UnitTestCase.
	 * @since 1.7.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @type array $points_data
	 */
	protected $points_data;

	/**
	 * The list of filters currently being watched.
	 *
	 * @since 1.5.0 This was a part of the WordPoints_Points_UnitTestCase.
	 * @since 1.7.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.0.0 Now an array of WordPoints_Mock_Filter objects.
	 * @since 2.1.0
	 *
	 * @see WordPoints_Points_UnitTestCase::listen_for_filter()
	 *
	 * @type WordPoints_Mock_Filter[] $watched_filters
	 */
	protected $watched_filters = array();

	/**
	 * The class name of the widget type that this test is for.
	 *
	 * @since 1.9.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @type string $widget_class
	 */
	protected $widget_class;

	/**
	 * The WordPoints component that this testcase is for.
	 *
	 * @since 1.9.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @type string $wordpoints_component
	 */
	protected $wordpoints_component;

	/**
	 * The database schema defined by this component.
	 *
	 * @see self::get_db_schema()
	 *
	 * @since 2.0.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $db_schema;

	/**
	 * The database tables created by this component.
	 *
	 * @see self::get_db_tables()
	 *
	 * @since 2.0.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @var string[]
	 */
	protected $db_tables;

	/**
	 * The previous version if this is an update testcase.
	 *
	 * @since 1.9.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @type string $previous_version
	 */
	protected $previous_version;

	/**
	 * A mock filesystem object.
	 *
	 * @since 2.0.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @var WP_Mock_Filesystem
	 */
	protected $mock_fs;

	/**
	 * A list of global variables to back up between tests.
	 *
	 * PHPUnit has built-in support for backing up globals between tests, but it has
	 * a few issues that make it difficult to use. First, it has only a blacklist, no
	 * whitelist. That means that when you enable the backup globals feature for a
	 * test, all of the globals will be backed up. This can be time-consuming, and
	 * also leads to breakage because of the way that the globals are backed up.
	 * PHPUnit backs up the globals by serializing them, which is necessary for some
	 * uses, but causes `$wpdb` to stop working after the globals are restored,
	 * causing all tests after that to fail. Our implementation here is much simpler,
	 * and is based on a whitelist so that we can just back up the globals that
	 * actually need to be backed up.
	 *
	 * @since 2.1.0
	 *
	 * @var string[]
	 */
	protected $backup_globals;

	/**
	 * Backed up values of global variables that are modified in the tests.
	 *
	 * @since 2.1.0
	 *
	 * @var array
	 */
	protected $backed_up_globals = array();

	/**
	 * A backup of the main app.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_App
	 */
	public static $backup_app;

	/**
	 * @since 2.0.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 */
	protected function checkRequirements() {

		parent::checkRequirements();

		$annotations = $this->getAnnotations();

		foreach ( array( 'class', 'method' ) as $depth ) {

			if ( empty( $annotations[ $depth ]['requires'] ) ) {
				continue;
			}

			$requires = array_flip( $annotations[ $depth ]['requires'] );

			if ( isset( $requires['WordPress multisite'] ) && ! is_multisite() ) {
				$this->markTestSkipped( 'Multisite must be enabled.' );
			} elseif ( isset( $requires['WordPress !multisite'] ) && is_multisite() ) {
				$this->markTestSkipped( 'Multisite must not be enabled.' );
			}

			if (
				isset( $requires['WordPoints network-active'] )
				&& ! is_wordpoints_network_active()
			) {
				$this->markTestSkipped( 'WordPoints must be network-activated.' );
			} elseif (
				isset( $requires['WordPoints !network-active'] )
				&& is_wordpoints_network_active()
			) {
				$this->markTestSkipped( 'WordPoints must not be network-activated.' );
			}
		}
	}

	/**
	 * @since 2.1.0
	 */
	public function setUp() {

		parent::setUp();

		if ( ! isset( $this->factory->wordpoints ) ) {
			$this->factory->wordpoints = WordPoints_PHPUnit_Factory::$factory;
		}

		if ( ! empty( $this->backup_globals ) ) {
			foreach ( $this->backup_globals as $global ) {
				$this->backed_up_globals[ $global ] = isset( $GLOBALS[ $global ] )
					? $GLOBALS[ $global ]
					: null;
			}
		}

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
	 * @since 2.1.0
	 */
	public function tearDown() {

		parent::tearDown();

		if ( isset( self::$backup_app ) ) {
			WordPoints_App::$main = self::$backup_app;
			self::$backup_app = null;
		}

		unset( $GLOBALS['current_screen'] );

		if ( ! empty( $this->backed_up_globals ) ) {
			foreach ( $this->backed_up_globals as $key => $value ) {
				$GLOBALS[ $key ] = $value;
			}
		}

		WordPoints_PHPUnit_Mock_Entity_Context::$current_id = 1;
	}

	//
	// Helpers.
	//

	/**
	 * Set the version of the plugin.
	 *
	 * @since 1.3.0 This was a part of the WordPoints_Points_Update_Test.
	 * @since 1.5.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @param string $version The version to set. Defaults to 1.0.0.
	 */
	protected function wordpoints_set_db_version( $version = '1.0.0' ) {

		$wordpoints_data = wordpoints_get_maybe_network_option( 'wordpoints_data' );
		$wordpoints_data['version'] = $version;
		wordpoints_update_maybe_network_option( 'wordpoints_data', $wordpoints_data );
	}

	/**
	 * Get the version of the plugin.
	 *
	 * @since 1.3.0 This was a part of the WordPoints_Points_Update_Test.
	 * @since 1.5.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @return string The version of the plugin.
	 */
	protected function wordpoints_get_db_version() {

		$wordpoints_data = wordpoints_get_maybe_network_option( 'wordpoints_data' );

		return ( isset( $wordpoints_data['version'] ) )
			? $wordpoints_data['version']
			: '';
	}

	/**
	 * Set the version of a component.
	 *
	 * @since 1.8.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @param string $component The slug of the component.
	 * @param string $version   The version to set. Defaults to 1.0.0.
	 */
	protected function set_component_db_version( $component, $version = '1.0.0' ) {

		$wordpoints_data = wordpoints_get_maybe_network_option( 'wordpoints_data' );
		$wordpoints_data['components'][ $component ]['version'] = $version;
		wordpoints_update_maybe_network_option( 'wordpoints_data', $wordpoints_data );
	}

	/**
	 * Get the version of a component.
	 *
	 * @since 1.8.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @param string $component The slug of the component.
	 *
	 * @return string The version of the points component.
	 */
	protected function get_component_db_version( $component ) {

		$wordpoints_data = wordpoints_get_maybe_network_option( 'wordpoints_data' );

		return ( isset( $wordpoints_data['components'][ $component ]['version'] ) )
			? $wordpoints_data['components'][ $component ]['version']
			: '';
	}

	/**
	 * Set the version of a module.
	 *
	 * @since 2.0.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @param string $module       The slug of the module.
	 * @param string $version      The version to set. Defaults to 1.0.0.
	 * @param bool   $network_wide Whether to set the network-wide version.
	 */
	protected function set_module_db_version( $module, $version = '1.0.0', $network_wide = false ) {

		if ( $network_wide ) {
			$wordpoints_data = get_site_option( 'wordpoints_data' );
		} else {
			$wordpoints_data = get_option( 'wordpoints_data' );
		}

		$wordpoints_data['modules'][ $module ]['version'] = $version;

		if ( $network_wide ) {
			update_site_option( 'wordpoints_data', $wordpoints_data );
		} else {
			update_option( 'wordpoints_data', $wordpoints_data );
		}
	}

	/**
	 * Get the version of a module.
	 *
	 * @since 2.0.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @param string $module       The slug of the component.
	 * @param bool   $network_wide Whether to get the network-wide version.
	 *
	 * @return string The version of the points component.
	 */
	protected function get_module_db_version( $module, $network_wide = false ) {

		if ( $network_wide ) {
			$wordpoints_data = get_site_option( 'wordpoints_data' );
		} else {
			$wordpoints_data = get_option( 'wordpoints_data' );
		}

		return ( isset( $wordpoints_data['modules'][ $module ]['version'] ) )
			? $wordpoints_data['modules'][ $module ]['version']
			: '';
	}

	/**
	 * Run an update for WordPoints.
	 *
	 * @since 1.10.3 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @param string $from The version to update from.
	 */
	protected function update_wordpoints( $from = null ) {

		if ( ! isset( $from ) ) {
			$from = $this->previous_version;
		}

		$this->wordpoints_set_db_version( $from );

		delete_site_transient( 'wordpoints_all_site_ids' );

		WordPoints_Installables::maybe_do_updates();
	}

	/**
	 * Run an update for a component.
	 *
	 * @since 1.8.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
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
		wordpoints_update_maybe_network_option(
			'wordpoints_active_components'
			, array( $component => 1 )
		);

		// Run the update.
		WordPoints_Installables::maybe_do_updates();
	}

	/**
	 * Create the points type used in the tests.
	 *
	 * @since 1.5.1 This was a part of the WordPoints_Points_UnitTestCase.
	 * @since 1.7.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 */
	protected function create_points_type() {

		$this->points_data = array(
			'name'   => 'Points',
			'prefix' => '$',
			'suffix' => 'pts.',
		);

		wordpoints_add_maybe_network_option(
			'wordpoints_points_types'
			, array( 'points' => $this->points_data )
		);
	}

	/**
	 * Alter temporary tables.
	 *
	 * @since 1.5.1 This was a part of the WordPoints_Points_UnitTestCase.
	 * @since 1.7.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @WordPoints\filter query Added by self::setUp().
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
	 * @since 2.0.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
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
	 * @since 2.0.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
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
	 * @since 2.0.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @return string The database schema defined by this component.
	 */
	public function get_db_schema() {

		if ( ! isset( $this->db_schema ) ) {
			$installer = WordPoints_Installables::get_installer( 'component', 'points' );
			$this->db_schema = $installer->get_db_schema();
		}

		return $this->db_schema;
	}

	/**
	 * Mock a filter function with an object.
	 *
	 * @since 2.0.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @param string $filter       The filter hook to attach to.
	 * @param mixed  $return_value The filtered value that should be returned.
	 *
	 * @return WordPoints_Mock_Filter The mock filter.
	 */
	protected function mock_filter( $filter, $return_value = null ) {

		$mock = new WordPoints_Mock_Filter( $return_value );

		add_filter( $filter, array( $mock, 'filter' ) );

		return $mock;
	}

	/**
	 * Listen for a WordPress action or filter.
	 *
	 * To limit the counting based on the filtered value, you can pass a
	 * $count_callback, which will be called with the value being filtered. The
	 * callback should return a boolean value, which will determine whether the
	 * filter call is counted.
	 *
	 * @since 1.5.0 This was a part of the WordPoints_Points_UnitTestCase.
	 * @since 1.7.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @param string   $filter         The filter to listen for.
	 * @param callable $count_callback Function to call to test if this filter call
	 *                                 should be counted.
	 *
	 * @return WordPoints_Mock_Filter The mock filter.
	 */
	protected function listen_for_filter( $filter, $count_callback = null ) {

		$mock = $this->mock_filter( $filter );

		if ( isset( $count_callback ) ) {
			$mock->count_callback = $count_callback;
		}

		$this->watched_filters[ $filter ] = $mock;

		return $mock;
	}

	/**
	 * Get the number of times a filter was called.
	 *
	 * @since 1.5.0 This was a part of the WordPoints_Points_UnitTestCase.
	 * @since 1.7.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @param string $filter The filter to check for.
	 *
	 * @return int How many times this filter was called.
	 */
	protected function filter_was_called( $filter ) {

		return $this->watched_filters[ $filter ]->call_count;
	}

	/**
	 * Check if an SQL string is a points logs query.
	 *
	 * @since 1.5.0 This was a part of the WordPoints_Points_UnitTestCase.
	 * @since 1.7.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @param string $sql The SQL query string.
	 *
	 * @return bool Whether the query is a points logs query.
	 */
	public function is_points_logs_query( $sql ) {

		return strpos( $sql, "FROM `{$GLOBALS['wpdb']->wordpoints_points_logs}`" ) !== false;
	}

	/**
	 * Check if an SQL string is a top users query.
	 *
	 * @since 1.5.0 This was a part of the WordPoints_Points_UnitTestCase.
	 * @since 1.7.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @param string $sql The SQL query string.
	 *
	 * @return bool Whether the query is a points logs query.
	 */
	public function is_top_users_query( $sql ) {

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
	 * @since 1.7.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
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
	 * @since 1.9.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
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
	 * @since 1.9.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
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

	/**
	 * Give the current user certain capabilities.
	 *
	 * @since 2.0.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @param string|string[] $caps The caps to give the user.
	 */
	protected function give_current_user_caps( $caps ) {

		/** @var WP_User $user */
		$user = $this->factory->user->create_and_get();

		foreach ( (array) $caps as $cap ) {
			$user->add_cap( $cap );
		}

		wp_set_current_user( $user->ID );
	}

	/**
	 * Begin mocking the filesystem.
	 *
	 * @since 2.0.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 */
	protected function mock_filesystem() {

		if ( ! class_exists( 'WP_Mock_Filesystem' ) ) {

			/**
			 * WordPress's base filesystem API class.
			 *
			 * @since 2.0.0
			 */
			require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );

			/**
			 * The filesystem API shim that uses mock filesystems.
			 *
			 * @since 2.0.0
			 */
			require_once( WORDPOINTS_TESTS_DIR . '/../../vendor/jdgrimes/wp-filesystem-mock/src/wp-filesystem-mock.php' );

			/**
			 * The mock filesystem class.
			 *
			 * @since 2.0.0
			 */
			require_once( WORDPOINTS_TESTS_DIR . '/../../vendor/jdgrimes/wp-filesystem-mock/src/wp-mock-filesystem.php' );
		}

		// Creating a new mock filesystem.
		$this->mock_fs = new WP_Mock_Filesystem;

		// Tell the WordPress filesystem API shim to use this mock filesystem.
		WP_Filesystem_Mock::set_mock( $this->mock_fs );

		// Tell the shim to start overriding whatever other filesystem access method
		// is in use.
		WP_Filesystem_Mock::start();

		if ( empty( $GLOBALS['wp_filesystem'] ) || ! ( $GLOBALS['wp_filesystem'] instanceof WP_Filesystem_Mock ) ) {
			WP_Filesystem();
		}
	}

	/**
	 * Set up the global apps object as a mock.
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_App The mock app.
	 */
	public static function mock_apps() {

		self::$backup_app = WordPoints_App::$main;

		return WordPoints_App::$main = new WordPoints_PHPUnit_Mock_App_Silent(
			'apps'
		);
	}

	/**
	 * Mock being in the network admin.
	 *
	 * @since 2.1.0
	 */
	public function set_network_admin() {
		$GLOBALS['current_screen'] = WP_Screen::get( 'test-network' );
	}

	//
	// Assertions.
	//

	/**
	 * Assert that a string is an error returned by one of the shortcodes.
	 *
	 * @since 1.7.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
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
	 * Assert that a string is an error output by one of the widgets.
	 *
	 * @since 1.9.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
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
	 * Assert that a string is an admin notice.
	 *
	 * @since 2.0.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
	 *
	 * @param string $string The string that is expected to contain an admin notice.
	 * @param array  $args   {
	 *        Other arguments.
	 *
	 *        @type string $type        The type of notice to expect.
	 *        @type bool   $dismissible Whether the notice should be dismissible.
	 *        @type string $option      The option that should be deleted on dismiss.
	 * }
	 */
	protected function assertWordPointsAdminNotice( $string, $args = array() ) {

		$document = new DOMDocument;
		$document->loadHTML( $string );
		$xpath = new DOMXPath( $document );

		$messages = $xpath->query( '//div[contains(@class, "notice")]' );

		$this->assertEquals( 1, $messages->length );

		$message = $messages->item( 0 );

		if ( isset( $args['type'] ) ) {

			$this->assertStringMatchesFormat(
				"%Snotice-{$args['type']}%S"
				, $message->attributes->getNamedItem( 'class' )->nodeValue
			);
		}

		if ( isset( $args['dismissible'] ) ) {

			$this->assertStringMatchesFormat(
				'%Sis-dismissible%S'
				, $message->attributes->getNamedItem( 'class' )->nodeValue
			);

			if ( isset( $args['option'] ) ) {

				$this->assertEquals(
					$args['option']
					, $message->attributes->getNamedItem( 'data-option' )->nodeValue
				);
			}
		}
	}

	/**
	 * Assert that all of this component's database tables have a certain charset.
	 *
	 * @since 2.0.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
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
	 * @since 2.0.0 This was a part of the WordPoints_UnitTestCase.
	 * @since 2.1.0
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

	/**
	 * Assert that a value is a hook reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $reaction The reaction.
	 */
	public function assertIsReaction( $reaction ) {

		if ( $reaction instanceof WP_Error ) {
			$reaction = $reaction->get_error_data();
		}

		if ( $reaction instanceof WordPoints_Hook_Reaction_Validator ) {

			$message = '';

			foreach ( $reaction->get_errors() as $error ) {
				$message .= PHP_EOL . 'Field: ' . implode( '.',  $error['field'] );
				$message .= PHP_EOL . 'Error: ' . $error['message'];
			}

			$this->fail( $message );
		}

		$this->assertInstanceOf( 'WordPoints_Hook_ReactionI', $reaction );
	}

	/**
	 * Assert that a database table exists.
	 *
	 * @since 2.1.0
	 *
	 * @param string $table_name The name of the table to assert exists.
	 */
	public function assertDBTableExists( $table_name ) {

		global $wpdb;

		$this->assertEquals(
			$table_name
			,
			$wpdb->get_var(
				$wpdb->prepare(
					'SHOW TABLES LIKE %s'
					,
					$wpdb->esc_like( $table_name )
				)
			)
		);
	}

	/**
	 * Create a points reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param array $settings The settings for this reaction.
	 *
	 * @return false|WordPoints_Hook_Reaction_Validator|WordPoints_Hook_ReactionI
	 *         The reaction, or false on failure.
	 */
	public function create_points_reaction( array $settings = array() ) {

		$settings = array_merge(
			array(
				'event'       => 'user_register',
				'target'      => array( 'user' ),
				'reactor'     => 'points',
				'points'      => 100,
				'points_type' => 'points',
				'log_text'    => 'Test log text.',
				'description' => 'Test description.',
			)
			, $settings
		);

		$store = wordpoints_hooks()->get_reaction_store( 'points' );

		return $store->create_reaction( $settings );
	}
}

// EOF
