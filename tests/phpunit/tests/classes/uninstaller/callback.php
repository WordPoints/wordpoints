<?php

/**
 * Test case for WordPoints_Uninstaller_Callback.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests WordPoints_Uninstaller_Callback.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Uninstaller_Callback
 */
class WordPoints_Uninstaller_Callback_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests that it calls the callback with each item.
	 *
	 * @since 2.4.0
	 */
	public function test_calls_callback() {

		$callback = new WordPoints_PHPUnit_Mock_Filter();

		$uninstaller = new WordPoints_Uninstaller_Callback(
			array( $callback, 'filter' )
			, array( 'a', 'b' )
		);

		$uninstaller->run();

		$this->assertSame(
			array( array( 'a' ), array( 'b' ) )
			, $callback->calls
		);
	}
}

// EOF
