<?php

/**
 * Test case for WordPoints_Uninstaller_Factory.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Uninstaller_Factory.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Uninstaller_Factory
 */
class WordPoints_Uninstaller_Factory_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests that it supports using context shortcuts for the list of uninstallers.
	 *
	 * @since 2.4.0
	 */
	public function test_supports_context_shortcuts() {

		$updater = new WordPoints_Uninstaller_Factory(
			array( 'local' => array( 'WordPoints_PHPUnit_Mock_Object' ) )
		);

		$this->assertCount( 1, $updater->get_for_single() );
		$this->assertCount( 1, $updater->get_for_site() );
		$this->assertCount( 0, $updater->get_for_network() );
	}

	/**
	 * Tests that it supports passing a class name with no args.
	 *
	 * @since 2.4.0
	 */
	public function test_no_args() {

		$updater = new WordPoints_Uninstaller_Factory(
			array( 'single' => array( 'WordPoints_PHPUnit_Mock_Object' ) )
		);

		new WordPoints_PHPUnit_Mock_Object();

		/** @var WordPoints_PHPUnit_Mock_Object[] $routines */
		$routines = $updater->get_for_single();

		$this->assertInstanceOf( 'WordPoints_PHPUnit_Mock_Object', $routines[0] );
		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => array() )
			, $routines[0]->calls[0]
		);
	}

	/**
	 * Tests that it supports passing a class and args.
	 *
	 * @since 2.4.0
	 */
	public function test_with_args() {

		$updater = new WordPoints_Uninstaller_Factory(
			array(
				'single' => array(
					array(
						'class' => 'WordPoints_PHPUnit_Mock_Object',
						'args'  => array( 'a', 'b' ),
					),
				),
			)
		);

		new WordPoints_PHPUnit_Mock_Object();

		/** @var WordPoints_PHPUnit_Mock_Object[] $routines */
		$routines = $updater->get_for_single();

		$this->assertInstanceOf( 'WordPoints_PHPUnit_Mock_Object', $routines[0] );
		$this->assertSame(
			array( 'name' => '__construct', 'arguments' => array( 'a', 'b' ) )
			, $routines[0]->calls[0]
		);
	}
}

// EOF
