<?php

/**
 * A test case for the maintenance file.
 *
 * @package WordPoints\Tests
 * @since 2.0.0
 */

/**
 * Tests the maintenance file.
 *
 * @since 2.0.0
 */
class WordPoints_Maintenance_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.0.0
	 */
	public function tearDown() {

		unset( $GLOBALS['upgrading'] );

		parent::tearDown();
	}

	/**
	 * Test that it sets the $upgrading global.
	 *
	 * @since 2.0.0
	 */
	public function test_sets_upgrading() {

		global $upgrading;

		$this->assertNull( $upgrading );

		$upgrading = time();

		$_GET['wordpoints_module_check'] = 'something';
		$_GET['check_module'] = 'something';

		require( WORDPOINTS_DIR . '/includes/maintenance.php' );

		/** @var int $time */
		$this->assertSame( $time - 10 * MINUTE_IN_SECONDS, $upgrading );
	}

	/**
	 * Test that it doesn't modify the value after ten minutes.
	 *
	 * @since 2.0.0
	 */
	public function test_ten_minutes_old() {

		global $upgrading;

		$this->assertNull( $upgrading );

		$raw = time() - 10 * MINUTE_IN_SECONDS - 34;

		$upgrading = $raw;

		$_GET['wordpoints_module_check'] = 'something';
		$_GET['check_module'] = 'something';

		require( WORDPOINTS_DIR . '/includes/maintenance.php' );

		$this->assertSame( $raw, $upgrading );
	}

	/**
	 * Test that it doesn't modify the value after ten minutes.
	 *
	 * @since 2.0.0
	 */
	public function test_not_request() {

		global $upgrading;

		$this->assertNull( $upgrading );

		require( WORDPOINTS_DIR . '/includes/maintenance.php' );

		$this->assertNull( $upgrading );
	}
}

// EOF
