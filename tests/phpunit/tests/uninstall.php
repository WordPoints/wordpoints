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
class WordPoints_Uninstall_Test extends WordPoints_Uninstall_UnitTestCase {

	/**
	 * Test installation and uninstallation.
	 *
	 * @since 1.0.0
	 */
	public function test_uninstall() {

		global $wpdb;

		/*
		 * We're going to do some actions here so that we are really testing whether
		 * everything is properly deleted on uninstall. Uninstalling a fresh install
		 * is important, but cleaning up the little things is also important. Doing
		 * this little dance here helps us to make sure we're doing that.
		 */

		// Add each of our widgets.
		wordpointstests_add_widget( 'wordpoints_points_widget' );
		wordpointstests_add_widget( 'wordpoints_top_users_widget' );
		wordpointstests_add_widget( 'wordpoints_points_logs_widget' );

		// Create a points type.
		add_option(
			'wordpoints_points_types'
			, array(
				'points' => array(
					'name'   => 'Points',
					'prefix' => '$',
					'suffix' => 'pts.',
				),
			)
		);

		// Add each of our points hooks.
		wordpointstests_add_points_hook( 'wordpoints_registration_points_hook' );
		wordpointstests_add_points_hook( 'wordpoints_post_points_hook' );
		wordpointstests_add_points_hook( 'wordpoints_comment_points_hook' );
		wordpointstests_add_points_hook( 'wordpoints_periodic_points_hook' );

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

		/*
		 * Install.
		 */

		$this->install();

		$wordpoints_data = get_option( 'wordpoints_data' );

		$this->assertInternalType( 'array', $wordpoints_data );
		$this->assertArrayHasKey( 'version', $wordpoints_data );
		$this->assertEquals( WORDPOINTS_VERSION, $wordpoints_data['version'] );

		$this->assertTableExists( $wpdb->wordpoints_points_logs );
		$this->assertTableExists( $wpdb->wordpoints_points_log_meta );

		/**
		 * Run install tests.
		 *
		 * @since 1.0.1
		 *
		 * @param WordPoints_Uninstall_Test $testcase The current instance.
		 */
		do_action( 'wordpoints_install_tests', $this );
	}
}

// end of file /tests/phpunit/tests/uninstall.php
