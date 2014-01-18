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
		$this->assertEquals( WORDPOINTS_TESTS_VERSION, $wordpoints_data['version'] );

		// Check that the points component is active.
		if ( $this->network_wide ) {
			$active_components = get_site_option( 'wordpoints_active_components' );
		} else {
			$active_components = get_option( 'wordpoints_active_components' );
		}

		$this->assertInternalType( 'array', $active_components );
		$this->assertArrayHasKey( 'points', $active_components );

		// Check that the points tables were added.
		$this->assertTableExists( $wpdb->base_prefix . 'wordpoints_points_logs' );
		$this->assertTableExists( $wpdb->base_prefix . 'wordpoints_points_log_meta' );

		/**
		 * Run install tests.
		 *
		 * @since 1.0.1
		 *
		 * @param WordPoints_Uninstall_Test $testcase The current instance.
		 */
		do_action( 'wordpoints_install_tests', $this );

		/*
		 * Uninstall.
		 */

		$this->uninstall();

		$this->assertTableNotExists( $wpdb->wordpoints_points_logs );
		$this->assertTableNotExists( $wpdb->wordpoints_points_log_meta );

		$this->assertNoOptionsWithPrefix( 'wordpoints' );
		$this->assertNoUserMetaWithPrefix( 'wordpoints' );
		$this->assertNoCommentMetaWithPrefix( 'wordpoints' );

		$this->assertNoOptionsWithPrefix( 'widget_wordpoints' );

	}
}

// end of file /tests/phpunit/tests/uninstall.php
