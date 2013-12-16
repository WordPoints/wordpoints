<?php

/**
 * Test modules backend.
 *
 * @package WordPoints\Tests
 * @since 1.1.0
 */

/**
 * Test the WordPoints_Modules class.
 *
 * @since 1.1.0 Previously, this was WordPoints_Modules_Test, but it was superceeded.
 *
 * @group modules
 */
class WordPoints_Modules_Legacy_Test extends WP_UnitTestCase {

	/**
	 * Set up for the tests.
	 *
	 * @since 1.0.1
	 */
	public function setUp() {

		parent::setUp();

		remove_filter( 'wordpoints_module_active', '__return_true', 100 );
	}

	/**
	 * Clean up after the tests.
	 *
	 * @scince 1.0.1
	 */
	public function tearDown() {

		add_filter( 'wordpoints_module_active', '__return_true', 100 );

		parent::tearDown();
	}

	/**
	 * Test that the instance() method returns and instance of the class.
	 *
	 * @since 1.0.1
	 *
	 * @expectedDeprecated WordPoints_Modules::instance
	 */
	public function test_instance_returns_instance() {

		$this->assertInstanceOf( 'WordPoints_Modules', WordPoints_Modules::instance() );
	}

	/**
	 * Test registration functions.
	 *
	 * @since 1.0.1
	 *
	 * @expectedDeprecated WordPoints_Modules::instance
	 * @expectedDeprecated WordPoints_Modules::register
	 * @expectedDeprecated WordPoints_Modules::get
	 * @expectedDeprecated WordPoints_Modules::is_registered
	 * @expectedDeprecated WordPoints_Modules::deregister
	 */
	public function test_registration() {

		$modules = WordPoints_Modules::instance();

		$modules->register(
			array(
				'slug'    => 'test_3',
				'name'    => 'Test 3',
				'version' => '0.3-beta-60986740293859',
			)
		);

		$this->assertArrayNotHasKey( 'test_3', $modules->get() );
		$this->assertFalse( $modules->is_registered( 'test_3' ) );
		$this->assertFalse( $modules->deregister( 'test_3' ) );
	}

	/**
	 * Test activation.
	 *
	 * @since 1.0.1
	 *
	 * @expectedDeprecated WordPoints_Modules::instance
	 * @expectedDeprecated WordPoints_Modules::activate
	 * @expectedDeprecated WordPoints_Modules::is_active
	 * @expectedDeprecated WordPoints_Modules::get_active
	 * @expectedDeprecated WordPoints_Modules::deactivate
	 */
	public function test_activation() {

		$modules = WordPoints_Modules::instance();

		$this->assertFalse( $modules->activate( 'test_4' ) );
		$this->assertTrue( $modules->is_active( 'test_4' ) );
		$this->assertArrayNotHasKey( 'test_4', $modules->get_active() );
// TODO		$this->assertEquals( 1, did_action( 'wordpoints_module_activate-test_4' ) );
		$this->assertTrue( $modules->deactivate( 'test_4' ) );
	}

	/**
	 * Test module dir getting.
	 *
	 * @since 1.1.0
	 *
	 * @expectedDeprecated WordPoints_Modules::instance
	 * @expectedDeprecated WordPoints_Modules::get_dir
	 */
	public function test_dir() {

		$this->assertEquals( wordpoints_modules_dir(), WordPoints_Modules::instance()->get_dir() );
	}
}

// end of file /tests/phpunit/tests/modules.php
