<?php

/**
 * Test case for the admin menu functions.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests the admin menu functions.
 *
 * @since 2.1.0
 */
class WordPoints_Admin_Menu_Functions_Test extends WordPoints_PHPUnit_TestCase_Admin {

	/**
	 * @since 2.1.0
	 */
	protected $backup_globals = array(
		'submenu',
		'menu',
		'_wp_real_parent_file',
		'_wp_submenu_nopriv',
		'_registered_pages',
		'_parent_pages',
	);

	/**
	 * @since 2.1.0
	 */
	public static function setUpBeforeClass() {

		if ( ! self::$included_files ) {

			/**
			 * WordPoints administration-side code.
			 *
			 * @since 2.1.0
			 */
			require_once( WORDPOINTS_DIR . '/components/points/admin/admin.php' );
		}

		parent::setUpBeforeClass();
	}

	/**
	 * Test the wordpoints_points_admin_menu() function.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_points_admin_menu
	 */
	public function test_wordpoints_points_admin_menu() {

		wordpoints_hooks_register_admin_apps( $this->mock_apps() );

		$this->give_current_user_caps( 'manage_options' );

		wordpoints_points_admin_menu();

		$this->assertAdminSubmenuRegistered( 'wordpoints_points_types' );

		/** @var WordPoints_Admin_Screens $app */
		$app = wordpoints_apps()->get_sub_app( 'admin' )->get_sub_app( 'screen' );

		$this->assertTrue(
			$app->is_registered(
				get_plugin_page_hookname(
					'wordpoints_points_types'
					, wordpoints_get_main_admin_menu()
				)
			)
		);
	}

	/**
	 * Test the wordpoints_points_admin_menu() function.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_points_admin_menu
	 */
	public function test_wordpoints_points_admin_menu_points_hooks() {

		wordpoints_hooks_register_admin_apps( $this->mock_apps() );

		$this->give_current_user_caps( 'manage_options' );

		wordpoints_update_maybe_network_option(
			'wordpoints_legacy_points_hooks_disabled'
			, array_fill_keys(
				array_keys( WordPoints_Points_Hooks::get_handlers() )
				, true
			)
			, is_network_admin()
		);

		wordpoints_points_admin_menu();

		$this->assertAdminSubmenuRegistered( 'wordpoints_points_types' );
		$this->assertAdminSubmenuNotRegistered( 'wordpoints_points_hooks' );
	}

	/**
	 * Test the wordpoints_points_admin_menu() function.
	 *
	 * @since 2.1.0
	 *
	 * @covers ::wordpoints_points_admin_menu
	 */
	public function test_wordpoints_points_admin_menu_points_hooks_enabled() {

		wordpoints_hooks_register_admin_apps( $this->mock_apps() );

		$this->give_current_user_caps( 'manage_options' );

		wordpoints_points_admin_menu();

		$this->assertAdminSubmenuRegistered( 'wordpoints_points_types' );
		$this->assertAdminSubmenuRegistered( 'wordpoints_points_hooks' );
	}

	/**
	 * Assert that a submenu item has been registered for an admin menu.
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug   The slug of the submenu item.
	 * @param string $parent The slug of the parent menu item.
	 */
	protected function assertAdminSubmenuRegistered( $slug, $parent = null ) {

		global $submenu;

		if ( null === $parent ) {
			$parent = wordpoints_get_main_admin_menu();
		}

		$this->assertArrayHasKey( $parent, $submenu );
		$this->assertContains( $slug, wp_list_pluck( $submenu[ $parent ], 2 ) );
	}

	/**
	 * Assert that a submenu item has not been registered for an admin menu.
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug   The slug of the submenu item.
	 * @param string $parent The slug of the parent menu item.
	 */
	protected function assertAdminSubmenuNotRegistered( $slug, $parent = null ) {

		global $submenu;

		if ( null === $parent ) {
			$parent = wordpoints_get_main_admin_menu();
		}

		if ( isset( $submenu[ $parent ] ) ) {
			$this->assertNotContains( $slug, wp_list_pluck( $submenu[ $parent ], 2 ) );
		} else {
			// We run an assertion anyway just so that it is counted.
			$this->assertArrayNotHasKey( $parent, $submenu );
		}
	}
}

// EOF
