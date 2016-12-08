<?php

/**
 * Test case for WordPoints_Points_Logs_Viewing_Restrictions.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.2.0
 */

/**
 * Tests WordPoints_Points_Logs_Viewing_Restrictions.
 *
 * @since 2.2.0
 *
 * @covers WordPoints_Points_Logs_Viewing_Restrictions
 * @covers WordPoints_Class_Registry_Children
 */
class WordPoints_Points_Logs_Viewing_Restrictions_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.2.0
	 */
	public $shared_fixtures = array(
		'site' => 1,
		'user' => 1,
		'points_log' => array(
			array( 'get' => true ),
			array(
				'get' => true,
				'args' => array( 'blog_id' => '$fixture_ids[site][0]' ),
			),
		),
	);

	/**
	 * @since 2.2.0
	 */
	public function tearDown() {

		WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction::$sites_construct  = array();
		WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction::$sites            = array();
		WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction::$listen_for_sites = false;

		parent::tearDown();
	}

	/**
	 * Test that it doesn't apply when there are no restrictions.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_none() {

		$this->mock_apps();

		$restrictions = new WordPoints_Points_Logs_Viewing_Restrictions();

		$restriction = $restrictions->get_restriction( $this->fixtures['points_log'][0] );

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it doesn't apply when the restrictions don't apply.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_none_apply() {

		$this->mock_apps();

		/** @var WordPoints_Points_Logs_Viewing_Restrictions $restrictions */
		$restrictions = wordpoints_component( 'points' )
			->get_sub_app( 'logs' )
			->get_sub_app( 'viewing_restrictions' );

		$restrictions->register(
			'test'
			, 'test_1'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Not_Applicable'
		);

		$restrictions->register(
			'test'
			, 'test_2'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Not_Applicable'
		);

		$restriction = $restrictions->get_restriction( $this->fixtures['points_log'][0] );

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it applies when some of the restrictions apply.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_some_apply() {

		$this->mock_apps();

		/** @var WordPoints_Points_Logs_Viewing_Restrictions $restrictions */
		$restrictions = wordpoints_component( 'points' )
			->get_sub_app( 'logs' )
			->get_sub_app( 'viewing_restrictions' );

		$restrictions->register(
			'test'
			, 'test_1'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Not_Applicable'
		);

		$restrictions->register(
			'test'
			, 'test_2'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Applicable'
		);

		$restriction = $restrictions->get_restriction( $this->fixtures['points_log'][0] );

		$this->assertTrue( $restriction->applies() );
	}

	/**
	 * Test that it applies when some of the top-level restrictions apply.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_all_apply() {

		$this->mock_apps();

		/** @var WordPoints_Points_Logs_Viewing_Restrictions $restrictions */
		$restrictions = wordpoints_component( 'points' )
			->get_sub_app( 'logs' )
			->get_sub_app( 'viewing_restrictions' );

		$restrictions->register(
			'test'
			, 'test_1'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Not_Applicable'
		);

		$restrictions->register(
			'all'
			, 'test_2'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Applicable'
		);

		$restriction = $restrictions->get_restriction( $this->fixtures['points_log'][0] );

		$this->assertTrue( $restriction->applies() );
	}

	/**
	 * Test that the user can when there are no restrictions.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_none() {

		$this->mock_apps();

		$restrictions = new WordPoints_Points_Logs_Viewing_Restrictions( 'test' );

		$restriction = $restrictions->get_restriction( $this->fixtures['points_log'][0] );

		$this->assertTrue( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can when the restrictions don't apply.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_none_apply() {

		$this->mock_apps();

		/** @var WordPoints_Points_Logs_Viewing_Restrictions $restrictions */
		$restrictions = wordpoints_component( 'points' )
			->get_sub_app( 'logs' )
			->get_sub_app( 'viewing_restrictions' );

		$restrictions->register(
			'test'
			, 'test_1'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Not_Applicable'
		);

		$restrictions->register(
			'test'
			, 'test_2'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Not_Applicable'
		);

		$restriction = $restrictions->get_restriction( $this->fixtures['points_log'][0] );

		$this->assertTrue( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can when some of the restrictions apply.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_some_apply() {

		$this->mock_apps();

		/** @var WordPoints_Points_Logs_Viewing_Restrictions $restrictions */
		$restrictions = wordpoints_component( 'points' )
			->get_sub_app( 'logs' )
			->get_sub_app( 'viewing_restrictions' );

		$restrictions->register(
			'test'
			, 'test_1'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Not_Applicable'
		);

		$restrictions->register(
			'test'
			, 'test_2'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Applicable'
		);

		$restriction = $restrictions->get_restriction( $this->fixtures['points_log'][0] );

		$this->assertTrue( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can't when some of the restrictions apply to them.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_some_restricted() {

		$this->mock_apps();

		/** @var WordPoints_Points_Logs_Viewing_Restrictions $restrictions */
		$restrictions = wordpoints_component( 'points' )
			->get_sub_app( 'logs' )
			->get_sub_app( 'viewing_restrictions' );

		$restrictions->register(
			'test'
			, 'test_1'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Not_Applicable'
		);

		$restrictions->register(
			'test'
			, 'test_2'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction'
		);

		$restriction = $restrictions->get_restriction( $this->fixtures['points_log'][0] );

		$this->assertFalse( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can when some of the top-level restrictions apply.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_all_apply() {

		$this->mock_apps();

		/** @var WordPoints_Points_Logs_Viewing_Restrictions $restrictions */
		$restrictions = wordpoints_component( 'points' )
			->get_sub_app( 'logs' )
			->get_sub_app( 'viewing_restrictions' );

		$restrictions->register(
			'test'
			, 'test_1'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Not_Applicable'
		);

		$restrictions->register(
			'all'
			, 'test_2'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Applicable'
		);

		$restriction = $restrictions->get_restriction( $this->fixtures['points_log'][0] );

		$this->assertTrue( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can when some of the top-level restrictions apply.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_all_restricted() {

		$this->mock_apps();

		/** @var WordPoints_Points_Logs_Viewing_Restrictions $restrictions */
		$restrictions = wordpoints_component( 'points' )
			->get_sub_app( 'logs' )
			->get_sub_app( 'viewing_restrictions' );

		$restrictions->register(
			'test'
			, 'test_1'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Not_Applicable'
		);

		$restrictions->register(
			'all'
			, 'test_2'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction'
		);

		$restriction = $restrictions->get_restriction( $this->fixtures['points_log'][0] );

		$this->assertFalse( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can when there are no restrictions.
	 *
	 * @since 2.2.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_user_can_none_switching() {

		$current_site_id = get_current_blog_id();

		$restrictions = new WordPoints_Points_Logs_Viewing_Restrictions( 'test' );

		$restriction = $restrictions->get_restriction( $this->fixtures['points_log'][1] );

		$this->assertTrue( $restriction->user_can( 0 ) );

		$this->assertEquals( $current_site_id, get_current_blog_id() );
	}

	/**
	 * Test that the user can when the restrictions don't apply.
	 *
	 * @since 2.2.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_user_can_none_apply_switching() {

		$current_site_id = get_current_blog_id();

		WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction::$listen_for_sites = true;

		$this->mock_apps();

		/** @var WordPoints_Points_Logs_Viewing_Restrictions $restrictions */
		$restrictions = wordpoints_component( 'points' )
			->get_sub_app( 'logs' )
			->get_sub_app( 'viewing_restrictions' );

		$restrictions->register(
			'test'
			, 'test_1'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Not_Applicable'
		);

		$restrictions->register(
			'test'
			, 'test_2'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Not_Applicable'
		);

		$restriction = $restrictions->get_restriction( $this->fixtures['points_log'][1] );

		$this->assertEquals( $current_site_id, get_current_blog_id() );

		$construct_args = array(
			'site_id' => $this->fixture_ids['site'][0],
			'log'     => $this->fixtures['points_log'][1],
		);

		$this->assertEquals(
			array( $construct_args, $construct_args )
			, WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction::$sites_construct
		);

		$this->assertTrue( $restriction->user_can( 0 ) );

		$this->assertEquals(
			array()
			, WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction::$sites
		);

		$this->assertEquals( $current_site_id, get_current_blog_id() );
	}

	/**
	 * Test that the user can when some of the restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_user_can_some_apply_switching() {

		$current_site_id = get_current_blog_id();

		WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction::$listen_for_sites = true;

		$this->mock_apps();

		/** @var WordPoints_Points_Logs_Viewing_Restrictions $restrictions */
		$restrictions = wordpoints_component( 'points' )
			->get_sub_app( 'logs' )
			->get_sub_app( 'viewing_restrictions' );

		$restrictions->register(
			'test'
			, 'test_1'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Not_Applicable'
		);

		$restrictions->register(
			'test'
			, 'test_2'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Applicable'
		);

		$restriction = $restrictions->get_restriction( $this->fixtures['points_log'][1] );

		$this->assertEquals( $current_site_id, get_current_blog_id() );

		$construct_args = array(
			'site_id' => $this->fixture_ids['site'][0],
			'log'     => $this->fixtures['points_log'][1],
		);

		$this->assertEquals(
			array( $construct_args, $construct_args )
			, WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction::$sites_construct
		);

		$this->assertTrue( $restriction->user_can( 0 ) );

		$this->assertEquals(
			array( $this->fixture_ids['site'][0] )
			, WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction::$sites
		);

		$this->assertEquals( $current_site_id, get_current_blog_id() );
	}

	/**
	 * Test that the user can't when some of the restrictions apply to them.
	 *
	 * @since 2.2.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_user_can_some_restricted_switching() {

		$current_site_id = get_current_blog_id();

		WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction::$listen_for_sites = true;

		$this->mock_apps();

		/** @var WordPoints_Points_Logs_Viewing_Restrictions $restrictions */
		$restrictions = wordpoints_component( 'points' )
			->get_sub_app( 'logs' )
			->get_sub_app( 'viewing_restrictions' );

		$restrictions->register(
			'test'
			, 'test_1'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Not_Applicable'
		);

		$restrictions->register(
			'test'
			, 'test_2'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction'
		);

		$restriction = $restrictions->get_restriction( $this->fixtures['points_log'][1] );

		$this->assertEquals( $current_site_id, get_current_blog_id() );

		$construct_args = array(
			'site_id' => $this->fixture_ids['site'][0],
			'log'     => $this->fixtures['points_log'][1],
		);

		$this->assertEquals(
			array( $construct_args, $construct_args )
			, WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction::$sites_construct
		);

		$this->assertFalse( $restriction->user_can( 0 ) );

		$this->assertEquals(
			array( $this->fixture_ids['site'][0] )
			, WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction::$sites
		);

		$this->assertEquals( $current_site_id, get_current_blog_id() );
	}

	/**
	 * Test that the user can when some of the top-level restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_user_can_all_apply_switching() {

		$current_site_id = get_current_blog_id();

		WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction::$listen_for_sites = true;

		$this->mock_apps();

		/** @var WordPoints_Points_Logs_Viewing_Restrictions $restrictions */
		$restrictions = wordpoints_component( 'points' )
			->get_sub_app( 'logs' )
			->get_sub_app( 'viewing_restrictions' );

		$restrictions->register(
			'test'
			, 'test_1'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Not_Applicable'
		);

		$restrictions->register(
			'all'
			, 'test_2'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Applicable'
		);

		$restriction = $restrictions->get_restriction( $this->fixtures['points_log'][1] );

		$this->assertEquals( $current_site_id, get_current_blog_id() );

		$construct_args = array(
			'site_id' => $this->fixture_ids['site'][0],
			'log'     => $this->fixtures['points_log'][1],
		);

		$this->assertEquals(
			array( $construct_args, $construct_args )
			, WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction::$sites_construct
		);

		$this->assertTrue( $restriction->user_can( 0 ) );

		$this->assertEquals(
			array( $this->fixture_ids['site'][0] )
			, WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction::$sites
		);

		$this->assertEquals( $current_site_id, get_current_blog_id() );
	}

	/**
	 * Test that the user can when some of the top-level restrictions apply.
	 *
	 * @since 2.2.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_user_can_all_restricted_switching() {

		$current_site_id = get_current_blog_id();

		WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction::$listen_for_sites = true;

		$this->mock_apps();

		/** @var WordPoints_Points_Logs_Viewing_Restrictions $restrictions */
		$restrictions = wordpoints_component( 'points' )
			->get_sub_app( 'logs' )
			->get_sub_app( 'viewing_restrictions' );

		$restrictions->register(
			'test'
			, 'test_1'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction_Not_Applicable'
		);

		$restrictions->register(
			'all'
			, 'test_2'
			, 'WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction'
		);

		$restriction = $restrictions->get_restriction( $this->fixtures['points_log'][1] );

		$this->assertEquals( $current_site_id, get_current_blog_id() );

		$construct_args = array(
			'site_id' => $this->fixture_ids['site'][0],
			'log'     => $this->fixtures['points_log'][1],
		);

		$this->assertEquals(
			array( $construct_args, $construct_args )
			, WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction::$sites_construct
		);

		$this->assertFalse( $restriction->user_can( 0 ) );

		$this->assertEquals(
			array( $this->fixture_ids['site'][0] )
			, WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction::$sites
		);

		$this->assertEquals( $current_site_id, get_current_blog_id() );
	}

	/**
	 * Test that the user can view the points log by default.
	 *
	 * @since 2.2.0
	 */
	public function test_can_view_by_default() {

		$log = $this->fixtures['points_log'][0];
		$restrictions = new WordPoints_Points_Logs_Viewing_Restrictions;

		$this->assertTrue(
			$restrictions->apply_legacy_filters( $this->fixture_ids['user'][0], $log )
		);
	}

	/**
	 * Test that it calls the proper filters.
	 *
	 * @since 2.2.0
	 *
	 * @expectedDeprecated wordpoints_user_can_view_points_log
	 */
	public function test_calls_generic_filter() {

		$log = $this->fixtures['points_log'][0];

		$filter = new WordPoints_PHPUnit_Mock_Filter();
		$filter->add_filter( 'wordpoints_user_can_view_points_log', 10, 6 );

		$restrictions = new WordPoints_Points_Logs_Viewing_Restrictions;

		$this->assertTrue(
			$restrictions->apply_legacy_filters( $this->fixture_ids['user'][0], $log )
		);

		$this->assertEquals(
			array( array( true, $this->fixture_ids['user'][0], $log ) )
			, $filter->calls
		);
	}

	/**
	 * Test that it calls the proper filters.
	 *
	 * @since 2.2.0
	 *
	 * @expectedDeprecated wordpoints_user_can_view_points_log-test
	 */
	public function test_calls_specific_filter() {

		$log = $this->fixtures['points_log'][0];

		$filter = new WordPoints_PHPUnit_Mock_Filter();
		$filter->add_filter(
			"wordpoints_user_can_view_points_log-{$log->log_type}"
			, 10
			, 6
		);

		$restrictions = new WordPoints_Points_Logs_Viewing_Restrictions;

		$this->assertTrue(
			$restrictions->apply_legacy_filters( $this->fixture_ids['user'][0], $log )
		);

		$this->assertEquals(
			array( array( true, $log, $this->fixture_ids['user'][0] ) )
			, $filter->calls
		);
	}

	/**
	 * Test that the value from the first filter is passed to the second.
	 *
	 * @since 2.2.0
	 *
	 * @expectedDeprecated wordpoints_user_can_view_points_log
	 * @expectedDeprecated wordpoints_user_can_view_points_log-test
	 */
	public function test_specific_filter_value_passed_to_generic_filter() {

		$log = $this->fixtures['points_log'][0];

		$generic_filter = new WordPoints_PHPUnit_Mock_Filter();
		$generic_filter->add_filter( 'wordpoints_user_can_view_points_log', 10, 6 );

		$specific_filter = new WordPoints_PHPUnit_Mock_Filter( false );
		$specific_filter->add_filter(
			"wordpoints_user_can_view_points_log-{$log->log_type}"
		);

		$restrictions = new WordPoints_Points_Logs_Viewing_Restrictions;

		$this->assertFalse(
			$restrictions->apply_legacy_filters( $this->fixture_ids['user'][0], $log )
		);

		$this->assertEquals(
			array( array( false, $this->fixture_ids['user'][0], $log ) )
			, $generic_filter->calls
		);
	}

	/**
	 * Test it sets the user as the current user when calling the specific filter.
	 *
	 * @since 2.2.0
	 *
	 * @expectedDeprecated wordpoints_user_can_view_points_log
	 * @expectedDeprecated wordpoints_user_can_view_points_log-test
	 */
	public function test_sets_current_user_when_calling_specific_filter() {

		$log = $this->fixtures['points_log'][0];

		$generic_filter = new WordPoints_PHPUnit_Mock_Filter();
		$generic_filter->listen_for_current_user(
			'wordpoints_user_can_view_points_log'
		);

		$specific_filter = new WordPoints_PHPUnit_Mock_Filter();
		$specific_filter->listen_for_current_user(
			"wordpoints_user_can_view_points_log-{$log->log_type}"
		);

		$current_user = $this->factory->user->create();
		wp_set_current_user( $current_user );

		$restrictions = new WordPoints_Points_Logs_Viewing_Restrictions;

		$this->assertTrue(
			$restrictions->apply_legacy_filters( $this->fixture_ids['user'][0], $log )
		);

		$this->assertEquals( $this->fixture_ids['user'][0], $specific_filter->current_user[0] );
		$this->assertEquals( $current_user, $generic_filter->current_user[0] );
		$this->assertEquals( $current_user, get_current_user_id() );
	}

	/**
	 * Test that it returns the values returned by the filters.
	 *
	 * @since 2.2.0
	 *
	 * @expectedDeprecated wordpoints_user_can_view_points_log
	 */
	public function test_returns_false_if_generic_filter_returns_false() {

		$log = $this->fixtures['points_log'][0];

		$filter = new WordPoints_PHPUnit_Mock_Filter( false );
		$filter->add_filter( 'wordpoints_user_can_view_points_log' );

		$restrictions = new WordPoints_Points_Logs_Viewing_Restrictions;

		$this->assertFalse(
			$restrictions->apply_legacy_filters( $this->fixture_ids['user'][0], $log )
		);
	}

	/**
	 * Test that it returns the values returned by the filters.
	 *
	 * @since 2.2.0
	 *
	 * @expectedDeprecated wordpoints_user_can_view_points_log-test
	 */
	public function test_returns_false_if_specific_filter_returns_false() {

		$log = $this->fixtures['points_log'][0];

		$filter = new WordPoints_PHPUnit_Mock_Filter( false );
		$filter->add_filter(
			"wordpoints_user_can_view_points_log-{$log->log_type}"
		);

		$restrictions = new WordPoints_Points_Logs_Viewing_Restrictions;

		$this->assertFalse(
			$restrictions->apply_legacy_filters( $this->fixture_ids['user'][0], $log )
		);
	}
}

// EOF
