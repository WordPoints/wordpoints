<?php

/**
 * Testcase for WordPoints_Module_Updates.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Test WordPoints_Module_Updates.
 *
 * @since 2.4.0
 *
 * @group modules
 *
 * @covers WordPoints_Module_Updates
 */
class WordPoints_Module_Updates_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests constructing the class.
	 *
	 * @since 2.4.0
	 */
	public function test_construct() {

		$versions = array( 'test/test.php' => '1.2.0' );
		$checked = array( 'test/test.php' => '1.1.3' );
		$time = time() - WEEK_IN_SECONDS;

		$updates = new WordPoints_Module_Updates( $versions, $checked, $time );

		$this->assertSame( $time, $updates->get_time_checked() );
		$this->assertSame( $checked, $updates->get_versions_checked() );
		$this->assertSame( $versions, $updates->get_new_versions() );
	}

	/**
	 * Tests constructing the class with the defaults.
	 *
	 * @since 2.4.0
	 */
	public function test_construct_defaults() {

		$updates = new WordPoints_Module_Updates();

		$this->assertSame( time(), $updates->get_time_checked() );
		$this->assertSame( array(), $updates->get_versions_checked() );
		$this->assertSame( array(), $updates->get_new_versions() );
	}

	/**
	 * Tests setting the time checked.
	 *
	 * @since 2.4.0
	 */
	public function test_set_time_checked() {

		$time = time() - WEEK_IN_SECONDS;

		$updates = new WordPoints_Module_Updates();

		$updates->set_time_checked( $time );

		$this->assertSame( $time, $updates->get_time_checked() );
	}

	/**
	 * Tests setting the versions checked.
	 *
	 * @since 2.4.0
	 */
	public function test_set_versions_checked() {

		$versions = array( 'test/test.php' => '1.1.3' );

		$updates = new WordPoints_Module_Updates();

		$updates->set_versions_checked( $versions );

		$this->assertSame( $versions, $updates->get_versions_checked() );
	}

	/**
	 * Tests setting the new versions.
	 *
	 * @since 2.4.0
	 */
	public function test_set_new_versions() {

		$versions = array( 'test/test.php' => '1.2.0' );

		$updates = new WordPoints_Module_Updates();

		$updates->set_new_versions( $versions );

		$this->assertSame( $versions, $updates->get_new_versions() );
	}

	/**
	 * Tests getting the new version of a particular module.
	 *
	 * @since 2.4.0
	 */
	public function test_get_new_version() {

		$module   = 'test/test.php';
		$version  = '1.2.0';

		$updates = new WordPoints_Module_Updates( array( $module => $version ) );

		$this->assertSame( $version, $updates->get_new_version( $module ) );
	}

	/**
	 * Tests getting the new version of a module that didn't have an update.
	 *
	 * @since 2.4.0
	 */
	public function test_get_new_version_not_set() {

		$updates = new WordPoints_Module_Updates();

		$this->assertFalse( $updates->get_new_version( 'test/test.php' ) );
	}

	/**
	 * Tests setting the new version of a particular module.
	 *
	 * @since 2.4.0
	 */
	public function test_set_new_version() {

		$module   = 'test/test.php';
		$version  = '1.2.0';

		$updates = new WordPoints_Module_Updates();

		$updates->set_new_version( $module, $version );

		$this->assertSame( $version, $updates->get_new_version( $module ) );
	}

	/**
	 * Tests filling the object with the data from the database.
	 *
	 * @since 2.4.0
	 */
	public function test_fill() {

		$versions = array( 'test/test.php' => '1.2.0' );
		$checked = array( 'test/test.php' => '1.1.3' );
		$time = time() - WEEK_IN_SECONDS;

		set_site_transient(
			'wordpoints_module_updates'
			, array(
				'new_versions' => $versions,
				'checked_versions' => $checked,
				'time_checked' => $time,
			)
		);

		$updates = new WordPoints_Module_Updates();
		$updates->fill();

		$this->assertSame( $time, $updates->get_time_checked() );
		$this->assertSame( $checked, $updates->get_versions_checked() );
		$this->assertSame( $versions, $updates->get_new_versions() );
	}

	/**
	 * Tests filling the object with the data from the database when it isn't there.
	 *
	 * @since 2.4.0
	 */
	public function test_fill_not_set() {

		$updates = new WordPoints_Module_Updates();
		$updates->fill();

		$this->assertSame( time(), $updates->get_time_checked() );
		$this->assertSame( array(), $updates->get_versions_checked() );
		$this->assertSame( array(), $updates->get_new_versions() );
	}

	/**
	 * Tests filling the object with the data from the database.
	 *
	 * @since 2.4.0
	 */
	public function test_fill_empty() {

		set_site_transient( 'wordpoints_module_updates', array() );

		$updates = new WordPoints_Module_Updates();
		$updates->fill();

		$this->assertSame( time(), $updates->get_time_checked() );
		$this->assertSame( array(), $updates->get_versions_checked() );
		$this->assertSame( array(), $updates->get_new_versions() );
	}

	/**
	 * Tests saving the data in the object to the database.
	 *
	 * @since 2.4.0
	 */
	public function test_save() {

		$versions = array( 'test/test.php' => '1.2.0' );
		$checked = array( 'test/test.php' => '1.1.3' );
		$time = time() - WEEK_IN_SECONDS;

		$updates = new WordPoints_Module_Updates( $versions, $checked, $time );
		$updates->save();

		$this->assertSame(
			array(
				'time_checked' => $time,
				'checked_versions' => $checked,
				'new_versions' => $versions,
			)
			, get_site_transient( 'wordpoints_module_updates' )
		);
	}

	/**
	 * Tests filling the object with the data from the database.
	 *
	 * @since 2.4.0
	 *
	 * @covers ::wordpoints_get_module_updates
	 */
	public function test_get_module_updates() {

		$versions = array( 'test/test.php' => '1.2.0' );
		$checked = array( 'test/test.php' => '1.1.3' );
		$time = time() - WEEK_IN_SECONDS;

		set_site_transient(
			'wordpoints_module_updates'
			, array(
				'new_versions' => $versions,
				'checked_versions' => $checked,
				'time_checked' => $time,
			)
		);

		$updates = wordpoints_get_module_updates();

		$this->assertSame( $time, $updates->get_time_checked() );
		$this->assertSame( $checked, $updates->get_versions_checked() );
		$this->assertSame( $versions, $updates->get_new_versions() );
	}
}

// EOF
