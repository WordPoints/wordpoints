<?php

/**
 * Test case for WordPoints_Uninstaller_Factory_Admin_Screens_With_List_Tables.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Uninstaller_Factory_Admin_Screens_With_List_Tables.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Uninstaller_Factory_Admin_Screens_With_List_Tables
 */
class WordPoints_Uninstaller_Factory_Admin_Screens_With_List_Tables_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests getting the uninstaller for a single site.
	 *
	 * @since 2.4.0
	 *
	 * @dataProvider data_provider_parents
	 *
	 * @param string $parent The parent screen.
	 */
	public function test_get_for_single( $parent = null ) {

		$args = array();

		if ( isset( $parent ) ) {
			$args['parent'] = $parent;
		} else {
			$parent = 'wordpoints';
		}

		$screen_id = 'screen_id';

		$factory = new WordPoints_Uninstaller_Factory_Admin_Screens_With_List_Tables(
			array( 'single' => array( $screen_id => $args ) )
		);

		$uninstallers = $factory->get_for_single();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_Metadata', $uninstallers[0] );

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page", 'test' );

		$uninstallers[0]->run();

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page" )
		);
	}

	/**
	 * Data provider for parent screens.
	 *
	 * @since 2.4.0
	 *
	 * @return string[][] The list of parent screens.
	 */
	public function data_provider_parents() {
		return array(
			'default'   => array(),
			'custom'    => array( 'parent_screen' ),
			'no_parent' => array( 'toplevel' ),
		);
	}

	/**
	 * Tests that the extensions screen receives special handling.
	 *
	 * @since 2.4.0
	 *
	 * @dataProvider data_provider_extensions_screen_id
	 *
	 * @param string $screen_id The ID of the extensions screen.
	 */
	public function test_get_for_single_extensions_screen( $screen_id ) {

		$parent = 'wordpoints';

		$factory = new WordPoints_Uninstaller_Factory_Admin_Screens_With_List_Tables(
			array( 'single' => array( $screen_id => array() ) )
		);

		$uninstallers = $factory->get_for_single();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_Metadata', $uninstallers[0] );

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page", 'test' );

		$uninstallers[0]->run();

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page" )
		);
	}

	/**
	 * Data provider for extension screen IDs.
	 *
	 * @since 2.4.0
	 *
	 * @return string[][] The list of extension screen IDs.
	 */
	public function data_provider_extensions_screen_id() {
		return array(
			'extensions' => array( 'wordpoints_extensions' ),
			'modules'    => array( 'wordpoints_modules' ),
		);
	}

	/**
	 * Tests getting the uninstaller for sites on the network.
	 *
	 * @since 2.4.0
	 */
	public function test_get_for_network_site() {

		$parent    = 'wordpoints';
		$screen_id = 'screen_id';

		$factory = new WordPoints_Uninstaller_Factory_Admin_Screens_With_List_Tables(
			array( 'site' => array( $screen_id => array() ) )
		);

		$uninstallers = $factory->get_for_network();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_Metadata', $uninstallers[0] );

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page", 'test' );
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}-networkcolumnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_network_per_page", 'test' );

		$uninstallers[0]->run();

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page" )
		);
		$this->assertSame(
			array( 'test' )
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}-networkcolumnshidden" )
		);
		$this->assertSame(
			array( 'test' )
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_network_per_page" )
		);
	}

	/**
	 * Tests that the extensions screen receives special handling.
	 *
	 * @since 2.4.0
	 *
	 * @dataProvider data_provider_extensions_screen_id
	 *
	 * @param string $screen_id The ID of the extensions screen.
	 */
	public function test_get_for_network_site_extensions_screen( $screen_id ) {

		$parent = 'toplevel';

		$factory = new WordPoints_Uninstaller_Factory_Admin_Screens_With_List_Tables(
			array( 'site' => array( $screen_id => array() ) )
		);

		$uninstallers = $factory->get_for_network();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_Metadata', $uninstallers[0] );

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page", 'test' );

		$uninstallers[0]->run();

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page" )
		);
	}

	/**
	 * Tests getting the uninstaller for the network.
	 *
	 * @since 2.4.0
	 *
	 * @dataProvider data_provider_parents
	 *
	 * @param string $parent The parent screen.
	 */
	public function test_get_for_network( $parent = null ) {

		$args = array();

		if ( isset( $parent ) ) {
			$args['parent'] = $parent;
		} else {
			$parent = 'wordpoints';
		}

		$screen_id = 'screen_id';

		$factory = new WordPoints_Uninstaller_Factory_Admin_Screens_With_List_Tables(
			array( 'network' => array( $screen_id => $args ) )
		);

		$uninstallers = $factory->get_for_network();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_Metadata', $uninstallers[0] );

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page", 'test' );
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}-networkcolumnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_network_per_page", 'test' );

		$uninstallers[0]->run();

		$this->assertSame(
			array( 'test' )
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden" )
		);
		$this->assertSame(
			array( 'test' )
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_per_page" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}-networkcolumnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_network_per_page" )
		);
	}

	/**
	 * Tests that the extensions screen receives special handling.
	 *
	 * @since 2.4.0
	 *
	 * @dataProvider data_provider_extensions_screen_id
	 *
	 * @param string $screen_id The ID of the extensions screen.
	 */
	public function test_get_for_network_extensions_screen( $screen_id ) {

		$parent = 'wordpoints';

		$factory = new WordPoints_Uninstaller_Factory_Admin_Screens_With_List_Tables(
			array( 'network' => array( $screen_id => array() ) )
		);

		$uninstallers = $factory->get_for_network();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_Metadata', $uninstallers[0] );

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}-networkcolumnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_network_per_page", 'test' );

		$uninstallers[0]->run();

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}-networkcolumnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_network_per_page" )
		);
	}

	/**
	 * Tests uninstalling list tables with custom options.
	 *
	 * @since 2.4.0
	 */
	public function test_custom_options() {

		$parent    = 'wordpoints';
		$screen_id = 'screen_id';

		$factory = new WordPoints_Uninstaller_Factory_Admin_Screens_With_List_Tables(
			array(
				'single' => array(
					$screen_id => array( 'options' => array( 'option' ) ),
				),
			)
		);

		$uninstallers = $factory->get_for_single();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_Metadata', $uninstallers[0] );

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden", 'test' );
		add_user_meta( $user_id, "{$parent}_page_{$screen_id}_option", 'test' );

		$uninstallers[0]->run();

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "manage{$parent}_page_{$screen_id}columnshidden" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "{$parent}_page_{$screen_id}_option" )
		);
	}

	/**
	 * Tests getting the uninstallers when there are no screens to uninstall.
	 *
	 * @since 2.4.0
	 */
	public function test_no_screens() {

		$factory = new WordPoints_Uninstaller_Factory_Admin_Screens_With_List_Tables(
			array( 'single' => array() )
		);

		$uninstallers = $factory->get_for_single();

		$this->assertSame( array(), $uninstallers );
	}

	/**
	 * Tests that it maps context shortcuts.
	 *
	 * @since 2.4.0
	 */
	public function test_maps_shortcuts() {

		$factory = new WordPoints_Uninstaller_Factory_Admin_Screens_With_List_Tables(
			array( 'local' => array( 'test' => array() ) )
		);

		$this->assertCount( 1, $factory->get_for_single() );
		$this->assertCount( 1, $factory->get_for_network() );
	}

	/**
	 * Tests that it merges the site and network contexts.
	 *
	 * @since 2.4.0
	 */
	public function test_run_for_network_site_and_network() {

		$factory = new WordPoints_Uninstaller_Factory_Admin_Screens_With_List_Tables(
			array(
				'site'    => array( 'test' => array() ),
				'network' => array( 'other' => array() ),
			)
		);

		$this->assertCount( 0, $factory->get_for_single() );
		$this->assertCount( 2, $factory->get_for_network() );
	}
}

// EOF
