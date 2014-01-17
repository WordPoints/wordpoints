<?php

/**
 * WordPress plugin uninstall test case.
 *
 * @package WP_Plugin_Uninstall_Tester
 * @since 0.1.0
 */

/**
 * Test WordPress plugin installation and uninstallation.
 *
 * @since 0.1.0
 */
abstract class WP_Plugin_Uninstall_UnitTestCase extends WP_UnitTestCase {

	//
	// Protected properties.
	//

	/**
	 * The full path to the main plugin file.
	 *
	 * @since 0.1.0
	 *
	 * @type string $plugin_file
	 */
	protected $plugin_file;

	/**
	 * The plugin's install function.
	 *
	 * @since 0.1.0
	 *
	 * @type callable $install_function
	 */
	protected $install_function;

	/**
	 * The plugin's uninstall function (if it has one).
	 *
	 * @since 0.1.0
	 *
	 * @type callable $uninstall_function
	 */
	protected $uninstall_function;

	//
	// Methods.
	//

	/**
	 * Set up for the tests.
	 *
	 * If you need to set any of the class properties (like $plugin_file), you'll
	 * need to have a setUp() method in your child class. Don't forget to call
	 * parent::setUp() at the end of it.
	 *
	 * @since 0.1.0
	 */
	public function setUp() {

		$this->install();

		parent::setUp();
	}

	/**
	 * Locate the config file for the WordPress tests.
	 *
	 * The script is exited with an error message if no config file can be found.
	 *
	 * @since 0.1.0
	 *
	 * @return string The path to the file, if found.
	 */
	protected function locate_wp_tests_config() {

		$config_file_path = getenv( 'WP_TESTS_DIR' );

		if ( ! file_exists( $config_file_path . '/wp-tests-config.php' ) ) {

			// Support the config file from the root of the develop repository.
			if (
				basename( $config_file_path ) === 'phpunit'
				&& basename( dirname( $config_file_path ) ) === 'tests'
			) {
				$config_file_path = dirname( dirname( $config_file_path ) );
			}
		}

		$config_file_path .= '/wp-tests-config.php';

		if ( ! is_readable( $config_file_path ) ) {
			exit( 'Error: Unable to locate the wp-tests-config.php file.' );
		}

		return $config_file_path;
	}

	/**
	 * Run the plugin's install script.
	 *
	 * Called by the setUp() method.
	 *
	 * Installation is run seperately, so the plugin is never actually loaded in this
	 * process. This provides more realistic testing of the uninstall process, since
	 * it is run while the plugin is inactive, just like in "real life".
	 *
	 * @since 0.1.0
	 */
	protected function install() {

		system(
			WP_PHP_BINARY
			. ' ' . escapeshellarg( dirname( dirname( __FILE__ ) ) . '/bin/install-plugin.php' )
			. ' ' . escapeshellarg( $this->plugin_file )
			. ' ' . escapeshellarg( $this->install_function )
			. ' ' . escapeshellarg( $this->locate_wp_tests_config() )
		);
	}

	/**
	 * Run the plugin's uninstall script.
	 *
	 * Call it and then run your uninstall assertions. You should always test
	 * installation before testing uninstallation.
	 *
	 * @since 0.1.0
	 */
	public function uninstall() {

		// We're going to do real table dropping, not temporary tables.
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );

		if ( empty( $this->plugin_file ) ) {
			exit( 'Error: $plugin_file property not set.' . PHP_EOL );
		}

		$plugin_dir = dirname( $this->plugin_file );

		if ( file_exists( $plugin_dir . '/uninstall.php' ) ) {

			define( 'WP_UNINSTALL_PLUGIN', $this->plugin_file );
			include $plugin_dir . '/uninstall.php';

		} elseif ( ! empty( $this->uninstall_function ) ) {

			include $this->plugin_file;

			add_action( 'uninstall_' . $this->plugin_file, $this->uninstall_function );

			do_action( 'uninstall_' . $this->plugin_file );

		} else {

			exit( 'Error: $uninstall_function property not set.' . PHP_EOL );
		}

		add_filter( 'query', array( $this, '_drop_temporary_tables' ) );
	}

	/**
	 * Asserts that a database table does not exist.
	 *
	 * @since 0.1.0
	 *
	 * @param string $table	  The table name.
	 * @param string $message An optional message.
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 */
	public static function assertTableNotExists( $table, $message = '' ) {

		self::assertThat( $table, self::isNotInDatabase(), $message );
	}

	/**
	 * Asserts that a database table exsists.
	 *
	 * @since 0.1.0
	 *
	 * @param string $table The table name.
	 * @param string $message An optional message.
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 */
	public static function assertTableExists( $table, $message = '' ) {

		self::assertThat( $table, self::isInDatabase(), $message );
	}

	/**
	 * Asserts that no options with a given prefix exist.
	 *
	 * @since 0.1.0
	 *
	 * @param string $prefix  The prefix to check for.
	 * @param string $message An optional message.
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 */
	public static function assertNoOptionsWithPrefix( $prefix, $message = '' ) {

		global $wpdb;

		self::assertThat( $prefix, self::tableColumnHasNoRowsWithPrefix( $wpdb->options, 'option_name', $prefix ), $message );
	}

	/**
	 * Asserts that no usermeta with a given prefix exists.
	 *
	 * @since 0.1.0
	 *
	 * @param string $prefix  The prefix to check for.
	 * @param string $message An optional message.
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 */
	public static function assertNoUserMetaWithPrefix( $prefix, $message = '' ) {

		global $wpdb;

		self::assertThat( $prefix, self::tableColumnHasNoRowsWithPrefix( $wpdb->usermeta, 'meta_key', $prefix ), $message );
	}

	/**
	 * Asserts that no postmeta with a given prefix exists.
	 *
	 * @since 0.1.0
	 *
	 * @param string $prefix  The prefix to check for.
	 * @param string $message An optional message.
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 */
	public static function assertNoPostMetaWithPrefix( $prefix, $message = '' ) {

		global $wpdb;

		self::assertThat( $prefix, self::tableColumnHasNoRowsWithPrefix( $wpdb->postmeta, 'meta_key', $prefix ), $message );
	}

	/**
	 * Asserts that no commentmeta with a given prefix exist.
	 *
	 * @since 0.1.0
	 *
	 * @param string $prefix  The prefix to check for.
	 * @param string $message An optional message.
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 */
	public static function assertNoCommentMetaWithPrefix( $prefix, $message = '' ) {

		global $wpdb;

		self::assertThat( $prefix, self::tableColumnHasNoRowsWithPrefix( $wpdb->commentmeta, 'meta_key', $prefix ), $message );
	}

	/**
	 * Database table not existant constraint.
	 *
	 * @since 0.1.0
	 *
	 * @return WP_Plugin_Uninstall_Tester_PHPUnit_Constraint_IsTableNonExistant
	 */
	public static function isNotInDatabase() {

		return new WP_Plugin_Uninstall_Tester_PHPUnit_Constraint_IsTableNonExistant;
	}

	/**
	 * Database table is in the database constraint.
	 *
	 * @since 0.1.0
	 *
	 * @return WP_Plugin_Uninstall_Tester_PHPUnit_Constraint_IsTableExistant
	 */
	public static function isInDatabase() {

		return new WP_Plugin_Uninstall_Tester_PHPUnit_Constraint_IsTableExistant;
	}

	/**
	 * No row values with prefix in DB table constraint.
	 *
	 * @since 0.1.0
	 *
	 * @param string $table  The name of the table.
	 * @param string $column The name of the row in the table to check.
	 *
	 * @return WP_Plugin_Uninstall_Tester_PHPUnit_Constraint_NoRowsWithPrefix
	 */
	public static function tableColumnHasNoRowsWithPrefix( $table, $column, $prefix ) {

		return new WP_Plugin_Uninstall_Tester_PHPUnit_Constraint_NoRowsWithPrefix( $table, $column, $prefix );
	}
}
