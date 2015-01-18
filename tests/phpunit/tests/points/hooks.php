<?php

/**
 * A test case for the points hooks API.
 *
 * @package WordPoints\Tests
 * @since 1.2.0
 */

/**
 * Test that the points hooks API functions properly.
 *
 * @since 1.2.0
 *
 * @group points
 * @group points_hooks
 */
class WordPoints_Points_Hooks_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * Test the get and save hooks functions.
	 *
	 * @since 1.2.0
	 */
	function test_get_and_save() {

		$hook = wordpointstests_add_points_hook(
			'wordpoints_registration_points_hook'
			, array( 'points' => 10 )
		);
		$this->assertInstanceOf( 'WordPoints_Registration_Points_Hook', $hook );

		$points_types_hooks = WordPoints_Points_Hooks::get_points_types_hooks();

		$this->assertEquals(
			array( 'points' => array( 0 => $hook->get_id() ) )
			, $points_types_hooks
		);

		$this->assertEquals( $points_types_hooks, get_option( 'wordpoints_points_types_hooks' ) );

		WordPoints_Points_Hooks::save_points_types_hooks( array() );

		$this->assertEquals( array(), get_option( 'wordpoints_points_types_hooks' ) );

		// Network mode.
		WordPoints_Points_Hooks::set_network_mode( true );

		$hook = wordpointstests_add_points_hook(
			'wordpoints_registration_points_hook'
			, array( 'points' => 10 )
		);
		$this->assertInstanceOf( 'WordPoints_Registration_Points_Hook', $hook );

		$points_types_hooks = WordPoints_Points_Hooks::get_points_types_hooks();

		$this->assertEquals(
			array( 'points' => array( 0 => $hook->get_id() ) )
			, $points_types_hooks
		);

		$this->assertEquals( $points_types_hooks, get_site_option( 'wordpoints_points_types_hooks' ) );

		WordPoints_Points_Hooks::save_points_types_hooks( array() );

		$this->assertEquals( array(), get_site_option( 'wordpoints_points_types_hooks' ) );

		WordPoints_Points_Hooks::set_network_mode( false );

		WordPoints_Points_Hooks::save_points_types_hooks( $points_types_hooks );

		$this->assertEquals( $points_types_hooks, WordPoints_Points_Hooks::get_points_types_hooks() );
	}

	/**
	 * Test that awarded points include network and standard hooks.
	 *
	 * @since 1.2.0
	 */
	public function test_network_and_standard_hooks_fired() {

		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Multisite must be enabled.' );
			return;
		}

		// Set up.
		WordPoints_Points_Hooks::set_network_mode( true );

		$hook = wordpointstests_add_points_hook(
			'wordpoints_registration_points_hook'
			, array( 'points' => 10 )
		);
		$this->assertInstanceOf( 'WordPoints_Registration_Points_Hook', $hook );

		$hook_1_number = $hook->get_number();

		WordPoints_Points_Hooks::set_network_mode( false );

		$hook = wordpointstests_add_points_hook(
			'wordpoints_registration_points_hook'
			, array( 'points' => 10 )
		);
		$this->assertInstanceOf( 'WordPoints_Registration_Points_Hook', $hook );

		$hook_2_number = $hook->get_number();

		// Test retrieving all instances.
		$instances = array( $hook_2_number => array( 'points' => 10 ) );
		$instances[ 'network_' . $hook_1_number ] = array( 'points' => 10 );

		$this->assertEquals( $instances, $hook->get_instances() );

		// Standard instances only.
		$this->assertEquals(
			array( $hook_2_number => array( 'points' => 10 ) )
			, $hook->get_instances( 'standard' )
		);

		// Network instances only.
		$this->assertEquals(
			array( $hook_1_number => array( 'points' => 10 ) )
			, $hook->get_instances( 'network' )
		);

		// Make sure points are awarded.
		$user_id = $this->factory->user->create();

		$this->assertEquals( 20, wordpoints_get_points( $user_id, 'points' ) );

	} // public function test_network_and_standard_hooks_fired()

	/**
	 * Test getting the description of a hook.
	 *
	 * @since 1.4.0
	 */
	public function test_get_hook_description() {

		$hook = wordpointstests_add_points_hook(
			'wordpoints_registration_points_hook'
			, array( 'points' => 10 )
		);

		// Description should be the default description of the hook.
		$this->assertEquals( $hook->get_option( 'description' ), $hook->get_description() );

		// Now set our own custom description.
		$hook->update_callback( array( 'points' => 10, '_description' => 'Test.' ), 1 );

		// The custom description should be returned.
		$this->assertEquals( 'Test.', $hook->get_description() );
	}

	/**
	 * Test uninstalling points hook types.
	 *
	 * @since 1.7.0
	 */
	public function test_hook_type_uninstall() {

		wordpointstests_add_points_hook( 'wordpoints_post_points_hook' );

		WordPoints_Points_Hooks::uninstall_hook_types(
			'wordpoints_post_points_hook'
		);

		$hook_type = WordPoints_Points_Hooks::get_handler_by_id_base(
			'wordpoints_post_points_hook'
		);

		$this->assertEmpty( $hook_type->get_instances() );

		// Try uninstalling multiple post types.
		wordpointstests_add_points_hook( 'wordpoints_post_points_hook' );
		wordpointstests_add_points_hook( 'wordpoints_post_delete_points_hook' );

		WordPoints_Points_Hooks::uninstall_hook_types(
			array(
				'wordpoints_post_points_hook',
				'wordpoints_post_delete_points_hook',
			)
		);

		$hook_type = WordPoints_Points_Hooks::get_handler_by_id_base(
			'wordpoints_post_points_hook'
		);

		$this->assertEmpty( $hook_type->get_instances() );
	}

	/**
	 * Test uninstalling points hook types on multisite.
	 *
	 * @since 1.7.0
	 */
	public function test_multisite_hook_type_uninstall() {

		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Multisite is required.' );
		}

		$blog_id = $this->factory->blog->create();

		switch_to_blog( $blog_id );
		wordpointstests_add_points_hook( 'wordpoints_post_points_hook' );
		restore_current_blog();

		WordPoints_Points_Hooks::uninstall_hook_types(
			'wordpoints_post_points_hook'
			, array( $blog_id )
		);

		switch_to_blog( $blog_id );

		$hook_type = WordPoints_Points_Hooks::get_handler_by_id_base(
			'wordpoints_post_points_hook'
		);

		$this->assertEmpty( $hook_type->get_instances() );

		// Try uninstalling multiple post types.
		wordpointstests_add_points_hook( 'wordpoints_post_points_hook' );
		wordpointstests_add_points_hook( 'wordpoints_post_delete_points_hook' );

		restore_current_blog();

		WordPoints_Points_Hooks::uninstall_hook_types(
			array(
				'wordpoints_post_points_hook',
				'wordpoints_post_delete_points_hook',
			)
			, array( $blog_id )
		);

		switch_to_blog( $blog_id );

		$hook_type = WordPoints_Points_Hooks::get_handler_by_id_base(
			'wordpoints_post_points_hook'
		);

		$this->assertEmpty( $hook_type->get_instances() );

		restore_current_blog();
	}

	/**
	 * Test uninstalling points hook types in network mode.
	 *
	 * @since 1.7.0
	 */
	public function test_network_hook_type_uninstall() {

		if ( ! is_wordpoints_network_active() ) {
			$this->markTestSkipped( 'WordPoints must be network active.' );
		}

		WordPoints_Points_Hooks::set_network_mode( true );

		wordpointstests_add_points_hook( 'wordpoints_post_points_hook' );

		WordPoints_Points_Hooks::uninstall_hook_types(
			'wordpoints_post_points_hook'
		);

		$hook_type = WordPoints_Points_Hooks::get_handler_by_id_base(
			'wordpoints_post_points_hook'
		);

		$this->assertEmpty( $hook_type->get_instances() );

		// Try uninstalling multiple post types.
		wordpointstests_add_points_hook( 'wordpoints_post_points_hook' );
		wordpointstests_add_points_hook( 'wordpoints_post_delete_points_hook' );

		WordPoints_Points_Hooks::uninstall_hook_types(
			array(
				'wordpoints_post_points_hook',
				'wordpoints_post_delete_points_hook',
			)
		);

		$hook_type = WordPoints_Points_Hooks::get_handler_by_id_base(
			'wordpoints_post_points_hook'
		);

		$this->assertEmpty( $hook_type->get_instances() );
	}

	/**
	 * Test get_option() and set_option().
	 *
	 * @since 1.9.0
	 */
	public function test_get_and_set_option() {

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base(
			'wordpoints_post_points_hook'
		);

		$hook->set_option( 'testing', __METHOD__ );
		$this->assertEquals( __METHOD__, $hook->get_option( 'testing' ) );
	}

	/**
	 * Test that get_option() returns null if the option isn't set.
	 *
	 * @since 1.9.0
	 */
	public function test_get_nonexistant_option() {

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base(
			'wordpoints_post_points_hook'
		);

		$this->assertEquals( null, $hook->get_option( __METHOD__ ) );
	}

	/**
	 * Test get_options() and set_options().
	 *
	 * @since 1.9.0
	 */
	public function test_get_and_set_options() {

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base(
			'wordpoints_post_points_hook'
		);

		$options = $hook->get_options();

		$this->assertInternalType( 'array', $options );

		$options[ __METHOD__ ] = true;

		$hook->set_options( $options );

		$options = $hook->get_options();

		$this->assertArrayHasKey( __METHOD__, $options );
		$this->assertTrue( $options[ __METHOD__ ] );
	}

	/**
	 * Test that set_option() will always set the options as an array.
	 *
	 * @since 1.9.0
	 */
	public function test_set_options_not_array() {

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base(
			'wordpoints_post_points_hook'
		);

		$options = $hook->get_options();

		$hook->set_options( 'not an array' );

		$this->assertEquals( array(), $hook->get_options() );

		// Put the options back, because this will carry over to other tests.
		$hook->set_options( $options );
	}

	/**
	 * Test get_number() and set_number().
	 *
	 * @since 1.9.0
	 */
	public function test_get_and_set_number() {

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base(
			'wordpoints_post_points_hook'
		);

		$hook->set_number( 4 );

		$this->assertEquals( 4, $hook->get_number() );
		$this->assertInternalType( 'int', $hook->get_number() );
	}

	/**
	 * Test that set_number() accepts an ID.
	 *
	 * @since 1.9.0
	 */
	public function test_set_number_by_id() {

		$hook = wordpointstests_add_points_hook( 'wordpoints_post_points_hook' );

		$id = $hook->get_id();

		$hook->set_number( $id );

		$this->assertEquals( $hook->get_number_by_id( $id ), $hook->get_number() );
		$this->assertInternalType( 'int', $hook->get_number() );
	}

	/**
	 * Test that get_number() returns false when no number is set.
	 *
	 * @since 1.9.0
	 */
	public function test_get_number_false_if_none() {

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base(
			'wordpoints_post_points_hook'
		);

		$hook->set_number( 'blah' );

		$this->assertFalse( $hook->get_number() );
	}
}

// EOF
