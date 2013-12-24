<?php

/**
 * Integrate Selenium tests with WordPoints.
 *
 * @package WordPoints\Tests
 * @since 1.0.1
 */

/**
 * WordPoints Selenium 2 test case.
 *
 * This class implements WordPoints testing with
 * PHPUnit_Extensions_Selenium2TestCase for the selenium UI testing. These tests
 * make up the selenium group, which can be run using the command `phpunit --group
 * ui`.
 *
 * Extensions of this test case must clean up after themselves - it's not automatic
 * as with WP_UnitTestCase.
 *
 * @since 1.0.1
 */
class WordPoints_Selenium2TestCase extends PHPUnit_Extensions_Selenium2TestCase {

	/**
	 * The capabilities that the testing user should have.
	 *
	 * Set this to change from the default, which is those of a subscriber.
	 *
	 * @since 1.0.1
	 *
	 * @type string|array $user_capabilities
	 */
	protected $user_capabilities;

	/**
	 * The roles that the testing user should have.
	 *
	 * Set this to change from the default, which is subscriber.
	 *
	 * @since 1.0.1
	 *
	 * @type array $user_roles
	 */
	protected $user_roles;

	/**
	 * Set up for the tests.
	 *
	 * We need to use WP_UnitTestCase to do proper set up and tear down. Before we
	 * set up though, we check if we are logged into the site, and if not log in.
	 * It's done before setup so we can possibly have persistent logins.
	 *
	 * @since 1.0.1
	 */
	protected function setUp() {

		$this->setBrowser( WORDPOINTS_TEST_BROWSER );
		$this->setBrowserUrl( get_site_url() );

		$this->set_user_privledges( wordpointstests_ui_user() );

		$this->activate_plugin();

		$this->flush_cache();
	}

	/**
	 * Log in the user on page set up.
	 *
	 * @since 1.0.1
	 */
	public function setUpPage() {

		$this->log_in();
	}

	/**
	 * Clean up after each test.
	 *
	 * @since 1.0.1
	 */
	protected function tearDown() {

		$this->flush_cache();

		$current_plugins = get_option( 'active_plugins' );

		unset( $current_plugins['wordpoints/wordpoints.php'] );

		update_option( 'active_plugins', $current_plugins );
	}

	/**
	 * Log in to the site.
	 *
	 * You'd think that we could just set the cookies up and then we could get around
	 * this. For some reason I couldn't get that to work. The result being that this
	 * was the simplest way to do it. It does sometimes misfire though. Needless to
	 * say, it needs work.
	 *
	 * @since 1.0.1
	 */
	private function log_in() {

		$this->url( wp_login_url() );

		$this->byId( 'user_login' )->value( 'wordpoints_ui_tester' );
		$this->byId( 'user_pass'  )->value( 'wordpoints_ui_tester' );
		$this->clickOnElement( 'wp-submit' );
	}

	/**
	 * Set up the required privledges for the user.
	 *
	 * By default, a regular subscriber is used for the tests.
	 * To use this, you need to add one or both of these properties to your child
	 * class.
	 *
	 * Test that require different privledges must currently go in different test
	 * cases.
	 *
	 * @since 1.0.1
	 *
	 * @param WP_User $user The user object.
	 */
	private function set_user_privledges( $user ) {

		if ( isset( $this->user_roles ) ) {

			foreach ( (array) $this->user_roles as $role ) {

				$user->add_role( $role );
			}

		} else {

			$user->set_role( 'subscriber' );
		}

		if ( isset( $this->user_capabilities ) ) {

			foreach ( $this->user_capabilities as $capability => $granted ) {

				$user->add_cap( $capability, $granted );
			}
		}
	}

	/**
	 * Activate the WordPoints plugin.
	 *
	 * This activates the symlink of the WordPoints plugin on the test suite
	 * WordPress install.
	 *
	 * @since 1.0.1
	 */
	private function activate_plugin() {

		$current_plugins = get_option( 'active_plugins' );

		if ( ! in_array( 'wordpoints/wordpoints.php', $current_plugins ) ) {

			$current_plugins[] = 'wordpoints/wordpoints.php';

			sort( $current_plugins );

			update_option( 'active_plugins', $current_plugins );
		}
	}

	/**
	 * Flush the cache.
	 *
	 * Based on WP_UnitTestCase::flush_cache()
	 *
	 * @since 1.0.1
	 */
	protected function flush_cache() {

		global $wp_object_cache;

		$wp_object_cache->group_ops = array();
		$wp_object_cache->stats = array();
		$wp_object_cache->memcache_debug = array();
		$wp_object_cache->cache = array();

		if ( method_exists( $wp_object_cache, '__remoteset' ) ) {
			$wp_object_cache->__remoteset();
		}

		wp_cache_flush();

		wp_cache_add_global_groups(
			array(
				'users',
				'userlogins',
				'usermeta',
				'user_meta',
				'site-transient',
				'site-options',
				'site-lookup',
				'blog-lookup',
				'blog-details',
				'rss',
				'global-posts',
				'blog-id-cache',
			)
		);

		wp_cache_add_non_persistent_groups( array( 'comment', 'counts', 'plugins' ) );
	}
}

// end of file /tests/phpunit/includes/class-wordpoints-selenium2testcase.php
