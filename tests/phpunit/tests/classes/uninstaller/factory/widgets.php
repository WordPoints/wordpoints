<?php

/**
 * Test case for WordPoints_Uninstaller_Factory_Widgets.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Uninstaller_Factory_Widgets.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Uninstaller_Factory_Widgets
 */
class WordPoints_Uninstaller_Factory_Widgets_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests that it returns an uninstaller for a widget.
	 *
	 * @since 2.4.0
	 */
	public function test_for_single() {

		$factory      = new WordPoints_Uninstaller_Factory_Widgets( array( 'text' ) );
		$uninstallers = $factory->get_for_single();

		$widget = $this->factory->wordpoints->widget->create();

		$this->assertCount( 1, $widget->get_settings() );

		$uninstallers[0]->run();

		$this->assertSame( array(), $widget->get_settings() );
	}

	/**
	 * Tests that it returns an uninstaller for a widget.
	 *
	 * @since 2.4.0
	 */
	public function test_for_site() {

		$factory      = new WordPoints_Uninstaller_Factory_Widgets( array( 'text' ) );
		$uninstallers = $factory->get_for_site();

		$widget = $this->factory->wordpoints->widget->create();

		$this->assertCount( 1, $widget->get_settings() );

		$uninstallers[0]->run();

		$this->assertSame( array(), $widget->get_settings() );
	}
}

// EOF
