<?php

/**
 * Test case for WordPoints_Uninstaller_Factory_Options.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Uninstaller_Factory_Options.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Uninstaller_Factory_Options
 */
class WordPoints_Uninstaller_Factory_Options_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests getting the uninstallers for a single site.
	 *
	 * @since 2.4.0
	 */
	public function test_get_for_single() {

		add_option( 'test', 'a' );
		add_option( 'tester', 'b' );

		$factory = new WordPoints_Uninstaller_Factory_Options(
			array( 'single' => array( 'test', 'test%' ) )
		);

		$uninstallers = $factory->get_for_single();

		$this->assertCount( 2, $uninstallers );
		$this->assertInstanceOf(
			'WordPoints_Uninstaller_Options_Wildcards'
			, $uninstallers[0]
		);
		$this->assertInstanceOf(
			'WordPoints_Uninstaller_Callback'
			, $uninstallers[1]
		);

		$uninstallers[1]->run();

		$this->assertFalse( get_option( 'test' ) );

		$uninstallers[0]->run();

		$this->assertFalse( get_option( 'tester' ) );
	}

	/**
	 * Tests getting the uninstallers for a site on the network.
	 *
	 * @since 2.4.0
	 */
	public function test_get_for_site() {

		add_option( 'test', 'a' );
		add_option( 'tester', 'b' );

		$factory = new WordPoints_Uninstaller_Factory_Options(
			array( 'site' => array( 'test', 'test%' ) )
		);

		$uninstallers = $factory->get_for_site();

		$this->assertCount( 2, $uninstallers );
		$this->assertInstanceOf(
			'WordPoints_Uninstaller_Options_Wildcards'
			, $uninstallers[0]
		);
		$this->assertInstanceOf(
			'WordPoints_Uninstaller_Callback'
			, $uninstallers[1]
		);

		$uninstallers[1]->run();

		$this->assertFalse( get_option( 'test' ) );

		$uninstallers[0]->run();

		$this->assertFalse( get_option( 'tester' ) );
	}

	/**
	 * Tests getting the uninstallers for the network.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_for_network() {

		add_site_option( 'test', 'a' );
		add_site_option( 'tester', 'b' );

		$factory = new WordPoints_Uninstaller_Factory_Options(
			array( 'network' => array( 'test', 'test%' ) )
		);

		$uninstallers = $factory->get_for_network();

		$this->assertCount( 2, $uninstallers );
		$this->assertInstanceOf(
			'WordPoints_Uninstaller_Options_Wildcards_Network'
			, $uninstallers[0]
		);
		$this->assertInstanceOf(
			'WordPoints_Uninstaller_Callback'
			, $uninstallers[1]
		);

		$uninstallers[1]->run();

		$this->assertFalse( get_site_option( 'test' ) );

		$uninstallers[0]->run();

		$this->assertFalse( get_site_option( 'tester' ) );
	}


	/**
	 * Tests getting the uninstallers when there are no options to uninstall.
	 *
	 * @since 2.4.0
	 */
	public function test_no_options() {

		$factory = new WordPoints_Uninstaller_Factory_Options(
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

		$factory = new WordPoints_Uninstaller_Factory_Options(
			array( 'local' => array( 'test' ) )
		);

		$this->assertCount( 1, $factory->get_for_single() );
		$this->assertCount( 1, $factory->get_for_site() );
		$this->assertCount( 0, $factory->get_for_network() );
	}
}

// EOF
