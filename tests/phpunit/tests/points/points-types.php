<?php

/**
 * Test points type API.
 *
 * @package WordPoints\Tests\Points
 * @since 1.0.0
 */

/**
 * Points types test case.
 *
 * @since 1.0.0
 *
 * @group points
 */
class WordPoints_Points_Type_Test extends WordPoints_Points_UnitTestCase {

	//
	// wordpoints_is_points_type()
	//

	/**
	 * Test that it returns true when a points type exists.
	 *
	 * @since 1.0.0
	 */
	public function test_returns_true_if_exists() {

		$this->assertTrue( wordpoints_is_points_type( 'points' ) );
	}

	/**
	 * Test that it returns false if a type doesn't exist.
	 *
	 * @since 1.0.0
	 */
	public function test_returns_false_if_nonexistant() {

		$this->assertFalse( wordpoints_is_points_type( 'notype' ) );
	}

	//
	// wordpoints_get_points_type()
	//

	/**
	 * Test that it returns an array of types present.
	 *
	 * @since 1.0.0
	 */
	public function test_get_returns_array_of_types() {

		$this->assertEquals( array( 'points' => $this->points_data ), wordpoints_get_points_types() );
	}

	/**
	 * Test behavior when no types exist.
	 *
	 * @since 1.0.0
	 */
	public function test_get_returns_empty_array_if_none() {

		wordpoints_delete_network_option( 'wordpoints_points_types' );

		$this->assertEquals( array(), wordpoints_get_points_types() );
	}

	//
	// wordpoints_add_points_type()
	//

	/**
	 * Test passing invalid settings.
	 *
	 * @since 1.0.0
	 */
	public function test_add_returns_false_if_invalid_settings() {

		$this->assertFalse( wordpoints_add_points_type( '' ) );
		$this->assertFalse( wordpoints_add_points_type( array() ) );
		$this->assertFalse( wordpoints_add_points_type( array( 'name' => '' ) ) );
	}

	/**
	 * Test creating a new type.
	 *
	 * @since 1.0.0
	 */
	public function test_add_updates_option() {

		$points_type = array( 'name' => 'Credits', 'suffix' => 'cr.' );

		$slug = wordpoints_add_points_type( $points_type );

		$this->assertEquals(
			array( 'points' => $this->points_data, $slug => $points_type )
			, wordpoints_get_network_option( 'wordpoints_points_types' )
		);
	}

	//
	// wordpoints_update_points_type()
	//

	/**
	 * Test updating a points type.
	 *
	 * @since 1.0.0
	 */
	public function test_update_updates_option() {

		$this->points_data['prefix'] = '€';
		wordpoints_update_points_type( 'points', $this->points_data );

		$this->assertEquals(
			array( 'points' => $this->points_data )
			, wordpoints_get_network_option( 'wordpoints_points_types' )
		);
	}

	/**
	 * Test that false is returned if $type is invalid.
	 *
	 * @since 1.0.0
	 */
	public function test_update_false_if_not_type() {

		$this->assertFalse( wordpoints_update_points_type( 'idontexist', array( 'name' => 'iexist' ) ) );
	}

	/**
	 * Test that false is retuned if 'name' isn't set.
	 *
	 * @since 1.0.0
	 */
	public function test_update_false_if_name_missing() {

		$this->assertFalse( wordpoints_update_points_type( 'points', array( 'prefix' => 'P' ) ) );
	}

	//
	// wordpoints_delete_points_type()
	//

	/**
	 * Test that false is returned if the slug isn't registered.
	 *
	 * @since 1.0.0
	 */
	public function test_delete_returns_false_if_nonexistant() {

		$this->assertFalse( wordpoints_delete_points_type( 'notatype' ) );
	}

	/**
	 * Test that it deletes the points type and related stuff.
	 *
	 * @since 1.0.0
	 */
	public function test_points_type_deleted() {

		// Get the meta key now, becuase we can't after the points type is deleted.
		$meta_key = wordpoints_get_points_user_meta_key( 'points' );

		$user_id = $this->factory->user->create();

		wordpoints_add_points( $user_id, 10, 'points', 'test', array( 'a' => 1 ) );
		wordpointstests_add_points_hook( 'wordpoints_registration_points_hook', array( 'points' => 10 ) );

		$was_deleted = wordpoints_delete_points_type( 'points' );

		$this->assertTrue( $was_deleted );
		$this->assertFalse( wordpoints_is_points_type( 'points' ) );
		$this->assertEquals( '', get_user_meta( $user_id, $meta_key, true ) );
		$this->assertEquals( array(), WordPoints_Points_Hooks::get_points_type_hooks( 'points' ) );

		global $wpdb;

		$logs = $wpdb->get_var(
			$wpdb->prepare(
				"
					SELECT COUNT(id)
					FROM {$wpdb->wordpoints_points_logs}
					WHERE `user_id` = %d
				"
				, $user_id
			)
		);

		$this->assertEquals( '0', $logs );

		$meta = $wpdb->get_var(
			"
				SELECT COUNT(meta_id)
				FROM {$wpdb->wordpoints_points_log_meta}
				WHERE `meta_key` = 'a'
			"
		);

		$this->assertEquals( '0', $meta );
	}

	//
	// wordpoints_get_points_type_setting()
	//

	/**
	 * Test that null is returned if the points type doesn't exist.
	 *
	 * @since 1.0.0
	 */
	public function test_null_returned_if_nonexistant_setting() {

		$this->assertEquals( null, wordpoints_get_points_type_setting( 'points', 'image' ) );
	}

	/**
	 * Test retrieval of a single setting.
	 *
	 * @since 1.0.0
	 */
	public function test_returns_setting_value() {

		$this->assertEquals( 'Points', wordpoints_get_points_type_setting( 'points', 'name' ) );
	}
}

// EOF
