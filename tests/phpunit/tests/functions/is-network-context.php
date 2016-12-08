<?php

/**
 * Test case for wordpoints_is_network_context().
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests wordpoints_is_network_context().
 *
 * @since 2.1.0
 *
 * @covers ::wordpoints_is_network_context
 */
class WordPoints_Is_Network_Context_Function_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.1.0
	 */
	protected $backup_globals = array( '_SERVER' );

	/**
	 * Test that it is false when not on multisite.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_not_multisite() {

		$this->assertFalse( wordpoints_is_network_context() );
	}

	/**
	 * Test that it is false by default.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_not() {

		$this->assertFalse( wordpoints_is_network_context() );
	}

	/**
	 * Test that true in the network admin.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_network_admin() {

		$this->set_network_admin();
		$this->assertTrue( wordpoints_is_network_context() );
	}

	/**
	 * Test that true for Ajax requests from the network admin.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_network_admin_ajax() {

		$_SERVER['HTTP_REFERER'] = network_admin_url() . '/admin-ajax.php';

		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		$this->assertTrue( wordpoints_is_network_context() );
	}

	/**
	 * Test that true in the network admin.
	 *
	 * @since 2.1.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_has_filter() {

		$filter = new WordPoints_PHPUnit_Mock_Filter( true );
		add_filter( 'wordpoints_is_network_context', array( $filter, 'filter' ) );

		$this->assertTrue( wordpoints_is_network_context() );

		$this->assertEquals( 1, $filter->call_count );
		$this->assertEquals( array( false ), $filter->calls[0] );
	}
}

// EOF
