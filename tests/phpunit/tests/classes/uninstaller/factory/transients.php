<?php

/**
 * Test case for WordPoints_Uninstaller_Factory_Transients.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Uninstaller_Factory_Transients.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Uninstaller_Factory_Transients
 */
class WordPoints_Uninstaller_Factory_Transients_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests getting the uninstallers for a single site.
	 *
	 * @since 2.4.0
	 */
	public function test_get_for_single() {

		$factory = new WordPoints_Uninstaller_Factory_Transients(
			array( 'single' => array( 'test' ) )
		);

		set_transient( 'test', 'a' );

		$uninstallers = $factory->get_for_single();
		$uninstallers[0]->run();

		$this->assertFalse( get_transient( 'test' ) );
	}

	/**
	 * Tests getting the uninstallers for a site on the network.
	 *
	 * @since 2.4.0
	 */
	public function test_get_for_site() {

		$factory = new WordPoints_Uninstaller_Factory_Transients(
			array( 'site' => array( 'test' ) )
		);

		set_transient( 'test', 'a' );

		$uninstallers = $factory->get_for_site();
		$uninstallers[0]->run();

		$this->assertFalse( get_transient( 'test' ) );
	}

	/**
	 * Tests getting the uninstallers for the network.
	 *
	 * @since 2.4.0
	 */
	public function test_get_for_network() {

		$factory = new WordPoints_Uninstaller_Factory_Transients(
			array( 'network' => array( 'test' ) )
		);

		set_site_transient( 'test', 'a' );

		$uninstallers = $factory->get_for_network();
		$uninstallers[0]->run();

		$this->assertFalse( get_site_transient( 'test' ) );
	}

	/**
	 * Tests getting the uninstallers when there are no transients to uninstall.
	 *
	 * @since 2.4.0
	 */
	public function test_no_transients() {

		$factory = new WordPoints_Uninstaller_Factory_Metadata(
			'user'
			, array( 'single' => array() )
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

		$factory = new WordPoints_Uninstaller_Factory_Metadata(
			'user'
			, array( 'local' => array( 'test' ) )
		);

		$this->assertCount( 1, $factory->get_for_single() );
		$this->assertCount( 1, $factory->get_for_site() );
		$this->assertCount( 0, $factory->get_for_network() );
	}
}

// EOF
