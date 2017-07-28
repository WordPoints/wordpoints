<?php

/**
 * Test case for WordPoints_Uninstaller_Factory_Admin_Screens_With_Meta_Boxes.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Uninstaller_Factory_Admin_Screens_With_Meta_Boxes.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Uninstaller_Factory_Admin_Screens_With_Meta_Boxes
 */
class WordPoints_Uninstaller_Factory_Admin_Screens_With_Meta_Boxes_Test extends WordPoints_PHPUnit_TestCase {

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

		$factory = new WordPoints_Uninstaller_Factory_Admin_Screens_With_Meta_Boxes(
			array( 'single' => array( 'screen_id' => $args ) )
		);

		$uninstallers = $factory->get_for_single();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_Metadata', $uninstallers[0] );

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id", 'test' );

		$uninstallers[0]->run();

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id" )
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
			'default' => array(),
			'custom' => array( 'parent_screen' ),
			'no_parent' => array( 'toplevel' ),
		);
	}

	/**
	 * Tests getting the uninstaller for sites on the network.
	 *
	 * @since 2.4.0
	 */
	public function test_get_for_network_site() {

		$parent = 'wordpoints';

		$factory = new WordPoints_Uninstaller_Factory_Admin_Screens_With_Meta_Boxes(
			array( 'site' => array( 'screen_id' => array() ) )
		);

		$uninstallers = $factory->get_for_network();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_Metadata', $uninstallers[0] );

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id-network", 'test' );
		add_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id-network", 'test' );
		add_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id-network", 'test' );

		$uninstallers[0]->run();

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array( 'test' )
			, get_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id-network" )
		);
		$this->assertSame(
			array( 'test' )
			, get_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id-network" )
		);
		$this->assertSame(
			array( 'test' )
			, get_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id-network" )
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

		$factory = new WordPoints_Uninstaller_Factory_Admin_Screens_With_Meta_Boxes(
			array( 'network' => array( 'screen_id' => $args ) )
		);

		$uninstallers = $factory->get_for_network();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_Metadata', $uninstallers[0] );

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id-network", 'test' );
		add_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id-network", 'test' );
		add_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id-network", 'test' );

		$uninstallers[0]->run();

		$this->assertSame(
			array( 'test' )
			, get_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array( 'test' )
			, get_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array( 'test' )
			, get_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id-network" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id-network" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id-network" )
		);
	}

	/**
	 * Tests uninstalling meta boxes with custom options.
	 *
	 * @since 2.4.0
	 */
	public function test_custom_options() {

		$parent = 'wordpoints';

		$factory = new WordPoints_Uninstaller_Factory_Admin_Screens_With_Meta_Boxes(
			array(
				'single' => array(
					'screen_id' => array( 'options' => array( 'option' ) ),
				),
			)
		);

		$uninstallers = $factory->get_for_single();

		$this->assertCount( 1, $uninstallers );
		$this->assertInstanceOf( 'WordPoints_Uninstaller_Metadata', $uninstallers[0] );

		$user_id = $this->factory->user->create();
		add_user_meta( $user_id, "option_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id", 'test' );
		add_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id", 'test' );

		$uninstallers[0]->run();

		$this->assertSame(
			array()
			, get_user_meta( $user_id, "option_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "closedpostboxes_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "metaboxhidden_{$parent}_page_screen_id" )
		);
		$this->assertSame(
			array()
			, get_user_meta( $user_id, "meta-box-order_{$parent}_page_screen_id" )
		);
	}

	/**
	 * Tests getting the uninstallers when there are no screens to uninstall.
	 *
	 * @since 2.4.0
	 */
	public function test_no_screens() {

		$factory = new WordPoints_Uninstaller_Factory_Admin_Screens_With_Meta_Boxes(
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

		$factory = new WordPoints_Uninstaller_Factory_Admin_Screens_With_Meta_Boxes(
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

		$factory = new WordPoints_Uninstaller_Factory_Admin_Screens_With_Meta_Boxes(
			array(
				'site' => array( 'test' => array() ),
				'network' => array( 'other' => array() ),
			)
		);

		$this->assertCount( 0, $factory->get_for_single() );
		$this->assertCount( 2, $factory->get_for_network() );
	}
}

// EOF
