<?php

/**
 * Test case for WordPoints_Multisite_Switched_State.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.2.0
 */

/**
 * Tests WordPoints_Multisite_Switched_State.
 *
 * @since 2.2.0
 *
 * @covers WordPoints_Multisite_Switched_State
 */
class WordPoints_Multisite_Switched_State_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.2.0
	 */
	protected $shared_fixtures = array( 'site' => 2 );

	/**
	 * Test that it restores the current site correctly.
	 *
	 * @since 2.2.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_backup_restore() {

		global $_wp_switched_stack, $switched;

		$current_site_id = get_current_blog_id();

		$this->assertEmpty( $_wp_switched_stack );
		$this->assertEmpty( $switched );

		$ms_switched_state = new WordPoints_Multisite_Switched_State;
		$ms_switched_state->backup();
		$ms_switched_state->restore();

		$this->assertEquals( $current_site_id, get_current_blog_id() );
		$this->assertEmpty( $_wp_switched_stack );
		$this->assertEmpty( $switched );
	}

	/**
	 * Test that it restores the current site correctly if in a switched state.
	 *
	 * @since 2.2.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_switched_backup_restore() {

		global $_wp_switched_stack, $switched;

		$previous_site_id = get_current_blog_id();

		$site_id = $this->fixture_ids['site'][0];

		switch_to_blog( $site_id );

		$this->assertEquals( array( $previous_site_id ), $_wp_switched_stack );
		$this->assertTrue( $switched );

		$ms_switched_state = new WordPoints_Multisite_Switched_State;
		$ms_switched_state->backup();
		$ms_switched_state->restore();

		$this->assertEquals( $site_id, get_current_blog_id() );
		$this->assertEquals( array( $previous_site_id ), $_wp_switched_stack );
		$this->assertTrue( $switched );

		restore_current_blog();

		$this->assertEquals( $previous_site_id, get_current_blog_id() );
		$this->assertEmpty( $_wp_switched_stack );
		$this->assertEmpty( $switched );
	}

	/**
	 * Test that it restores the current site correctly once in a switched state.
	 *
	 * @since 2.2.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_backup_restore_switched() {

		global $_wp_switched_stack, $switched;

		$previous_site_id = get_current_blog_id();

		$site_id = $this->fixture_ids['site'][0];

		$this->assertEmpty( $_wp_switched_stack );
		$this->assertEmpty( $switched );

		$ms_switched_state = new WordPoints_Multisite_Switched_State;
		$ms_switched_state->backup();

		switch_to_blog( $site_id );

		$this->assertEquals( array( $previous_site_id ), $_wp_switched_stack );
		$this->assertTrue( $switched );

		$ms_switched_state->restore();

		$this->assertEquals( $previous_site_id, get_current_blog_id() );
		$this->assertEmpty( $_wp_switched_stack );
		$this->assertEmpty( $switched );
	}

	/**
	 * Test that it restores the current site correctly once in a switched state.
	 *
	 * @since 2.2.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_switched_backup_restore_switched() {

		global $_wp_switched_stack, $switched;

		$previous_site_id = get_current_blog_id();

		$site_id = $this->fixture_ids['site'][0];
		$another_site_id = $this->fixture_ids['site'][1];

		switch_to_blog( $site_id );

		$this->assertEquals( array( $previous_site_id ), $_wp_switched_stack );
		$this->assertTrue( $switched );

		$ms_switched_state = new WordPoints_Multisite_Switched_State;
		$ms_switched_state->backup();

		switch_to_blog( $another_site_id );

		$this->assertEquals(
			array( $previous_site_id, $site_id )
			, $_wp_switched_stack
		);

		$this->assertTrue( $switched );

		$ms_switched_state->restore();

		$this->assertEquals( $site_id, get_current_blog_id() );
		$this->assertEquals( array( $previous_site_id ), $_wp_switched_stack );
		$this->assertTrue( $switched );

		restore_current_blog();

		$this->assertEquals( $previous_site_id, get_current_blog_id() );
		$this->assertEmpty( $_wp_switched_stack );
		$this->assertEmpty( $switched );
	}
}

// EOF
