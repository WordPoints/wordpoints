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
class WordPoints_Components_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.4.0
	 */
	public function tearDown() {

		$components = WordPoints_Components::instance();
		$components->deregister( 'test' );
		$components->deregister( 'test_2' );

		parent::tearDown();
	}

	/**
	 * Test that the instance() method returns an instance of the class.
	 *
	 * @since 1.0.1
	 *
	 * @covers WordPoints_Components::instance
	 */
	public function test_instance_returns_instance() {

		$this->assertInstanceOf( 'WordPoints_Components', WordPoints_Components::instance() );
	}

	/**
	 * Test that there is only one instance.
	 *
	 * @since 2.0.0
	 *
	 * @covers WordPoints_Components::instance
	 */
	public function test_instance_returns_one_instance() {

		$this->assertSame( WordPoints_Components::instance(), WordPoints_Components::instance() );
	}

	/**
	 * Test registration functions.
	 *
	 * @since 1.0.1
	 *
	 * @covers WordPoints_Components::register
	 * @covers WordPoints_Components::is_registered
	 */
	public function test_registration() {

		$components = WordPoints_Components::instance();

		$components->register(
			array(
				'slug' => 'test',
				'name' => 'Test',
				'file' => WORDPOINTS_TESTS_DIR . '/data/components/test/test.php',
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
	 *
	 * @covers WordPoints_Components::register
	 */
	public function test_register_fails_if_already_registered() {

		$this->assertFalse(
			WordPoints_Components::instance()->register(
				array(
					'slug' => 'points',
					'name' => 'Points',
					'file' => WORDPOINTS_TESTS_DIR . '/data/components/test/test.php',
				)
			)
		);
	}

	/**
	 * Test that load() registers an installable if active.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Components::register
	 * @covers WordPoints_Components::load
	 */
	public function test_load_registers_installable_if_active() {

		$this->mock_apps();

		$mock = new WordPoints_PHPUnit_Mock_Filter();

		add_filter( 'wordpoints_component_active', '__return_true' );

		$components = WordPoints_Components::instance();
		$components->register(
			array(
				'slug'        => 'test',
				'name'        => 'Test',
				'file'        => WORDPOINTS_TESTS_DIR . '/data/components/test/test.php',
				'installable' => array( $mock, 'filter' ),
			)
		);

		$components->load();

		/** @var WordPoints_Installables_App $installables */
		$installables = wordpoints_apps()->get_sub_app( 'installables' );
		$installables->maybe_update();

		$this->assertSame( 1, $mock->call_count );
	}

	/**
	 * Test that load() doesn't register an installable if inactive.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Components::register
	 * @covers WordPoints_Components::load
	 */
	public function test_register_not_registers_installable_inactive() {

		$this->mock_apps();

		$mock = new WordPoints_PHPUnit_Mock_Filter();

		$components = WordPoints_Components::instance();
		$components->register(
			array(
				'slug'        => 'test',
				'name'        => 'Test',
				'file'        => WORDPOINTS_TESTS_DIR . '/data/components/test/test.php',
				'installable' => array( $mock, 'filter' ),
			)
		);

		$components->load();

		/** @var WordPoints_Installables_App $installables */
		$installables = wordpoints_apps()->get_sub_app( 'installables' );
		$installables->maybe_update();

		$this->assertSame( 0, $mock->call_count );
	}

	/**
	 * Test activation.
	 *
	 * @since 1.0.1
	 *
	 * @covers WordPoints_Components::activate
	 * @covers WordPoints_Components::is_active
	 *
	 * @expectedDeprecated WordPoints_Installables::register
	 * @expectedDeprecated WordPoints_Un_Installer_Base::__construct
	 */
	public function test_activation() {

		$this->mock_apps();

		$components = WordPoints_Components::instance();
		$components->register(
			array(
				'slug'         => 'test_2',
				'name'         => 'Test 2',
				'file'         => WORDPOINTS_TESTS_DIR . '/data/components/test/test.php',
				'un_installer' => WORDPOINTS_TESTS_DIR . '/data/components/test/un-installer.php',
			)
		);

		$components->activate( 'test_2' );

		$this->assertTrue( $components->is_active( 'test_2' ) );
		$this->assertArrayHasKey( 'test_2', $components->get_active() );
		$this->assertSame( 1, did_action( 'wordpoints_component_activate-test_2' ) );

		// The component should have been loaded.
		$this->assertTrue(
			function_exists( 'wordpoints_test_component_function_lllll' )
		);

		$components->deactivate( 'test_2' );

		$this->assertFalse( $components->is_active( 'test_2' ) );
		$this->assertArrayNotHasKey( 'test_2', $components->get_active() );
		$this->assertSame( 1, did_action( 'wordpoints_component_deactivate-test_2' ) );
	}

	/**
	 * Test that an unregistered component can't be activated.
	 *
	 * @since 1.0.1
	 *
	 * @covers WordPoints_Components::activate
	 */
	public function test_activation_fails_if_not_registered() {

		$this->assertFalse( WordPoints_Components::instance()->activate( 'not_registered' ) );
	}

	/**
	 * Test that all components are returned by get().
	 *
	 * @since 1.10.0
	 *
	 * @covers WordPoints_Components::get
	 */
	public function test_get() {

		$components = WordPoints_Components::instance()->get();

		$this->assertInternalType( 'array', $components );
		$this->assertArrayHasKey( 'points', $components );
		$this->assertInternalType( 'array', $components['points'] );
	}

	/**
	 * Test getting the data for a component.
	 *
	 * @since 1.10.0
	 *
	 * @covers WordPoints_Components::get_component
	 */
	public function test_get_component() {

		$component = WordPoints_Components::instance()->get_component( 'points' );

		$this->assertInternalType( 'array', $component );
	}

	/**
	 * Test getting the data for an unregistered component.
	 *
	 * @since 1.10.0
	 *
	 * @covers WordPoints_Components::get_component
	 */
	public function test_get_unregistered_component() {

		$this->assertFalse(
			WordPoints_Components::instance()->get_component( 'unregistered' )
		);
	}
}

// EOF
