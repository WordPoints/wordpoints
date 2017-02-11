<?php

/**
 * Test case for WordPoints_Points_Logs_Viewing_Restriction_Wrapper.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.2.0
 */

/**
 * Tests WordPoints_Points_Logs_Viewing_Restriction_Wrapper.
 *
 * @since 2.2.0
 *
 * @covers WordPoints_Points_Logs_Viewing_Restriction_Wrapper
 */
class WordPoints_Points_Logs_Viewing_Restriction_Wrapper_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.2.0
	 */
	public $shared_fixtures = array(
		'points_log' => array( 'get' => true ),
		'site' => 1,
	);

	/**
	 * Test getting the description when there are no restrictions.
	 *
	 * @since 2.2.0
	 */
	public function test_get_description_none() {

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Wrapper(
			$this->fixtures['points_log'][0]
		);

		$this->assertSame( array(), $restriction->get_description() );
	}

	/**
	 * Test getting the description when the restrictions don't apply.
	 *
	 * @since 2.2.0
	 */
	public function test_get_description_none_apply() {

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Wrapper(
			$this->fixtures['points_log'][0]
			, array(
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$this->fixtures['points_log'][0]
					, true
					, false
				),
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$this->fixtures['points_log'][0]
					, true
					, false
				),
			)
		);

		$this->assertSame( array(), $restriction->get_description() );
	}

	/**
	 * Test getting the description when the restrictions apply but don't restrict.
	 *
	 * @since 2.2.0
	 */
	public function test_get_description_some_apply() {

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Wrapper(
			$this->fixtures['points_log'][0]
			, array(
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$this->fixtures['points_log'][0]
					, true
					, false
				),
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$this->fixtures['points_log'][0]
					, true
				),
			)
		);

		$this->assertCount( 1, $restriction->get_description() );
	}

	/**
	 * Test getting the description when some restrictions say the user can't.
	 *
	 * @since 2.2.0
	 */
	public function test_get_description_some_cant() {

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Wrapper(
			$this->fixtures['points_log'][0]
			, array(
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$this->fixtures['points_log'][0]
					, true
					, false
				),
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$this->fixtures['points_log'][0]
				),
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$this->fixtures['points_log'][0]
					, true
				),
			)
		);

		$this->assertCount( 2, $restriction->get_description() );
	}

	/**
	 * Test that it doesn't apply when there are no restrictions.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_none() {

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Wrapper(
			$this->fixtures['points_log'][0]
		);

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it doesn't apply when the restrictions don't apply.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_none_apply() {

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Wrapper(
			$this->fixtures['points_log'][0]
			, array(
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$this->fixtures['points_log'][0]
					, true
					, false
				),
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$this->fixtures['points_log'][0]
					, true
					, false
				),
			)
		);

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it applies when some of the restrictions apply.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_some_apply() {

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Wrapper(
			$this->fixtures['points_log'][0]
			, array(
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$this->fixtures['points_log'][0]
					, true
					, false
				),
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$this->fixtures['points_log'][0]
					, true
				),
			)
		);

		$this->assertTrue( $restriction->applies() );
	}

	/**
	 * Test that the user can when there are no restrictions.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_none() {

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Wrapper(
			$this->fixtures['points_log'][0]
		);

		$this->assertTrue( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can when the restrictions don't apply.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_none_apply() {

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Wrapper(
			$this->fixtures['points_log'][0]
			, array(
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$this->fixtures['points_log'][0]
					, true
					, false
				),
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$this->fixtures['points_log'][0]
					, true
					, false
				),
			)
		);

		$this->assertTrue( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can when the restrictions apply but don't restrict.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_some_apply() {

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Wrapper(
			$this->fixtures['points_log'][0]
			, array(
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$this->fixtures['points_log'][0]
					, true
					, false
				),
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$this->fixtures['points_log'][0]
					, true
				),
			)
		);

		$this->assertTrue( $restriction->user_can( 0 ) );
	}

	/**
	 * Test that the user can't when some restrictions say they can't.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_some_cant() {

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Wrapper(
			$this->fixtures['points_log'][0]
			, array(
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$this->fixtures['points_log'][0]
					, true
					, false
				),
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$this->fixtures['points_log'][0]
				),
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$this->fixtures['points_log'][0]
					, true
				),
			)
		);

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

		switch_to_blog( $this->fixture_ids['site'][0] );
		$log = $this->factory->wordpoints->points_log->create_and_get();
		restore_current_blog();

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Wrapper(
			$log
		);

		$this->assertTrue( $restriction->user_can( 0 ) );

		$this->assertSame( $current_site_id, get_current_blog_id() );
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

		switch_to_blog( $this->fixture_ids['site'][0] );
		$log = $this->factory->wordpoints->points_log->create_and_get();
		restore_current_blog();

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Wrapper(
			$log
			, array(
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$log
					, true
					, false
				),
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$log
					, true
					, false
				),
			)
		);

		$this->assertTrue( $restriction->user_can( 0 ) );

		$this->assertSame( $current_site_id, get_current_blog_id() );
	}

	/**
	 * Test that the user can when the restrictions apply but don't restrict.
	 *
	 * @since 2.2.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_user_can_some_apply_switching() {

		$current_site_id = get_current_blog_id();

		switch_to_blog( $this->fixture_ids['site'][0] );
		$log = $this->factory->wordpoints->points_log->create_and_get();
		restore_current_blog();

		$mock = new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
			$log
			, true
		);

		$mock->listen_for_site = true;

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Wrapper(
			$log
			, array(
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$log
					, true
					, false
				),
				$mock,
			)
		);

		$this->assertTrue( $restriction->user_can( 0 ) );

		$this->assertSame( $current_site_id, get_current_blog_id() );
		$this->assertSame( $this->fixture_ids['site'][0], $mock->site[0] );
	}

	/**
	 * Test that the user can't when some restrictions say they can't.
	 *
	 * @since 2.2.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_user_can_some_cant_switching() {

		$current_site_id = get_current_blog_id();

		switch_to_blog( $this->fixture_ids['site'][0] );
		$log = $this->factory->wordpoints->points_log->create_and_get();
		restore_current_blog();

		$mock = new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction( $log );

		$mock->listen_for_site = true;

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Wrapper(
			$log
			, array(
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$log
					, true
					, false
				),
				$mock,
				new WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction(
					$log
					, true
				),
			)
		);

		$this->assertFalse( $restriction->user_can( 0 ) );

		$this->assertSame( $current_site_id, get_current_blog_id() );
		$this->assertSame( $this->fixture_ids['site'][0], $mock->site[0] );
	}
}

// EOF
