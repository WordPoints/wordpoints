<?php

/**
 * Test case for WordPoints_Routine.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Routine.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Routine
 */
class WordPoints_Routine_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.4.0
	 */
	protected $shared_fixtures = array( 'site' => 1 );

	/**
	 * Test the basic behaviour of run().
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_run_not_multisite() {

		$routine = $this->getMockForAbstractClass( 'WordPoints_Routine' );
		$routine->expects( $this->once() )->method( 'run_for_single' );
		$routine->expects( $this->never() )->method( 'run_for_network' );
		$routine->expects( $this->never() )->method( 'run_for_site' );

		$routine->run();
	}

	/**
	 * Test the basic behaviour of run() on multisite.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_multisite() {

		$routine = $this->getPartialMockForAbstactClass(
			'WordPoints_Routine'
			, array( 'run_for_sites' )
		);

		$routine->expects( $this->never() )->method( 'run_for_single' );
		$routine->expects( $this->never() )->method( 'run_for_sites' );
		$routine->expects( $this->once() )->method( 'run_for_network' );
		$routine->expects( $this->once() )->method( 'run_for_site' );

		$routine->run();
	}

	/**
	 * Test the basic behaviour of run() when network wide is true.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_network_wide() {

		$routine = $this->getPartialMockForAbstactClass(
			'WordPoints_Routine'
			, array( 'run_for_sites' )
		);

		$routine->expects( $this->never() )->method( 'run_for_single' );
		$routine->expects( $this->once() )->method( 'run_for_sites' );
		$routine->expects( $this->once() )->method( 'run_for_network' );

		$this->make_network_wide( $routine );

		$routine->run();
	}

	/**
	 * Test that run() switches to each site when running network wide.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_network_wide_switches_to_each_site() {

		$site_id = $this->fixture_ids['site'][0];
		$current_site_id = get_current_blog_id();

		$site = new WordPoints_PHPUnit_Mock_Filter();
		$site->callback = 'get_current_blog_id';

		$routine = $this->getMockForAbstractClass( 'WordPoints_Routine' );
		$routine->method( 'run_for_site' )
			->willReturnCallback( array( $site, 'filter' ) );

		$this->make_network_wide( $routine );

		$routine->run();

		$this->assertSame(
			array( $current_site_id, $site_id )
			, $site->callback_results
		);
	}

	/**
	 * Test that run() restores the current site after running network wide.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_network_wide_current_site_restored() {

		global $_wp_switched_stack, $switched;

		$current_site_id = get_current_blog_id();

		$this->assertSame( array(), $_wp_switched_stack );
		$this->assertFalse( $switched );

		$routine = $this->getMockForAbstractClass( 'WordPoints_Routine' );
		$routine->run();

		$this->assertSame( $current_site_id, get_current_blog_id() );
		$this->assertSame( array(), $_wp_switched_stack );
		$this->assertFalse( $switched );
	}

	/**
	 * Test that run() restores the current site after running network wide.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_network_wide_current_site_restored_switched() {

		global $_wp_switched_stack, $switched;

		$previous_site_id = get_current_blog_id();

		$site_id = $this->fixture_ids['site'][0];

		switch_to_blog( $site_id );

		$this->assertSame( array( $previous_site_id ), $_wp_switched_stack );
		$this->assertTrue( $switched );

		$routine = $this->getMockForAbstractClass( 'WordPoints_Routine' );
		$routine->run();

		$this->assertSame( $site_id, get_current_blog_id() );
		$this->assertSame( array( $previous_site_id ), $_wp_switched_stack );
		$this->assertTrue( $switched );

		restore_current_blog();

		$this->assertSame( $previous_site_id, get_current_blog_id() );
		$this->assertSame( array(), $_wp_switched_stack );
		$this->assertFalse( $switched );
	}

	/**
	 * Test how hooks mode is handled during run().
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_run_not_multisite_hooks_mode() {

		$this->mock_apps();

		$hooks = wordpoints_hooks();
		$hooks->set_current_mode( 'test' );

		$single = new WordPoints_PHPUnit_Mock_Filter();
		$single->callback = array( $hooks, 'get_current_mode' );

		$routine = $this->getMockForAbstractClass( 'WordPoints_Routine' );
		$routine->method( 'run_for_single' )
			->willReturnCallback( array( $single, 'filter' ) );

		$routine->run();

		$this->assertSame( array( 'standard' ), $single->callback_results );

		$this->assertSame( 'test', $hooks->get_current_mode() );
	}

	/**
	 * Test how hooks mode is handled during run() on multisite.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_multisite_hooks_mode() {

		$this->mock_apps();

		$hooks = wordpoints_hooks();
		$hooks->set_current_mode( 'test' );

		$site = new WordPoints_PHPUnit_Mock_Filter();
		$site->callback = array( $hooks, 'get_current_mode' );

		$network = clone $site;

		$routine = $this->getMockForAbstractClass( 'WordPoints_Routine' );
		$routine->method( 'run_for_site' )
			->willReturnCallback( array( $site, 'filter' ) );
		$routine->method( 'run_for_network' )
			->willReturnCallback( array( $network, 'filter' ) );

		$routine->run();

		$this->assertSame( array( 'standard' ), $site->callback_results );
		$this->assertSame( array( 'network' ), $network->callback_results );

		$this->assertSame( 'test', $hooks->get_current_mode() );
	}

	/**
	 * Test how hooks mode is handled during run() when network-wide.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_run_network_wide_hooks_mode() {

		$this->mock_apps();

		$hooks = wordpoints_hooks();
		$hooks->set_current_mode( 'test' );

		$site = new WordPoints_PHPUnit_Mock_Filter();
		$site->callback = array( $hooks, 'get_current_mode' );

		$network = clone $site;

		$routine = $this->getMockForAbstractClass( 'WordPoints_Routine' );
		$routine->method( 'run_for_site' )
			->willReturnCallback( array( $site, 'filter' ) );
		$routine->method( 'run_for_network' )
			->willReturnCallback( array( $network, 'filter' ) );

		$this->make_network_wide( $routine );

		$routine->run();

		// An extra site is created for these tests.
		$this->assertSame( array( 'standard', 'standard' ), $site->callback_results );
		$this->assertSame( array( 'network' ), $network->callback_results );

		$this->assertSame( 'test', $hooks->get_current_mode() );
	}

	/**
	 * Makes the routine network wide.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Routine $routine The routine.
	 */
	protected function make_network_wide( $routine ) {

		$reflection          = new ReflectionClass( $routine );
		$reflection_property = $reflection->getProperty( 'network_wide' );
		$reflection_property->setAccessible( true );
		$reflection_property->setValue( $routine, true );
	}
}

// EOF
