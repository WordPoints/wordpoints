<?php

/**
 * Test uninstallation.
 *
 * @package WordPoints\Tests
 * @since 1.0.0
 */

/**
 * WordPoints uninstall test case.
 *
 * @since 1.0.0
 *
 * @group uninstall
 */
class WordPoints_Uninstall_Test extends WP_Plugin_Uninstall_UnitTestCase {

	//
	// Protected properties.
	//

	/**
	 * The full path to the main plugin file.
	 *
	 * @since 1.2.0
	 *
	 * @type string $plugin_file
	 */
	protected $plugin_file;

	/**
	 * The plugin's install function.
	 *
	 * @since 1.2.0
	 *
	 * @type callable $install_function
	 */
	protected $install_function = 'wordpoints_activate';

	/**
	 * Whether the tests are being run with the plugin network-activated.
	 *
	 * @since 1.2.0
	 *
	 * @type bool $network_wide
	 */
	protected $network_wide = false;

	//
	// Public methods.
	//

	/**
	 * Set up for the tests.
	 *
	 * @since 1.2.0
	 */
	public function setUp() {

		$this->plugin_file = dirname( dirname( WORDPOINTS_TESTS_DIR ) ) . '/src/wordpoints.php';
		$this->simulation_file = WORDPOINTS_TESTS_DIR . '/includes/usage-simulator.php';

		parent::setUp();
	}

	/**
	 * Tear down after the tests.
	 *
	 * @since 1.2.0
	 */
	public function tearDown() {

		// We've just deleted the tables, so this will have a DB error.
		remove_action( 'delete_blog', 'wordpoints_delete_points_logs_for_blog' );

		parent::tearDown();
	}

	/**
	 * Test installation and uninstallation.
	 *
	 * @since 1.0.0
	 */
	public function test_uninstall() {

		global $wpdb;

		/*
		 * Install.
		 */

		// Check the the basic plugin data option was added.
		if ( $this->network_wide ) {
			$wordpoints_data = get_site_option( 'wordpoints_data' );
		} else {
			$wordpoints_data = get_option( 'wordpoints_data' );
		}

		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'version', $wordpoints_data );
		$this->assertEquals( WORDPOINTS_VERSION, $wordpoints_data['version'] );

		// Flush the cache.
		unset( $GLOBALS['wp_roles'] );

		// Check that the capabilities were added.
		$administrator = get_role( 'administrator' );
		$this->assertTrue( $administrator->has_cap( 'install_wordpoints_modules' ) );
		$this->assertTrue( $administrator->has_cap( 'activate_wordpoints_modules' ) );
		$this->assertTrue( $administrator->has_cap( 'delete_wordpoints_modules' ) );

		if ( $this->network_wide ) {
			$active_components = get_site_option( 'wordpoints_active_components' );
		} else {
			$active_components = get_option( 'wordpoints_active_components' );
		}

		$this->assertInternalType( 'array', $active_components );

		$this->assertPointsComponentInstalled( $active_components );

		/**
		 * Run install tests.
		 *
		 * @since 1.0.1
		 *
		 * @param WordPoints_Uninstall_Test $testcase The current instance.
		 */
		do_action( 'wordpoints_install_tests', $this );

		/*
		 * Simulated Usage.
		 */

		$this->simulate_usage();

		$this->assertRanksComponentInstalled();

		/*
		 * Uninstall.
		 */

		$this->uninstall();

		$this->assertPointsComponentUninstalled();

		$this->assertNoUserMetaWithPrefix( 'wordpoints' );

		if ( is_multisite() ) {

			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

			$original_blog_id = get_current_blog_id();

			foreach ( $blog_ids as $blog_id ) {

				switch_to_blog( $blog_id );

				$this->assertNoUserOptionsWithPrefix( 'wordpoints' );
				$this->assertNoOptionsWithPrefix( 'wordpoints' );
				$this->assertNoOptionsWithPrefix( 'widget_wordpoints' );
				$this->assertNoCommentMetaWithPrefix( 'wordpoints' );

				$administrator = get_role( 'administrator' );
				$this->assertFalse( $administrator->has_cap( 'install_wordpoints_modules' ) );
				$this->assertFalse( $administrator->has_cap( 'activate_wordpoints_modules' ) );
				$this->assertFalse( $administrator->has_cap( 'delete_wordpoints_modules' ) );
			}

			switch_to_blog( $original_blog_id );

			// See http://wordpress.stackexchange.com/a/89114/27757
			unset( $GLOBALS['_wp_switched_stack'] );
			$GLOBALS['switched'] = false;

		} else {

			$this->assertNoOptionsWithPrefix( 'wordpoints' );
			$this->assertNoOptionsWithPrefix( 'widget_wordpoints' );
			$this->assertNoCommentMetaWithPrefix( 'wordpoints' );

			$administrator = get_role( 'administrator' );
			$this->assertFalse( $administrator->has_cap( 'install_wordpoints_modules' ) );
			$this->assertFalse( $administrator->has_cap( 'activate_wordpoints_modules' ) );
			$this->assertFalse( $administrator->has_cap( 'delete_wordpoints_modules' ) );
		}

	} // function test_uninstall()

	//
	// Assertions.
	//

	/**
	 * Assert that the points component is installed.
	 *
	 * @since 1.7.0
	 *
	 * @param array $active_components The list of active components.
	 */
	protected function assertPointsComponentInstalled( $active_components ) {

		global $wpdb;

		// Check that the points component is active.
		$this->assertArrayHasKey( 'points', $active_components );

		// Check that the points tables were added.
		$this->assertTableExists( $wpdb->base_prefix . 'wordpoints_points_logs' );
		$this->assertTableExists( $wpdb->base_prefix . 'wordpoints_points_log_meta' );

		// Check that the capabilities were added.
		$administrator = get_role( 'administrator' );
		$this->assertTrue( $administrator->has_cap( 'set_wordpoints_points' ) );

		if ( $this->network_wide ) {
			$this->assertFalse( $administrator->has_cap( 'manage_wordpoints_points_types' ) );
		} else {
			$this->assertTrue( $administrator->has_cap( 'manage_wordpoints_points_types' ) );
		}
	}

	/**
	 * Assert that the points component is uninstalled.
	 *
	 * @since 1.7.0
	 */
	protected function assertPointsComponentUninstalled() {

		global $wpdb;

		$this->assertTableNotExists( $wpdb->wordpoints_points_logs );
		$this->assertTableNotExists( $wpdb->wordpoints_points_log_meta );

		if ( is_multisite() ) {

			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

			$original_blog_id = get_current_blog_id();

			foreach ( $blog_ids as $blog_id ) {

				switch_to_blog( $blog_id );

				$administrator = get_role( 'administrator' );
				$this->assertFalse(
					$administrator->has_cap( 'set_wordpoints_points' )
				);
			}

			switch_to_blog( $original_blog_id );

			// See http://wordpress.stackexchange.com/a/89114/27757
			unset( $GLOBALS['_wp_switched_stack'] );
			$GLOBALS['switched'] = false;

		} else {

			$administrator = get_role( 'administrator' );
			$this->assertFalse(
				$administrator->has_cap( 'set_wordpoints_points' )
			);
		}
	}

	/**
	 * Assert that the ranks component is installed.
	 *
	 * @since 1.7.0
	 */
	protected function assertRanksComponentInstalled() {

		global $wpdb;

		if ( $this->network_wide ) {
			$active_components = get_site_option( 'wordpoints_active_components' );
		} else {
			$active_components = get_option( 'wordpoints_active_components' );
		}

		$this->assertInternalType( 'array', $active_components );

		$this->assertArrayHasKey( 'ranks', $active_components );

		$this->assertTableExists( $wpdb->base_prefix . 'wordpoints_ranks' );
		$this->assertTableExists( $wpdb->base_prefix . 'wordpoints_rankmeta' );
	}
}

// EOF
