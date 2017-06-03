<?php

/**
 * Testcase for WordPoints_Extension_Updates.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Test WordPoints_Extension_Updates.
 *
 * @since 2.4.0
 *
 * @group extensions
 *
 * @covers WordPoints_Extension_Updates
 */
class WordPoints_Extension_Updates_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests constructing the class.
	 *
	 * @since 2.4.0
	 */
	public function test_construct() {

		$versions = array( 'test/test.php' => '1.2.0' );
		$checked = array( 'test/test.php' => '1.1.3' );
		$time = time() - WEEK_IN_SECONDS;

		$updates = new WordPoints_Extension_Updates( $versions, $checked, $time );

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

		$updates = new WordPoints_Extension_Updates();

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

		$updates = new WordPoints_Extension_Updates();

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

		$updates = new WordPoints_Extension_Updates();

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

		$updates = new WordPoints_Extension_Updates();

		$updates->set_new_versions( $versions );

		$this->assertSame( $versions, $updates->get_new_versions() );
	}

	/**
	 * Tests checking if a particular extension has an update.
	 *
	 * @since 2.4.0
	 */
	public function test_has_update() {

		$extension = 'test/test.php';
		$updates = new WordPoints_Extension_Updates( array( $extension => '1.2.0' ) );

		$this->assertTrue( $updates->has_update( $extension ) );
	}

	/**
	 * Tests checking if a particular extension has an update when it doesn't.
	 *
	 * @since 2.4.0
	 */
	public function test_has_update_not() {

		$updates = new WordPoints_Extension_Updates();

		$this->assertFalse( $updates->has_update( 'test/test.php' ) );
	}

	/**
	 * Tests getting the new version of a particular extension.
	 *
	 * @since 2.4.0
	 */
	public function test_get_new_version() {

		$extension = 'test/test.php';
		$version   = '1.2.0';

		$updates = new WordPoints_Extension_Updates( array( $extension => $version ) );

		$this->assertSame( $version, $updates->get_new_version( $extension ) );
	}

	/**
	 * Tests getting the new version of an extension that didn't have an update.
	 *
	 * @since 2.4.0
	 */
	public function test_get_new_version_not_set() {

		$updates = new WordPoints_Extension_Updates();

		$this->assertFalse( $updates->get_new_version( 'test/test.php' ) );
	}

	/**
	 * Tests setting the new version of a particular extension.
	 *
	 * @since 2.4.0
	 */
	public function test_set_new_version() {

		$extension = 'test/test.php';
		$version   = '1.2.0';

		$updates = new WordPoints_Extension_Updates();

		$updates->set_new_version( $extension, $version );

		$this->assertSame( $version, $updates->get_new_version( $extension ) );
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
			'wordpoints_extension_updates'
			, array(
				'new_versions' => $versions,
				'checked_versions' => $checked,
				'time_checked' => $time,
			)
		);

		$updates = new WordPoints_Extension_Updates();
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

		$updates = new WordPoints_Extension_Updates();
		$updates->fill();

		$this->assertSame( 0, $updates->get_time_checked() );
		$this->assertSame( array(), $updates->get_versions_checked() );
		$this->assertSame( array(), $updates->get_new_versions() );
	}

	/**
	 * Tests filling the object with the data from the database.
	 *
	 * @since 2.4.0
	 */
	public function test_fill_empty() {

		set_site_transient( 'wordpoints_extension_updates', array() );

		$updates = new WordPoints_Extension_Updates();
		$updates->fill();

		$this->assertSame( 0, $updates->get_time_checked() );
		$this->assertSame( array(), $updates->get_versions_checked() );
		$this->assertSame( array(), $updates->get_new_versions() );
	}

	/**
	 * Tests filling the object from the database overwrites the properties.
	 *
	 * @since 2.4.0
	 */
	public function test_fill_empty_already_set() {

		$versions = array( 'test/test.php' => '1.2.0' );
		$checked = array( 'test/test.php' => '1.1.3' );
		$time = time() - WEEK_IN_SECONDS;

		$updates = new WordPoints_Extension_Updates( $versions, $checked, $time );
		$updates->fill();

		$this->assertSame( 0, $updates->get_time_checked() );
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

		$updates = new WordPoints_Extension_Updates( $versions, $checked, $time );
		$updates->save();

		$this->assertSame(
			array(
				'time_checked' => $time,
				'checked_versions' => $checked,
				'new_versions' => $versions,
			)
			, get_site_transient( 'wordpoints_extension_updates' )
		);
	}

	/**
	 * Tests filling the object with the data from the database.
	 *
	 * @since 2.4.0
	 *
	 * @covers ::wordpoints_get_extension_updates
	 */
	public function test_get_extension_updates() {

		$versions = array( 'test/test.php' => '1.2.0' );
		$checked = array( 'test/test.php' => '1.1.3' );
		$time = time() - WEEK_IN_SECONDS;

		set_site_transient(
			'wordpoints_extension_updates'
			, array(
				'new_versions' => $versions,
				'checked_versions' => $checked,
				'time_checked' => $time,
			)
		);

		$updates = wordpoints_get_extension_updates();

		$this->assertSame( $time, $updates->get_time_checked() );
		$this->assertSame( $checked, $updates->get_versions_checked() );
		$this->assertSame( $versions, $updates->get_new_versions() );
	}
}

// EOF
