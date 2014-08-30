<?php

/**
 * Test components back end.
 *
 * @package WordPoints\Tests
 * @since 1.0.1
 */

/**
 * Test the WordPoints_Components class.
 *
 * @since 1.0.1
 *
 * @group components
 */
class WordPoints_Components_Test extends WP_UnitTestCase {

	/**
	 * Set up for the tests.
	 *
	 * @sine 1.0.1
	 */
	public function setUp() {

		parent::setUp();

		remove_filter( 'wordpoints_component_active', '__return_true', 100 );
	}

	/**
	 * Clean up after the tests.
	 *
	 * @scince 1.0.1
	 */
	public function tearDown() {

		add_filter( 'wordpoints_component_active', '__return_true', 100 );

		parent::tearDown();
	}

	/**
	 * Test that the instance() method returns and instance of the class.
	 *
	 * @since 1.0.1
	 */
	public function test_instance_returns_instance() {

		$this->assertInstanceOf( 'WordPoints_Components', WordPoints_Components::instance() );
	}

	/**
	 * Test registration functions.
	 *
	 * @since 1.0.1
	 *
	 * @expectedIncorrectUsage WordPoints_Components::register
	 */
	public function test_registration() {

		$components = WordPoints_Components::instance();

		$components->register(
			array(
				'slug' => 'test',
				'name' => 'Test',
			)
		);

		$this->assertArrayHasKey( 'test', $components->get() );
		$this->assertTrue( $components->is_registered( 'test' ) );

		$components->deregister( 'test' );

		$this->assertArrayNotHasKey( 'test', $components->get() );
		$this->assertFalse( $components->is_registered( 'test' ) );
	}

	/**
	 * Test that register() returns false if already registered.
	 *
	 * @since 1.0.1
	 */
	public function test_register_fails_if_already_registered() {

		$this->assertFalse(
			WordPoints_Components::instance()->register(
				array( 'slug' => 'points' )
			)
		);
	}

	/**
	 * Test activation.
	 *
	 * @since 1.0.1
	 *
	 * @expectedIncorrectUsage WordPoints_Components::register
	 */
	public function test_activation() {

		$components = WordPoints_Components::instance();
		$components->register( array( 'slug' => 'test_2', 'name' => 'Test 2' ) );

		$components->activate( 'test_2' );

		$this->assertTrue( $components->is_active( 'test_2' ) );
		$this->assertArrayHasKey( 'test_2', $components->get_active() );
		$this->assertEquals( 1, did_action( 'wordpoints_component_activate-test_2' ) );

		$components->deactivate( 'test_2' );

		$this->assertFalse( $components->is_active( 'test_2' ) );
		$this->assertArrayNotHasKey( 'test_2', $components->get_active() );
		$this->assertEquals( 1, did_action( 'wordpoints_component_deactivate-test_2' ) );
	}

	/**
	 * Test that an unregistered component can't be activated.
	 *
	 * @since 1.0.1
	 */
	public function test_activation_fails_if_not_registered() {

		$this->assertFalse( WordPoints_Components::instance()->activate( 'not_registered' ) );
	}
}

// EOF
